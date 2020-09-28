<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 2:37 PM.
 */

namespace Alpipego\Resizefly\Image;

use Alpipego\Resizefly\Upload\UploadsInterface;
use WP_Query;

/**
 * Holds all information relevant to the image.
 */
final class Image implements ImageInterface
{
    /**
     * @var array requested size
     */
    private $resize;
    /**
     * @var string relative path requested file
     */
    private $input;
    /**
     * @var string requested file name
     */
    private $filename;
    /**
     * @var string full requested file url
     */
    private $url;
    /**
     * @var string full requested file path
     */
    private $path;
    /**
     * @var string original image file name
     */
    private $originalFilename;
    /**
     * @var string original image path
     */
    private $originalPath;
    /**
     * @var int pixel density
     */
    private $density;
    /**
     * @var string basename for requested file
     */
    private $basename;
    /**
     * @var array `wp_upload_dir`
     */
    private $uploads;
    /**
     * @var string full path to resizefly cache dir
     */
    private $cachePath;
    /**
     * @var string full url to resizefly cache dir
     */
    private $cacheUrl;
    /**
     * @var string Full path to duplicates dir
     */
    private $duplicatesPath;
    /**
     * @var string wordpress site url
     */
    private $siteUrl;
    /**
     * @var int
     */
    private $id;
    /**
     * @var array
     */
    private $meta;

    /**
     * Image constructor. Sets up member variables.
     *
     * @param UploadsInterface $uploads `wp_upload_dir` array
     * @param string $siteUrl the full website url
     * @param string $cachePath the full path to resizefly cache dir
     * @param string $cacheUrl
     * @param string $duplicatesPath Full path to duplicates dir, since 2.0.0
     */
    public function __construct(UploadsInterface $uploads, $siteUrl, $cachePath, $cacheUrl, $duplicatesPath)
    {
        $this->uploads        = $uploads;
        $this->cachePath      = $cachePath;
        $this->cacheUrl       = $cacheUrl;
        $this->duplicatesPath = $duplicatesPath;
        $this->siteUrl        = $siteUrl;
    }

    public function setImage(array $file)
    {
        $this->input            = sanitize_text_field($file[0]);
        $this->basename         = pathinfo($this->input)['basename'];
        $this->filename         = pathinfo($this->input)['filename'];
        $this->url              = $this->setImageUrl();
        $this->path             = $this->setImagePath();
        $this->originalFilename = array_slice(explode(DIRECTORY_SEPARATOR, $file['file']), -1)[0].'.'.$file['ext'];
        $this->originalPath     = $this->setOriginalFile($file);
        $this->id               = $this->setId();
        $this->resize           = [
            'width'  => (int) $file['width'],
            'height' => (int) $file['height'],
        ];
        $this->density          = ! isset($file['density']) ? 1 : (int) $file['density'];

        return $this;
    }

    /**
     * @deprecated 1.4.0 use more verbose `getDuplicatePath()`
     * @see getDuplicatePath()
     */
    public function getDuplicate()
    {
        return $this->getDuplicatePath();
    }

    /**
     * Get the path for the duplicated original.
     *
     * @return string Path to duplicate image
     */
    public function getDuplicatePath()
    {
        return str_replace($this->uploads->getBasePath(), $this->duplicatesPath, $this->originalPath);
    }

    /**
     * @deprecated  1.4.0 use more verbose `getOriginalPath()`
     * @see getOriginalPath()
     */
    public function getOriginal()
    {
        return $this->getOriginalPath();
    }

    public function getOriginalPath()
    {
        return $this->originalPath;
    }

    public function getOriginalUrl()
    {
        return str_replace($this->uploads->getBasePath(), $this->uploads->getBaseUrl(), $this->originalPath);
    }

    public function getDensity()
    {
        return (int) $this->density;
    }

    public function getWidth()
    {
        return (int) $this->resize['width'];
    }

    public function getHeight()
    {
        return (int) $this->resize['height'];
    }

    /**
     * Set the correct URL for the image.
     *
     * @return string new image url
     */
    private function setImageUrl()
    {
        if (0 === strpos($this->input, $this->siteUrl)) {
            return $this->input;
        }

        return untrailingslashit($this->siteUrl).$this->input;
    }

    /**
     * Set the correct path to the image, regardless of WordPress setup.
     *
     * @return string path to image
     */
    private function setImagePath()
    {
        // if this a full url to this wp install, set path
        if (0 === strpos($this->input, $this->uploads->getBaseUrl())) {
            $filePath = $this->uploads->getBasePath().str_replace($this->uploads->getBaseUrl(), '', $this->url);
        } else {
            $abspathArr = explode(DIRECTORY_SEPARATOR, ABSPATH);
            $uploadsArr = explode(DIRECTORY_SEPARATOR, $this->uploads->getBasePath());
            $pathArr    = array_intersect($abspathArr, $uploadsArr);
            $path       = implode(DIRECTORY_SEPARATOR, $pathArr);
            $filePath   = $path.str_replace(trailingslashit($this->siteUrl), '', $this->url);
        }

        return str_replace($this->basename, '', $filePath);
    }

    /**
     * Set path to the original image file.
     *
     * @param array $file
     *
     * @return string
     */
    private function setOriginalFile($file)
    {
        $path = str_replace($this->cachePath, $this->uploads->getBasePath(), $this->path);
        if (! file_exists($path.$this->originalFilename)) {
            $this->originalFilename = sprintf(
                '%s-%dx%d.%s',
                substr($this->originalFilename, 0, strlen($this->originalFilename) - (strlen($file['ext'] + 1))),
                $file['width'],
                $file['height'],
                $file['ext']
            );
        }

        return $path.$this->originalFilename;
    }

    /**
     * Get the attachment ID for this image.
     *
     * @return int Attachment ID on success, 0 on failure
     */
    function setId()
    {
        static $queryCache = [];
        $args     = [
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
            'fields'      => 'ids',
            'meta_query'  => [
                [
                    'value'   => $this->originalFilename,
                    'compare' => 'LIKE',
                    'key'     => '_wp_attachment_metadata',
                ],
            ],
        ];
        $queryKey = md5(wp_json_encode($args));
        if (! array_key_exists($queryKey, $queryCache)) {
            $queryCache[$queryKey] = new WP_Query($args);
        }

        /** @var WP_Query $query */
        $query = $queryCache[$queryKey];

        foreach ($query->posts as $post_id) {
            $meta = wp_get_attachment_metadata($post_id);

            if (
                $this->originalFilename === $meta['file']
                || $this->originalFilename === substr($meta['file'], 8)
                || in_array($this->originalFilename, wp_list_pluck($meta['sizes'], 'file'), true)
            ) {
                $this->meta = $meta;

                return $post_id;
            }
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
