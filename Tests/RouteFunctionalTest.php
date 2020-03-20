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

use function Clue\React\Block\await;

/**
 * Class RouteFunctionalTest.
 */
class RouteFunctionalTest extends WebsocketFunctionalTest
{
    /**
     * Decorate configuration.
     *
     * @param array $configuration
     *
     * @return array
     */
    protected static function decorateConfiguration(array $configuration): array
    {
        $configuration = parent::decorateConfiguration($configuration);

        $configuration['websocket'] = [
            'routes' => [
                'main' => [
                    'path' => '/',
                ],
                'another' => [
                    'path' => '/another/',
                ],
                'auth' => [
                    'path' => '/auth/',
                    'auth' => true,
                ],
            ],
        ];

        return $configuration;
    }

    /**
     * Test simple route connection.
     */
    public function testRouteConnection()
    {
        $this->setupInfrastructure();
        $loop = $this->get('reactphp.event_loop');
        $websocketServer = $this->createSocketServer('8001');
        usleep(300000);
        list($conn1, $_) = $this->connectToSocket('8001');
        usleep(300000);
        list($conn2, $_) = $this->connectToSocket('8001');
        usleep(300000);

        $promise = $this->get('drit.event_bus.public')->dispatch(new TestEvent());
        await($promise, $loop);
        usleep(300000);

        $this->assertContains('Opened connection', $websocketServer->getOutput());
        $this->assertContains('TestEvent', $websocketServer->getOutput());
        $this->assertContains('Exchanges subscribed: events', $websocketServer->getOutput());
        $this->assertContains('Routes: main, another', $websocketServer->getOutput());
        $this->assertContains('Port: 8001', $websocketServer->getOutput());
        $this->assertContains('Host: localhost', $websocketServer->getOutput());

        $this->assertContains('Opened connection', $conn1->getOutput());
        $this->assertContains(TestEvent::class, $conn1->getOutput());
        $this->assertNotContains('Opened connection', $conn2->getOutput());
        $this->assertContains(TestEvent::class, $conn2->getOutput());
        usleep(100000);

        $conn1->stop();
        $conn2->stop();
        $websocketServer->stop();
    }

    /**
     * Test auth without authorization.
     */
    public function testAuthConnectionNotAuthorized()
    {
        $this->setupInfrastructure();
        $loop = $this->get('reactphp.event_loop');
        $websocketServer = $this->createSocketServer('8001');
        usleep(300000);
        list($conn1, $_) = $this->connectToSocket('8001', '/auth/');
        list($conn2, $_) = $this->connectToSocket('8001', '/auth/');
        usleep(300000);

        $promise = $this->get('drit.event_bus.public')->dispatch(new TestEvent());
        await($promise, $loop);
        usleep(300000);

        $this->assertNotContains('TestEvent', $conn1->getOutput());
        $this->assertNotContains('TestEvent', $conn2->getOutput());

        $conn1->stop();
        $conn2->stop();
        $websocketServer->stop();
    }

    /**
     * Test auth with authorization.
     *
     * @group lele
     */
    public function testAuthConnectionAuthorized()
    {
        $this->setupInfrastructure();
        $websocketServer = $this->createSocketServer('8001');
        usleep(300000);

        [$conn1, $stdin1] = $this->connectToSocket('8001', '/auth/');
        [$conn2, $stdin2] = $this->connectToSocket('8001', '/auth/');
        usleep(300000);

        fwrite($stdin1, json_encode(['root', 'engonga']).PHP_EOL);
        fwrite($stdin2, json_encode(['raat', 'anganga']).PHP_EOL);
        usleep(500000);

        fwrite($stdin1, 'LOLAZO!'.PHP_EOL);
        fwrite($stdin2, 'LOLAMEN!'.PHP_EOL);

        usleep(500000);
        $this->assertContains('LOLAMEN!', $conn1->getOutput());
        $this->assertNotContains('raat', $conn1->getOutput());
        $this->assertContains('LOLAZO!', $conn2->getOutput());
        $this->assertNotContains('root', $conn2->getOutput());

        $conn1->stop();
        $conn2->stop();
        $websocketServer->stop();
    }

    /**
     * Test auth with authorization timeout.
     */
    public function testAuthConnectionAuthorizationTimeout()
    {
        $this->setupInfrastructure();
        $websocketServer = $this->createSocketServer('8001');
        usleep(300000);

        list($conn, $_) = $this->connectToSocket('8001', '/auth/');
        usleep(1500000);

        $this->assertContains('Closed connection', $websocketServer->getOutput());

        usleep(100000);

        $conn->stop();
        $websocketServer->stop();
    }
}
