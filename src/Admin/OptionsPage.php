<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 21/07/16
 * Time: 17:36
 */

namespace Alpipego\Resizefly\Admin;

class OptionsPage {
	public $page;
	protected $viewsPath;

	function __construct( $pluginPath ) {
		$this->viewsPath = $pluginPath . 'views/';
		$this->page = 'media_page_resizefly';
	}

	public function run() {
		\add_action( 'admin_menu', [ $this, 'addPage' ] );
	}

	function addPage() {
		\add_media_page(
			'Resizefly Settings',
			'Resizefly',
			'manage_options',
			'resizefly',
			[
				$this,
				'callback'
			]
		);
	}

	function callback() {
		$args = [
			'page' => $this->page,
		];

		include $this->viewsPath . 'page/resizefly.php';
	}
}
