<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 17.07.2017
 * Time: 15:29.
 */

return [
    'Alpipego\Resizefly\Image\EditorWrapper' => Alpipego\Resizefly\object()
        ->constructorParam('editor', 'wp_image_editor'),
    'Alpipego\Resizefly\Image\Handler'       => Alpipego\Resizefly\object()
        ->constructorParam('editor', 'Alpipego\Resizefly\Image\EditorWrapper')
        ->constructorParam('cachePath', 'options.cache.path')
        ->constructorParam('duplicatesPath', 'options.duplicates.path'),
];
