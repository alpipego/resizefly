<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 4:32 PM
 */

namespace Alpipego\Resizefly\Image;

use WP_Error;

/**
 * Handle the requested image
 *
 * @package Alpipego\Resizefly\Image
 */
final class Handler implements HandlerInterface {
	/**
	 * @var string
	 */
	protected $file;
	/**
	 * @var Image
	 */
	protected $image;
	/**
	 * @var EditorWrapperInterface
	 */
	protected $editor;
	/**
	 * @var array containing width and height
	 */
	protected $aspect = [];
	/**
	 * @var string full path to resizefly cache
	 */
	protected $cachePath;

	/**
	 * @var string full path to duplicates dir
	 */
	protected $duplicatesPath;

	/**
	 * Handler constructor.
	 *
	 * @param ImageInterface $image
	 * @param EditorWrapperInterface $editor
	 * @param string $cachePath
	 * @param string $duplicatesPath
	 */
	public function __construct( ImageInterface $image, EditorWrapperInterface $editor, $cachePath, $duplicatesPath ) {
		$this->image          = $image;
		$this->editor         = $editor;
		$this->cachePath      = $cachePath;
		$this->duplicatesPath = $duplicatesPath;
	}

	/**
	 * Try to resize and save the image, else return a WP_Error
	 *
	 * @return WP_Error | void
	 */
	public function run() {
		if ( ! file_exists( $this->setImageName() ) && $this->editor->resizeImage(
				$this->aspect['width'],
				$this->aspect['height'],
				$this->aspect['density'],
				$this->aspect['focal_x'],
				$this->aspect['focal_y']
			) ) {
			$this->editor->saveImage( $this->file );
			$this->editor->streamImage();
		}
		$this->editor->streamImage( $this->setImageName() );
	}

	/**
	 * Sets the image path to-save
	 *
	 * @return string full image path
	 */
	protected function setImageName() {
		$size = ! empty( $this->aspect ) ? $this->aspect : $this->parseRequestedImageSize();
		$path = pathinfo( $this->image->getDuplicatePath() );
		$dir  = str_replace( $this->duplicatesPath, $this->cachePath, $path['dirname'] );

		return $this->file = sprintf(
			'%s/%s-%dx%d@%s.%s',
			untrailingslashit( $dir ),
			$path['filename'],
			$size['width'],
			$size['height'],
			$size['density'],
			$path['extension']
		);
	}

	/**
	 * Parse the requested image size
	 *
	 * @param array $size
	 *
	 * @return array ['width' => int, 'height' => int]
	 */
	protected function parseRequestedImageSize( $size = [] ) {
		$origWidth  = $this->editor->getWidth();
		$origHeight = $this->editor->getHeight();

		// if width or height is larger than the image itself, set it to the original width/height
		// TODO if only one is larger, the output will be rather unexpected; maybe change to original aspect ratio
		if ( ! isset( $size['width'] ) || $this->image->getWidth() > $origWidth ) {
			$size['width'] = $this->image->getWidth() > $origWidth ? $origWidth : $this->image->getWidth();
		}
		if ( ! isset( $size['height'] ) || $this->image->getHeight() > $origHeight ) {
			$size['height'] = $this->image->getHeight() > $origHeight ? $origHeight : $this->image->getHeight();
		}
		if ( ! isset( $size['density'] ) ) {
			$size['density'] = $this->image->getDensity();
		}
		if ( ! isset( $size['focal_x'] ) || $size['focal_x'] < 0 || $size['focal_x'] > 100 ) {
			$size['focal_x'] = 50;
		}
		if ( ! isset( $size['focal_y'] ) || $size['focal_y'] < 0 || $size['focal_y'] > 100 ) {
			$size['focal_y'] = 50;
		}

		// if either width or height is 0, resize to original aspect ratio
		if ( $size['width'] === 0 && $size['height'] === 0 ) {
			$size['width']  = $origWidth;
			$size['height'] = $origHeight;
		} elseif ( $size['width'] === 0 ) {
			$size['width'] = round( $size['height'] * $this->editor->getRatio( 'width' ) );
		} elseif ( $size['height'] === 0 ) {
			$size['height'] = round( $size['width'] * $this->editor->getRatio( 'height' ) );
		}

		return $this->aspect = $size;
	}

	public function getImage() {
		return $this->setImageName();
	}

	// TODO make this easier readable
	public function allowedImageSize( array $allowedSizes ) {
		// if requested size is original size
		if (
			$this->image->getWidth() === $this->editor->getWidth()
			&& $this->image->getHeight() === $this->editor->getHeight()
		) {
			return true;
		}

		foreach ( $allowedSizes as $size ) {
			if ( ! (bool) $size['active'] ) {
				continue;
			}

			$width  = (int) $size['width'];
			$height = (int) $size['height'];

			if ( (bool) $size['crop'] ) {
				if ( $this->image->getWidth() === $width && $this->image->getHeight() === $height ) {
					$aspect = [ 'width' => $width, 'height' => $height ];
					if ( is_array( $size['crop'] ) ) {
						$focal             = [
							'x' => [
								'left'   => 0,
								'center' => 50,
								'right'  => 100,
							],
							'y' => [
								'top'    => 0,
								'center' => 50,
								'bottom' => 100,
							],
						];
						$aspect['focal_x'] = $focal['x'][ $size['crop'][0] ];
						$aspect['focal_y'] = $focal['y'][ $size['crop'][1] ];
					}

					$this->parseRequestedImageSize( $aspect );

					return true;
				}
			} else {
				if ( $this->image->getWidth() === $width ) {
					$this->parseRequestedImageSize( [ 'width' => $width, 'height' => 0 ] );

					return true;
				}

				if ( $this->image->getHeight() === $height ) {
					$this->parseRequestedImageSize( [ 'width' => 0, 'height' => $height ] );

					return true;
				}
			}
		}

		return ! empty( $this->aspect );
	}
}
