<?php

namespace Alpipego\Resizefly\Upload;

interface UploadsInterface
{
    public function getPath();

    public function getUrl();

    public function getBasePath();

    public function getBaseUrl();
}
