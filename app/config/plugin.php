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
use function Alpipego\Resizefly\object;
use Alpipego\Resizefly\Upload\UploadsInterface;

return [
    UploadsInterface::class  => Uploads::class,
    ImageInterface::class    => Image::class,
    'cacheUrl'               => 'options.cache.url',
    Filter::class            => object(),
    Image::class             => object()
        ->constructorParam('siteUrl', 'config.siteurl')
        ->constructorParam('cachePath', 'options.cache.path')
        ->constructorParam('duplicatesPath', 'options.duplicates.path'),
    DuplicateOriginal::class => object()
        ->constructorParam('duplicateDir', 'options.duplicate.suffix'),
];
