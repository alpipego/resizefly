<?php

/**
 * Plugin Name: Resizefly
 * Description: Dynamically resize your images on the fly
 * Plugin URI:  http://resizefly.com/
 * Version:     1.3.5
 * Author:      alpipego
 * Author URI:  http://alpipego.com/
 * Text Domain: resizefly
 * GitHub Plugin URI: https://github.com/alpipego/resizefly
 * GitHub Branch: master
 */

// PHP 5.2 compatible version check
require_once dirname(__FILE__) . '/version-check.php';
$check = new Resizefly_Version_Check(__FILE__);

use Alpipego\Resizefly\Plugin;
use Alpipego\Resizefly\Upload\Uploads;

if (! $check->errors()) {
    $classLoader = require_once __DIR__ . '/app/bootstrap.php';
    require_once __DIR__ . '/app/functions.php';

    add_action('plugins_loaded', function () use ($classLoader) {
        $plugin = new Plugin();
        $plugin->addDefiniton(__DIR__ . '/app/config/plugin.php');
        $plugin->loadTextdomain(__DIR__ . '/languages');

        $plugin['config.path']     = trailingslashit(realpath(plugin_dir_path(__FILE__)));
        $plugin['config.url']      = plugin_dir_url(__FILE__);
        $plugin['config.basename'] = plugin_basename(__FILE__);
        $plugin['config.siteurl']  = get_bloginfo('url');
        $plugin['config.version']  = '1.3.5';

        // settings/filterable configuration values
        $plugin['options.cache.suffix']     = get_option('resizefly_resized_path', 'resizefly');
        $plugin['options.duplicate.suffix'] = apply_filters('resizefly_duplicate_dir', 'resizefly-duplicate');

        // set the cache path throughout the plugin
        $plugin['options.cache.path'] = function (Plugin $plugin) {
            return trailingslashit($plugin->get(Uploads::class)
                                          ->getBasePath()) . trim($plugin->get('options.cache.suffix'),
                    DIRECTORY_SEPARATOR);
        };
        $plugin['options.cache.url']  = function (Plugin $plugin) {
            return trailingslashit($plugin->get(Uploads::class)
                                          ->getBaseUrl()) . trim($plugin->get('options.cache.suffix'),
                    DIRECTORY_SEPARATOR);
        };
        // set the duplicates path
        $plugin['options.duplicates.path'] = function (Plugin $plugin) {
            return trailingslashit($plugin->get(Uploads::class)
                                          ->getBasePath()) . trim($plugin->get('options.duplicate.suffix'),
                    DIRECTORY_SEPARATOR);
        };

        $plugin->offsetSet('loader', $classLoader);

        // filter for addons to register themselves
        $plugin->offsetSet('addons', apply_filters('resizefly_addons', []));

        foreach ($plugin->get('addons') as $addonName => $addon) {
            add_filter("resizefly_plugin_{$addonName}", function () use ($plugin) {
                return $plugin;
            });
        }

        // Add own implementation to image editors
        add_filter('wp_image_editors', function (array $editors) {
            array_unshift($editors, '\\Alpipego\\Resizefly\\Image\\EditorImagick');

            return $editors;
        });

        if (is_admin()) {
            $plugin->addDefiniton(__DIR__ . '/app/config/admin.php');
        }

        $plugin->run();

        // register user added image sizes
        require_once __DIR__ . '/app/actions/after-setup-theme.php';

        // check if image size in attachment metadata
        require_once __DIR__ . '/app/actions/wp-get-attachment-src.php';

        // handle image resizing
        require_once __DIR__ . '/app/actions/template-redirect.php';

    });
}

