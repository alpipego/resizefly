<?php

namespace Alpipego\Resizefly\Upload;

use Alpipego\Resizefly\Image\ImageInterface;

/**
 * Class Filter.
 */
class Filter
{
    /**
     * @var array
     */
    private $uploads;
    /**
     * @var ImageInterface
     */
    private $image;
    /**
     * @var string
     */
    private $cacheUrl;

    /**
     * Filter constructor.
     *
     * @param string $cacheUrl
     */
    public function __construct(UploadsInterface $uploads, ImageInterface $image, $cacheUrl)
    {
        $this->uploads  = $uploads;
        $this->image    = $image;
        $this->cacheUrl = $cacheUrl;
    }

    /**
     * Run filters.
     */
    public function run()
    {
        // filter urls for usage with resizefly
        add_filter('resizefly/filter/url', [$this, 'imageUrl']);
        add_filter('resizefly/filter/add_cache', [$this, 'addCache']);
        add_filter('resizefly/filter/metadata_file', [$this, 'wpFileName']);
        add_filter('resizefly/filter/metadata_basename', [$this, 'wpBaseName']);

        // add density to js attachments
        add_filter('wp_prepare_attachment_for_js', function ($post) {
            if (isset($post['sizes'])) {
                foreach ($post['sizes'] as $key => $size) {
                    $post['sizes'][$key]['url'] = $this->addDensity($this->imageUrl($size['url']), 1);
                }
            }

            return $post;
        });

        // add density to all image sources
        add_filter('wp_get_attachment_image_src', function ($image) {
            $image[0] = $this->addDensity($this->imageUrl($image[0]), 1);

            return $image;
        });

        // add density to urls already in html content somewhere
        add_filter('the_content', [$this, 'urlInHtml']);
        add_filter('post_thumbnail_html', [$this, 'urlInHtml']);
        add_filter('get_header_image_tag', [$this, 'urlInHtml']);
        add_filter('admin_post_thumbnail_html', [$this, 'urlInHtml']);

        // add resizefly url before loaded into editor, remove before saving
        add_filter('media_send_to_editor', [$this, 'urlInHtml']);
        add_filter('content_edit_pre', [$this, 'urlInHtml']);
        add_filter('content_save_pre', [$this, 'revertOriginalContent']);
    }

    /**
     * @param string $url
     * @param int    $density
     *
     * @return string
     */
    public function addDensity($url, $density)
    {
        if (! $this->isValidUrl($url, $matches)) {
            return $url;
        }

        if (empty($matches['density'])) {
            $url = sprintf('%s-%dx%d@%d.%s', $matches['file'], $matches['width'], $matches['height'], $density, $matches['ext']);
        }

        return $url;
    }

    /**
     * Filter the passed URL and add cache fragment if not present.
     *
     * @param string $url
     *
     * @return string
     */
    public function imageUrl($url)
    {
        if (! $this->isValidUrl($url)) {
            return $url;
        }

        $url = $this->addCache($url);

        return $url;
    }

    /**
     * Add cache fragment if not already present.
     *
     * @param string $url
     *
     * @return string
     */
    public function addCache($url)
    {
        if (false === strpos($url, $this->cacheUrl)) {
            $url = str_replace($this->uploads->getBaseUrl(), $this->cacheUrl, $url);
        }

        return $url;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function revertOriginalContent($content)
    {
        return preg_replace_callback(
            "%{$this->cacheUrl}(?<image>[^\",\s]*?)(?<dim>-\d+x\d+)?(?:@\d)?\.(?<ext>png|jpe?g|gif)%",
            function ($matches) {
                return sprintf('%s%s%s.%s', $this->uploads->getBaseUrl(), $matches['image'], $matches['dim'], $matches['ext']);
            },
            $content
        );
    }

    /**
     * @param string $src
     *
     * @return string
     */
    public function getOriginal($src)
    {
        return preg_replace_callback(
            "%{$this->cacheUrl}(?<image>[^\",\s]*?)(?<dim>-\d+x\d+)?(?:@\d)?\.(?<ext>png|jpe?g|gif)%",
            function ($matches) {
                return sprintf('%s%s.%s', $this->uploads->getBaseUrl(), $matches['image'], $matches['ext']);
            },
            $src
        );
    }

    /**
     * Get the filename as stored in post meta data.
     *
     * @param string $url
     *
     * @return string
     */
    public function wpFileName($url)
    {
        return $this->wpName('/@\d/', $url);
    }

    /**
     * Get the filename for the full image as stored in post meta data.
     *
     * @param string $url
     *
     * @return string
     */
    public function wpBaseName($url)
    {
        return $this->wpName('/-\d+x\d+@\d/', $url);
    }

    /**
     * Add density to resizefly url if not already present
     * Matches URLs inside html.
     *
     * @param string $content
     *
     * @return string
     */
    public function urlInHtml($content)
    {
        $content = preg_replace_callback(
            "%{$this->uploads->getBaseUrl()}(?:[^\",\s]*?)(?:\d+x\d+)(?:@\d)?\.(?:png|jpe?g|gif)%",
            function ($matches) {
                return $this->addDensity($this->imageUrl($matches[0]), 1);
            },
            $content
        );

        return $content;
    }

    /**
     * Check if this is a valid image url and not the original image.
     *
     * @param string $url
     * @param array  $matches Passed by reference - same as `preg_match`
     *
     * @return bool
     */
    private function isValidUrl($url, array &$matches = [])
    {
        $regex = '/(?<file>.*?)-(?<width>[0-9]+)x(?<height>[0-9]+)@?(?<density>[0-3])?\.(?<ext>jpe?g|png|gif)/i';
        $valid = preg_match($regex, $url, $matches);

        // if this is a valid URL, check if it's the original image
        if ($valid) {
            if ($this->image->setImage($matches)->getOriginalUrl() === $url) {
                return false;
            }
        }

        return $valid;
    }

    /**
     * Gets filename to match post meta data.
     *
     * @param string $regex
     * @param string $url
     *
     * @return string
     */
    private function wpName($regex, $url)
    {
        return preg_replace($regex, '', pathinfo($url)['basename']);
    }
}
