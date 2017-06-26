<?php

/**
 * Plugin Name: Resizefly
 * Description: Dynamically resize your images on the fly
 * Plugin URI:  http://resizefly.com/
 * Version:     1.3.5
 * Author:      alpipego
 * Author URI:  http://alpipego.com/
 * Text Domain: resizefly
 * GitHub Plugin URI: https://github.com/alpipego/resizefly
 * GitHub Branch: develop
 */

// PHP 5.2 compatible version check
require_once dirname( __FILE__ ) . '/version-check.php';
$check = new RzfVersionCheck( __FILE__ );

use Alpipego\Resizefly\Autoload;
use Alpipego\Resizefly\Plugin;
use Alpipego\Resizefly\Upload\Dir;
use Alpipego\Resizefly\Upload\DuplicateOriginal;

if ( ! $check->errors() ) {
	// add autoloader
	require_once __DIR__ . '/src/Autoload.php';
	new Autoload();

	// register user added image sizes
	require_once __DIR__ . '/actions/register-image-sizes.php';

	// handle image resizing
	require_once __DIR__ . '/actions/template-redirect.php';

	// setup the plugin after
	add_action( 'plugins_loaded', function () use ( $check ) {
		$plugin = new Plugin();

		$plugin->loadTextdomain( __DIR__ . '/languages' );

		$plugin['path']     = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR;
		$plugin['url']      = plugin_dir_url( __FILE__ );
		$plugin['basename'] = plugin_basename( __FILE__ );
		$plugin['version']  = '1.3.5';

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
			return new Dir();
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
	} );
}
