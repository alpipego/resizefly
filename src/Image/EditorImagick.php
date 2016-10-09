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
	public function getImagick() {
		return $this->image;
	}

	public function getFile() {
		return $this->file;
	}

	public function blurImage($radius, $float) {
		return $this->image->blurImage($radius, $float);
	}

	public function getImageBlob() {
		return $this->image->getImageBlob();
	}
}
