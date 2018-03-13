<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 08/10/16
 * Time: 12:32
 */

use Alpipego\Resizefly\Admin\Admin;
use Alpipego\Resizefly\Admin\Cache\CacheSection;
use Alpipego\Resizefly\Admin\Cache\PathField;
use Alpipego\Resizefly\Admin\Cache\PurgeCacheField;
use Alpipego\Resizefly\Admin\Cache\RemoveResizedField;
use Alpipego\Resizefly\Admin\OptionsPage;
use Alpipego\Resizefly\Admin\PageInterface;
use Alpipego\Resizefly\Admin\Sizes\RegisteredSizesSection;
use Alpipego\Resizefly\Admin\Sizes\RestrictSizesField;
use Alpipego\Resizefly\Admin\Sizes\SizesField;
use Alpipego\Resizefly\Admin\Sizes\UserSizesField;
use Alpipego\Resizefly\Upload\Cache;
use Alpipego\Resizefly\Upload\Fake;
use Alpipego\Resizefly\Upload\RemoveResized;

return [
	PageInterface::class          => OptionsPage::class,
	'pluginPath'                  => 'config.path',
	OptionsPage::class            => Alpipego\Resizefly\object()
		->constructorParam( 'pluginUrl', 'config.url' ),
	CacheSection::class           => Alpipego\Resizefly\object()
		->constructorParam( 'pluginPath', 'config.path' ),
	PathField::class              => Alpipego\Resizefly\object()
		->constructorParam( 'section', CacheSection::class ),
	PurgeCacheField::class        => Alpipego\Resizefly\object()
		->constructorParam( 'section', CacheSection::class ),
	Cache::class                  => Alpipego\Resizefly\object()
		->constructorParam( 'field', PurgeCacheField::class )
		->constructorParam( 'cachePath', 'options.cache.path' )
		->constructorParam( 'addons', 'addons' ),
	RemoveResizedField::class     => Alpipego\Resizefly\object()
		->constructorParam( 'section', CacheSection::class ),
	RemoveResized::class          => Alpipego\Resizefly\object()
		->constructorParam( 'field', RemoveResizedField::class ),
	RegisteredSizesSection::class => Alpipego\Resizefly\object(),
	RestrictSizesField::class     => Alpipego\Resizefly\object()
		->constructorParam( 'section', RegisteredSizesSection::class ),
	SizesField::class             => Alpipego\Resizefly\object()
		->constructorParam( 'section', RegisteredSizesSection::class ),
	Fake::class                   => Alpipego\Resizefly\object(),
	Admin::class                  => Alpipego\Resizefly\object()
		->constructorParam( 'basename', 'config.basename' )
		->constructorParam( 'pluginUrl', 'config.url' ),
];
