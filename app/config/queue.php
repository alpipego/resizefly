<?php

use function Alpipego\Resizefly\object;

return [
    'queueId'                                            => 'rzf/queue',
    'Alpipego\Resizefly\Async\Queue\ConnectionInterface' => 'Alpipego\Resizefly\Async\Queue\DatabaseConnection',
    'Alpipego\Resizefly\Async\Queue\WorkerInterface'     => 'Alpipego\Resizefly\Async\Queue\Worker',
    'Alpipego\Resizefly\Async\Queue\Queue'               => object(),
];
