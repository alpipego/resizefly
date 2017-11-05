<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 15.07.2017
 * Time: 07:13
 */

use Alpipego\Resizefly\Image\Image;
use Alpipego\Resizefly\Image\ImageInterface;
use Alpipego\Resizefly\Upload\DuplicateOriginal;
use Alpipego\Resizefly\Upload\Filter;
use Alpipego\Resizefly\Upload\Uploads;
use Alpipego\Resizefly\Upload\UploadsInterface;

return [
    UploadsInterface::class  => Uploads::class,
    ImageInterface::class    => Image::class,
    'cacheUrl'               => 'options.cache.url',
    Filter::class            => Alpipego\Resizefly\object()
        ->constructorParam('imgRegex', 'config.imgregex'),
    Image::class             => Alpipego\Resizefly\object()
        ->constructorParam('siteUrl', 'config.siteurl')
        ->constructorParam('cachePath', 'options.cache.path')
        ->constructorParam('duplicatesPath', 'options.duplicates.path'),
    DuplicateOriginal::class => Alpipego\Resizefly\object()
        ->constructorParam('duplicateDir', 'options.duplicates.suffix'),
];
