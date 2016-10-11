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
	private $uploads;

	/**
	 * Dir constructor.
	 */
	public function __construct() {
		\add_filter( 'upload_dir', [ $this, 'resolvePath' ] );
		\add_filter( 'upload_dir', [ $this, 'resolveUrl' ] );
	}

	public function run() {
		\add_filter( 'resizefly_filter_url', [ $this, 'filterImageUrl' ] );
		\add_filter( 'wp_prepare_attachment_for_js', function ( $response ) {
			$response['url'] = $this->filterImageUrl( $response['url'] );

			return $response;
		} );

		\add_filter( 'wp_get_attachment_image_src', function ( $image ) {
			$image[0] = $this->filterImageUrl( $image[0] );

			return $image;
		} );
	}

	public function filterImageUrl( $url ) {
		if (!preg_match('%(png|jpe?g|gif)%', $url)) {
			return $url;
		}
		$resizeUrl = \trailingslashit( $this->uploads['baseurl'] ) . trim( get_option( 'resizefly_resized_path', 'resizefly' ), DIRECTORY_SEPARATOR );
		if ( strpos( $url, $resizeUrl ) === false ) {
			$url = str_replace( $this->uploads['baseurl'], $resizeUrl, $url );
		}

		return $url;
	}

	/**
	 * @return array wp_uploads_dir()
	 */
	public function getUploads() {
		return $this->uploads;
	}

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
