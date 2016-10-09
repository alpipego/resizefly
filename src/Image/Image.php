<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 2:37 PM
 */

namespace Alpipego\Resizefly\Image;

use Alpipego\Resizefly\Upload\Dir;

/**
 * Holds all information relevant to the image
 * @package Alpipego\Resizefly\Image
 */
class Image {
	/**
	 * @var array requested size
	 */
	public $resize;
	/**
	 * @var array `wp_upload_dir` array
	 */
	protected $uploadDir;
	/**
	 * @var string original image file name
	 */
	protected $originalFile;
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
	protected $cachePath;
	/**
	 * @var string Full path to duplicates dir
	 */
	protected $duplicatesPath;
	/**
	 * @var string original image path
	 * @deprecated 1.4.0 Use the method getOrignal() to get the original image path
	 */
	private $original;

	protected $density;

	/**
	 * Image constructor. Sets up member variables
	 *
	 * @param string $file The requested image
	 * @param Dir $uploads `wp_upload_dir` array
	 * @param string $siteUrl the full website url
	 * @param string $cachePath the full path to resizefly cache dir
	 * @param string $duplicatesPath Full path to duplicates dir, since 1.4.0
	 */
	public function __construct( $file, Dir $uploads, $siteUrl, $cachePath, $duplicatesPath ) {
		$this->input          = \sanitize_text_field( $file[0] );
		$this->uploadDir      = $uploads->getUploads();
		$this->cachePath      = $cachePath;
		$this->duplicatesPath = $duplicatesPath;
		$this->resize         = [
			'width'  => $file[2],
			'height' => $file[3],
		];
		$this->density        = strlen($file[4]) ? $file[4] : 1;
		$this->file           = array_slice( explode( DIRECTORY_SEPARATOR, $this->input ), - 1 )[0];
		$this->originalFile   = array_slice( explode( DIRECTORY_SEPARATOR, $file[1] ), - 1 )[0] . '.' . $file[5];
		$this->url            = $this->setImageUrl( $siteUrl );
		$this->path           = $this->setImagePath( $siteUrl );
	}

	/**
	 * Needs doc
	 *
	 * @param string $siteUrl
	 *
	 * @return string new image url
	 */
	protected function setImageUrl( $siteUrl ) {
		$urlArr = explode( DIRECTORY_SEPARATOR, $siteUrl );
		unset( $urlArr[3] );
		$url = implode( DIRECTORY_SEPARATOR, $urlArr );

		return $url . $this->input;
	}

	/**
	 * Get the correct path to the image, regardless of WordPress setup
	 *
	 * @param string $siteUrl WordPress site url
	 *
	 * @return string path to image
	 */
	protected function setImagePath( $siteUrl ) {
		if ( strpos( $this->url, $this->uploadDir['baseurl'] ) !== false ) {

			return $this->uploadDir['basedir'] . str_replace( $this->uploadDir['baseurl'], '', $this->url );
		} else {
			$abspathArr = explode( DIRECTORY_SEPARATOR, ABSPATH );
			$uploadsArr = explode( DIRECTORY_SEPARATOR, $this->uploadDir['basedir'] );
			$pathArr    = array_intersect( $abspathArr, $uploadsArr );
			$path       = implode( DIRECTORY_SEPARATOR, $pathArr );

			return $path . str_replace( \trailingslashit( $siteUrl ), '', $this->url );
		}
	}

	/**
	 * @return string Path to duplicate image
	 */
	public function getDuplicate() {
		return str_replace( $this->uploadDir['basedir'], $this->duplicatesPath, $this->getOriginal() );
	}

	/**
	 * Set the path of the original (unresized) image
	 *
	 * @return string image path
	 */
	public function getOriginal() {
		$origPath = str_replace( $this->cachePath, $this->uploadDir['basedir'], $this->path );

		return str_replace( $this->file, $this->originalFile, $origPath );
	}

	public function getDensity() {
		return $this->density;
	}
}
