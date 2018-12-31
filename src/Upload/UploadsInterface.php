<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 14.07.2017
 * Time: 13:54.
 */

namespace Alpipego\Resizefly\Upload;

interface UploadsInterface
{
    public function getPath();

    public function getUrl();

    public function getBasePath();

    public function getBaseUrl();
}
