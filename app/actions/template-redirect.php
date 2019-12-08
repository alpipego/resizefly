<?php
/**
 * Main image resize handling through 404 template redirect.
 */

use Alpipego\Resizefly\Image\Image;
use Alpipego\Resizefly\Plugin;
use function Alpipego\Resizefly\throw404;
use Alpipego\Resizefly\Upload\DuplicateOriginal;

/* @var Plugin $plugin */
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
            throw404();
        }

        // check if to resize from duplicate
        /** @var DuplicateOriginal $duplicate */
        $duplicate         = $plugin->get('Alpipego\Resizefly\Upload\DuplicateOriginal');
        $bigImageThreshold = $duplicate->setImageSizeThreshold([$image->getWidth(), $image->getHeight()], $image->getOriginalPath());

        if ((bool) apply_filters('resizefly/duplicate', true) && $matches['width'] <= $bigImageThreshold) {
            if (file_exists($image->getDuplicatePath())) {
                $plugin['wp_image_editor'] = wp_get_image_editor($image->getDuplicatePath());
            } else {
                $plugin->get('Alpipego\Resizefly\Async\Queue\Queue')->resolve($plugin->get('Alpipego\Resizefly\Upload\DuplicateOriginal'), $image->getOriginalPath());
                $plugin['wp_image_editor'] = wp_get_image_editor($image->getOriginalPath());
            }
        } else {
            $plugin['wp_image_editor'] = wp_get_image_editor($image->getOriginalPath());
        }

        if (is_wp_error($plugin['wp_image_editor'])) {
            throw404();
        }

        // check if image size is allowed
        if ((bool) get_option('resizefly_restrict_sizes', apply_filters('resizefly/restrict_sizes', true))) {
            if (! $plugin->get('Alpipego\Resizefly\Image\Handler')->allowedImageSize(get_option('resizefly_sizes', []))) {
                throw404();
            }
        }

        $plugin->run();
    }
});
