<?php

/**
 * Plugin Name: Resizefly
 * Description: Dynamically resize your images on the fly
 * Plugin URI:  http://resizefly.com/
 * Version:     1.3.3
 * Author:      alpipego
 * Author URI:  http://alpipego.com/
 * Text Domain: resizefly
 * GitHub Plugin URI: https://github.com/alpipego/resizefly
 * GitHub Branch: develop
 */

require_once dirname( __FILE__ ) . '/version-check.php';
$check = new RzfVersionCheck( __FILE__ );

use Alpipego\Resizefly\Autoload;
use Alpipego\Resizefly\Plugin;
use Alpipego\Resizefly\Image\Editor as ImageEditor;
use Alpipego\Resizefly\Image\Handler as ImageHandler;
use Alpipego\Resizefly\Image\Image;
use Alpipego\Resizefly\Image\Stream;
use Alpipego\Resizefly\Upload\Dir;
use Alpipego\Resizefly\Upload\DuplicateOriginal;


if ( ! $check->errors() ) {
	require_once __DIR__ . '/src/Autoload.php';
	new Autoload();

	\add_action( 'plugins_loaded', function () use ( $check ) {
		$plugin = new Plugin();

		$plugin->loadTextdomain( __DIR__ . '/languages' );

		$plugin['path']     = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR;
		$plugin['url']      = plugin_dir_url( __FILE__ );
		$plugin['basename'] = plugin_basename( __FILE__ );
		$plugin['version']  = '1.3.3';

		// filterable dir values
		$plugin['cache_suffix']     = get_option( 'resizefly_resized_path', 'resizefly' );
		$plugin['duplicate_suffix'] = apply_filters( 'resizefly_duplicate_dir', 'resizefly-duplicate' );

		// filter for addons to register themselves
		$plugin['addons'] = apply_filters( 'resizefly_addons', [] );

		foreach ( $plugin['addons'] as $addonName => $addon ) {
			add_filter( "resizefly_plugin_{$addonName}", function () use ( $plugin ) {
				return $plugin;
			} );
		}

		// wordpress uploads array
		$plugin['uploads'] = function () {
			return new Dir( wp_upload_dir( null, false ) );
		};

		$plugin->extend( 'uploads', function ( $uploads ) {
			/** @var Dir $uploads */
			$uploads->setUploads( wp_upload_dir( null, false ) );

			return $uploads;
		} );

		// set the cache path throughout the plugin
		$plugin['cache_path'] = trailingslashit( $plugin['uploads']->getUploads()['basedir'] ) . trim( $plugin['cache_suffix'], DIRECTORY_SEPARATOR );
		$plugin['cache_url']  = trailingslashit( $plugin['uploads']->getUploads()['baseurl'] ) . trim( $plugin['cache_suffix'], DIRECTORY_SEPARATOR );

		// set the duplicates path
		$plugin['duplicates_path'] = trailingslashit( $plugin['uploads']->getUploads()['basedir'] ) . trim( $plugin['duplicate_suffix'], DIRECTORY_SEPARATOR );

		// Add own implementation to image editors
		add_filter( 'wp_image_editors', function ( $editors ) {
			array_unshift( $editors, '\\Alpipego\\Resizefly\\Image\\EditorImagick' );

			return $editors;
		} );

		// duplicate every uploaded image, so that the cropping happens on an already optimized (i.e. small image)
		$plugin['duplicate_original'] = function ( $plugin ) {
			return new DuplicateOriginal( $plugin['uploads'], $plugin['duplicate_suffix'] );
		};

		if ( is_admin() ) {
			require_once __DIR__ . '/config/admin.php';
		}

		$plugin->run();

		add_action( 'template_redirect', function () use ( $plugin ) {
			if ( ! is_404() ) {
				return;
			}

			if ( preg_match( '/(.*?)-([0-9]+)x([0-9]+)\.(jpe?g|png|gif)/i', urldecode( $_SERVER['REQUEST_URI'] ), $matches ) ) {

				$plugin['requested_file'] = $matches;

				// get the correct path ("regardless" of WordPress installation path etc)
				$plugin['image'] = function ( $plugin ) {
					return new Image( $plugin['requested_file'], $plugin['uploads'], get_bloginfo( 'url' ), $plugin['cache_path'], $plugin['duplicates_path'] );
				};

				if ( ! file_exists( $plugin['image']->getDuplicate() ) ) {
					$plugin['duplicate_original']->rebuild( $plugin['image']->getOriginal() );
				}

				// get wp image editor and handle errors
				$plugin['wp_image_editor'] = wp_get_image_editor( $plugin['image']->getDuplicate() );
				if ( ! file_exists( $plugin['image']->getOriginal() ) || is_wp_error( $plugin['wp_image_editor'] ) ) {
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

				// output stream the resized image
				$plugin['output'] = function ( $plugin ) {
					return new Stream( wp_get_image_editor( $plugin['image_handler']->getImage() ) );
				};

				$plugin->run();
			}
		} );
	} );
}
