<?php

/**
 * Plugin Name: Resizefly
 * Description: Dynamically resize your images on the fly
 * Plugin URI:  http://resizefly.com/
 * Version:     1.3.5
 * Author:      alpipego
 * Author URI:  http://alpipego.com/
 * Text Domain: resizefly
 */

require_once dirname( __FILE__ ) . '/version-check.php';
$check = new RzfVersionCheck( __FILE__ );

use Alpipego\Resizefly\Image\Editor as ImageEditor;
use Alpipego\Resizefly\Image\Handler as ImageHandler;
use Alpipego\Resizefly\Image\Image;
use Alpipego\Resizefly\Image\Stream;
use Alpipego\Resizefly\Upload\Fake;
use Alpipego\Resizefly\Plugin;
use Alpipego\Resizefly\Autoload;

use Alpipego\Resizefly\Admin\OptionsPage;
use Alpipego\Resizefly\Admin\BasicOptionsSection;
use Alpipego\Resizefly\Admin\PathField;
use Alpipego\Resizefly\Admin\Admin;

if ( ! $check->errors() ) :
	require_once __DIR__ . '/src/Autoload.php';
	new Autoload();

	\add_action( 'plugins_loaded', function () use ( $check ) {
		$plugin = new Plugin();

		$plugin->loadTextdomain( __DIR__ . '/languages' );

		$plugin['path']     = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR;
		$plugin['url']      = plugin_dir_url( __FILE__ );
		$plugin['basename'] = plugin_basename( __FILE__ );
		$plugin['version']  = '1.3.3';

		$plugin['addons'] = apply_filters( 'resizefly_addons', [] );

		foreach ( $plugin['addons'] as $addonName => $addon ) {
			add_filter( "resizefly_plugin_{$addonName}", function () use ( $plugin ) {
				return $plugin;
			} );
		}

		$plugin['fake'] = function () {
			return new Fake();
		};

		// wordpress uploads array
		$plugin['uploads'] = wp_upload_dir( null, false );

		// set the cache path throughout the plugin
		$suffix               = apply_filters( 'resizefly_resized_path', get_option( 'resizefly_resized_path', '' ) );
		$plugin['cache_path'] = trailingslashit( $plugin['uploads']['basedir'] ) . trim( $suffix, DIRECTORY_SEPARATOR );
		$plugin['cache_url']  = trailingslashit( $plugin['uploads']['baseurl'] ) . trim( $suffix, DIRECTORY_SEPARATOR );

		if ( is_admin() ) {
			$plugin['options_page'] = function ( $plugin ) {
				return new OptionsPage( $plugin['path'] );
			};

			$plugin['options_basic_settings'] = function ( $plugin ) {
				return new BasicOptionsSection( $plugin['options_page']->page, $plugin['path'] );
			};

			$plugin['options_field_path'] = function ( $plugin ) {
				return new PathField( $plugin['options_page']->page, $plugin['options_basic_settings']->optionsGroup['id'], $plugin['path'] );
			};

			$plugin['admin'] = function ( $plugin ) {
				return new Admin( $plugin );
			};
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
					return new Image( $plugin['requested_file'], $plugin['uploads'], get_bloginfo( 'url' ), $plugin['cache_url'] );
				};

				// get wp image editor and handle errors
				$plugin['wp_image_editor'] = wp_get_image_editor( $plugin['image']->original );
				if ( ! file_exists( $plugin['image']->original ) || is_wp_error( $plugin['wp_image_editor'] ) ) {
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
					return new ImageHandler( $plugin['image'], $plugin['image_editor'], $plugin['cache_path'] );
				};

				// output stream the resized image
				$plugin['output'] = function ( $plugin ) {
					return new Stream( wp_get_image_editor( $plugin['image_handler']->file ) );
				};

				$plugin->run();
			}
		} );
	} );

endif;
