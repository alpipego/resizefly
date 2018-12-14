<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 19/07/16
 * Time: 17:47.
 */

namespace Alpipego\Resizefly\Addon;

use Alpipego\Resizefly\Plugin;

/**
 * Class AbstractAddon.
 */
abstract class AbstractAddon
{
    /**
     * @var array defined addon data
     */
    protected $addonData;

    /**
     * AbstractAddon constructor.
     *
     * @param Plugin $plugin the plugin container
     * @param string $addon  the addon name
     */
    public function __construct(Plugin $plugin, $addon)
    {
        $this->addonData = $plugin['addons'][$addon];
    }

    /**
     * `run()` method for plugin container.
     */
    abstract public function run();
}
