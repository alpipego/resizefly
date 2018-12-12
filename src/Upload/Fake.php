<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 6:31 PM.
 */

namespace Alpipego\Resizefly\Upload;

/**
 * Make WordPress think image sizes have been created.
 */
class Fake
{
    /**
     * @var array array to hold registered image sizes
     */
    private $sizes;

    private $uploads;

    public function __construct(UploadsInterface $uploads)
    {
        $this->uploads = $uploads;
    }

    /**
     * register filters.
     */
    public function run()
    {
        add_filter('intermediate_image_sizes_advanced', [$this, 'getRegisteredImageSizes']);
        add_filter('wp_generate_attachment_metadata', [$this, 'fakeImageResize']);
    }

    /**
     * get the registered image sizes and return an empty set.
     *
     * @param array $sizes filter param - registered sizes
     *
     * @return array always return empty array
     */
    public function getRegisteredImageSizes($sizes)
    {
        $this->sizes = $sizes;

        return [];
    }

    /**
     * Create a fake meta entry so WordPress thinks, the image size has been created
     * TODO check if this can be merged with function in wp_get_attachment_src filter.
     *
     * @param array $metadata filter param - image size metadata
     *
     * @return array either the original array or a manipulated one
     */
    public function fakeImageResize($metadata)
    {
        $file = pathinfo(realpath($this->uploads->getBasePath().'/'.$metadata['file']));
        if (! in_array($file['extension'], ['jpg', 'jpeg', 'png', 'gif'], true)) {
            return $metadata;
        }

        foreach ($this->sizes as $name => $size) {
            // figure out what size WP would make this:
            $newsize = image_resize_dimensions($metadata['width'], $metadata['height'], $size['width'], $size['height'], $size['crop']);

            if ($newsize) {
                // build the fake meta entry for the size in question
                // TODO update for density
                $metadata['sizes'][$name] = [
                    'file'   => sprintf('%s-%sx%s.%s', $file['filename'], $newsize[4], $newsize[5], $file['extension']),
                    'width'  => $newsize[4],
                    'height' => $newsize[5],
                ];
            }
        }

        $metadata['sizes']['full'] = [
            'file'   => $file['basename'],
            'width'  => $metadata['width'],
            'height' => $metadata['height'],
        ];

        return $metadata;
    }
}
