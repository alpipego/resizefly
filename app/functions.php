<?php

namespace Alpipego\Resizefly;

use Alpipego\Resizefly\DI\ObjectDefinition;

function object()
{
    return new ObjectDefinition();
}

function throw404()
{
    status_header('404');
    @include_once get_404_template();

    exit;
}
