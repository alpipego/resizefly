<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 18.07.2017
 * Time: 11:35
 */

namespace Alpipego\Resizefly\Image;


interface EditorWrapperInterface
{
    /**
     * Wrapper to return image width
     *
     * @return int
     */
    public function getWidth();

    /**
     * Wrapper to return image height
     *
     * @return int
     */
    public function getHeight();

    /**
     * Wrapper to return image quality
     *
     * @return int
     */
    public function getQuality();

    /**
     * Wrapper to return image ratio
     *
     * @param array $aspect containing either w|width or h|height or both
     *
     * @return float
     */
    public function getRatio(array $aspect);

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
    public function resizeImage($width, $height, $focalX, $focalY);

    /**
     * @param string $file full path to save image file
     *
     * @see WP_Image_Editor::save()
     * @return array|\WP_Error {'path'=>string, 'file'=>string, 'width'=>int, 'height'=>int, 'mime-type'=>string}
     */
    public function saveImage($file);

    /**
     * Stream the image
     *
     * @return void
     */
    public function streamImage();
}
