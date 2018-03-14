<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 14.03.2018
 * Time: 14:56
 */

add_action( 'upgrader_process_complete', function ( $upgrader, array $options ) use ( $plugin ) {
	if ( $options['action'] !== 'update' || $options['type'] !== 'plugin' ) {
		return;
	}

	if ( ! in_array( $plugin->get( 'config.path' ), $options['plugins'], true ) ) {
		return;
	}

	update_option( 'resizefly_version', $plugin->get( 'config.version' ) );
}, 10, 2 );
