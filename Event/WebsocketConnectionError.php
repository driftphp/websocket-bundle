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

use App\Connections;
use Exception;
use Ratchet\ConnectionInterface;

/**
 * Class WebsocketConnectionError.
 */
class WebsocketConnectionError extends WebsocketEvent
{
    /**
     * @var Exception
     */
    private $exception;

    /**
     * WebsocketOnConnection constructor.
     *
     * @param string              $route
     * @param Connections         $connections
     * @param ConnectionInterface $connection
     * @param Exception           $exception
     */
    public function __construct(
        string $route,
        Connections $connections,
        ConnectionInterface $connection,
        Exception $exception
    ) {
        parent::__construct($route, $connections, $connection);

        $this->exception = $exception;
    }

    /**
     * @return Exception
     */
    public function getException(): Exception
    {
        return $this->exception;
    }
}
