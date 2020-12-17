<?php

/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 15/06/16
 * Time: 19:11
 */
class Resizefly_Version_Check {
	const PHP_VERSION = '5.6';
	const WP_VERSION = '4.7';

	private $versions = array(
		'php'    => true,
		'wp'     => true,
		'editor' => true,
    );
	private $file;

	public function __construct( $file ) {
		$this->file = $file;
		$this->run();
	}

	private function run() {
		$this->checkVersions();

		if ( $this->errors() ) {
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			add_action( 'admin_notices', [ $this, 'notice' ] );
			add_action( 'plugins_loaded', [ $this, 'deactivate' ] );
			add_action( 'admin_init', [ $this, 'deactivate' ] );
			add_action( 'init', [ $this, 'deactivate' ] );
			register_activation_hook( $this->file, [ $this, 'deactivate' ] );
		}
	}

	private function checkVersions() {
		$this->checkPHPVersion();
		$this->checkWPVersion();
		$this->checkImageEditor();
	}

	private function checkPHPVersion() {
		if ( version_compare( PHP_VERSION, self::PHP_VERSION, '<' ) ) {
			$this->versions['php'] = false;
		}

		return $this->versions;
	}

	private function checkWPVersion() {
		if ( version_compare( get_bloginfo( 'version' ), self::WP_VERSION, '<=' ) ) {
			$this->versions['wp'] = false;
		}

		return $this->versions;
	}

	private function checkImageEditor() {
		if ( $this->versions['wp'] && $this->versions['php'] ) {
			$editor = wp_get_image_editor( __DIR__ . '/rzf-test.jpg', [ 'mime_type' => 'image/jpeg' ] );

			if ( is_wp_error( $editor ) ) {
				$this->versions['editor'] = false;
			}
		}

		return $this->versions;
	}

	public function errors() {
		return in_array( false, $this->versions, true );
	}

	public function notice() {
		$notices = [
			'php'    => sprintf(
				__( 'Resizefly requires at least PHP %s to function properly. Please upgrade PHP to use Resizefly.',
					'resizefly'
				),
				self::PHP_VERSION
			),
			'wp'     => sprintf(
				__( 'Resizefly requires at least WordPress %s to function properly. Please upgrade WordPress to use Resizefly.',
					'resizefly'
				),
				self::WP_VERSION
			),
			'editor' => __( 'Resizefly could not find an Image Editor. Please make sure you have either GD or Imagick installed.',
				'resizefly'
			),
		];

		foreach ( $this->versions as $key => $version ) {
			if ( ! $version ) {
				printf( '<div class="error"><p>%s</p></div>', esc_html( $notices[ $key ] ) );
			}
		}

	}

	public function deactivate() {
		deactivate_plugins( plugin_basename( $this->file ), true );
	}
}
