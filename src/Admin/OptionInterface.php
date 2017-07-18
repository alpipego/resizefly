<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 12:18
 */

namespace Alpipego\Resizefly\Admin;


/**
 * Interface OptionInterface
 * @package Alpipego\Resizefly\Admin
 */
interface OptionInterface {

	/**
	 * run actions and filters
	 *
	 * @return void
	 */
	public function run();

	/**
	 * Register the setting
	 *
	 * @return void
	 */
	public function registerSetting();

	/**
	 * Add the settings field
	 *
	 * @return void
	 */
	public function addField();

	/**
	 * Add a callback to settings field
	 *
	 * @return void
	 */
	public function callback();

	/**
	 * Sanitize values added to this settings field
	 *
	 * @param mixed $value value to sanititze
	 *
	 * @return mixed
	 */
	public function sanitize( $value );

	public function getId();

	public function getTitle();
}
