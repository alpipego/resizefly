<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 14:23
 */

namespace Alpipego\Resizefly\Admin;

class PurgeCacheField extends AbstractOption implements OptionInterface {
	public function __construct( $page, $section, $pluginPath ) {
		$this->optionsField = [
			'id'    => 'resizefly_purge_cache',
			'title' => __( 'Purge Cache', 'resizefly' ),
			'args'  => ['class' => 'hide-if-no-js'],
		];
		parent::__construct( $page, $section, $pluginPath );
	}

	public function callback() {
		$path = \get_option( 'resizefly_resized_path', 'resizefly' );
		if ( $path ) {
			$uploadDir = \wp_upload_dir( null, false );
			$path      = \trailingslashit( \trailingslashit( $uploadDir['basedir'] ) . $path );
		}

		$args = array_merge( $this->optionsField, [ 'path' => $path ] );
		$this->includeView( $this->optionsField['id'], $args );
	}

	public function sanitize( $value ) {
		return $value;
	}

}
