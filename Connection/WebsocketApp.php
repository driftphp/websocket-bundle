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
use Drift\Websocket\Event\WebsocketConnectionClosed;
use Drift\Websocket\Event\WebsocketConnectionError;
use Drift\Websocket\Event\WebsocketConnectionOpened;
use Drift\Websocket\Event\WebsocketMessageReceived;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

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
     * @var InlineEventBus
     */
    private $eventBus;

    /**
     * @var OutputPrinter
     */
    private $outputPrinter;

    /**
     * WebsocketsApp constructor.
     *
     * @param string         $name
     * @param Connections    $connections
     * @param InlineEventBus $eventBus
     */
    public function __construct(
        string $name,
        Connections $connections,
        InlineEventBus $eventBus
    ) {
        $this->name = $name;
        $this->connections = $connections;
        $this->eventBus = $eventBus;
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
        $this->connections->addConnection($connection);
        $this->eventBus->dispatch($event);

        if ($this->outputPrinter) {
            (new ConsoleWebsocketMessage(sprintf(
                'Connection %s opened on route %s',
                spl_object_hash($connection),
                $this->name
            ), '~', true))->print($this->outputPrinter);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        $this->connections->removeConnection($connection);
        $event = new WebsocketConnectionClosed($this->name, $this->connections, $connection);
        $this->eventBus->dispatch($event);

        if ($this->outputPrinter) {
            (new ConsoleWebsocketMessage(sprintf(
                'Connection %s closed from route %s',
                spl_object_hash($connection),
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
        $this->eventBus->dispatch($event);

        if ($this->outputPrinter) {
            (new ConsoleWebsocketMessage(sprintf(
                'Connection %s throw error on route %s',
                spl_object_hash($connection),
                $this->name
            ), '~', true))->print($this->outputPrinter);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $message)
    {
        $event = new WebsocketMessageReceived($this->name, $this->connections, $from, $message);
        $this->eventBus->dispatch($event);

        if ($this->outputPrinter) {
            (new ConsoleWebsocketMessage(sprintf(
                'Message from connection %s on route %s - %s',
                spl_object_hash($from),
                $this->name,
                $message
            ), '~', true))->print($this->outputPrinter);
        }
    }
}
