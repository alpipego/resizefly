<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/09/16
 * Time: 15:54
 */

namespace Alpipego\Resizefly\Image;

use WP_Image_Editor_Imagick;

class EditorImagick extends WP_Image_Editor_Imagick {
	public function setOption( $key, $value ) {
		return $this->image->setOption( $key, $value );
	}

	public function setColorspace( $colorspace ) {
		return $this->image->setColorspace( $colorspace );
	}

	public function posterizeImage( $levels, $dither ) {
		return $this->image->posterizeImage( $levels, $dither );
	}

	public function setResourceLimit( $type, $limit ) {
		return $this->image->setResourceLimit( $type, $limit );
	}

	public function getResourceLimit( $type ) {
		return $this->image->getResourceLimit( $type );
	}

	public function stripImage() {
		return $this->image->stripImage();
	}

	public function getFile() {
		return $this->file;
	}

	public function blurImage( $radius, $float ) {
		return $this->image->blurImage( $radius, $float );
	}

	public function getImageBlob() {
		return $this->image->getImageBlob();
	}

	public function setImageFormat( $format ) {
		return $this->image->setImageFormat( $format );
	}

	public function set_quality( $quality = null ) {
		// call grandparent set_quality
		$quality_result = \WP_Image_Editor::set_quality( $quality );
		if ( is_wp_error( $quality_result ) ) {
			return $quality_result;
		}
		$quality = $this->get_quality();

		try {
			$this->image->setImageCompressionQuality( $quality );
			$this->image->setInterlaceScheme( \Imagick::INTERLACE_PLANE );
			if ( $this->mime_type === 'image/jpeg' ) {
				$this->image->setImageCompression( \Imagick::COMPRESSION_JPEG );
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'image_quality_error', $e->getMessage() );
		}

		return true;
	}
}
