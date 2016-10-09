<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 4:32 PM
 */

namespace Alpipego\Resizefly\Image;

use Exception;
use Alpipego\Resizefly\Upload\Dir;

/**
 * Handle the requested image
 *
 * @package Alpipego\Resizefly\Image
 */
class Handler {
	/**
	 * @var string
	 */
	protected $file;
	/**
	 * @var Image
	 */
	protected $image;
	/**
	 * @var Editor
	 */
	protected $editor;
	/**
	 * @var array containing width and height
	 */
	protected $aspect;
	/**
	 * @var string full path to resizefly cache
	 */
	protected $cachePath;

	/**
	 * @var string full path to duplicates dir
	 */
	protected $duplicatePath;

	/**
	 * Handler constructor.
	 *
	 * @param Image $image
	 * @param Editor $editor
	 * @param string $cachePath
	 * @param Dir $upload
	 */
	public function __construct( Image $image, Editor $editor, $cachePath, $duplicatePath ) {
		$this->image  = $image;
		$this->editor = $editor;
		$this->cachePath = $cachePath;
		$this->duplicatePath = $duplicatePath;
	}

	/**
	 * @return array|\WP_Error
	 * @throws Exception if image can't be resized
	 */
	public function run() {
		if ( ! file_exists( $this->setImageName() ) ) {
			if ( $this->editor->resizeImage( $this->aspect['width'], $this->aspect['height'], 50, 50 ) ) {
				$image = $this->editor->saveImage( $this->file );
			} else {
				throw new Exception( sprintf( 'Could not resize image: %s. Destination was: %s.', $this->image->getOriginal(), $this->file ) );
			}
		} else {
			$image['path'] = $this->file;
		}

		return $image;
	}

	/**
	 * Sets the image path to-save
	 *
	 * @return string full image path
	 */
	protected function setImageName() {
		$size     = $this->parseRequestedImageSize();
		$pathinfo = pathinfo( $this->image->getDuplicate() );
		$dir = str_replace( $this->duplicatePath, $this->cachePath, $pathinfo['dirname'] );

		return $this->file = sprintf( '%s/%s-%dx%d@%s.%s', untrailingslashit( $dir ), $pathinfo['filename'], $size['width'], $size['height'], $size['density'], $pathinfo['extension'] );
	}

	public function getImage() {
		return $this->setImageName();
	}

	/**
	 * Parse the requested image size
	 *
	 * @return array ['width' => int, 'height' => int]
	 */
	protected function parseRequestedImageSize() {
		$origWidth  = $this->editor->getWidth();
		$origHeight = $this->editor->getHeight();

		// if width or height is larger than the image itself, set it to the original width/height
		// TODO if only one is larger, the output will be rather unexpected; maybe change to original aspect ratio
		$width  = $this->image->resize['width'] > $origWidth ? $origWidth : $this->image->resize['width'];
		$height = $this->image->resize['height'] > $origHeight ? $origHeight : $this->image->resize['height'];
		$density = $this->image->getDensity();

		// if either width or height is 0, resize to original aspect ratio
		if ( $width == 0 && $height == 0 ) {
			$width  = $origWidth;
			$height = $origHeight;
		} elseif ( $width == 0 ) {
			$width = round( $height * $this->editor->getRatio( 'width' ) );
		} elseif ( $height == 0 ) {
			$height = round( $width * $this->editor->getRatio( 'height' ) );
		}

		return $this->aspect = \apply_filters('resizefly_aspect', [ 'width' => (int) $width, 'height' => (int) $height, 'density' => (int) $density ]);
	}

}
