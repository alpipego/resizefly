<?php
/**
 * Setup plugin, add options, add tables.
 *
 * @var Plugin
 */
use Alpipego\Resizefly\Common\Psr\Container\ContainerExceptionInterface;
use Alpipego\Resizefly\Common\Psr\Container\NotFoundExceptionInterface;
use Alpipego\Resizefly\Plugin;

if (version_compare(get_option('resizefly_version'), $plugin['config.version'], '<')) {
    // try to move thumbnail files
    try {
        $plugin->get('Alpipego\Resizefly\Upload\Cache')->warmUpAll();
    } catch (NotFoundExceptionInterface $e) {
    } catch (ContainerExceptionInterface $e) {
    }

    require_once __DIR__.'/../queue-tables.php';

    // update the version option
    add_option('resizefly_version_initial', $plugin['config.version']);
    update_option('resizefly_version', $plugin['config.version']);
}
