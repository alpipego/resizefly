<?php
/**
 * Main image resize handling through 404 template redirect
 * included in main plugin file.
 *
 * @var Plugin $plugin
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26.06.2017
 * Time: 16:53
 */

use Alpipego\Resizefly\Image\Image;
use Alpipego\Resizefly\Plugin;
use Alpipego\Resizefly\Upload\DuplicateOriginal;

add_action('template_redirect', function () use ($plugin) {
    if (! is_404()) {
        return;
    }

    $requested = urldecode($_SERVER['REQUEST_URI']);
    if (0 === strpos($plugin->get('options.cache.url'), $requested)) {
        $requested = str_replace($plugin->get('config.cache.url'), $plugin->get('uploads')->getBaseUrl(), $requested);
    }

    $regex = '/(?<file>.*?)-(?<width>[0-9]+)x(?<height>[0-9]+)@(?<density>[0-3])\.(?<ext>jpe?g|png|gif)/i';
    if (preg_match($regex, $requested, $matches)) {
        $plugin['request.file'] = $matches;
        $plugin->addDefiniton($plugin->get('config.path').'/app/config/image.php');

        /** @var Image $image */
        $image = $plugin->get('Alpipego\Resizefly\Image\Image');
        $image->setImage($plugin['request.file']);

        if (! file_exists($image->getOriginalPath())) {
            \Alpipego\Resizefly\throw404();
        }

        // check if to resize from duplicate
        /** @var DuplicateOriginal $duplicate */
        $duplicate         = $plugin->get('Alpipego\Resizefly\Upload\DuplicateOriginal');
        $meta              = $image->getMeta();
        $bigImageThreshold = is_null($meta)
            ? 2560
            : $duplicate->setImageSizeThreshold([$image->getMeta()['width'], $image->getMeta()['height']], $image->getOriginalPath(), $image->getId());

        if ((bool) apply_filters('resizefly/duplicate', true) && $matches['width'] <= $bigImageThreshold) {
            if (! file_exists($image->getDuplicatePath())) {
                $duplicate->rebuild($image->getOriginalPath());
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
        if ((bool) get_option('resizefly_restrict_sizes', apply_filters('resizefly/restrict_sizes', true))) {
            if (! $plugin->get('Alpipego\Resizefly\Image\Handler')->allowedImageSize(get_option('resizefly_sizes', []))) {
                \Alpipego\Resizefly\throw404();
            }
        }

        $plugin->run();
    }
});
