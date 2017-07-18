<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 16/09/16
 * Time: 10:15
 */

namespace Alpipego\Resizefly\Upload;


/**
 * Class Dir
 * @package Alpipego\Resizefly\Upload
 */
class Uploads implements UploadsInterface
{
    /**
     * @var
     */
    private $uploads;

    /**
     * Dir constructor.
     */
    public function __construct()
    {
        add_filter('upload_dir', [$this, 'resolvePath']);
        add_filter('upload_dir', [$this, 'resolveUrl']);
        $this->setUploads(wp_upload_dir(null, false));
    }

    /**
     * Wrap `wp_uploads_dir`
     *
     * @param array $uploads wp_uploads_dir
     *
     * @return array wp_uploads_dir
     */
    private function setUploads($uploads)
    {
        return $this->uploads = $uploads;
    }

    /**
     * @deprecated 1.4.0 Method has moved to \\Alpipego\\Resizefly\\Uploads\\Filter, this method returns the original URL without filtering it
     *
     * @param string $url
     *
     * @return string
     */
    public function filterImageUrl($url)
    {
        return $url;
    }

    /**
     * @return array wp_uploads_dir()
     *
     * @deprecated 1.4.0 use getPath(), getUrl(), getBasePath(), getBaseUrl() directly
     */
    public function getUploads()
    {
        return $this->uploads;
    }

    public function getPath()
    {
        return $this->_get('path');
    }

    public function getUrl()
    {
        return $this->_get('url');
    }

    public function getBasePath()
    {
        return $this->_get('basedir');
    }

    public function getBaseUrl()
    {
        return $this->_get('baseurl');
    }

    /**
     * Resolve all relative path parts
     *
     * @param array $uploads wp_uploads_dir
     *
     * @return array wp_uploads_dir
     */
    public function resolvePath(array $uploads)
    {
        $uploads['path']    = $this->normalizePath($uploads['path']);
        $uploads['basedir'] = $this->normalizePath($uploads['basedir']);

        return $uploads;
    }

    /**
     * Normalize the path passed to uploads
     *
     * @param string $path
     *
     * @return string
     */
    private function normalizePath($path)
    {
        return array_reduce(explode('/', $path), function ($a, $b) {
            if ($a === 0) {
                $a = '/';
            }

            if ($b === '' || $b === '.') {
                return $a;
            }

            if ($b === '..') {
                return dirname($a);
            }

            return preg_replace('/\/+/', '/', "$a/$b");
        }, 0);
    }

    /**
     * Resolve all relative url parts
     *
     * @param array $uploads wp_uploads_dir
     *
     * @return array wp_uploads_dir
     */
    public function resolveUrl(array $uploads)
    {
        // resolve all /./
        while (strpos($uploads['url'], '/./')) {
            $uploads['url'] = preg_replace('%(?:/\.{1}/)%', '/', $uploads['url']);
        }
        while (strpos($uploads['baseurl'], '/./')) {
            $uploads['baseurl'] = preg_replace('%(?:/\.{1}/)%', '/', $uploads['baseurl']);
        }

        // resolve all /../
        while (strpos($uploads['url'], '/../')) {
            $uploads['url'] = preg_replace('%(?:([^/]+?)/\.{2}/)%', '', $uploads['url']);
        }
        while (strpos($uploads['baseurl'], '/../')) {
            $uploads['baseurl'] = preg_replace('%(?:([^/]+?)/\.{2}/)%', '', $uploads['baseurl']);
        }

        return $uploads;
    }

    private function _get($arg)
    {
        return isset($this->uploads[$arg]) ? $this->uploads[$arg] : '';
    }
}
