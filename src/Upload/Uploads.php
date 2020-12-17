<?php

namespace Alpipego\Resizefly\Upload;

/**
 * Class Dir.
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
     * Resolve all relative path parts.
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
     * Resolve all relative url parts.
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

    /**
     * Wrap `wp_uploads_dir`.
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
     * Normalize the path passed to uploads.
     *
     * @param string $path
     *
     * @return string
     */
    private function normalizePath($path)
    {
        return array_reduce(explode(DIRECTORY_SEPARATOR, $path), function ($a, $b) {
            if (0 === $a) {
                // fix Windows drive letters
                if (preg_match('/[a-zA-Z]:/', $b)) {
                    return '';
                }
                $a = '/';
            }

            if ('' === $b || '.' === $b) {
                return $a;
            }

            if ('..' === $b) {
                return dirname($a);
            }

            return preg_replace('@[/]+@', '/', "$a/$b");
        }, 0);
    }

    private function _get($arg)
    {
        return isset($this->uploads[$arg]) ? $this->uploads[$arg] : '';
    }
}
