<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 19/07/16
 * Time: 17:47
 */

namespace Alpipego\Resizefly\Addon;

use Alpipego\Resizefly\Plugin;

abstract class AbstractAddon {
	protected $addonData;

	public function __construct( Plugin $plugin, $addon ) {
		$this->addonData = $plugin['addons'][ $addon ];
	}

	abstract public function run();

}
