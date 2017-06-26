<?php
/**
 * Register image sizes added by user in backend
 * included in main plugin file
 * @var Plugin $plugin
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26.06.2017
 * Time: 17:01
 */

use Alpipego\Resizefly\Plugin;

add_action( 'after_setup_theme', function () use ( $plugin ) {
	$userSizes = get_option( 'resizefly_user_sizes', [] );
	if ( ! empty( $userSizes ) ) {
		foreach ( $userSizes as $size ) {
			add_image_size( $size['name'], (int) $size['width'], (int) $size['height'], (bool) $size['crop'] );
		}
	}
} );

