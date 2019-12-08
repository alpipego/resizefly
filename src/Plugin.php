<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/17/16
 * Time: 12:23 PM.
 */

namespace Alpipego\Resizefly;

use Alpipego\Resizefly\Common\Pimple\Container;
use Alpipego\Resizefly\Common\Psr\Container\ContainerInterface;
use Alpipego\Resizefly\DI\NotFoundException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * Extends Pimple Container.
 */
class Plugin extends Container implements ContainerInterface
{
    private $definitions = [];

    private $stock = [];

    /**
     * Calls `run()` method on all objects registered on plugin container.
     *
     * @throws ReflectionException
     */
    public function run()
    {
        $keys = array_merge($this->keys(), array_keys($this->definitions));
        foreach ($keys as $key) {
            if (in_array($key, $this->stock)) {
                continue;
            }
            $this->stock[] = $key;
            $content       = $this->get($key);

            if (! is_object($content)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($content);
                try {
                    $dependencies = $reflection->getMethod('run')->getParameters();
                    $dependencies = array_map(function (ReflectionParameter $dependency) use ($key) {
                        return $this->resolveDependency($dependency, $key);
                    }, $dependencies);

                    call_user_func_array([$content, 'run'], $dependencies);
                } catch (ReflectionException $e) {
                }
            } catch (ReflectionException $e) {
            }
        }
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id identifier of the entry to look for
     *
     * @return mixed entry
     * @throws ReflectionException
     *
     * @throws \Alpipego\Resizefly\DI\NotFoundException
     */
    public function get($id)
    {
        // simple value exists
        if ($this->has($id)) {
            return $this[$id];
        }

        try {
            $reflector = new ReflectionClass($id);
        } catch (ReflectionException $e) {
            // check if complex value exists
            $configArray = $this->configArray($id);
            if (! empty($configArray)) {
                return $configArray;
            }
            // if mapped value exists
            if (array_key_exists($id, $this->definitions)) {
                if ($this->has($this->definitions[$id])) {
                    return $this[$this->definitions[$id]];
                }

                return $this->definitions[$id];
            }
            throw new NotFoundException(sprintf('Identifier "%s" is not defined.', $id));
        }

        if ($reflector->isInterface()) {
            if (array_key_exists($id, $this->definitions)) {
                $id = $this->definitions[$id];
                if ($this->has($id)) {
                    return $this[$id];
                }
                $reflector = new ReflectionClass($id);
            }
        }

        /** @var null|\ReflectionMethod */
        $constructor = $reflector->getConstructor();

        if (null !== $constructor) {
            /** @var \ReflectionParameter[] */
            $dependencies = $constructor->getParameters();
        }

        if (null === $constructor || empty($dependencies)) {
            $this->offsetSet($id, function () use ($id) {
                return new $id();
            });

            return $this[$id];
        }

        $dependencies = array_map(function (ReflectionParameter $dependency) use ($id) {
            return $this->resolveDependency($dependency, $id);
        }, $dependencies);

        $this->offsetSet($id, function () use ($reflector, $dependencies) {
            return $reflector->newInstanceArgs($dependencies);
        });

        return $this[$id];
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id identifier of the entry to look for
     *
     * @return bool
     */
    public function has($id)
    {
        return isset($this[$id]);
    }

    /**
     * @param mixed $definition
     *
     * @throws \Exception
     */
    public function addDefiniton($definition)
    {
        if (is_string($definition)) {
            if (! file_exists($definition)) {
                throw new \Exception(sprintf('%s not a readable file', gettype($definition)));
            }
            $definition = require_once $definition;
        }

        if (! is_array($definition)) {
            throw new \Exception(sprintf('Definiton has to be an array, %s given', gettype($definition)));
        }

        $this->definitions = array_merge($this->definitions, $definition);
    }

    /**
     * @param string $dir path to languages dir
     *
     * @deprecated 3.1.0
     * wrapper for `load_plugin_textdomain`.
     *
     */
    public function loadTextdomain($dir)
    {
        load_plugin_textdomain('resizefly', false, $dir);
    }

    public function getEarly()
    {
        array_walk($this->definitions, function ($element, $id) {
            if (! is_a($element, 'Alpipego\Resizefly\DI\ObjectDefinition')) {
                return;
            }

            if (! $element->instantiateEarly) {
                return;
            }

            $this->get($id);
        });
    }

    /**
     * @param string $id
     *
     * @return array
     */
    private function configArray($id)
    {
        // check if this is an array-like config request and return it as array
        $return = [];
        $idArr  = (array)explode('.', $id);
        foreach ($this->keys() as $key) {
            $keyArr = explode('.', $key);
            if (! array_diff($idArr, $keyArr)) {
                $keys  = array_diff($keyArr, $idArr);
                $value = $this[$key];
                while ($key = array_pop($keys)) {
                    $value = [$key => $value];
                }

                $return = array_merge_recursive($return, $value);
            }
        }

        return $return;
    }

    /**
     * @param string $id
     *
     * @return mixed
     * @throws ReflectionException
     *
     */
    private function resolveDependency(ReflectionParameter $dependency, $id)
    {
        if (array_key_exists($id, $this->definitions)) {
            if (array_key_exists($dependency->getName(), $this->definitions[$id]->constructorParams)) {
                return $this->get($this->definitions[$id]->constructorParams[$dependency->getName()]);
            }
            // configuration values
            if (! $dependency->isCallable()) {
                // simple values
                if ($this->has($dependency->getName())) {
                    return $this->get($dependency->getName());
                }
                // mapped values
                if (array_key_exists(
                        $dependency->getName(),
                        $this->definitions
                    ) && $this->has($this->definitions[$dependency->getName()])
                ) {
                    return $this->get($this->definitions[$dependency->getName()]);
                }
            }
        }

        if (array_key_exists($dependency->getName(), $this->definitions)) {
            return $this->definitions[$dependency->getName()];
        }

        if ($dependency->isDefaultValueAvailable()) {
            return $dependency->getDefaultValue();
        }

        if (null === $dependency->getClass()) {
            throw new NotFoundException(sprintf('Identifier "%s" is not defined.', $dependency));
        }

        return $this->get($dependency->getClass()->getName());
    }
}
