<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 19/07/16
 * Time: 18:18
 */

namespace Alpipego\Resizefly\Upload;

use Alpipego\Resizefly\Admin\OptionInterface;
use RecursiveDirectoryIterator;
use SplFileInfo;
use WP_Post;

class RemoveResized {
	private $action;
	private $uploads;
	private $filesize = 0;
	private $files = 0;
	private $cropped = [];

	public function __construct( UploadsInterface $uploads, OptionInterface $field ) {
		$this->action  = $field->getId();
		$this->uploads = $uploads;
	}

	public function run() {
		add_action( "wp_ajax_{$this->action}", [ $this, 'unlinkImages' ] );
	}

	public function unlinkImages() {
		$canDelete = current_user_can( apply_filters( 'resizefly/delete_attachment_cap', 'delete_posts' ) );
		if ( check_ajax_referer( $this->action ) && $canDelete ) {
			$freed = $this->unlinkAll( $this->getOriginals() );
			wp_send_json( $freed );
		} else {
			wp_send_json( 'fail' );
		}
	}

	private function unlinkAll( $originals ) {
		foreach ( $originals as $image ) {
			if ( empty( $image ) ) {
				continue;
			}
			$file  = new SplFileInfo( $image );
			$dir   = new RecursiveDirectoryIterator( $file->getPathInfo() );
			$match = '%^' . $file->getBasename( '.' . $file->getExtension() ) . '-[0-9]+x[0-9]+\.' . $file->getExtension() . '$%i';

			/** @var SplFileInfo $file */
			foreach ( $dir as $file ) {
				if ( preg_match( $match, $file->getBasename() ) && ! in_array( $file->getRealPath(), $originals ) ) {
					$size = $file->getSize();
					if ( unlink( $file->getRealPath() ) ) {
						$this->files ++;
						$this->filesize += $size;
					}
				}
			}
		}

		return [ 'files' => $this->files, 'size' => $this->filesize ];
	}

	private function getOriginals() {
		$images    = $this->getAllImages();
		$originals = array_map( [ $this, 'filterOriginals' ], $images );
		array_map( [ $this, 'getManipulated' ], array_column( $images, 'ID' ) );

		return array_merge( $originals, $this->cropped );
	}

	private function getAllImages() {
		$images = get_posts( [
				'post_type'      => 'attachment',
				'posts_per_page' => - 1,
			]
		);

		return $images;
	}

	private function filterOriginals( WP_Post $img ) {
		if ( substr( $img->post_mime_type, 0, 6 ) === 'image/' ) {

			return get_attached_file( $img->ID );
		}
	}

	private function getManipulated( $id ) {
		$manipulated = get_post_meta( $id, '_wp_attachment_backup_sizes', true );
		$cropped     = [];
		if ( $manipulated ) {
			$matches = preg_grep( '%(full-(?!orig).+)%', array_keys( $manipulated ) );
			if ( ! empty( $matches ) ) {
				$parent = get_attached_file( $id );
				foreach ( $matches as $key ) {
					$this->cropped[] = str_replace( $manipulated['full-orig'], $manipulated[ $key ], $parent );
				}
			}
		}

		return $cropped;
	}
}
