<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 21/07/16
 * Time: 17:54
 */

namespace Alpipego\Resizefly\Admin;


class PathField extends AbstractOption implements OptionInterface {
	public function __construct( $page, $section, $plugin ) {
		$this->optionsField = [
			'id'    => 'resizefly_resized_path',
			'title' => esc_attr__( 'Path for resized images', 'resizefly' ),
		];
		parent::__construct( $page, $section, $plugin );
	}

	public function callback() {
		$uploadDir   = \wp_upload_dir( null, false );
		$path        = \trailingslashit( \trailingslashit( $uploadDir['basedir'] ) . \get_option( 'resizefly_resized_path', '' ) );
		$permissions = is_writeable( $path );

		if ( ! is_dir( $path ) ) {
			$permissions = mkdir( $path, 0755, true );
		}

		$this->includeView( $this->optionsField['id'], array_merge( $this->optionsField, [ 'path'        => $path,
		                                                                                   'permissions' => $permissions
		] ) );
	}

	public function sanitize( $path ) {
		if ( strpos( $path, '/' ) !== false ) {
			$pathArr = explode( '/', $path );
			$pathArr = array_filter( $pathArr, function ( $value ) {
				return ! empty( $value );
			} );
			$path    = end( $pathArr );
			stripslashes( $path );
		}

		if ( $path !== get_option( 'resizefly_resized_path', '' ) ) {
			$this->renameDir( $path );
		}

		return $path;
	}

	private function renameDir( $path ) {
		$uploadDir = \wp_upload_dir( null, false );
		$oldPath   = \trailingslashit( \trailingslashit( $uploadDir['basedir'] ) . \get_option( 'resizefly_resized_path', '' ) );
		$newPath   = \trailingslashit( \trailingslashit( $uploadDir['basedir'] ) . $path );

		if (!is_dir($newPath)) {
			rename($oldPath, $newPath);

			return $path;
		}

		return get_option( 'resizefly_resized_path' );
	}

}
