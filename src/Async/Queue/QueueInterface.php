<?php

namespace Alpipego\Resizefly\Async\Queue;

use Alpipego\Resizefly\Async\Job;

interface QueueInterface
{
    /**
     * Push a job onto the queue;.
     *
     * @param int $delay
     *
     * @return bool|int
     */
    public function push(Job $job, $delay = 0);

    /**
     * Setup the watch tasks.
     */
    public function watch();

    /**
     * Pass a class name and check if there is an asynchronous implementation.
     *
     * @param string $class   Class name to get asynchronous class for
     * @param mixed  ...$args Args that will be passed to async class
     *
     * @return mixed
     */
    public function resolve($class, ...$args);
}
