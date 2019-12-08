<?php

use function Alpipego\Resizefly\object;

return [
    'Alpipego\Resizefly\Admin\PageInterface'                => 'Alpipego\Resizefly\Admin\OptionsPage',
    'Alpipego\Resizefly\Upload\CacheInterface'              => 'Alpipego\Resizefly\Upload\Cache',
    'pluginPath'                                            => 'config.path',
    'pluginUrl'                                             => 'config.url',
    'Alpipego\Resizefly\Admin\OptionsPage'                  => object(),
    'Alpipego\Resizefly\Admin\Cache\CacheSection'           => object(),
    'Alpipego\Resizefly\Admin\Cache\PathField'              => object()
        ->constructorParam('section', 'Alpipego\Resizefly\Admin\Cache\CacheSection'),
    'Alpipego\Resizefly\Admin\Cache\PurgeCacheField'        => object()
        ->constructorParam('section', 'Alpipego\Resizefly\Admin\Cache\CacheSection'),
    'Alpipego\Resizefly\Admin\Cache\PurgeSingle'            => object(),
    'Alpipego\Resizefly\Admin\Cache\PurgeAll'               => object()
        ->constructorParam('field', 'Alpipego\Resizefly\Admin\Cache\PurgeCacheField'),
    'Alpipego\Resizefly\Upload\Cache'                       => object()
        ->constructorParam('cachePath', 'options.cache.path')
        ->constructorParam('addons', 'addons'),
    'Alpipego\Resizefly\Admin\Cache\RemoveResizedField'     => object()
        ->constructorParam('section', 'Alpipego\Resizefly\Admin\Cache\CacheSection'),
    'Alpipego\Resizefly\Upload\RemoveResized'               => object()
        ->constructorParam('field', 'Alpipego\Resizefly\Admin\Cache\RemoveResizedField'),
    'Alpipego\Resizefly\Admin\Sizes\RegisteredSizesSection' => object(),
    'Alpipego\Resizefly\Admin\Sizes\RestrictSizesField'     => object()
        ->constructorParam('section', 'Alpipego\Resizefly\Admin\Sizes\RegisteredSizesSection'),
    'Alpipego\Resizefly\Admin\Sizes\SizesField'             => object()
        ->constructorParam('section', 'Alpipego\Resizefly\Admin\Sizes\RegisteredSizesSection'),
    'Alpipego\Resizefly\Upload\Fake'                        => object(),
    'Alpipego\Resizefly\Admin\Admin'                        => object()
        ->constructorParam('basename', 'config.basename'),
];
