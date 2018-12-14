<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 08/10/16
 * Time: 12:32.
 */

return [
    'Alpipego\Resizefly\Admin\PageInterface'                => 'Alpipego\Resizefly\Admin\OptionsPage',
    'Alpipego\Resizefly\Upload\CacheInterface'              => 'Alpipego\Resizefly\Upload\Cache',
    'pluginPath'                                            => 'config.path',
    'pluginUrl'                                             => 'config.url',
    'Alpipego\Resizefly\Admin\OptionsPage'                  => Alpipego\Resizefly\object(),
    'Alpipego\Resizefly\Admin\Cache\CacheSection'           => Alpipego\Resizefly\object(),
    'Alpipego\Resizefly\Admin\Cache\PathField'              => Alpipego\Resizefly\object()
        ->constructorParam('section', 'Alpipego\Resizefly\Admin\Cache\CacheSection'),
    'Alpipego\Resizefly\Admin\Cache\PurgeCacheField'        => Alpipego\Resizefly\object()
        ->constructorParam('section', 'Alpipego\Resizefly\Admin\Cache\CacheSection'),
    'Alpipego\Resizefly\Admin\Cache\PurgeSingle'            => \Alpipego\Resizefly\object(),
    'Alpipego\Resizefly\Admin\Cache\PurgeAll'               => \Alpipego\Resizefly\object()
        ->constructorParam('field', 'Alpipego\Resizefly\Admin\Cache\PurgeCacheField'),
    'Alpipego\Resizefly\Upload\Cache'                       => Alpipego\Resizefly\object()
        ->constructorParam('cachePath', 'options.cache.path')
        ->constructorParam('addons', 'addons'),
    'Alpipego\Resizefly\Admin\Cache\RemoveResizedField'     => Alpipego\Resizefly\object()
        ->constructorParam('section', 'Alpipego\Resizefly\Admin\Cache\CacheSection'),
    'Alpipego\Resizefly\Upload\RemoveResized'               => Alpipego\Resizefly\object()
        ->constructorParam('field', 'Alpipego\Resizefly\Admin\Cache\RemoveResizedField'),
    'Alpipego\Resizefly\Admin\Sizes\RegisteredSizesSection' => Alpipego\Resizefly\object(),
    'Alpipego\Resizefly\Admin\Sizes\RestrictSizesField'     => Alpipego\Resizefly\object()
        ->constructorParam('section', 'Alpipego\Resizefly\Admin\Sizes\RegisteredSizesSection'),
    'Alpipego\Resizefly\Admin\Sizes\RestrictSizesField'     => Alpipego\Resizefly\object()
        ->constructorParam('section', 'Alpipego\Resizefly\Admin\Sizes\RegisteredSizesSection'),
    'Alpipego\Resizefly\Upload\Fake'                        => Alpipego\Resizefly\object(),
    'Alpipego\Resizefly\Admin\Admin'                        => Alpipego\Resizefly\object()
        ->constructorParam('basename', 'config.basename'),
];
