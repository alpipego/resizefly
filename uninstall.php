<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 31.10.2017
 * Time: 10:42.
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// simple uninstallation for now
$options = get_option('resizefly_options');

/** @var WP_Filesystem_Base $wp_filesystem */
$wp_filesystem = $GLOBALS['wp_filesystem'];
/** @var wpdb $wpdb */
$wpdb = $GLOBALS['wpdb'];

foreach ([$options['cache']['path'], $options['duplicates']['path']] as $dir) {
    if (is_dir($dir)) {
        $wp_filesystem->rmdir($dir, true);
    }
}

// remove queue databases
$before = $wpdb->hide_errors();
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rzf_queue_jobs");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}rzf_queue_failures");
$wpdb->show_errors($before);

// delete database options
$options = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'resizefly_%';");
foreach ($options as $option) {
    delete_option($option);
}
