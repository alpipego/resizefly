<?php

namespace Alpipego\Resizefly\Addon;

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
     * @param array $addon the addon data
     */
    public function __construct($addon)
    {
        $this->addonData = $addon;
    }

    /**
     * `run()` method for plugin container.
     */
    abstract public function run();
}
