<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 18.07.2017
 * Time: 12:05
 */

namespace Alpipego\Resizefly\Image;

/**
 * Handle the requested image
 *
 * @package Alpipego\Resizefly\Image
 */
interface HandlerInterface
{
    public function run();


    /**
     * Get the full path for the image to-be-saved
     *
     * @return string
     */
    public function getImage();


    /**
     * Check if requested image size is in array of allowed sizes
     *
     * @param array $allowedSizes
     *
     * @return bool
     */
    public function allowedImageSize(array $allowedSizes);
}
