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
	 * @var string $page id of this settings page
	 */
	public $page;

	/**
	 * @var string $viewsPath path to views dir
	 */
	protected $viewsPath;

	/**
	 * OptionsPage constructor.
	 *
	 * @param string $pluginPath plugin base path
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
	 * Wrapper for add_media_section
	 *
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
