<?php
/**
 * Main image resize handling through 404 template redirect
 * included in main plugin file
 * @var Plugin $plugin
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26.06.2017
 * Time: 16:53
 */

use Alpipego\Resizefly\Image\Editor as ImageEditor;
use Alpipego\Resizefly\Image\Handler as ImageHandler;
use Alpipego\Resizefly\Image\Image;
use Alpipego\Resizefly\Plugin;

add_action( 'template_redirect', function () use ( $plugin ) {
	if ( ! is_404() ) {
		return;
	}

	$requested = urldecode( $_SERVER['REQUEST_URI'] );
	if ( strpos( $plugin['cache_url'], $requested ) === 0 ) {
		$requested = str_replace( $plugin['cache_url'], $plugin['uploads']->getUploads()['baseurl'], $requested );
	}

	if ( preg_match( '/(.*?)-([0-9]+)x([0-9]+)@?(\d)?\.(jpe?g|png|gif)/i', $requested, $matches ) ) {

		$plugin['requested_file'] = $matches;

		// get the correct path ("regardless" of WordPress installation path etc)
		$plugin['image'] = function ( $plugin ) {
			return new Image( $plugin['requested_file'], $plugin['uploads'], get_bloginfo( 'url' ), $plugin['cache_path'], $plugin['duplicates_path'] );
		};
		/** @var Image $image */
		$image = $plugin['image'];

		if ( ! file_exists( $image->getOriginal() ) ) {
			status_header( '404' );
			@include_once get_404_template();

			exit;
		}

		if ( ! file_exists( $image->getDuplicate() ) ) {
			$plugin['duplicate_original']->rebuild( $image->getOriginal() );
		}

		// get wp image editor and handle errors
		$plugin['wp_image_editor'] = wp_get_image_editor( $image->getDuplicate() );
		if ( is_wp_error( $plugin['wp_image_editor'] ) ) {
			status_header( '404' );
			@include_once get_404_template();

			exit;
		}

		// create image editor wrapper instance
		$plugin['image_editor'] = function ( $plugin ) {
			return new ImageEditor( $plugin['wp_image_editor'] );
		};

		// create image handling instance
		$plugin['image_handler'] = function ( $plugin ) {
			return new ImageHandler( $plugin['image'], $plugin['image_editor'], $plugin['cache_path'], $plugin['duplicates_path'] );
		};

		$plugin->run();
	}
} );
