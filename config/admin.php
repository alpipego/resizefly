<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 08/10/16
 * Time: 12:32
 */

use Alpipego\Resizefly\Admin\OptionsPage;
use Alpipego\Resizefly\Admin\CacheSection;
use Alpipego\Resizefly\Admin\PathField;
use Alpipego\Resizefly\Admin\Admin;
use Alpipego\Resizefly\Admin\PurgeCacheField;
use Alpipego\Resizefly\Admin\RegisteredSizesSection;
use Alpipego\Resizefly\Admin\RemoveResizedField;
use Alpipego\Resizefly\Admin\RestrictSizesField;
use Alpipego\Resizefly\Upload\Cache;
use Alpipego\Resizefly\Upload\RemoveResized;
use Alpipego\Resizefly\Upload\Fake;

$plugin['options_page'] = function ( $plugin ) {
	return new OptionsPage( $plugin['path'] );
};

$plugin['cache_section'] = function ( $plugin ) {
	return new CacheSection( $plugin['options_page']->page, $plugin['path'] );
};

$plugin['options_field_path'] = function ( $plugin ) {
	return new PathField( $plugin['options_page']->page, $plugin['cache_section']->optionsGroup['id'], $plugin['path'] );
};

$plugin['admin'] = function ( $plugin ) {
	return new Admin( $plugin, $plugin['options_page'] );
};

$plugin[ 'option_purge_cache' ] = function ( $plugin ) {
	return new PurgeCacheField( $plugin['options_page']->page, $plugin[ 'cache_section' ]->optionsGroup['id'], $plugin['path'] );
};

$plugin[ 'option_remove_resized' ] = function ( $plugin ) {
	return new RemoveResizedField( $plugin['options_page']->page, $plugin[ 'cache_section' ]->optionsGroup['id'], $plugin['path'] );
};

$plugin[ 'controller_purge_cache' ] = function ( $plugin ) {
	/** @var  PurgeCacheField $purgeOption */
	$purgeOption = $plugin[ 'option_purge_cache' ];

	return new Cache( $plugin['uploads'], $purgeOption->optionsField['id'], $plugin['cache_path'] );
};

$plugin[ 'controller_remove_resized' ] = function ( $plugin ) {
	/** @var  RemoveResizedField $removeResizedOption */
	$removeResizedOption = $plugin[ 'option_remove_resized' ];

	return new RemoveResized( $removeResizedOption->optionsField['id'], $plugin['uploads'] );
};

$plugin['section_registered_sizes'] = function ( $plugin ) {
	return new RegisteredSizesSection( $plugin['options_page']->page, $plugin['path'] );
};

$plugin['option_restrict_sizes'] = function ( $plugin ) {
	return new RestrictSizesField( $plugin['options_page']->page, $plugin['section_registered_sizes']->optionsGroup['id'], $plugin['path'] );
};


$plugin->extend( 'admin', function ( $admin, $plugin ) {
	/** @var PurgeCacheField $purgeOption */
	$purgeOption = $plugin[ 'option_purge_cache' ];

	/** @var RemoveResizedField $removeResizedOption */
	$removeResizedOption = $plugin[ 'option_remove_resized' ];

	/** @var Admin $admin */
	$admin->localizeScript( [ 'purge_id' => $purgeOption->optionsField['id'] ] );
	$admin->localizeScript( [ 'resized_id' => $removeResizedOption->optionsField['id'] ] );

	return $admin;
} );

// fake the resized images to WordPress
$plugin['fake'] = function ( $plugin ) {
	return new Fake( $plugin['uploads'] );
};
