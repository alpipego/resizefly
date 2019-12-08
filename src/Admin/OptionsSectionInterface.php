<?php

namespace Alpipego\Resizefly\Admin;

/**
 * Interface OptionsSectionInterface.
 */
interface OptionsSectionInterface
{
    /**
     * actions and filters to be added.
     */
    public function run();

    /**
     * Wrapper for `add_settings_section`.
     */
    public function addSection();

    /**
     * Callback function.
     */
    public function callback();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();
}
