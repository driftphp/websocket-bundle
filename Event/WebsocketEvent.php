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

namespace Drift\Websocket\Event;

use Drift\Websocket\Connection\Connections;
use Ratchet\ConnectionInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class WebsocketEvent.
 */
abstract class WebsocketEvent extends Event
{
    /**
     * @var string
     */
    private $route;

    /**
     * @var Connections
     */
    private $connections;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * WebsocketOnConnection constructor.
     *
     * @param string              $route
     * @param Connections         $connections
     * @param ConnectionInterface $connection
     */
    public function __construct(
        string $route,
        Connections $connections,
        ConnectionInterface $connection
    ) {
        $this->route = $route;
        $this->connections = $connections;
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return Connections
     */
    public function getConnections(): Connections
    {
        return $this->connections;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }
}
