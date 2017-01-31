<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/09/16
 * Time: 18:05
 */

namespace Alpipego\Resizefly\Upload;

use WP_Image_Editor;
use Imagick;

/**
 * Class DuplicateOriginal
 * @package Alpipego\Resizefly\Upload
 */
class DuplicateOriginal {
	/**
	 * @var array
	 */
	private $uploads;

	/**
	 * @var int
	 */
	private $recursion = 0;

	/**
	 * DuplicateOriginal constructor.
	 *
	 * @param Dir $uploads
	 * @param string $duplicateDir
	 */
	public function __construct( Dir $uploads, $duplicateDir ) {
		$this->uploads = $uploads->getUploads();
		$this->path    = \trailingslashit( \trailingslashit( $this->uploads['basedir'] ) . $duplicateDir );
	}

	/**
	 * DI run method
	 * register hooks/filters
	 */
	public function run() {
		if ( $this->dirExists() ) {
			\add_filter( 'wp_generate_attachment_metadata', [ $this, 'generateMeta' ], 11, 2 );
		}

		\add_action( 'delete_attachment', [ $this, 'delete' ] );
	}

	/**
	 * Check if the directory exists (and is writeable), try to create it
	 *
	 * @return bool
	 */
	private function dirExists() {
		$permissions = is_writeable( $this->path );

		if ( ! is_dir( $this->path ) && $permissions ) {
			$permissions = mkdir( $this->path, 0755, true );
		}

		if ( ! $permissions ) {
			\add_action( 'admin_init', function () {
				\add_action( 'admin_notices', function () {
					echo '<div class="error"><p>';
					printf( __( 'The directory %s is not writeable by ResizeFly. Please correct the permissions.', 'resizefly' ), '<code>' . $this->path . '</code>' );
					echo '</p></div>';
				} );
			} );
		}

		return $permissions;
	}

	/**
	 * Rebuild the image (if it is missing)
	 *
	 * @param string $image image URL
	 */
	public function rebuild( $image ) {
		$this->create( $image );
	}

	/**
	 * Duplicate the image
	 *
	 * @param string $image image URL
	 *
	 * @return bool
	 */
	protected function create( $image ) {
		$editor = \wp_get_image_editor( $image );
		if ( ! \is_wp_error( $editor ) ) {
			$duplicate = str_replace( \trailingslashit( $this->uploads['basedir'] ), $this->path, $image );

			if ( (bool) \apply_filters( 'resizefly_smaller_image', true ) && method_exists( $editor, 'getImagick' ) ) {
				$sizes  = $editor->get_size();
				$larger = false;
				foreach ( $sizes as $size ) {
					if ( $size > (int) \apply_filters( 'resizefly_smaller_image_threshold', 1200 ) ) {
						$larger = true;
						break;
					}
				}
				if ( $larger && $this->calculateMemory( $sizes, $editor ) ) {
					$editor->getImagick()->blurImage( 1, .5 );
				}
			}

			$editor->set_quality( 70 );

			if ( method_exists( $editor, 'getImagick' ) ) {
				$editor->getImagick()->stripImage();
			}

			// check if image could be saved
			$save = $editor->save( $duplicate );
			if ( \is_wp_error( $save ) ) {
				// delete the zero byte file
				if ( file_exists( $duplicate ) ) {
					unlink( $duplicate );
				}

				// try setting resources and try once more
				if ( $this->trySettingResources( $editor ) && $this->recursion < 1 ) {
					$this->recursion ++;

					$this->create( $image );
				}
			}

			return ! \is_wp_error( $save );
		}

		return false;
	}

	/**
	 * Calculate the memory Imagick will need based on amount of pixels
	 *
	 * @param array $sizes ['width', 'height']
	 * @param \WP_Image_Editor $editor
	 *
	 * @return bool
	 */
	private function calculateMemory( array $sizes, WP_Image_Editor $editor ) {
		$bytesImage   = $sizes['width'] * $sizes['height'] * 64;
		$bytesImagick = $editor->getImagick()->getResourceLimit( Imagick::RESOURCETYPE_MEMORY );

		return $bytesImage < $bytesImagick;
	}

	/**
	 * Try tweaking the resources to save an image (that could not be saved before)
	 * @param WP_Image_Editor $editor
	 *
	 * @return bool
	 */
	private function trySettingResources( WP_Image_Editor $editor ) {
		if ( method_exists( $editor, 'getImagick' ) ) {
			$editor->getImagick()->setResourceLimit( Imagick::RESOURCETYPE_AREA, 1 );

			return true;
		}

		return false;
	}

	/**
	 * Action method
	 *
	 * @param $metadata
	 * @param $attId
	 *
	 * @return mixed
	 */
	public function generateMeta( $metadata, $attId ) {
		$this->create( \get_attached_file( $attId ) );

		return $metadata;
	}

	/**
	 * Delete the duplicate if original is deleted
	 *
	 * @param integer $id Attachment Id
	 */
	public function delete( $id ) {
		$file = str_replace( \trailingslashit( $this->uploads['basedir'] ), $this->path, \get_attached_file( $id ) );
		if ( file_exists( $file ) ) {
			unlink( $file );
		}
	}
}
