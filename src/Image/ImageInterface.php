<?php

namespace Alpipego\Resizefly\Image;

/**
 * Holds all information relevant to the image.
 */
interface ImageInterface
{
    /**
     * Parse the requested image and setup member variables.
     *
     * @param array $file relative path to image file
     *
     * @return ImageInterface
     */
    public function setImage(array $file);

    /**
     * Get the full path to the duplicated original image.
     *
     * @return string
     */
    public function getDuplicatePath();

    /**
     * Get the full path of the original image.
     *
     * @return string
     */
    public function getOriginalPath();

    /**
     * Get the fully qualified URL for the original image.
     *
     * @return string
     */
    public function getOriginalUrl();

    /**
     * Get the requested pixel density as integer.
     *
     * @return int
     */
    public function getDensity();

    /**
     * Get the requested image width.
     *
     * @return int
     */
    public function getWidth();

    /**
     * Get the requested image height.
     *
     * @return int
     */
    public function getHeight();
}
