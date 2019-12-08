<?php

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

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $name
     *
     * @return string
     */
    public function getView($name);
}
