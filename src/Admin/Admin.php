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
	private $plugin;
	protected $optionsPage;

	public function __construct( Plugin $plugin, $optionsPage ) {
		$this->plugin = $plugin;
		$this->optionsPage = $optionsPage;
	}

	public function run() {
		\add_filter( 'plugin_action_links_' . $this->plugin['basename'], [ $this, 'addActionLinks' ] );
		\add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
	}

	public function enqueueScripts( $page ) {
		if ( $page == $this->optionsPage->page ) {
			\wp_enqueue_script( 'resizefly-admin', $this->plugin['url'] . 'js/resizefly-admin.min.js', [ 'jquery' ], '1.0.0', true );
			// add strings to translate
			$this->localizeScript( [
				'purge_result' => sprintf( __( '%s file(s) have been removed and %s of disk space has been freed.', 'resizefly' ), '<span class="resizefly-files"></span>', '<span class="resizefly-size"></span>' ),
				'purge_empty' => __('No files were removed because the cache was already empty.', 'resizefly'),
			] );


			\wp_localize_script( 'resizefly-admin', 'resizefly', $this->localized );
		}
	}

	public function addActionLinks( $links ) {
		$links[] = sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', \get_admin_url( null, 'upload.php?page=resizefly' ), __( 'ResizeFly Settings', 'resizefly' ) );

		return $links;
	}
}
