<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 16/09/16
 * Time: 10:15
 */

namespace Alpipego\Resizefly\Upload;


/**
 * Class Dir
 * @package Alpipego\Resizefly\Upload
 */
class Dir {
	/**
	 * @var
	 */
	public $uploads;

	/**
	 * Wrap `wp_uploads_dir`
	 *
	 * @param $uploads array wp_uploads_dir
	 *
	 * @return array wp_uploads_dir
	 */
	public function setUploads( $uploads ) {
		return $this->uploads = $uploads;
	}

	public function __construct( $uploads ) {
		add_filter( 'upload_dir', [ $this, 'resolvePath' ] );
		add_filter( 'upload_dir', [ $this, 'resolveUrl' ] );
	}

	/**
	 * Resolve all relative path parts
	 *
	 * @param $uploads array wp_uploads_dir
	 *
	 * @return array wp_uploads_dir
	 */
	public function resolvePath( $uploads ) {
		$uploads['path']    = realpath( $uploads['path'] );
		$uploads['basedir'] = realpath( $uploads['basedir'] );

		return $uploads;
	}


	/**
	 * Resolve all relative url parts
	 *
	 * @param $uploads array wp_uploads_dir
	 *
	 * @return array wp_uploads_dir
	 */
	public function resolveUrl( $uploads ) {
		// resolve all /./
		while ( strpos( $uploads['url'], '/./' ) ) {
			$uploads['url'] = preg_replace( '%(?:/\.{1}/)%', '/', $uploads['url'] );
		}
		while ( strpos( $uploads['baseurl'], '/./' ) ) {
			$uploads['baseurl'] = preg_replace( '%(?:/\.{1}/)%', '/', $uploads['baseurl'] );
		}

		// resolve all /../
		while ( strpos( $uploads['url'], '/../' ) ) {
			$uploads['url'] = preg_replace( '%(?:([^/]+?)/\.{2}/)%', '', $uploads['url'] );
		}
		while ( strpos( $uploads['baseurl'], '/../' ) ) {
			$uploads['baseurl'] = preg_replace( '%(?:([^/]+?)/\.{2}/)%', '', $uploads['baseurl'] );
		}

		return $uploads;
	}
}
