<?php

namespace Alpipego\DynamicImage;

//use Alpipego\DynamicImage\Autoload;

/**
 * Plugin Name: Dynamic Image Resizer
 * Plugin URI:  https://alpipego.com/
 * Version:     1.0.0
 * Author:      Alex
 * Author URI:  http://alpipego.com/
 */

require_once __DIR__ . '/src/Autoload.php';

new Autoload(__DIR__);

\add_action('plugins_loaded', function() {
    $plugin = new Plugin();

    $plugin['path'] = realpath(\plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR;
    $plugin['url'] = \plugin_dir_url(__FILE__);
    $plugin['version'] = '1.0.0';
    $plugin->run();
});
