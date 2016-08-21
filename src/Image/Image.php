<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 2:37 PM
 */

namespace Alpipego\Resizefly\Image;

/**
 * Holds all information relevant to the image
 * @package Alpipego\Resizefly\Image
 */
class Image
{
	/**
	 * @var string original image path
	 */
	public $original;
	/**
	 * @var string original image file name
	 */
	public $originalFile;
	/**
	 * @var array requested size
	 */
	public $resize;
	/**
	 * @var array `wp_upload_dir` array
	 */
	public $uploadDir;
	/**
	 * @var string relative path requested file
	 */
	protected $input;
	/**
	 * @var string requested file name
	 */
	protected $file;
	/**
	 * @var string full requested file url
	 */
	protected $url;
	/**
	 * @var string full requested file path
	 */
	protected $path;
	/**
	 * @var string full url to resizefly cache folder
	 */
	protected $cacheUrl;

	/**
	 * Image constructor. Sets up member variables
	 *
	 * @param string $file The requested image
	 * @param array $uploads `wp_upload_dir` array
	 * @param string $siteUrl the full website url
	 * @param string $cacheUrl the full path to resizefly cache dir
	 */
	public function __construct($file, $uploads, $siteUrl, $cacheUrl)
    {
        $this->input = \sanitize_text_field($file[0]);
        $this->file = array_slice(explode(DIRECTORY_SEPARATOR, $this->input), -1)[0];
        $this->originalFile = array_slice(explode(DIRECTORY_SEPARATOR, $file[1]), -1)[0] . '.' . $file[4];
        $this->url = $this->setImageUrl($siteUrl);
        $this->path = $this->setImagePath($uploads, $siteUrl);
        $this->original = $this->setImageOriginal();
        $this->resize = [
            'width' => $file[2],
            'height' => $file[3],
        ];
        $this->uploadDir = $uploads;
	    $this->cacheUrl = $cacheUrl;
    }

	/**
	 * Needs doc
	 * @param string $siteUrl
	 *
	 * @return string new image url
	 */
	protected function setImageUrl($siteUrl)
    {
        $urlArr = explode(DIRECTORY_SEPARATOR, $siteUrl);
        unset($urlArr[3]);
        $url = implode(DIRECTORY_SEPARATOR, $urlArr);

	    return $url . $this->input;
    }

	/**
	 * Get the correct path to the image, regardless of WordPress setup
	 *
	 * @param array $uploads `wp_uploads_dir` array
	 * @param string $siteUrl WordPress site url
	 *
	 * @return string path to image
	 */
	protected function setImagePath($uploads, $siteUrl)
    {
        if (strpos($this->url, $uploads['baseurl']) !== false) {

            return $uploads['basedir'] . str_replace($uploads['baseurl'], '', $this->url);
        } else {
	        while (strpos($uploads['basedir'], '/./')) {
		        $uploads['basedir'] = preg_replace( '%(?:/\.{1}/)%', '/', $uploads['basedir'] );
	        }
	        while (strpos($uploads['basedir'], '/../')) {
		        $uploads['basedir'] = preg_replace( '%(?:([^/]+?)/\.{2}/)%', '', $uploads['basedir']);
	        }
            $abspathArr = explode(DIRECTORY_SEPARATOR, ABSPATH);
            $uploadsArr = explode(DIRECTORY_SEPARATOR, $uploads['basedir']);
            $pathArr = array_intersect($abspathArr, $uploadsArr);
            $path = implode(DIRECTORY_SEPARATOR, $pathArr);

            return $path . str_replace(\trailingslashit($siteUrl), '', $this->url);
        }
    }

	/**
	 * Set the url of the original (unresized) image
	 *
	 * @return string image url
	 */
	protected function setImageOriginal()
    {
	    $origPath = str_replace($this->cacheUrl, $this->uploadDir['baseurl'], $this->path );

        return str_replace($this->file, $this->originalFile, $origPath);
    }
}
