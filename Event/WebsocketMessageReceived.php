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

/**
 * Class WebsocketMessageReceived.
 */
class WebsocketMessageReceived extends WebsocketEvent
{
    /**
     * @var string
     */
    private $message;

    /**
     * WebsocketOnConnection constructor.
     *
     * @param string              $route
     * @param Connections         $connections
     * @param ConnectionInterface $connection
     * @param string              $message
     */
    public function __construct(
        string $route,
        Connections $connections,
        ConnectionInterface $connection,
        string $message
    ) {
        parent::__construct($route, $connections, $connection);

        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
