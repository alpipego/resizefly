<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25/07/16
 * Time: 15:10
 */

namespace Alpipego\Resizefly\Admin;


abstract class AbstractOption {
	public $optionsField = [
		'id'    => null,
		'title' => null,
	];
	protected $viewsPath;
	protected $optionsPage;
	protected $optionsGroup;

	public function __construct( $page, $section, $pluginPath ) {
		$this->viewsPath    = $pluginPath . 'views/';
		$this->optionsPage  = $page;
		$this->optionsGroup = $section;
	}

	public function run() {
		\add_action( 'admin_init', [ $this, 'addField' ] );
		\add_action( 'admin_init', [ $this, 'registerSetting' ] );
	}

	public function registerSetting() {
		\register_setting( 'resizefly', $this->optionsField['id'], [ $this, 'sanitize' ] );
	}

	public function addField() {
		\add_settings_field( $this->optionsField['id'], $this->optionsField['title'], [
			$this,
			'callback'
		], $this->optionsPage, $this->optionsGroup );
	}

	protected function includeView( $name, $args ) {
		$fileArr = preg_split( '/(?=[A-Z-_])/', $name );
		$fileArr = array_map( function ( &$value ) {
			return trim( $value, '-_' );
		}, $fileArr );
		$fileArr = array_map( 'strtolower', $fileArr );

		include $this->viewsPath . 'field/' . implode( '-', $fileArr ) . '.php';
	}
}
