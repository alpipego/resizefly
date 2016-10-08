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
class CacheSection extends AbstractOptionsSection implements OptionsSectionInterface {

	/**
	 * BasicOptionsSection constructor.
	 *
	 * @param $plugin
	 * @param $page
	 */
	public function __construct( $plugin, $page ) {
		$this->optionsGroup = [
			'id'   => 'resizefly_cache',
			'name' => __('Cache Settings', 'resizefly'),
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
