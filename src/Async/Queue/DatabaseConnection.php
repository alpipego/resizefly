<?php

namespace Alpipego\Resizefly\Async\Queue;

use Alpipego\Resizefly\Async\Job;
use Exception;
use wpdb;

class DatabaseConnection implements ConnectionInterface
{
    /**
     * @var wpdb
     */
    protected $database;

    /**
     * @var string
     */
    protected $jobs_table;

    /**
     * @var string
     */
    protected $failures_table;

    public function __construct()
    {
        $this->database       = $GLOBALS['wpdb'];
        $this->jobs_table     = $this->database->prefix.'rzf_queue_jobs';
        $this->failures_table = $this->database->prefix.'rzf_queue_failures';
    }

    /**
     * Push a job onto the queue.
     *
     * @param int $delay
     *
     * @return bool|int
     */
    public function push(Job $job, $delay = 0)
    {
        $result = $this->database->insert($this->jobs_table, [
            'job'          => serialize($job),
            'available_at' => $this->datetime($delay),
            'created_at'   => $this->datetime(),
        ]);

        if (! $result) {
            return false;
        }

        return $this->database->insert_id;
    }

    /**
     * Retrieve a job from the queue.
     *
     * @return bool|Job
     */
    public function pop()
    {
        $this->releaseReserved();

        $sql = $this->database->prepare("
			SELECT * FROM {$this->jobs_table}
			WHERE reserved_at IS NULL
			AND available_at <= %s
			ORDER BY available_at
			LIMIT 1
		", $this->datetime());

        $raw_job = $this->database->get_row($sql);

        if (is_null($raw_job)) {
            return false;
        }

        $job = $this->vitalizeJob($raw_job);

        $this->reserve($job);

        return $job;
    }

    /**
     * Delete a job from the queue.
     *
     * @param Job $job
     *
     * @return bool
     */
    public function delete($job)
    {
        $where = [
            'id' => $job->getId(),
        ];

        if ($this->database->delete($this->jobs_table, $where)) {
            return true;
        }

        return false;
    }

    /**
     * Release a job back onto the queue.
     *
     * @param Job $job
     *
     * @return bool
     */
    public function release($job)
    {
        $data  = [
            'job'         => serialize($job),
            'attempts'    => $job->getAttempts(),
            'reserved_at' => null,
        ];
        $where = [
            'id' => $job->getId(),
        ];

        if ($this->database->update($this->jobs_table, $data, $where)) {
            return true;
        }

        return false;
    }

    /**
     * Push a job onto the failure queue.
     *
     * @param Job $job
     *
     * @return bool
     */
    public function failure($job, Exception $exception)
    {
        $insert = $this->database->insert($this->failures_table, [
            'job'       => serialize($job),
            'error'     => $this->formatException($exception),
            'failed_at' => $this->datetime(),
        ]);

        if ($insert) {
            $this->delete($job);

            return true;
        }

        return false;
    }

    /**
     * Get total jobs in the queue.
     *
     * @return int
     */
    public function jobs()
    {
        return (int) $this->database->get_var("SELECT COUNT(*) FROM {$this->jobs_table}");
    }

    /**
     * Get total jobs in the failures queue.
     *
     * @return int
     */
    public function failedJobs()
    {
        return (int) $this->database->get_var("SELECT COUNT(*) FROM {$this->failures_table}");
    }

    /**
     * Reserve a job in the queue.
     *
     * @param Job $job
     */
    protected function reserve($job)
    {
        $data = [
            'reserved_at' => $this->datetime(),
        ];

        $this->database->update($this->jobs_table, $data, [
            'id' => $job->getId(),
        ]);
    }

    /**
     * Release reserved jobs back onto the queue.
     */
    protected function releaseReserved()
    {
        $expired = $this->datetime(-300);

        $sql = $this->database->prepare("
				UPDATE {$this->jobs_table}
				SET attempts = attempts + 1, reserved_at = NULL
				WHERE reserved_at <= %s", $expired);

        $this->database->query($sql);
    }

    /**
     * Vitalize Job with latest data.
     *
     * @param mixed $raw_job
     *
     * @return Job
     */
    protected function vitalizeJob($raw_job)
    {
        /** @var Job $job */
        $job = unserialize($raw_job->job);

        $job->setId($raw_job->id)
            ->setAttempts($raw_job->attempts)
            ->setReservedAt(empty($raw_job->reserved_at) ? null : $raw_job->reserved_at)
            ->setAvailableAt($raw_job->available_at)
            ->setCreatedAt($raw_job->created_at);

        return $job;
    }

    /**
     * Get MySQL datetime.
     *
     * @param int $offset seconds, can pass negative int
     *
     * @return string
     */
    protected function datetime($offset = 0)
    {
        $timestamp = time() + $offset;

        return gmdate('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Format an exception error string.
     *
     * @return string
     */
    protected function formatException(Exception $exception)
    {
        $string = get_class($exception);

        if (! empty($exception->getMessage())) {
            $string .= " : {$exception->getMessage()}";
        }

        if (! empty($exception->getCode())) {
            $string .= " (#{$exception->getCode()})";
        }

        return $string;
    }
}
