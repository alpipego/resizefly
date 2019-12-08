<?php

namespace Alpipego\Resizefly\Image;

/**
 * Handle the requested image.
 */
interface HandlerInterface
{
    public function run();

    /**
     * Get the full path for the image to-be-saved.
     *
     * @return string
     */
    public function getImage();

    /**
     * Check if requested image size is in array of allowed sizes.
     *
     * @return bool
     */
    public function allowedImageSize(array $allowedSizes);
}
