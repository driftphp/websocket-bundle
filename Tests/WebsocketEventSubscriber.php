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

namespace Drift\Websocket\Tests;

use Drift\Websocket\Connection\Connections;
use Drift\Websocket\Event\WebsocketConnectionClosed;
use Drift\Websocket\Event\WebsocketConnectionOpened;
use Drift\Websocket\Event\WebsocketMessageReceived;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class WebsocketEventListener.
 */
class WebsocketEventSubscriber implements EventSubscriberInterface
{
    private Connections $connections;

    /**
     * WebsocketEventSubscriber constructor.
     *
     * @param Connections $mainConnections
     */
    public function __construct(Connections $mainConnections)
    {
        $this->connections = $mainConnections;
    }

    /**
     * @param WebsocketConnectionOpened $event
     */
    public function onConnectionOpened(WebsocketConnectionOpened $event)
    {
        $route = $event->getRoute();
        $message = 'Opened connection on route '.$route;
        $event
            ->getConnections()
            ->broadcast($message);
    }

    /**
     * @param WebsocketConnectionClosed $event
     */
    public function onConnectionClosed(WebsocketConnectionClosed $event)
    {
        $route = $event->getRoute();
        $message = 'Connection closed from route '.$route;

        $event
            ->getConnections()
            ->broadcast($message);
    }

    /**
     * @param WebsocketMessageReceived $event
     */
    public function onMessageReceived(WebsocketMessageReceived $event)
    {
        $route = $event->getRoute();
        $message = 'Message received from route '.$route.': '.$event->getMessage();

        $event
            ->getConnections()
            ->broadcast($message);
    }

    /**
     * @param TestEvent $event
     */
    public function onTestEvent(TestEvent $event)
    {
        $message = 'Event received from main route: '.get_class($event);

        $this
            ->connections
            ->broadcast($message);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TestEvent::class => [['onTestEvent', 0]],
            WebsocketConnectionOpened::class => [['onConnectionOpened', 0]],
            WebsocketConnectionClosed::class => [['onConnectionClosed', 0]],
            WebsocketMessageReceived::class => [['onMessageReceived', 0]],
        ];
    }
}
