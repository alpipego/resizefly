<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 04/08/16
 * Time: 11:46
 */

namespace Alpipego\Resizefly\Admin;

use Alpipego\Resizefly\Plugin;

/**
 * Class Admin - add action links on plugin screen
 *
 * @package Alpipego\Resizefly\Admin
 */
class Admin {
	/**
	 * @var Plugin $plugin instance of this plugin
	 */
	private $plugin;

	/**
	 * Admin constructor.
	 *
	 * @param Plugin $plugin instance of this plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Add plugin action links on plugin page
	 */
	public function run() {
		\add_filter( 'plugin_action_links_' . $this->plugin['basename'], [ $this, 'addActionLinks' ] );
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
