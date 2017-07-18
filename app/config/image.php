<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 17.07.2017
 * Time: 15:29
 */

use Alpipego\Resizefly\Image\Editor;
use Alpipego\Resizefly\Image\Handler;
use function Alpipego\Resizefly\object;

return [
    Editor::class  => object()
        ->constructorParam('editor', 'wp_image_editor'),
    Handler::class => object()
        ->constructorParam('cachePath', 'options.cache.path')
        ->constructorParam('duplicatesPath', 'options.duplicates.path'),
];
