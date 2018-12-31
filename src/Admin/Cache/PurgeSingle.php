<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 01.11.18
 * Time: 11:28.
 */

namespace Alpipego\Resizefly\Admin\Cache;

use Alpipego\Resizefly\Upload\Cache;
use WP_Post;

class PurgeSingle
{
    private $action = 'resizefly_purge_single';
    private $pluginUrl;
    private $cache;

    public function __construct(Cache $cache, $pluginUrl)
    {
        $this->cache     = $cache;
        $this->pluginUrl = $pluginUrl;
    }

    public function run()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);

        add_action("wp_ajax_{$this->action}", [$this, 'ajaxPurge']);
        add_action('delete_attachment', [$this, 'purge']);

        add_filter('media_row_actions', [$this, 'addRowAction'], 10, 2);
        add_filter('attachment_fields_to_edit', [$this, 'addAttachmentAction'], 10, 2);
    }

    public function addRowAction($actions, WP_Post $post)
    {
        if (! current_user_can(apply_filters('resizefly/delete_attachment_cap', 'delete_posts'))) {
            return $actions;
        }

        $actions[$this->action] = sprintf(
            '<button class="hide-no-js button-link rzf-purge-single" data-nonce="%s" data-postid="%s">%s</button>
<span class="help" style="color: #32373c"></span>',
            wp_create_nonce($this->action),
            $post->ID,
            esc_html__('Purge Cache', 'resizefly')
        );

        return $actions;
    }

    public function addAttachmentAction($fields, WP_Post $post)
    {
        if (! current_user_can(apply_filters('resizefly/delete_attachment_cap', 'delete_posts'))) {
            return $fields;
        }

        $fields['resizefly_purge_single'] = [
            'label' => esc_html__('Resizefly Cache', 'resizefly'),
            'input' => 'html',
            'html'  => sprintf(
                '<button class="hide-no-js button-secondary rzf-purge-single" data-nonce="%s" data-postid="%s">%s</button>',
                wp_create_nonce($this->action),
                $post->ID,
                esc_html__('Purge Cache')
            ),
            'helps' => esc_html__('Delete all generated sizes for this image.'),
        ];

        return $fields;
    }

    public function purge($id)
    {
        return $this->cache->purgeSingle($id);
    }

    public function ajaxPurge()
    {
        if (check_ajax_referer($this->action)) {
            wp_send_json(['files' => $this->purge((int) $_REQUEST['id'])]);
        }

        wp_send_json('fail');
    }

    public function enqueueAssets($page)
    {
        if (in_array($page, ['post.php', 'upload.php'], true)) {
            wp_enqueue_script('resizefly-purge-single', sprintf(
                '%sjs/resizefly-purge-single.%sjs',
                $this->pluginUrl,
                defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : 'min.'
            ), ['jquery'], '3.0.0', true);

            wp_localize_script('resizefly-purge-single', 'resizefly', [
                'purge_action' => $this->action,
                'purge_result' => sprintf(
                    esc_html__('%s file(s) have been removed.', 'resizefly'),
                    '<span class="resizefly-files"></span>'
                ),
                'purge_empty'  => __('No files were removed because the cache was already empty.', 'resizefly'),
            ]);
        }
    }
}
