<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 17.07.2017
 * Time: 15:29
 */

use Alpipego\Resizefly\Image\EditorWrapper;
use Alpipego\Resizefly\Image\Handler;

return [
	EditorWrapper::class => Alpipego\Resizefly\object()
		->constructorParam( 'editor', 'wp_image_editor' ),
	Handler::class       => Alpipego\Resizefly\object()
		->constructorParam( 'editor', EditorWrapper::class )
		->constructorParam( 'cachePath', 'options.cache.path' )
		->constructorParam( 'duplicatesPath', 'options.duplicates.path' ),
];
