<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 01.11.18
 * Time: 13:41
 */

namespace Alpipego\Resizefly\Admin\Cache;

use Alpipego\Resizefly\Admin\OptionInterface;
use Alpipego\Resizefly\Upload\Cache;

class PurgeAll {
	private $action;
	private $cache;
	private $cachePath;

	public function __construct( OptionInterface $field, Cache $cache, $cachePath ) {
		$this->action    = $field->getId();
		$this->cache     = $cache;
		$this->cachePath = $cachePath;
	}

	public function run() {
		add_action( "wp_ajax_{$this->action}", [ $this, 'ajaxPurge' ] );
	}

	public function ajaxPurge() {
		if (
			check_ajax_referer( $this->action )
			&& current_user_can( apply_filters( 'resizefly/delete_attachment_cap', 'delete_posts' ) )
		) {
			$freed = $this->cache->purgeAll( $this->cachePath );
			wp_send_json( $freed );
		}

		wp_send_json( 'fail' );
	}
}
