<?php

namespace Alpipego\Resizefly\Common\WP_Queue;

use Alpipego\Resizefly\Common\WP_Queue\Connections\DatabaseConnection;
use Alpipego\Resizefly\Common\WP_Queue\Connections\RedisConnection;
use Alpipego\Resizefly\Common\WP_Queue\Connections\SyncConnection;
use Alpipego\Resizefly\Common\WP_Queue\Exceptions\ConnectionNotFoundException;

class QueueManager
{
    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * Resolve a Queue instance for required connection.
     *
     * @param string $connection
     *
     * @return Queue
     */
    public static function resolve($connection)
    {
        if (isset(static::$instances[$connection])) {
            return static::$instances[$connection];
        }

        return static::build($connection);
    }

    /**
     * Build a queue instance.
     *
     * @param string $connection
     *
     * @throws \Exception
     *
     * @return Queue
     */
    protected static function build($connection)
    {
        $connections = static::connections();

        if (empty($connections[$connection])) {
            throw new ConnectionNotFoundException();
        }

        static::$instances[$connection] = new Queue($connections[$connection]);

        return static::$instances[$connection];
    }

    /**
     * Get available connections.
     *
     * @return array
     */
    protected static function connections()
    {
        $connections = [
            'database' => new DatabaseConnection($GLOBALS['wpdb']),
            'redis'    => new RedisConnection(),
            'sync'     => new SyncConnection(),
        ];

        return apply_filters('wp_queue_connections', $connections);
    }
}
