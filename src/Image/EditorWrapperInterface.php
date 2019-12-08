<?php

namespace Alpipego\Resizefly\Image;

use WP_Error;

interface EditorWrapperInterface
{
    /**
     * Wrapper to return image width.
     *
     * @return int
     */
    public function getWidth();

    /**
     * Wrapper to return image height.
     *
     * @return int
     */
    public function getHeight();

    /**
     * Wrapper to return image quality.
     *
     * @return int
     */
    public function getQuality();

    /**
     * Wrapper to return image ratio.
     *
     * @param string $aspect specify if ration based on width or height
     *
     * @return float
     */
    public function getRatio($aspect);

    /**
     * Crop the image to requested size
     * Focal point in center.
     *
     * @param int $width
     * @param int $height
     * @param int $density
     * @param int $focalX
     * @param int $focalY
     *
     * @return bool|WP_Error true on success | \WP_Error on error
     *
     * @see WP_Image_Editor::crop()
     */
    public function resizeImage($width, $height, $density, $focalX, $focalY);

    /**
     * @param string $file full path to save image file
     *
     * @return array|WP_Error {'path'=>string, 'file'=>string, 'width'=>int, 'height'=>int, 'mime-type'=>string}
     *
     * @see WP_Image_Editor::save()
     */
    public function saveImage($file);

    /**
     * Stream the image.
     *
     * @param string $image
     */
    public function streamImage($image = '');
}
