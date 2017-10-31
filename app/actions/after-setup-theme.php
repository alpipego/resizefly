<?php
/**
 * Register image sizes added by user in backend
 * included in main plugin file
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26.06.2017
 * Time: 17:01
 */

add_action( 'after_setup_theme', function () {
	$userSizes = get_option( 'resizefly_user_sizes', [] );
	if ( ! empty( $userSizes ) ) {
		foreach ( $userSizes as $size ) {
			if ( !is_array( $size['crop'] ) || count( $size['crop'] ) !== 2 ) {
				$size['crop'] = (bool) $size['crop'];
			}
			add_image_size( $size['name'], (int) $size['width'], (int) $size['height'], $size['crop'] );
		}
	}
} );

