<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25/07/16
 * Time: 14:30
 */

namespace Alpipego\Resizefly\Admin;


/**
 * Class AbstractOptionsSection
 * @package Alpipego\Resizefly\Admin
 */
abstract class AbstractOptionsSection {
	/**
	 * @var array $optionsGroup id and title for field group
	 */
	public $optionsGroup = [
		'id'   => null,
		'name' => null,
	];

	/**
	 * @var string $optionsPage id to pass to add_settings_section
	 */
	protected $optionsPage;

	/**
	 * @var string $viewsPath path to views dir
	 */
	protected $viewsPath;

	/**
	 * AbstractOptionsSection constructor.
	 *
	 * @param string $page settings page id
	 * @param string $pluginPath base plugin path
	 */
	public function __construct( $page, $pluginPath ) {
		$this->viewsPath   = $pluginPath . 'views/';
		$this->optionsPage = $page;
	}

	/**
	 * Add section to WP Admin on admin_init hook
	 */
	public function run() {
		\add_action( 'admin_init', [ $this, 'addSection' ] );
	}

	/**
	 * Wrapper for add_settings_section
	 */
	public function addSection() {
		\add_settings_section( $this->optionsGroup['id'], $this->optionsGroup['name'], [
			$this,
			'callback'
		], $this->optionsPage );
	}

	/**
	 * Include view for settings group
	 *
	 * @param string $name name of template file to include
	 * @param array $args optional array of variables to pass to template
	 */
	protected function includeView( $name, $args = [] ) {
		$fileArr = preg_split( '/(?=[A-Z-_])/', $name );
		$fileArr = array_map( function ( &$value ) {
			return trim( $value, '-_' );
		}, $fileArr );
		$fileArr = array_map( 'strtolower', $fileArr );

		include $this->viewsPath . 'section/' . implode( '-', $fileArr ) . '.php';
	}
}
