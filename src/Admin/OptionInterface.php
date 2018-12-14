<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 12:18.
 */

namespace Alpipego\Resizefly\Admin;

/**
 * Interface OptionInterface.
 */
interface OptionInterface
{
    /**
     * run actions and filters.
     */
    public function run();

    /**
     * Register the setting.
     */
    public function registerSetting();

    /**
     * Add the settings field.
     */
    public function addField();

    /**
     * Add a callback to settings field.
     */
    public function callback();

    /**
     * Sanitize values added to this settings field.
     *
     * @param mixed $value value to sanititze
     *
     * @return mixed
     */
    public function sanitize($value);

    public function getId();

    public function getTitle();

    public function getView($name);
}
