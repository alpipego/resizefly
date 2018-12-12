<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 12:10.
 */

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

    public function getId();

    public function getTitle();
}
