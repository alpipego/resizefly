<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 4:32 PM
 */

namespace Alpipego\Resizefly\Image;

use Exception;

class Handler {
	public $file;
	protected $image;
	protected $editor;
	protected $aspect;

	public function __construct( Image $image, Editor $editor ) {
		$this->image  = $image;
		$this->editor = $editor;
	}

	public function run() {
		if ( ! file_exists( $this->setImage() ) ) {
			if ( $this->editor->resizeImage( $this->aspect['width'], $this->aspect['height'] ) ) {
				$image = $this->editor->saveImage( $this->file );
			} else {
				throw new Exception( sprintf( 'Could not resize image: %s. Destination was: %s.', $this->image->original, $this->file ) );
			}
		} else {
			$image['path'] = $this->file;
		}

		return $image;
	}

	private function setImage() {
		$size     = $this->parseRequestedImageSize();
		$pathinfo = pathinfo( $this->image->original );
		$suffix   = apply_filters( 'resizefly_resized_path', '' );
		$dir      = $pathinfo['dirname'];
		if ( ! empty( $suffix ) ) {
			$dir = str_replace( $this->image->uploadDir['basedir'], trailingslashit( $this->image->uploadDir['basedir'] ) . trim( $suffix, DIRECTORY_SEPARATOR ), $pathinfo['dirname'] );
		}

		return $this->file = sprintf( '%s/%s-%dx%d.%s', untrailingslashit( $dir ), $pathinfo['filename'], $size['width'], $size['height'], $pathinfo['extension'] );
	}

	private function parseRequestedImageSize() {
		$origWidth  = $this->editor->getWidth();
		$origHeight = $this->editor->getHeight();

		// if width or height is larger than the image itself, set it to the original width/height
		// TODO if only one is larger, the output will be rather unexpected; maybe change to original aspect ratio
		$width  = $this->image->resize['width'] > $origWidth ? $origWidth : $this->image->resize['width'];
		$height = $this->image->resize['height'] > $origHeight ? $origHeight : $this->image->resize['height'];

		if ( $width == 0 && $height == 0 ) {
			$width  = $origWidth;
			$height = $origHeight;
		} elseif ( $width == 0 ) {
			$width = round( $height * $this->editor->getRatio( 'width' ) );
		} elseif ( $height == 0 ) {
			$height = round( $width * $this->editor->getRatio( 'height' ) );
		}

		return $this->aspect = [ 'width' => (int) $width, 'height' => (int) $height ];
	}

}
