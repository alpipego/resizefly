<?php

use function Alpipego\Resizefly\object;

return [
    'Alpipego\Resizefly\Upload\UploadsInterface'  => 'Alpipego\Resizefly\Upload\Uploads',
    'Alpipego\Resizefly\Image\ImageInterface'     => 'Alpipego\Resizefly\Image\Image',
    'cacheUrl'                                    => 'options.cache.url',
    'Alpipego\Resizefly\Upload\Filter'            => object()
        ->constructorParam('imgRegex', 'config.imgregex'),
    'Alpipego\Resizefly\Image\Image'              => object()
        ->constructorParam('siteUrl', 'config.siteurl')
        ->constructorParam('cachePath', 'options.cache.path')
        ->constructorParam('duplicatesPath', 'options.duplicates.path'),
    'Alpipego\Resizefly\Upload\DuplicateOriginal' => object()
        ->constructorParam('duplicateDir', 'options.duplicates.suffix'),
];
