<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 04/08/16
 * Time: 11:46.
 */

namespace Alpipego\Resizefly\Admin;

final class Admin
{
    private $basename;
    private $pluginUrl;

    public function __construct($basename, $pluginUrl)
    {
        $this->basename  = $basename;
        $this->pluginUrl = $pluginUrl;
    }

    public function run()
    {
        add_filter('plugin_action_links_' . $this->basename, [$this, 'addActionLinks']);
        if (!get_transient('rzf_notice_shown')) {
            add_action('admin_notices', [$this, 'deprecationNotice']);
        }
        add_action('wp_ajax_rzf_dismiss_notice', [$this, 'setNoticeTransient']);
    }

    /**
     * Add a link to the settings on plugin page.
     *
     * @param array $links array with existing plugin actions
     *
     * @return array
     */
    public function addActionLinks($links)
    {
        $links[] = sprintf(
            '<a href="%1$s" title="%2$s">%2$s</a>',
            get_admin_url(null, 'upload.php?page=resizefly'),
            __('Resizefly Settings', 'resizefly')
        );

        return $links;
    }

    public function deprecationNotice()
    {
        ?>
        <div class="notice notice-info is-dismissible" id="rzf-deprecation-notice">
            <h2>ResizeFly Deprecation Notice</h2>
            <p>
                The free version of ResizeFly on WordPress.org will no longer be updated or maintained. For now, you can continue using it.
            </p>
            <p>
                I will ask the plugins team to close the plugin before the end of the year. If you want to continue using ResizeFly, please drop me an e-mail at <a href="mailto:hi@resizefly.com?subject=ResizeFly%20Deprecation&body=I%20would%20love%20to%20see%20some%20details%20on%20how%20you've%20been%20using%20ResizeFly.%20It%20would%20also%20be%20great%20if%20you%20included%20a%20URL%20to%20your%20website%20using%20the%20plugin.%20Thank%20you!">hi@resizefly.com</a> to receive a custom offer
                for future versions of the plugin. Thank you for using ResizeFly!</p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
        <script>
            window.addEventListener('load', () => {
                const notice = document.getElementById('rzf-deprecation-notice');
                notice.querySelector('.notice-dismiss').addEventListener('click', () => {
                    const data = new FormData();
                    data.append('action', 'rzf_dismiss_notice');
                    data.append('_ajax_nonce', '<?= wp_create_nonce('rzf_dismiss_notice') ?>');
                    fetch(window.ajaxurl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: data
                    })
                        .finally(response => notice.remove());
                });
            });
        </script>
        <?php
    }

    public function setNoticeTransient()
    {
        check_ajax_referer('rzf_dismiss_notice');
        set_transient('rzf_notice_shown', true, MONTH_IN_SECONDS);
        wp_send_json_success(['transient_set']);
    }
}
