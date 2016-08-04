<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 04/08/16
 * Time: 11:46
 */

namespace Alpipego\Resizefly\Admin;

use Alpipego\Resizefly\Plugin;

class Admin {
	private $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function run() {
		\add_filter( 'plugin_action_links_' . $this->plugin['basename'], [ $this, 'addActionLinks' ] );
	}

	public function addActionLinks( $links ) {
		$links[] = sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', \get_admin_url( null, 'upload.php?page=resizefly' ), __( 'ResizeFly Settings', 'resizefly' ) );

		return $links;
	}
}
