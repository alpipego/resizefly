<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 31.10.2017
 * Time: 10:42.
 */
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// simple uninstallation for now
$options = get_option('resizefly_options');

global $wp_filesystem, $wpdb;

foreach ([$options['cache']['path'], $options['duplicates']['path']] as $dir) {
    if (is_dir($dir)) {
        $wp_filesystem->rmdir($dir, true);
    }
}

// delete database options
$options = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'resizefly_%';");
foreach ($options as $option) {
    delete_option($option);
}
