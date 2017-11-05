<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 14.07.2017
 * Time: 10:45
 */

use Alpipego\Resizefly\Common\Composer\Autoload\ClassLoader;

require_once __DIR__ . '/../src/Common/Composer/Autoload/ClassLoader.php';

$loader = new ClassLoader();
$loader->setPsr4('Alpipego\\Resizefly\\', realpath(__DIR__ . '/../src/'));
$loader->register();
