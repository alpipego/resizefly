<?php

namespace Alpipego\Resizefly\Async\Queue;

use Alpipego\Resizefly\Async\Job;

class Queue implements QueueInterface
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @var int
     */
    protected $interval = 5;

    /**
     * @var WorkerInterface
     */
    protected $worker;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var int
     */
    protected $workerStartTime;
    protected $cronLock;

    public function __construct(ConnectionInterface $connection, WorkerInterface $worker, $queueId)
    {
        $this->connection = $connection;
        $this->id         = $queueId;
        $this->worker     = $worker;
        $this->interval   = (int)apply_filters('resizefly/queue/interval', $this->interval);
        $this->cronLock   = (int)apply_filters('resizefly/queue/cron_lock', $this->cronLock);
        $this->worker->setInterval($this->interval);
    }

    /**
     * Push a job onto the queue;.
     *
     * @param int $delay
     *
     * @return bool|int
     */
    public function push(Job $job, $delay = 0)
    {
        $return = $this->connection->push($job, $delay);
        $this->trigger($delay);

        return $return;
    }

    /**
     * Setup the watch tasks.
     */
    public function watch()
    {
        add_filter('cron_schedules', [$this, 'addSchedule']);
        add_action($this->id.'_watch', [$this, 'checkJobs']);
        add_action($this->id, [$this, 'checkJobs']);

        if (wp_next_scheduled($this->id)) {
            define('WP_CRON_LOCK_TIMEOUT', $this->cronLock);
        }

        if (!wp_next_scheduled($this->id.'_watch')) {
            // schedule watcher
            wp_schedule_event(time(), $this->id, $this->id.'_watch');
        }
    }

    /**
     * Add interval to cron schedules.
     *
     * @param array $schedules
     *
     * @return array
     */
    public function addSchedule($schedules)
    {
        $schedules[$this->id] = [
            'interval' => MINUTE_IN_SECONDS * $this->interval,
            'display'  => sprintf(esc_html(_n('Every %d Minute', 'Every %d Minutes', $this->interval, 'resizefly')), $this->interval),
        ];

        return $schedules;
    }

    /**
     * Process any jobs in the queue.
     */
    public function checkJobs()
    {
        if ($this->worker->isLocked() || empty($this->connection->jobs())) {
            return;
        }

        $this->workerStartTime = time();

        $this->worker->lock();

        while (!$this->timeExceeded() && !$this->memoryExceeded() && $this->connection->jobs() > 0) {
            $this->worker->process();
        }

        $this->worker->unlock();

        if ($this->connection->jobs() > 0 || $this->cronLock <= 60) {
            $url = get_bloginfo('wpurl').'/wp-cron.php';
            if (wp_doing_cron() && ($lock = _get_cron_lock())) {
                $url = add_query_arg(['wp_doing_cron' => $lock], $url);
            }

            sleep($this->cronLock);
            wp_remote_get($url, [
                'timeout'   => 0.01,
                'blocking'  => false,
                'sslverify' => false,
            ]);
        }
    }

    /**
     * Memory exceeded.
     *
     * Ensures the worker process never exceeds 80%
     * of the maximum allowed PHP memory.
     *
     * @return bool
     */
    protected function memoryExceeded()
    {
        $memory_limit   = $this->getMemoryLimit() * 0.8; // 80% of max memory
        $current_memory = memory_get_usage(true);
        $return         = false;

        if ($current_memory >= $memory_limit) {
            $return = true;
        }

        return (bool)apply_filters('resizefly/queue/memory_exceeded', $return);
    }

    /**
     * Get memory limit.
     *
     * @return int
     */
    protected function getMemoryLimit()
    {
        $memoryLimit = '256M';
        if (function_exists('ini_get')) {
            $memoryLimit = ini_get('memory_limit');
        }

        if (!$memoryLimit || $memoryLimit == -1) {
            // Unlimited, set to 1GB
            $memoryLimit = '1000M';
        }

        $memoryLimit = (int)apply_filters('resizefly/queue/memory_limit', $memoryLimit);

        return intval($memoryLimit) * 1024 * 1024;
    }

    /**
     * Time exceeded.
     *
     * Ensures the worker never exceeds a sensible time limit (20s by default).
     * A timeout limit of 30s is common on shared hosting.
     *
     * @return bool
     */
    protected function timeExceeded()
    {
        $finish = $this->workerStartTime + apply_filters('resizefly/queue/time_limit', 20); // 20 seconds
        $return = false;

        if (time() >= $finish) {
            $return = true;
        }

        return (bool)apply_filters('resizefly/queue/time_exceeded', $return);
    }

    /**
     * Pass a class name and check if there is an asynchronous implementation.
     *
     * @param string $class Class name to get asynchronous class for
     * @param mixed ...$args Args that will be passed to async class
     *
     * @return mixed
     */
    public function resolve($class, ...$args)
    {
        try {
            $namespace      = explode('\\', __NAMESPACE__);
            $requestedClass = explode('\\', get_class($class));
            $commonNs       = array_intersect($namespace, $requestedClass);
            if (empty($commonNs)) {
                throw new \Exception();
            }
            array_push($commonNs, 'Async');
            array_splice($requestedClass, 0, count($commonNs) - 1, $commonNs);
            $asyncClass = implode('\\', $requestedClass);
            if (!class_exists($asyncClass)) {
                throw new \Exception();
            }

            $this->push(new $asyncClass($class, ...$args));
            $this->trigger($this->cronLock);
        } catch (\Exception $e) {
        }
    }

    /**
     * Trigger cron handler
     *
     * @param int $delay
     */
    protected function trigger($delay = 0)
    {
        if (!wp_next_scheduled($this->id)) {
            add_action('shutdown', function () use ($delay) {
                wp_schedule_single_event(time() + $delay, $this->id);
            });
        }
    }
}
