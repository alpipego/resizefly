<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 31.10.2017
 * Time: 10:42
 */

if (! defined('WP_UNINSTALL_PLUGIN') || ! isset($_REQUEST['plugin'])) {
    exit();
}

check_admin_referer( "deactivate-plugin_{$_REQUEST['plugin']}" );

if (version_compare(PHP_VERSION, '5.5', '<')) {
    exit();
}

// simple uninstallation for now
$options = get_option('resizefly_options');

if (is_dir($options['cache']['path'])) {
    unlink($options['cache']['path']);
}

if (is_dir($options['duplicates']['path'])) {
    unlink($options['duplicates']['path']);
}

delete_option('resizefly_options');
delete_option('resizefly_restrict_sizes');
delete_option('resizefly_sizes');
delete_option('resizefly_user_sizes');
delete_option('resizefly_resized_path');
