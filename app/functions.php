<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 15.07.2017
 * Time: 07:41
 */

namespace Alpipego\Resizefly;


use Alpipego\Resizefly\DI\ObjectDefinition;

function object()
{
    return new ObjectDefinition();
}

function throw404() {
    status_header('404');
    @include_once get_404_template();

    exit;
}
