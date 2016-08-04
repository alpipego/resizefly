<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 21/07/16
 * Time: 17:36
 */

namespace Alpipego\Resizefly\Admin;

/**
 * Class OptionsPage
 * @package Alpipego\Resizefly\Admin
 */
class OptionsPage {
	/**
	 * @var string
	 */
	public $page;
	/**
	 * @var string
	 */
	protected $viewsPath;

	/**
	 * OptionsPage constructor.
	 *
	 * @param $pluginPath
	 */
	function __construct( $pluginPath ) {
		$this->viewsPath = $pluginPath . 'views/';
		$this->page = 'media_page_resizefly';
	}

	/**
	 * Add the page to admin menu action
	 */
	public function run() {
		\add_action( 'admin_menu', [ $this, 'addPage' ] );
	}

	/**
	 * Add the page in media section
	 */
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

	/**
	 * Include the view
	 */
	function callback() {
		$args = [
			'page' => $this->page,
		];

		include $this->viewsPath . 'page/resizefly.php';
	}
}
