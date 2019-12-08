<?php

namespace Alpipego\Resizefly\DI;

class ObjectDefinition
{
    public $constructorParams = [];
    public $instantiateEarly  = false;

    public function constructorParam($name, $value)
    {
        $this->constructorParams[$name] = $value;

        return $this;
    }

    public function instantiateEarly($early = true)
    {
        $this->instantiateEarly = $early;

        return $this;
    }
}
