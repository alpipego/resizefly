<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 16:05
 */

namespace Alpipego\Resizefly\Admin;

class RemoveResizedField extends AbstractOption implements OptionInterface {
	public function __construct( $page, $section, $pluginPath ) {
		$this->optionsField = [
			'id' => 'resizefly_remove_resized',
			'title' => esc_attr__('Remove All Resized Images', 'resizefly'),
			'args'  => ['class' => 'hide-if-no-js'],
		];
		parent::__construct( $page, $section, $pluginPath );
	}

	public function callback() {
		$this->includeView($this->optionsField['id'], $this->optionsField);
	}

	public function sanitize( $value ) {
		return $value;
	}
}
