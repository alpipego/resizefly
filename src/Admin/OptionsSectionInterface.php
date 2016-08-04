<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 12:10
 */

namespace Alpipego\Resizefly\Admin;


/**
 * Interface OptionsSectionInterface
 * @package Alpipego\Resizefly\Admin
 */
interface OptionsSectionInterface {

	/**
	 * @return void
	 */
	function run();

	/**
	 * Wrapper for `add_settings_section`
	 *
	 * @return void
	 */
	function addSection();

	/**
	 * @return mixed
	 */
	function callback();

}
