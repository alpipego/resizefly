<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 12:07 PM
 */

namespace Alpipego\Resizefly\Image;

use WP_Image_Editor;

/**
 * Wrapper for `WP_Image_Editor`
 * @package Alpipego\Resizefly\Image
 */
class Editor {
	/**
	 * @var WP_Image_Editor
	 */
	private $editor;

	/**
	 * inject image editor
	 *
	 * @param WP_Image_Editor $editor
	 */
	public function __construct( WP_Image_Editor $editor ) {
		$this->editor = $editor;
	}

	/**
	 * Get the image ratio
	 *
	 * @param string $aspect if width or height should be calculated; accepts 'width', 'w', 'height', 'h'
	 *
	 * @return float|int
	 */
	public function getRatio( $aspect ) {
		if ( in_array( $aspect, [ 'width', 'w' ] ) ) {

			return $this->getWidth() / $this->getHeight();
		} elseif ( in_array( $aspect, [ 'height', 'h' ] ) ) {

			return $this->getHeight() / $this->getWidth();
		}

		return 1;
	}

	/**
	 * Wrapper to return image width
	 *
	 * @return int
	 */
	public function getWidth() {
		return (int) $this->getSize()['width'];
	}

	/**
	 * Wrapper get image size
	 *
	 * @see WP_Image_Editor::get_size()
	 * @return array
	 */
	private function getSize() {
		return $this->editor->get_size();
	}

	/**
	 * Wrapper to return image height
	 *
	 * @return int
	 */
	public function getHeight() {
		return (int) $this->getSize()['height'];
	}

	/**
	 * Crop the image to requested size
	 * Focal point in center
	 *
	 * @param int $width
	 * @param int $height
	 * @param int $focalX
	 * @param int $focalY
	 *
	 * @see WP_Image_Editor::crop()
	 * @return bool|\WP_Error true on success | \WP_Error on error
	 */
	public function resizeImage( $width, $height, $focalX = 50, $focalY = 50 ) {
		$origWidth  = $this->getWidth();
		$origHeight = $this->getHeight();
		$ratio      = max( $width / $origWidth, $height / $origHeight );
		$srcX       = round( ( $origWidth - $width / $ratio ) * $focalX / 100 );
		$srcY       = round( ( $origHeight - $height / $ratio ) * $focalY / 100 );

		return $this->editor->crop( $srcX, $srcY, $width / $ratio, $height / $ratio, $width, $height );
	}

	/**
	 * Save image
	 *
	 * @param string $file full path to save image file
	 *
	 * @see WP_Image_Editor::save()
	 * @return array|\WP_Error {'path'=>string, 'file'=>string, 'width'=>int, 'height'=>int, 'mime-type'=>string}
	 */
	public function saveImage( $file ) {
		return $this->editor->save( $file );
	}

	/**
	 * @param $name
	 *
	 * @return bool|mixed
	 */
	public function __get( $name ) {
		if ( method_exists( $this, 'get' . ucfirst( $name ) ) ) {
			return call_user_func( [ $this, 'get' . ucfirst( $name ) ] );
		}

		return false;
	}

	/**
	 * Stream the image
	 * Cache for a day
	 *
	 * @return void
	 */
	protected function streamImage() {
		$cacheAge = \apply_filters('resizefly_cache_age', 31536000 );
		http_response_code( 200 );
		header( 'Pragma: public' );
		header( 'Cache-Control: max-age=' . $cacheAge . ', public' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() + $cacheAge ) );

		$this->editor->stream();
	}
}
