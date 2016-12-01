<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 21/07/16
 * Time: 17:54
 */

namespace Alpipego\Resizefly\Admin;


/**
 * Class PathField
 * @package Alpipego\Resizefly\Admin
 */
class PathField extends AbstractOption implements OptionInterface {
	/**
	 * PathField constructor.
	 *
	 * {@inheritDoc}
	 */
	public function __construct( $page, $section, $plugin ) {
		$this->optionsField = [
			'id'    => 'resizefly_resized_path',
			'title' => \esc_attr__( 'Path for resized images', 'resizefly' ),
		];
		parent::__construct( $page, $section, $plugin );
	}

	/**
	 * Include view
	 *
	 * Check if the provided path is writeable - pass permissions back to view
	 */
	public function callback() {
		$uploadDir   = \wp_upload_dir( null, false );
		$path        = \trailingslashit( \trailingslashit( $uploadDir['basedir'] ) . \get_option( 'resizefly_resized_path', 'resizefly' ) );
		$permissions = is_writeable( $path );

		if ( ! is_dir( $path ) ) {
			$permissions = mkdir( $path, 0755, true );
		}

		$this->includeView( $this->optionsField['id'], array_merge( $this->optionsField, [
			'path'        => $path,
			'permissions' => $permissions
		] ) );
	}

	/**
	 * Remove slashes from path fragment only use last fragment if longer
	 *
	 * @param string $path The user submitted path
	 *
	 * @return string Path fragment to save
	 */
	public function sanitize( $path ) {
		$path         = \sanitize_text_field( $path );
		$previousPath = \get_option( 'resizefly_resized_path', 'resizefly' );

		if ( strpos( $path, '/' ) !== false ) {
			$pathArr = explode( '/', $path );
			$pathArr = array_filter( $pathArr, function ( $value ) {

				return ! empty( $value );
			} );
			$path    = end( $pathArr );
			stripslashes( $path );
		}

		if ( $path !== $previousPath ) {
			$renamedPath = $this->renameDir( $path );

			if ( $renamedPath ) {
				$path = $renamedPath;
			} else {
				\add_settings_error( $this->optionsField['id'], 'resizefly-dir-exists', __( 'This directory already exists. Please remove the directory manually and try setting it again.', 'resizefly' ) );
				$path = $previousPath;
			}
		}

		return $path;
	}

	/**
	 * Rename the cache directory (if not exists)
	 *
	 * @param string $path The new cache dir name
	 *
	 * @return string
	 */
	private function renameDir( $path ) {
		$uploadDir = \wp_upload_dir( null, false );
		$oldPath   = \trailingslashit( \trailingslashit( $uploadDir['basedir'] ) . \get_option( 'resizefly_resized_path', 'resizefly' ) );
		$newPath   = \trailingslashit( \trailingslashit( $uploadDir['basedir'] ) . $path );

		if ( ! is_dir( $newPath ) ) {
			rename( $oldPath, $newPath );

			return $path;
		}

		return '';
	}

}
