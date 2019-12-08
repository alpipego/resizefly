<?php

namespace Alpipego\Resizefly\DI;

use Alpipego\Resizefly\Common\Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
}
