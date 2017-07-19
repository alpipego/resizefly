<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 29.06.2017
 * Time: 11:50
 */

namespace Alpipego\Resizefly\Upload;

use Alpipego\Resizefly\Image\ImageInterface;


/**
 * Class Filter
 * @package Alpipego\Resizefly\Upload
 */
class Filter {
	/**
	 * @var array
	 */
	private $uploads;
	private $image;
	private $cacheUrl;

    /**
     * Filter constructor.
     *
     * @param UploadsInterface $uploads
     * @param ImageInterface $image
     * @param string $cacheUrl
     */
	public function __construct( UploadsInterface $uploads, ImageInterface $image, $cacheUrl ) {
		$this->uploads = $uploads;
		$this->image   = $image;
		$this->cacheUrl = $cacheUrl;
	}

	/**
	 * Run filters
	 */
	public function run() {
		add_filter( 'resizefly_filter_url', [ $this, 'imageUrl' ] );
		add_filter( 'wp_prepare_attachment_for_js', function ( $post ) {
			if ( isset( $post['sizes'] ) ) {
				foreach ( $post['sizes'] as $key => $size ) {
					$post['sizes'][ $key ]['url'] = $this->addDensity( $this->imageUrl( $size['url'] ), 1 );
				}
			}

			return $post;
		} );

		add_filter( 'wp_get_attachment_image_src', function ( $image ) {
			$image[0] = $this->addDensity( $this->imageUrl( $image[0] ), 1 );

			return $image;
		} );

		add_filter( 'the_content', [ $this, 'urlInHtml' ] );
		add_filter( 'post_thumbnail_html', [ $this, 'urlInHtml' ] );
		add_filter( 'get_header_image_tag', [ $this, 'urlInHtml' ] );
	}

	/**
	 * @param string $url
	 * @param integer $density
	 *
	 * @return string
	 */
	public function addDensity( $url, $density ) {
		if ( ! $this->isValidUrl( $url, $matches ) ) {
			return $url;
		}

		if ( empty( $matches['density'] ) ) {
			$url = sprintf( '%s-%dx%d@%d.%s', $matches['file'], $matches['width'], $matches['height'], $density, $matches['ext'] );
		}

		return $url;
	}

	/**
	 * @param string $url
	 * @param array $matches Passed by reference - same as `preg_match`
	 *
	 * @return bool
	 */
	private function isValidUrl( $url, &$matches = [] ) {
		$valid = preg_match( '/(?<file>.*?)-(?<width>[0-9]+)x(?<height>[0-9]+)@?(?<density>[1-3])?\.(?<ext>jpe?g|png|gif)/i', $url, $matches );

		// if this is a valid URL, check if it's the original image
		if ( $valid ) {
			if ( $this->image->setImage( $matches )->getOriginalUrl() === $url ) {
				return false;
			}
		}

		return $valid;
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 */
	public function imageUrl( $url ) {
		if ( ! $this->isValidUrl( $url ) ) {
			return $url;
		}

		if ( strpos( $url, $this->cacheUrl ) === false ) {
			$url = str_replace( $this->uploads->getBaseUrl(), $this->cacheUrl, $url );
		}

		return $url;
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	public function urlInHtml( $content ) {
		$content = preg_replace_callback( "%{$this->uploads->getBaseUrl()}(?:[^\",\s]*?)(?:\d+x\d+)(?:@\d)?\.(?:png|jpe?g|gif)%",
			function ( $matches ) {
				return $this->addDensity( $this->imageUrl( $matches[0] ), 1 );
			},
			$content
		);

		return $content;
	}

}
