<?php

namespace Alpipego\Resizefly\Async\Queue;

use Exception;

class Worker implements WorkerInterface
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @var int
     */
    protected $attempts = 3;

    /**
     * @var string
     */
    protected $queueId;

    /**
     * @var int
     */
    protected $watcherInterval = 5;

    /**
     * @param string $queueId
     */
    public function __construct(ConnectionInterface $connection, $queueId)
    {
        $this->connection = $connection;
        $this->attempts   = apply_filters('resizefly/queue/worker_attempts', $this->attempts);
        $this->queueId    = $queueId;
    }

    /**
     * Process a job on the queue.
     *
     * @return bool
     */
    public function process()
    {
        $job = $this->connection->pop();

        if (! $job) {
            return false;
        }

        $exception = null;

        try {
            $job->handle();
        } catch (Exception $exception) {
            $job->release();
        }

        if ($job->getAttempts() >= $this->attempts) {
            if (empty($exception)) {
                $exception = new Exception('Worker attempst exceeded');
            }

            $job->fail();
        }

        if ($job->isFailed()) {
            $this->connection->failure($job, $exception);
        } elseif ($job->isReleased()) {
            $this->connection->release($job);
        } else {
            $this->connection->delete($job);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return (bool) get_site_transient($this->queueId);
    }

    public function lock()
    {
        set_site_transient($this->queueId, time(), ($this->watcherInterval * MINUTE_IN_SECONDS) - 1);
    }

    public function unlock()
    {
        delete_site_transient($this->queueId);
    }

    /**
     * {@inheritdoc}
     */
    public function setInterval($interval)
    {
        $this->watcherInterval = $interval;
    }
}
