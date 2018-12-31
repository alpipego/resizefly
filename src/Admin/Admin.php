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
        add_filter('plugin_action_links_'.$this->basename, [$this, 'addActionLinks']);
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
        $links[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', get_admin_url(null, 'upload.php?page=resizefly'),
            __('Resizefly Settings', 'resizefly')
        );

        return $links;
    }
}
