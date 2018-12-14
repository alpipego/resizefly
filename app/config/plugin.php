<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 15.07.2017
 * Time: 07:13.
 */

return [
    'Alpipego\Resizefly\Upload\UploadsInterface'  => 'Alpipego\Resizefly\Upload\Uploads',
    'Alpipego\Resizefly\Image\ImageInterface'     => 'Alpipego\Resizefly\Image\Image',
    'cacheUrl'                                    => 'options.cache.url',
    'Alpipego\Resizefly\Upload\Filter'            => Alpipego\Resizefly\object()
        ->constructorParam('imgRegex', 'config.imgregex'),
    'Alpipego\Resizefly\Image\Image'             => Alpipego\Resizefly\object()
        ->constructorParam('siteUrl', 'config.siteurl')
        ->constructorParam('cachePath', 'options.cache.path')
        ->constructorParam('duplicatesPath', 'options.duplicates.path'),
    'Alpipego\Resizefly\Upload\DuplicateOriginal' => Alpipego\Resizefly\object()
        ->constructorParam('duplicateDir', 'options.duplicates.suffix'),
];
