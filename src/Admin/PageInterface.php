<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 08/10/16
 * Time: 13:59
 */

namespace Alpipego\Resizefly\Admin;

/**
 * Interface AdminInterface
 * @package Alpipego\Resizefly\Admin
 */
interface PageInterface
{
    /**
     * @return mixed
     */
    public function run();

    public function getId();

    public function localize(array $toLocalize);
}
