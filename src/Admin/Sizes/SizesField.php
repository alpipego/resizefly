<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25.06.2017
 * Time: 12:24
 */

namespace Alpipego\Resizefly\Admin\Sizes;


use Alpipego\Resizefly\Admin\AbstractOption;
use Alpipego\Resizefly\Admin\OptionInterface;

class SizesField extends AbstractOption implements OptionInterface {

	/**
	 * RestrictSizesField constructor.
	 *
	 * @param string $page
	 * @param string $section
	 * @param string $pluginPath
	 */
	public function __construct( $page, $section, $pluginPath ) {
		// set default
		add_action( 'after_setup_theme', function () {
			add_option( 'resizefly_sizes', $this->getRegisteredImageSizes() );
		} );

		// add inline styles
		add_action( 'admin_enqueue_scripts', function () {
			wp_add_inline_style( 'wp-admin', '.rzf-image-sizes th {padding-left:10px;}' );
		} );

		$this->optionsField = [
			'id'    => 'resizefly_sizes',
			'title' => esc_attr__( 'Image Sizes', 'resizefly' ),
			'args'  => [ 'class' => 'hide-if-no-js', 'label_for' => 'resizefly_sizes_section' ],
		];
		parent::__construct( $page, $section, $pluginPath );
	}

	protected function getRegisteredImageSizes() {
		$intermediate = get_intermediate_image_sizes();
		$additional   = wp_get_additional_image_sizes();
		$sizes        = [];

		foreach ( $intermediate as $size ) {
			if ( ! array_key_exists( $size, $additional ) ) {
				$sizes[ $size ]['width']  = (int) get_option( "{$size}_size_w" );
				$sizes[ $size ]['height'] = (int) get_option( "{$size}_size_h" );
				$sizes[ $size ]['crop']   = (bool) get_option( "{$size}_crop" );
			} elseif ( isset( $additional[ $size ] ) ) {
				$sizes[ $size ] = [
					'width'  => (int) $additional[ $size ]['width'],
					'height' => (int) $additional[ $size ]['height'],
					'crop'   => (bool) $additional[ $size ]['crop'],
				];
			}

			if ( array_key_exists( $size, $sizes ) ) {
				$sizes[ $size ]['active'] = true;
			}
		}

		return $sizes;
	}

	/**
	 * Add a callback to settings field
	 *
	 * @return void
	 */
	public function callback() {
		$args                = $this->optionsField;
		$args['image_sizes'] = $this->getImageSizes();

		$this->includeView( $this->optionsField['id'], $args );
	}

	protected function getImageSizes() {
		$sizes = get_option( 'resizefly_sizes', [] );
		$sizes = array_intersect_key( $sizes, $this->getRegisteredImageSizes() );

		return $this->sortImageSizes( array_merge( $this->getRegisteredImageSizes(), $sizes ) );
	}

	protected function sortImageSizes( $sizes ) {
		// order results
		uasort( $sizes, function ( $a, $b ) {
			if ( $a['width'] === $b['width'] ) {
				return $a['height'] - $b['height'];
			}

			return $a['width'] - $b['width'];
		} );

		return $sizes;
	}

	/**
	 * Sanitize values added to this settings field
	 *
	 * @param array $sizes
	 *
	 * @return array
	 * @internal param mixed $value value to sanititze
	 *
	 */
	public function sanitize( $sizes ) {
		// cast types
		foreach ( $sizes as $name => &$size ) {
			$size['width']  = (int) $size['width'];
			$size['height'] = (int) $size['height'];
			$size['crop']   = (bool) $size['crop'];
			$size['active'] = isset( $size['active'] );
		}

//		return $this->getRegisteredImageSizes();
		return $sizes;
	}
}
