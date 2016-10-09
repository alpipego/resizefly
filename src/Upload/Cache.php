<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 27/09/16
 * Time: 12:03
 */

namespace Alpipego\Resizefly\Upload;

use SplFileInfo;
use RecursiveDirectoryIterator;

class Cache {
	private $uploads;
	private $action;
	private $cachePath;
	private $filesize = 0;
	private $files = 0;

	public function __construct( Dir $uploads, $action, $cachePath ) {
		$this->uploads   = $uploads->getUploads();
		$this->cachePath = $cachePath;
		$this->action = $action;
	}

	public function run() {
		\add_filter( 'media_row_actions', [ $this, 'deleteSingle' ], 10, 2 );
		\add_action( 'delete_attachment', [ $this, 'purgeSingle' ] );
		\add_action( "wp_ajax_{$this->action}", [ $this, 'ajaxCallback' ] );
	}

	public function deleteSingle( $actions, $post ) {
		if ( substr( $post->post_mime_type, 0, 6 ) !== 'image/' || ! \current_user_can( 'delete_posts' ) ) {
			return $actions;
		}
//
//		$url = wp_nonce_url( admin_url( 'tools.php?page=fly-images&delete-fly-image&ids=' . $post->ID ), 'delete_fly_image', 'fly_nonce' );
//		$actions['fly-image-delete'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( __( 'Delete all cached image sizes for this image', 'fly-images' ) ) . '">' . __( 'Delete Cached Fly Images', 'fly-images' ) . '</a>';

		return $actions;
	}

	public function purgeSingle( $id ) {
		$file  = new SplFileInfo( \get_attached_file( $id ) );
		$path  = str_replace( $this->uploads['basedir'], $this->cachePath, $file->getPathInfo() );
		$dir   = new RecursiveDirectoryIterator( $path );
		$match = '%^' . $file->getBasename( '.' . $file->getExtension() ) . '-[0-9]+x[0-9]+\.' . $file->getExtension() . '$%i';

		/** @var SplFileInfo $file */
		foreach ( $dir as $file ) {
			if ( preg_match( $match, $file->getBasename() ) ) {
				unlink( $file->getRealPath() );
			}
		}
	}

	public function ajaxCallback() {
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
