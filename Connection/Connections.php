<?php

/*
 * This file is part of the DriftPHP Project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Drift\Websocket\Connection;

use Ratchet\ConnectionInterface;
use SplObjectStorage;

/**
 * Class Connections.
 */
class Connections
{
    /**
     * @var SplObjectStorage
     */
    private $connections;

    /**
     * Connections constructor.
     */
    public function __construct()
    {
        $this->connections = new SplObjectStorage();
    }

    /**
     * Add connection.
     *
     * @param ConnectionInterface $connection
     */
    public function addConnection(ConnectionInterface $connection): void
    {
        $this->connections->attach($connection);
    }

    /**
     * Remove connection.
     *
     * @param ConnectionInterface $connection
     */
    public function removeConnection(ConnectionInterface $connection): void
    {
        $this->connections->detach($connection);
    }

    /**
     * Count.
     */
    public function count(): int
    {
        return count($this->connections);
    }

    /**
     * Broadcast data.
     *
     * An excluded connection can be optionally added
     *
     * @param string              $data
     * @param ConnectionInterface $excludedConnection
     */
    public function broadcast(
        string $data,
        ConnectionInterface $excludedConnection = null
    ): void {
        if (is_null($excludedConnection)) {
            foreach ($this->connections as $connection) {
                $connection->send($data);
            }

            return;
        }

        foreach ($this->connections as $connection) {
            if ($connection === $excludedConnection) {
                continue;
            }

            $connection->send($data);
        }
    }
}
