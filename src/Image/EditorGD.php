<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/09/16
 * Time: 15:54
 */

namespace Alpipego\Resizefly\Image;

use WP_Image_Editor_GD;

class EditorGD extends WP_Image_Editor_GD {
	public function blurImage( $radius, $float ) {
		for ( $i = 0; $i < 25; $i ++ ) {
			if ( $i % 10 == 0 ) {//each 10th time apply 'IMG_FILTER_SMOOTH' with 'level of smoothness' set to -7
				imagefilter( $this->image, IMG_FILTER_SMOOTH, - 7 );
			}
			imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);
		}
	}
}
