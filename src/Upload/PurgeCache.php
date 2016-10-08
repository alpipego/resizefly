<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 19/07/16
 * Time: 18:18
 */

namespace Alpipego\Resizefly\Upload;


class PurgeCache {
	private $action;
	private $cachePath;
	private $filesize = 0;
	private $files = 0;

	public function __construct( $action, $cachePath ) {
		$this->action    = $action;
		$this->cachePath = $cachePath;
	}

	public function run() {
		\add_action( "wp_ajax_{$this->action}", [ $this, 'purge' ] );
	}

	public function purge() {
		if ( \check_ajax_referer( $this->action ) && \current_user_can( 'delete_posts' ) ) {
			$freed = $this->purgeAll( $this->cachePath );
			\wp_send_json( $freed );
		} else {
			\wp_send_json( 'fail' );
		}
	}

	private function purgeAll( $dir ) {
		foreach ( glob( "{$dir}/*" ) as $file ) {
			if ( is_dir( $file ) ) {
				$this->purgeAll( $file );
			} else {
				$this->filesize += filesize( $file );
				$this->files ++;
				unlink( $file );
			}
		}

		return [ 'files' => $this->files, 'size' => $this->filesize ];
	}
}
