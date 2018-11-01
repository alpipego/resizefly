<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 27/09/16
 * Time: 12:03
 */

namespace Alpipego\Resizefly\Upload;

use Alpipego\Resizefly\Admin\OptionInterface;
use Alpipego\Resizefly\Image\ImageInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class Cache
 * @package Alpipego\Resizefly\Upload
 */
class Cache {
	private $uploads;
	private $cachePath;
	private $filesize = 0;
	private $files = 0;
	private $addons;
	private $image;

	public function __construct( UploadsInterface $uploads, ImageInterface $image, $cachePath, $addons ) {
		$this->uploads   = $uploads;
		$this->cachePath = $cachePath;
		$this->addons    = $addons;
		$this->image     = $image;
	}

	public function purgeSingle( $id, $deleteDuplicate = true ) {
		$amount = 0;
		$file   = new SplFileInfo( get_attached_file( $id ) );
		$path   = str_replace( $this->uploads->getBasePath(), $this->cachePath, $file->getPathInfo() );
		try {
			$dir = new RecursiveDirectoryIterator( $path );
		} catch ( \Exception $e ) {
			// probably the directory does not exist
			return $amount;
		}

		if ( $deleteDuplicate ) {
			if ( preg_match(
				'/(?<file>.*?)-(?<width>[0-9]+)x(?<height>[0-9]+)@(?<density>[0-3])\.(?<ext>jpe?g|png|gif)/i',
				wp_get_attachment_image_src( $id )[0],
				$matches
			) ) {
				$this->image->setImage( $matches );
				$duplicate = $this->image->getDuplicatePath();
				if ( file_exists( $duplicate ) && unlink( $duplicate ) ) {
					$amount ++;
				}
			}
		}

		$match = sprintf(
			'/^(?<file>%s)-(?<width>[0-9]+)x(?<height>[0-9]+)@(?<density>[0-3])\.(?<ext>%s)/i',
			$file->getBasename( '.' . $file->getExtension() ),
			$file->getExtension()
		);

		/** @var SplFileInfo $file */
		foreach ( $dir as $file ) {
			if ( ! $file->isFile() ) {
				continue;
			}

			if ( preg_match( $match, $file->getBasename() ) && unlink( $file->getRealPath() ) ) {
				$amount ++;
			}
		}

		return $amount;
	}

	/**
	 * Purge ResizeFly cache - all (or smart)
	 *
	 * @param string $dir ResizeFly cache dir
	 *
	 * @return array
	 *      'files' => int number of files cleared,
	 *      'size' => float sum of freed space
	 */
	public function purgeAll( $dir ) {
		$iterator   = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir,
			RecursiveDirectoryIterator::SKIP_DOTS ) );
		$smartPurge = filter_var( $_POST['smart-purge'], FILTER_VALIDATE_BOOLEAN );
		if ( $smartPurge ) {
			$retain = $this->smartPurge();
		}
		foreach ( $iterator as $path ) {
			if ( ! $path->isDir() ) {
				$file = $path->__toString();
				if ( $smartPurge && preg_match( $retain, $file ) ) {
					continue;
				}
				$this->filesize += filesize( $file );
				$this->files ++;
				unlink( $file );
			}
		}

		return [ 'files' => $this->files, 'size' => $this->filesize ];
	}

	/**
	 * Gather filesizes to keep
	 *
	 * @return string
	 */
	private function smartPurge() {
		$retain   = $this->getThumbnails();
		$retain[] = $this->getLqir();

		$retain = array_unique( array_filter( $retain ) );

		return '/-(' . implode( '|', $retain ) . ')\.(jpe?g|png|gif)$/i';
	}

	/**
	 * Get built in thumbnail sizes
	 *
	 * @param bool $density
	 *
	 * @return array
	 */
	private function getThumbnails( $density = true ) {
		$intermediate = get_intermediate_image_sizes();
		$sizes        = [];
		foreach ( [ 'thumbnail', 'medium' ] as $size ) {
			if ( ! in_array( $size, $intermediate ) ) {
				continue;
			}
			$regex = (int) get_option( "{$size}_size_w" ) . 'x';
			$regex .= (bool) get_option( "{$size}_crop" ) ? (int) get_option( "{$size}_size_h" ) : '\d+?';
			if ( $density ) {
				$regex .= '@[1-9]';
			}

			$sizes[ $size ] = $regex;
		}

		return $sizes;
	}

	/**
	 * If lqir addon present, keep the currently set filesize
	 *
	 * @return string
	 */
	private function getLqir() {
		if ( ! array_key_exists( 'lqir', $this->addons ) ) {
			return '';
		}

		return ( (int) get_option( 'resizefly_lqir_size', apply_filters( 'resizefly/lqir/size', 150 ) ) ) . 'x\d+?@0';
	}

	public function populateOnInstall( ImageInterface $image ) {
		$thumbnails = $this->getThumbnails( false );
		// if there are no registered thumbnail sizes return
		if ( empty( $thumbnails ) ) {
			return;
		}

		$thumbRegex = '/-(' . implode( '|', $thumbnails ) . ')\.(jpe?g|png|gif)$/i';
		$iterator   = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $this->uploads->getBasePath(),
			RecursiveDirectoryIterator::SKIP_DOTS ) );

		foreach ( $iterator as $path ) {
			if ( ! $path->isDir() ) {
				$file = $path->__toString();

				// if this is not a thumbnail size skip
				if ( ! preg_match( $thumbRegex, $file ) ) {
					continue;
				}

				// if this is not either in uploads directly or in a year/month based folder skip
				if ( ! preg_match( '%' . $this->uploads->getBasePath() . '/(\d{4}/\d{2}/)?[^/]+\.(?:jpe?g|png|gif)$%',
					$file, $fragments ) ) {
					continue;
				}

				// if this is an original skip
				$regex = '/(?<file>.*?)-(?<width>[0-9]+)x(?<height>[0-9]+)\.(?<ext>jpe?g|png|gif)/i';
				$url   = str_replace( $this->uploads->getBasePath(), $this->uploads->getBaseUrl(), $file );
				preg_match( $regex, $url, $matches );

				if ( $image->setImage( $matches )->getOriginalPath() === $file ) {
					continue;
				}

				$newFile = sprintf(
					'%s-%dx%d@%d.%s',
					str_replace( $this->uploads->getBaseUrl(), $this->cachePath, $matches['file'] ),
					$matches['width'],
					$matches['height'],
					1,
					$matches['ext']
				);

				// skip if a file with the name already exists
				if ( file_exists( $newFile ) ) {
					continue;
				}

				// if the dir can't be created skip
				if ( ! wp_mkdir_p( $this->cachePath . '/' . $fragments[1] ) ) {
					continue;
				}

				// actually move the file
				copy( $file, $newFile );
			}
		}
	}
}
