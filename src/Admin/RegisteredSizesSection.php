<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25.06.2017
 * Time: 12:12
 */

namespace Alpipego\Resizefly\Admin;


class RegisteredSizesSection extends AbstractOptionsSection implements OptionsSectionInterface {

	/**
	 * RegisteredSizesSection constructor.
	 *
	 * @param string $plugin
	 * @param string $page
	 */
	public function __construct( $plugin, $page ) {
		$this->optionsGroup = [
			'id'   => 'resizefly_registered_sizes',
			'name' => __('Registered Sizes Settings', 'resizefly'),
		];
		parent::__construct( $plugin, $page );
	}

	/**
	 * Callback function
	 *
	 * @return void
	 */
	function callback() {
		$this->includeView($this->optionsGroup['id'], $this->optionsGroup);
	}
}
