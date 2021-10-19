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
        sleep(1);
        $conn1 = $this->connectToSocket('8001');
        sleep(2);
        $conn2 = $this->connectToSocket('8001');
        sleep(2);

        $promise = $this->get('drit.event_bus.public')->dispatch(new TestEvent());
        await($promise, $loop);
        sleep(1);

        $this->assertStringContainsString('Opened connection', $websocketServer->getOutput());
        $this->assertStringContainsString('TestEvent', $websocketServer->getOutput());
        $this->assertStringContainsString('Exchanges subscribed: events', $websocketServer->getOutput());
        $this->assertStringContainsString('Routes: main, another', $websocketServer->getOutput());
        $this->assertStringContainsString('Port: 8001', $websocketServer->getOutput());
        $this->assertStringContainsString('Host: localhost', $websocketServer->getOutput());

        $this->assertStringContainsString('Opened connection', $conn1->getOutput());
        $this->assertStringContainsString(TestEvent::class, $conn1->getOutput());
        $this->assertStringContainsString('Opened connection', $conn2->getOutput());
        $this->assertStringContainsString(TestEvent::class, $conn2->getOutput());

        $websocketServer->stop();
        $conn1->stop();
        $conn2->stop();
    }
}
