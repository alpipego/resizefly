<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 19/07/16
 * Time: 17:47
 */

namespace Alpipego\Resizefly\Addon;

use Alpipego\Resizefly\Plugin;

/**
 * Class AbstractAddon
 * @package Alpipego\Resizefly\Addon
 */
abstract class AbstractAddon {
	/**
	 * @var array $addonData defined addon data
	 */
	protected $addonData;

	/**
	 * AbstractAddon constructor.
	 *
	 * @param Plugin $plugin the plugin container
	 * @param string $addon the addon name
	 */
	public function __construct( Plugin $plugin, $addon ) {
		$this->addonData = $plugin['addons'][ $addon ];
	}

	/**
	 * `run()` method for plugin container
	 */
	abstract public function run();

}
