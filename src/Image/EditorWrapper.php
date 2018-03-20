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
final class EditorWrapper implements EditorWrapperInterface {
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

	public function getRatio( $aspect ) {
		if ( in_array( $aspect, [ 'width', 'w' ] ) ) {
			return $this->getWidth() / $this->getHeight();
		}

		if ( in_array( $aspect, [ 'height', 'h' ] ) ) {
			return $this->getHeight() / $this->getWidth();
		}

		return 1;
	}

	public function getWidth() {
		return (int) $this->editor->get_size()['width'];
	}

	public function getHeight() {
		return (int) $this->editor->get_size()['height'];
	}

	public function resizeImage( $width, $height, $density, $focalX, $focalY ) {
		$origWidth  = $this->getWidth();
		$origHeight = $this->getHeight();
		$ratio      = max( $width / $origWidth, $height / $origHeight );
		$srcX       = round( ( $origWidth - $width / $ratio ) * $focalX / 100 );
		$srcY       = round( ( $origHeight - $height / $ratio ) * $focalY / 100 );

		if ( $density > 0 ) {
			list( $quality, $width, $height ) = $this->parseDensity( $width, $height, $density );
			$this->editor->set_quality( $quality );
		}

		// make sure not to request an image larger than the original
		$width  = $width > $origWidth ? $origWidth : $width;
		$height = $height > $origHeight ? $origHeight : $height;

		return $this->editor->crop( $srcX, $srcY, $width / $ratio, $height / $ratio, $width, $height );
	}

	/**
	 * Parse density and set quality, width and height accordingly
	 *
	 * @param int $width
	 * @param int $height
	 * @param int $density
	 *
	 * @return array
	 *      0 => int $quality
	 *      1 => int $width
	 *      2 => int $height
	 */
	private function parseDensity( $width, $height, $density ) {
		$width   = $width * $density;
		$height  = $height * $density;
		$quality = 40;
		if ( $density === 1 ) {
			$quality = $this->getQuality();
		}

		return array_map( 'intVal', [ $quality, $width, $height ] );
	}

	public function getQuality() {
		return (int) $this->editor->get_quality();
	}

	public function saveImage( $file ) {
		do_action( 'resizefly/before_save', $file, $this->editor );

		return $this->editor->save( $file );
	}

	/**
	 * Stream the image
	 *
	 * @param string $image
	 *
	 * @return void
	 */
	public function streamImage( $image = '' ) {
		$cacheAge = apply_filters( 'resizefly/cache_age', 31536000 );
		http_response_code( 200 );
		header( 'HTTP/1.1 200 OK' );
		header( 'Pragma: public' );
		header( 'Cache-Control: max-age=' . $cacheAge . ', public' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() + $cacheAge ) );

		if ( empty( $image ) ) {
			if ( method_exists( $this->editor, 'getImageBlob' ) ) {
				header( 'Content-Length: ' . strlen( $this->editor->getImageBlob() ) );
			}
			$this->editor->stream();
		} else {
			$imgString = file_get_contents( $image );
			header( 'Content-Length: ' . strlen( $imgString ) );
			header( 'Content-Type: ' . mime_content_type( $image ) );
			echo $imgString;
		}
		exit;
	}
}
