<?php
/**
 * Main image resize handling through 404 template redirect
 * included in main plugin file
 * @var Plugin $plugin
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26.06.2017
 * Time: 16:53
 */

use Alpipego\Resizefly\Image\Handler;
use Alpipego\Resizefly\Image\Image;
use Alpipego\Resizefly\Plugin;
use Alpipego\Resizefly\Upload\DuplicateOriginal;

add_action('template_redirect', function () use ($plugin) {
    if (! is_404()) {
        return;
    }

    $requested = urldecode($_SERVER['REQUEST_URI']);
    if (strpos($plugin->get('options.cache.url'), $requested) === 0) {
        $requested = str_replace($plugin->get('config.cache.url'), $plugin->get('uploads')->getBaseUrl(), $requested);
    }

    if (preg_match($plugin->get('config.imgregex'), $requested, $matches)) {
        $plugin['request.file'] = $matches;
        $plugin->addDefiniton($plugin->get('config.path') . '/app/config/image.php');

        /** @var Image $image */
        $image = $plugin->get(Image::class);
        $image->setImage($plugin['request.file']);

        if (! file_exists($image->getOriginalPath())) {
            \Alpipego\Resizefly\throw404();
        }

        // check if to resize from duplicate
        if ((bool)apply_filters('resizefly_smaller_image', true)) {
            if (! file_exists($image->getDuplicatePath())) {
                $plugin->get(DuplicateOriginal::class)->rebuild($image->getOriginalPath());
            }

            // get wp image editor and handle errors
            $plugin['wp_image_editor'] = wp_get_image_editor($image->getDuplicatePath());
        } else {
            $plugin['wp_image_editor'] = wp_get_image_editor($image->getOriginalPath());
        }

        if (is_wp_error($plugin['wp_image_editor'])) {
            \Alpipego\Resizefly\throw404();
        }
        
        // check if image size is allowed
        if ((bool)get_option('resizefly_restrict_sizes', apply_filters('resizefly_restrict_sizes', true))) {
            if (! $plugin->get(Handler::class)->allowedImageSize(get_option('resizefly_sizes', []))) {
                \Alpipego\Resizefly\throw404();
            }
        }

        $plugin->run();
    }
});
