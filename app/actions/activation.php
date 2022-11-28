<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 1.11.2017
 * Time: 12:21.
 *
 * @var \Alpipego\Resizefly\Plugin $plugin
 */

// try to move thumbnail files
use Alpipego\Resizefly\Admin\Sizes\SizesField;
use Alpipego\Resizefly\Common\Psr\Container\ContainerExceptionInterface;
use Alpipego\Resizefly\Common\Psr\Container\NotFoundExceptionInterface;

if (version_compare(get_option('resizefly_version'), $plugin['config.version'], '<')) {
    try {
        $plugin->get('Alpipego\Resizefly\Upload\Cache')->warmUpAll();
    } catch (NotFoundExceptionInterface $e) {
    } catch (ContainerExceptionInterface $e) {
    }

    // update the version option
    add_option('resizefly_version_initial', $plugin['config.version']);
    update_option('resizefly_version', $plugin['config.version']);
}

/** @var SizesField $imageSizes */
$imageSizes = $plugin->get('Alpipego\Resizefly\Admin\Sizes\SizesField');
$imageSizes->updateImageSizes(true);
