<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 2:37 PM
 */

namespace Alpipego\Resizefly\Image;

class Image
{
    public $original;
    public $originalFile;
    public $resize;
    protected $input;
    protected $file;
    protected $url;
    protected $path;

    public function __construct($file, $uploads, $siteUrl)
    {
        $this->input = \sanitize_text_field($file[0]);
        $this->file = array_slice(explode(DIRECTORY_SEPARATOR, $this->input), -1)[0];
        $this->originalFile = array_slice(explode(DIRECTORY_SEPARATOR, $file[1]), -1)[0] . '.' . $file[4];
        $this->url = $this->setImageUrl($siteUrl);
        $this->path = $this->setImagePath($uploads, $siteUrl);
        $this->original = $this->setImageOriginal($file);
        $this->resize = [
            'width' => $file[2],
            'height' => $file[3],
        ];
    }

    protected function setImageUrl($siteUrl)
    {
        $urlArr = explode(DIRECTORY_SEPARATOR, $siteUrl);
        unset($urlArr[3]);
        $url = implode(DIRECTORY_SEPARATOR, $urlArr);

        return $url . $this->input;
    }

    protected function setImagePath($uploads, $siteUrl)
    {
        if (strpos($this->url, $uploads['baseurl']) !== false) {
            return $uploads['basedir'] . str_replace($uploads['baseurl'], '', $this->url);
        } else {
            $abspathArr = explode(DIRECTORY_SEPARATOR, ABSPATH);
            $uploadsArr = explode(DIRECTORY_SEPARATOR, $uploads['basedir']);
            $pathArr = array_intersect($abspathArr, $uploadsArr);
            $path = implode(DIRECTORY_SEPARATOR, $pathArr);

            return $path . str_replace(\trailingslashit($siteUrl), '', $this->url);
        }
    }

    protected function setImageOriginal($file)
    {
        return str_replace($this->file, $this->originalFile, $this->path);
    }
}
