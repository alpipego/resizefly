<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 15.07.2017
 * Time: 07:45
 */

namespace Alpipego\Resizefly\DI;


class ObjectDefinition
{
    public $constructorParams = [];

    public function constructorParam($name, $value) {
        $this->constructorParams[$name] = $value;

        return $this;
    }
}
