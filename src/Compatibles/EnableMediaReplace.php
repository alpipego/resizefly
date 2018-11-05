<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 01.11.18
 * Time: 16:31
 */

namespace Alpipego\Resizefly\Compatibles;

use Alpipego\Resizefly\Upload\CacheInterface;

class EnableMediaReplace {
	private $cache;

	public function __construct( CacheInterface $cache ) {
		$this->cache = $cache;
	}

	public function run() {
		add_action( 'wp_handle_replace', function ( $args ) {

			$this->cache->purgeSingle( $args['post_id'] );
		} );

		add_action( 'wp_handle_upload', function ( $args ) {
			$this->cache->warmUpSingle( $args['file'] );

			// Enable Media Replace unfortunately uses the same name for the action as WordPress does for its filter
			// In order for both to work, return $args
			return $args;
		} );
	}
}
