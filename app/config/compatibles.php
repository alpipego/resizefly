<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 01.11.18
 * Time: 16:36
 */

use Alpipego\Resizefly\Compatibles\WPML;

return [
    WPML::class => \Alpipego\Resizefly\object()
        ->instantiateEarly(),
];
