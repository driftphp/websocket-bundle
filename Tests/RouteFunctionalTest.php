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
use Symfony\Component\Process\InputStream;

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
        $inputServer = new InputStream();
        $websocketServer = $this->createSocketServer('8001', $inputServer);
        usleep(500000);
        $input1 = new InputStream();
        $conn1 = $this->connectToSocket('8001', $input1);
        $input2 = new InputStream();
        $conn2 = $this->connectToSocket('8001', $input2);
        usleep(500000);

        $promise = $this->get('drit.event_bus.public')->dispatch(new TestEvent());
        await($promise, $loop);
        usleep(500000);

        $this->assertContains('opened on route main', $websocketServer->getOutput());
        $this->assertContains('TestEvent', $websocketServer->getOutput());

        $this->assertContains('Opened connection', $conn1->getOutput());
        $this->assertContains(TestEvent::class, $conn1->getOutput());
        $this->assertContains('Opened connection', $conn2->getOutput());
        $this->assertContains(TestEvent::class, $conn2->getOutput());
    }
}
