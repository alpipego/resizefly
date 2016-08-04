<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 6:28 PM
 */

namespace Alpipego\Resizefly\Image;


/**
 * Stream the image instead of saving it
 *
 * @package Alpipego\Resizefly\Image
 */
class Stream extends Editor {

	/**
	 * run this when object called
	 */
	function run() {
        $this->streamImage();
    }
}
