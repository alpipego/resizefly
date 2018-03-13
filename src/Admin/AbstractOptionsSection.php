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
		'id'    => null,
		'title' => null,
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
	 * @param PageInterface $page
	 * @param string $pluginPath base plugin path
	 */
	public function __construct( PageInterface $page, $pluginPath ) {
		$this->optionsPage = $page;
		$this->viewsPath   = $pluginPath . 'views/';
	}

	/**
	 * Add section to WP Admin on admin_init hook
	 */
	public function run() {
		add_action( 'admin_init', [ $this, 'addSection' ] );
	}

	/**
	 * Wrapper for add_settings_section
	 */
	public function addSection() {
		add_settings_section( $this->optionsGroup['id'], $this->optionsGroup['title'], [
			$this,
			'callback',
		], $this->optionsPage->getId() );
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

	public function getId() {
		return $this->optionsGroup['id'];
	}

	public function getTitle() {
		return $this->optionsGroup['title'];
	}
}
