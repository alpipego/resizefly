<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 04/08/16
 * Time: 11:46
 */

namespace Alpipego\Resizefly\Admin;

use Alpipego\Resizefly\Plugin;

class Admin extends AbstractAdmin implements AdminInterface {
	protected $optionsPage;
	private $plugin;

	public function __construct( Plugin $plugin, $optionsPage ) {
		$this->plugin      = $plugin;
		$this->optionsPage = $optionsPage;
	}

	/**
	 * Add plugin action links on plugin page
	 */
	public function run() {
		add_filter( 'plugin_action_links_' . $this->plugin['basename'], [ $this, 'addActionLinks' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
	}

	public function enqueueAssets( $page ) {
		if ( $page === $this->optionsPage->page ) {
			wp_enqueue_script( 'resizefly-admin', $this->plugin['url'] . 'js/resizefly-admin.min.js', [ 'jquery' ], '1.0.0', true );
			wp_add_inline_style( 'wp-admin', file_get_contents( $this->plugin['url'] . '/css/resizefly-admin.css' ) );

			// localize
			/** @var PurgeCacheField $purgeOption */
			$purgeOption = $this->plugin['option_purge_cache'];
			/** @var RemoveResizedField $removeResizedOption */
			$removeResizedOption = $this->plugin['option_remove_resized'];

			$this->localizeScript( [
				'purge_result'     => sprintf( __( '%s file(s) have been removed and %s of disk space has been freed.', 'resizefly' ), '<span class="resizefly-files"></span>', '<span class="resizefly-size"></span>' ),
				'purge_empty'      => __( 'No files were removed because the cache was already empty.', 'resizefly' ),
				'purge_id'         => $purgeOption->optionsField['id'],
				'resized_id'       => $removeResizedOption->optionsField['id'],
				'user_size_errors' => [
					'dimension' => __( 'Please supply at least one of width or height', 'resizefly' ),
					'name'      => __( 'Please add a unique (and descriptive) name for this image size', 'resizefly' ),
				]
			] );

			wp_localize_script( 'resizefly-admin', 'resizefly', $this->localized );
		}
	}

	/**
	 * Add a link to the settings on plugin page
	 *
	 * @param array $links array with existing plugin actions
	 *
	 * @return array
	 */
	public function addActionLinks( $links ) {
		$links[] = sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', \get_admin_url( null, 'upload.php?page=resizefly' ), __( 'ResizeFly Settings', 'resizefly' ) );

		return $links;
	}
}
