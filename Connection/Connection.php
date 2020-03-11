<?php


namespace Drift\Websocket\Connection;

use Ratchet\ConnectionInterface;

/**
 * Class Connection
 */
class Connection
{
    /**
     * Get connection hash
     *
     * @param ConnectionInterface $connection
     *
     * @return string
     */
    public static function getConnectionHash(ConnectionInterface $connection) : string
    {
        return substr(md5(spl_object_hash($connection)), 0, 13);
    }
}