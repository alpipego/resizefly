<?php

use function Alpipego\Resizefly\object;

return [
    'Alpipego\Resizefly\Image\EditorWrapper' => object()
        ->constructorParam('editor', 'wp_image_editor'),
    'Alpipego\Resizefly\Image\Handler'       => object()
        ->constructorParam('editor', 'Alpipego\Resizefly\Image\EditorWrapper')
        ->constructorParam('cachePath', 'options.cache.path')
        ->constructorParam('duplicatesPath', 'options.duplicates.path'),
];
