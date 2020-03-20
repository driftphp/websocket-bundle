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

use Drift\Console\OutputPrinter;
use Drift\EventBus\Bus\InlineEventBus;
use Drift\Websocket\Console\ConsoleWebsocketMessage;
use Drift\Websocket\Event\WebsocketConnectionAuth;
use Drift\Websocket\Event\WebsocketConnectionClosed;
use Drift\Websocket\Event\WebsocketConnectionError;
use Drift\Websocket\Event\WebsocketConnectionOpened;
use Drift\Websocket\Event\WebsocketMessageReceived;
use Drift\Websocket\Exception\WebsocketAuthException;
use Exception;
use function React\Promise\resolve;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

/**
 * Class WebsocketApp.
 */
class WebsocketApp implements MessageComponentInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Connections
     */
    private $connections;

    /**
     * @var Connections
     */
    private $unauthorizedConnections;

    /**
     * @var InlineEventBus
     */
    private $eventBus;

    /**
     * @var OutputPrinter
     */
    private $outputPrinter;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var bool
     */
    private $authorizable;

    /**
     * WebsocketsApp constructor.
     *
     * @param string         $name
     * @param Connections    $connections
     * @param InlineEventBus $eventBus
     * @param LoopInterface  $loop
     * @param bool           $authorizable
     */
    public function __construct(
        string $name,
        Connections $connections,
        InlineEventBus $eventBus,
        LoopInterface $loop,
        bool $authorizable
    ) {
        $this->name = $name;
        $this->connections = $connections;
        $this->unauthorizedConnections = new Connections();
        $this->eventBus = $eventBus;
        $this->loop = $loop;
        $this->authorizable = $authorizable;
    }

    /**
     * @param OutputPrinter $outputPrinter
     */
    public function setOutputPrinter(OutputPrinter $outputPrinter): void
    {
        $this->outputPrinter = $outputPrinter;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $event = new WebsocketConnectionOpened($this->name, $this->connections, $connection);
        $promise = $this
            ->eventBus
            ->dispatch($event)
            ->then(function () use ($connection) {
                if ($this->authorizable) {
                    $this
                        ->unauthorizedConnections
                        ->addConnection($connection);

                    $this
                        ->loop
                        ->addTimer(1, function () use ($connection) {
                            if ($this->unauthorizedConnections->hasConnection($connection)) {
                                $connection->close();
                            }
                        });
                } else {
                    $this
                        ->connections
                        ->addConnection($connection);
                }
            })
            ->then(function () use ($connection) {
                if ($this->outputPrinter) {
                    (new ConsoleWebsocketMessage(sprintf(
                        'Connection %s - %s - Opened connection',
                        Connection::getConnectionHash($connection),
                        $this->name
                    ), '~', true))->print($this->outputPrinter);
                }
            });

        $this
            ->loop
            ->futureTick(function () use ($promise) {
                resolve($promise);
            });
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        $this->connections->removeConnection($connection);
        $this->unauthorizedConnections->removeConnection($connection);
        $event = new WebsocketConnectionClosed($this->name, $this->connections, $connection);
        $promise = $this->eventBus->dispatch($event);

        $this
            ->loop
            ->futureTick(function () use ($promise) {
                resolve($promise);
            });

        if ($this->outputPrinter) {
            (new ConsoleWebsocketMessage(sprintf(
                'Connection %s - %s - Closed connection',
                Connection::getConnectionHash($connection),
                $this->name
            ), '~', true))->print($this->outputPrinter);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, Exception $exception)
    {
        $event = new WebsocketConnectionError($this->name, $this->connections, $connection, $exception);

        $this
            ->loop
            ->futureTick(function () use ($event) {
                resolve($this->eventBus->dispatch($event));
            });

        if ($this->outputPrinter) {
            (new ConsoleWebsocketMessage(sprintf(
                'Connection %s - %s - Error thrown',
                Connection::getConnectionHash($connection),
                $this->name
            ), '~', true))->print($this->outputPrinter);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        $this->unauthorizedConnections->hasConnection($from);
        $promise = $this->unauthorizedConnections->hasConnection($from)
            ? $this->authorizeConnection($from, $message)
            : resolve(false);

        $promise->then(function (bool $isAuth) use ($from, $message) {
            if ($isAuth) {
                return;
            }

            $event = new WebsocketMessageReceived($this->name, $this->connections, $from, $message);
            $this->eventBus->dispatch($event);

            if ($this->outputPrinter) {
                (new ConsoleWebsocketMessage(sprintf(
                    'Connection %s - %s - Messaged "%s"',
                    Connection::getConnectionHash($from),
                    $this->name,
                    trim($message, " \ \t\n\r\0\x0B")
                ), '~', true))->print($this->outputPrinter);
            }
        });

        $this
            ->loop
            ->futureTick(function () use ($promise) {
                resolve($promise);
            });
    }

    /**
     * Authorize connection.
     *
     * @param ConnectionInterface $connection
     * @param mixed               $message
     *
     * @return PromiseInterface
     */
    public function authorizeConnection(ConnectionInterface $connection, $message): PromiseInterface
    {
        $event = new WebsocketConnectionAuth($this->name, $this->connections, $connection, $message);

        return $this
            ->eventBus
            ->dispatch($event)
            ->then(function () use ($connection) {
                $this->connections->addConnection($connection);
                $this->unauthorizedConnections->removeConnection($connection);

                return true;
            })
            ->otherwise(function (WebsocketAuthException $exception) use ($connection) {
                // Auth rejected.
                $connection->close();

                throw $exception;
            });
    }
}
