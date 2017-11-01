<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 1.11.2017
 * Time: 12:21
 *
 * @var \Alpipego\Resizefly\Plugin $plugin
 */

// try to move thumbnail files
if (version_compare(get_option('resizefly_version'), $plugin['config.version'], '<')) {
    $plugin
        ->get(\Alpipego\Resizefly\Upload\Cache::class)
        ->populateOnInstall(
            $plugin->get(\Alpipego\Resizefly\Image\Image::class),
            $plugin->get(\Alpipego\Resizefly\Admin\Cache\PathField::class)->getId()
        );
    // update the version option
    update_option('resizefly_version', $plugin['config.version']);
}
