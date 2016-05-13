<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/13/16
 * Time: 2:32 PM
 */

namespace Alpipego\DynamicImage;

class Autoload
{
    private $dir;
    private $ns;

    public function __construct($dir, $ns = __NAMESPACE__)
    {
        $this->dir = $dir;
        $this->ns = $ns;
        $this->load();
    }

    public function load()
    {
        \spl_autoload_register(function($class) {
            $class = ltrim($class, '\\');
            if(strpos($class, $this->ns) !== 0) {
                return;
            }
            $class = str_replace($this->ns, '', $class);
            $path = $this->dir . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            require_once $path;
        });
    }
}
