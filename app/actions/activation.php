<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 1.11.2017
 * Time: 12:21
 *
 * @var \Alpipego\Resizefly\Plugin $plugin
 */

// try to move thumbnail files
use Alpipego\Resizefly\Common\Psr\Container\ContainerExceptionInterface;
use Alpipego\Resizefly\Common\Psr\Container\NotFoundExceptionInterface;
use Alpipego\Resizefly\Image\Image;
use Alpipego\Resizefly\Upload\Cache;

if ( version_compare( get_option( 'resizefly_version' ), $plugin['config.version'], '<' ) ) {
	try {
		$plugin
			->get( Cache::class )
			->populateOnInstall( $plugin->get( Image::class ) );
	} catch ( NotFoundExceptionInterface $e ) {
	} catch ( ContainerExceptionInterface $e ) {
	}

	// update the version option
	add_option( 'resizefly_version_initial', $plugin['config.version'] );
	update_option( 'resizefly_version', $plugin['config.version'] );
}
