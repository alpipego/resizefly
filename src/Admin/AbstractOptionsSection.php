<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25/07/16
 * Time: 14:30
 */

namespace Alpipego\Resizefly\Admin;


abstract class AbstractOptionsSection {
	public $optionsGroup = [
		'id'   => null,
		'name' => null,
	];
	protected $optionsPage;
	protected $viewsPath;

	public function __construct( $page, $pluginPath ) {
		$this->viewsPath   = $pluginPath . 'views/';
		$this->optionsPage = $page;
	}

	public function run() {
		\add_action( 'admin_init', [ $this, 'addSection' ] );
	}

	public function addSection() {
		\add_settings_section( $this->optionsGroup['id'], $this->optionsGroup['name'], [
			$this,
			'callback'
		], $this->optionsPage );
	}

//	abstract public function callback();

	protected function includeView( $name, $args ) {
		$fileArr = preg_split( '/(?=[A-Z-_])/', $name );
		$fileArr = array_map( function ( &$value ) {
			return trim( $value, '-_' );
		}, $fileArr );
		$fileArr = array_map( 'strtolower', $fileArr );

		include $this->viewsPath . 'section/' . implode( '-', $fileArr ) . '.php';
	}
}
