<?php
/**
 * Run after resizefly has been updated.
 *
 * @var Plugin $plugin
 */
use Alpipego\Resizefly\Plugin;

add_action('upgrader_process_complete', function ($upgrader, array $options) use ($plugin) {
    if ('update' !== $options['action'] || 'plugin' !== $options['type']) {
        return;
    }

    if (! in_array($plugin->get('config.path'), $options['plugins'], true)) {
        return;
    }

    require_once __DIR__.'/../queue-tables.php';

    update_option('resizefly_version', $plugin->get('config.version'));
}, 10, 2);
