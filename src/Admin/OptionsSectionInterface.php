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
interface OptionsSectionInterface
{

    /**
     * actions and filters to be added
     *
     * @return void
     */
    public function run();

    /**
     * Wrapper for `add_settings_section`
     *
     * @return void
     */
    public function addSection();

    /**
     * Callback function
     *
     * @return void
     */
    public function callback();

    public function getId();

    public function getTitle();
}
