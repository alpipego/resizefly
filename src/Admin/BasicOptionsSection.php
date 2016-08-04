<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25/07/16
 * Time: 14:31
 */

namespace Alpipego\Resizefly\Admin;


/**
 * Class BasicOptionsSection
 * @package Alpipego\Resizefly\Admin
 */
class BasicOptionsSection extends AbstractOptionsSection implements OptionsSectionInterface {

	/**
	 * BasicOptionsSection constructor.
	 *
	 * @param $plugin
	 * @param $page
	 */
	public function __construct( $plugin, $page ) {
		$this->optionsGroup = [
			'id'   => 'resizefly_basic',
			'name' => 'Basic Options',
		];
		parent::__construct( $plugin, $page );
	}

	/**
	 * callback for section
	 */
	public function callback() {
		$this->includeView($this->optionsGroup['id'], $this->optionsGroup);
	}
}
