<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 31.10.2017
 * Time: 10:42
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// simple uninstallation for now
$options = get_option( 'resizefly_options' );

global $wp_filesystem;

foreach ( [ $options['cache']['path'], $options['duplicates']['path'] ] as $dir ) {
	if ( is_dir( $dir ) ) {
		$wp_filesystem->rmdir( $dir, true );
	}
}

// delete database options
delete_option( 'resizefly_options' );
delete_option( 'resizefly_restrict_sizes' );
delete_option( 'resizefly_sizes' );
delete_option( 'resizefly_user_sizes' );
delete_option( 'resizefly_resized_path' );
delete_option( 'resizefly_version' );
