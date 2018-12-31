<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 21/07/16
 * Time: 17:36.
 */

namespace Alpipego\Resizefly\Admin;

/**
 * Class OptionsPage.
 */
final class OptionsPage extends AbstractPage implements PageInterface
{
    /**
     * @var string PAGE id of this settings page
     */
    const PAGE = 'media_page_resizefly';

    /**
     * @var string SLUG menu slug for this page
     */
    const SLUG = 'resizefly';

    /**
     * @var string url to plugin dir
     */
    private $pluginUrl;

    /**
     * @var string path to plugin dir
     */
    private $pluginPath;

    /**
     * OptionsPage constructor.
     *
     * @param string $pluginPath path to plugin dir
     * @param string $pluginUrl  url to plugin dir
     */
    public function __construct($pluginPath, $pluginUrl)
    {
        $this->pluginPath = $pluginPath;
        $this->pluginUrl  = $pluginUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlug()
    {
        return self::SLUG;
    }

    /**
     * Add the page to admin menu action.
     */
    public function run()
    {
        add_action('admin_menu', [$this, 'addPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Wrapper for add_media_section.
     *
     * Add the page in media section
     */
    public function addPage()
    {
        add_media_page('Resizefly Settings', 'Resizefly', 'manage_options', self::SLUG, [
            $this,
            'callback',
        ]);
    }

    /**
     * Include the view.
     */
    public function callback()
    {
        $sections = array_unique(array_merge([
            self::PAGE => __('ResizeFly', 'resizefly'),
        ], (array) apply_filters('resizefly/admin/sections', [])));

        $args = [
            'sections' => $sections,
        ];

        include $this->pluginPath.'views/page/resizefly.php';
    }

    /**
     * @param string $page current admin page hook
     */
    public function enqueueAssets($page)
    {
        if (self::PAGE === $page) {
            wp_enqueue_script('resizefly-admin', sprintf(
                '%sjs/resizefly-admin.%sjs',
                $this->pluginUrl,
                defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : 'min.'
            ), ['jquery', 'wp-util'], '3.0.0', true);

            wp_add_inline_style('wp-admin', file_get_contents($this->pluginPath.'css/resizefly-admin.css'));

            wp_localize_script('resizefly-admin', 'resizefly', $this->localized);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return self::PAGE;
    }
}
