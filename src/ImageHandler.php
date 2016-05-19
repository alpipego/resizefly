<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/01/16
 * Time: 9:34 AM
 */

namespace Alpipego\DynamicImage;


class ImageHandler {
	public $image = [
		'input' => '',
		'url' => '',
		'path' => '',
	];

	public $imageEditor;

	protected $resize = [
		'width' => 0,
		'height' => 0,
		'quality'=> 0,
	];

	protected $resizedImage;

	public function __construct($file)
	{
		$this->image['input'] = \sanitize_text_field($file);
	}

	public function setImageData(array $uploads, $siteUrl)
	{
		$this->setImageUrl($siteUrl);
		$this->setImagePath($uploads, $siteUrl);
	}

	protected function setImageUrl($siteUrl)
	{
		return $this->image['url'] = $siteUrl . $this->image['input'];
	}

	protected function setImagePath($uploads, $siteUrl)
	{
		if (strpos($this->image['url'], $uploads['baseurl']) !== false) {
			$this->image['path'] = $uploads['basedir'] . str_replace($uploads['baseurl'], '', $this->image['url']);
		} else {
			$abspathArr = explode('/', ABSPATH);
			$uploadsArr = explode('/', $uploads['basedir']);

			$pathArr = array_intersect($abspathArr, $uploadsArr);
			$path = implode('/', $pathArr);

			$this->image['path'] = $path . str_replace(trailingslashit($siteUrl), '', $this->image['url']);
		}

		return $this->image['path'];
	}

	function resizeImage()
	{
		if ($this->resize['width'] > 0 || $this->resize['height'] > 0) {
			return $this->imageEditor->resize($this->resize['width'], $this->resize['height'], true);
		}
	}

	function saveImage()
	{
		return $this->imageEditor->save($this->resizedImage);
	}

	function resizedImageExists()
	{
		$pathinfo = pathinfo($this->image['path']);
		$this->resizedImage = sprintf('%s/%s-%d-%d-%d.%s', $pathinfo['dirname'], $pathinfo['filename'], $this->resize['width'], $this->resize['height'], $this->resize['quality'], $pathinfo['extension']);

		return file_exists($this->resizedImage);
	}

	function parseQueryArgs()
	{
		$origSize = $this->imageEditor->get_size();
		$origWidth = $origSize['width'];
		$origHeight = $origSize['height'];
		$this->resize['width'] = isset($_GET['width']) ? (int) $_GET['width'] : 0;
		$this->resize['height'] = isset($_GET['height']) ? (int) $_GET['height'] : 0;

		if ($this->resize['width'] === 0 && $this->resize['height'] === 0) {
			$this->resize['width'] = $origWidth;
			$this->resize['height'] = $origHeight;
		} elseif ($this->resize['width'] === 0) {
			$this->resize['width'] = (int) round($this->resize['height'] / ($origHeight / $origWidth));
		} elseif ($this->resize['height'] === 0) {
			$this->resize['height'] = (int) round($this->resize['width'] / ($origWidth / $origHeight));
		}

		if (isset($_GET['quality']) && ((int) $_GET['quality'] <= 100 && (int) $_GET['quality'] > 0 )) {
			$this->resize['quality'] = (int) $_GET['quality'];
		} else {
			$this->resize['quality'] = $this->imageEditor->get_quality();
		}

		return $this->resize;
	}

	function setImageEditor(\WP_Image_Editor $imageEditor)
	{
		$this->imageEditor = $imageEditor;
	}

	function handleImage()
	{
		$this->parseQueryArgs();
		$image = [];

		if (!$this->resizedImageExists()) {
			$this->resizeImage();
			$image = $this->saveImage();
		} else {
			$image['path'] = $this->resizedImage;
			$image['file'] = 'input';
		}

		return $image;
	}

	function outputImage($image)
	{

	}
}
