<?php

namespace Alpipego\Resizefly\Async;

abstract class Job
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $attempts;

    private $reservedAt;

    private $availableAt;

    private $createdAt;

    /**
     * @var bool
     */
    private $released = false;

    /**
     * @var bool
     */
    private $failed = false;

    /**
     * Determine which properties should be serialized.
     *
     * @return array
     */
    public function __sleep()
    {
        $object_props   = get_object_vars($this);
        $excluded_props = [
            'id',
            'attempts',
            'reservedAt',
            'availableAt',
            'createdAt',
            'released',
            'failed',
        ];

        foreach ($excluded_props as $prop) {
            unset($object_props[$prop]);
        }

        return array_keys($object_props);
    }

    /**
     * Handle job logic.
     */
    abstract public function handle();

    /**
     * Flag job as released.
     */
    public function release()
    {
        $this->released = true;
        ++$this->attempts;
    }

    /**
     * Flag job as failed.
     */
    public function fail()
    {
        $this->failed = true;
    }

    /**
     * @param int $id
     *
     * @return Job
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $reservedAt
     *
     * @return Job
     */
    public function setReservedAt($reservedAt)
    {
        $this->reservedAt = $reservedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReservedAt()
    {
        return $this->reservedAt;
    }

    /**
     * @param mixed $availableAt
     *
     * @return Job
     */
    public function setAvailableAt($availableAt)
    {
        $this->availableAt = $availableAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvailableAt()
    {
        return $this->availableAt;
    }

    /**
     * @param mixed $createdAt
     *
     * @return Job
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param int $attempts
     *
     * @return Job
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * @return bool
     */
    public function isReleased()
    {
        return $this->released;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->failed;
    }
}
