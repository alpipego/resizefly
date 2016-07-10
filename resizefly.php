<?php

/**
 * Plugin Name: Resizefly
 * Description: Dynamically resize your images on the fly
 * Plugin URI:  http://resizefly.com/
 * Version:     1.2.0
 * Author:      alpipego
 * Author URI:  http://alpipego.com/
 * Text Domain: resizefly
 */

require_once dirname( __FILE__ ) . '/version-check.php';

use Alpipego\Resizefly\Image\Editor as ImageEditor;
use Alpipego\Resizefly\Image\Handler as ImageHandler;
use Alpipego\Resizefly\Image\Image;
use Alpipego\Resizefly\Image\Stream;
use Alpipego\Resizefly\Upload\Fake;
use Alpipego\Resizefly\Plugin;
use Alpipego\Resizefly\Autoload;

require_once __DIR__ . '/src/Autoload.php';
new Autoload();

\add_action( 'plugins_loaded', function () {
	$plugin = new Plugin();

	$plugin->loadTextdomain( __DIR__ );

	$plugin['path']    = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR;
	$plugin['url']     = plugin_dir_url( __FILE__ );
	$plugin['version'] = '1.1.4';

	$plugin['addons'] = apply_filters( 'resizefly_addons', [ ] );

	foreach ( $plugin['addons'] as $addonName => $addon ) {
		add_filter( "resizefly_plugin_{$addonName}", function () use ( $plugin ) {
			return $plugin;
		} );
	}

	$plugin['fake'] = function () {
		return new Fake();
	};

	$plugin->run();

	add_action( 'template_redirect', function () use ( $plugin ) {
		if ( ! is_404() ) {
			return;
		}

		$uploadDir     = wp_upload_dir( null, false );
		$suffix        = apply_filters( 'resizefly_resized_path', '' );
		$requestedFile = $_SERVER['REQUEST_URI'];
		if ( ! empty( $suffix ) ) {
			$requestedFile = str_replace( $_SERVER['DOCUMENT_ROOT'], '', str_replace( trailingslashit( $uploadDir['basedir'] ) . trim( $suffix, DIRECTORY_SEPARATOR ), $uploadDir['basedir'], $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'] ) );
		}


		if ( preg_match( '/(.*?)-([0-9]+)x([0-9]+)\.(jpeg|jpg|png|gif)/i', $requestedFile, $matches ) ) {
			$plugin['requested_file'] = $matches;

			// get the correct path ("regardless" of WordPress installation path etc)
			$plugin['image'] = function ( $plugin ) use ( $uploadDir ) {
				return new Image( $plugin['requested_file'], $uploadDir, get_bloginfo( 'url' ) );
			};

			// get wp image editor and handle errors
			$plugin['wp_image_editor'] = wp_get_image_editor( $plugin['image']->original );
			if ( ! file_exists( $plugin['image']->original ) || is_wp_error( $plugin['wp_image_editor'] ) ) {
				status_header( '404' );
				exit;
			}

			// create image editor wrapper instance
			$plugin['image_editor'] = function ( $plugin ) {
				return new ImageEditor( $plugin['wp_image_editor'] );
			};

			// create image handling instance
			$plugin['image_handler'] = function ( $plugin ) {
				return new ImageHandler( $plugin['image'], $plugin['image_editor'] );
			};

			// output stream the resized image
			$plugin['output'] = function ( $plugin ) {
				return new Stream( wp_get_image_editor( $plugin['image_handler']->file ) );
			};

			try {
				$plugin->run();
			} catch ( Exception $e ) {
				error_log( date( 'd.m.Y H:i:s', strtotime( 'now' ) ) . ":\n" . print_r( $e->getMessage(), true ) . "\n", 3, trailingslashit( WP_CONTENT_DIR ) . 'debug.log' );
			}
		}
	} );
} );
