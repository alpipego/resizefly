<?php

namespace Alpipego\Resizefly\Async\Queue;

interface WorkerInterface
{
    /**
     * @return bool
     */
    public function process();

    /**
     * @param string $interval
     */
    public function setInterval($interval);

    /**
     * @return bool
     */
    public function isLocked();

    public function lock();

    public function unlock();
}
