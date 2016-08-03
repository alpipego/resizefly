<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 12:18
 */

namespace Alpipego\Resizefly\Admin;


interface OptionInterface {
	public function run();

	public function registerSetting();

	public function addField();

	public function callback();

	public function sanitize( $value );
}
