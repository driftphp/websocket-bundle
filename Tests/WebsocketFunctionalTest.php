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

use Drift\EventBus\Bus\EventBus;
use Drift\Websocket\WebsocketBundle;
use Mmoreram\BaseBundle\Kernel\DriftBaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

/**
 * Class WebsocketFunctionalTest.
 */
abstract class WebsocketFunctionalTest extends BaseFunctionalTest
{
    /**
     * Get kernel.
     *
     * @return KernelInterface
     */
    protected static function getKernel(): KernelInterface
    {
        $configuration = [
            'parameters' => [
                'kernel.secret' => 'gdfgfdgd',
            ],
            'framework' => [
                'test' => true,
            ],
            'services' => [
                '_defaults' => [
                    'autowire' => true,
                    'autoconfigure' => true,
                    'public' => true,
                ],
                'reactphp.event_loop' => [
                    'class' => LoopInterface::class,
                    'public' => true,
                    'factory' => [
                        Factory::class,
                        'create',
                    ],
                ],
                WebsocketEventSubscriber::class => [],
                'drit.event_bus.public' => [
                    'alias' => EventBus::class,
                ],
            ],
            'event_bus' => [
                'exchanges' => [
                    'events' => 'events',
                ],
                'async_adapter' => [
                    'adapter' => 'amqp',
                    'amqp' => [
                        'host' => 'localhost',
                    ],
                ],
            ],
            'websocket' => [
                'routes' => [
                    'main' => [
                        'path' => '/',
                    ],
                ],
            ],
        ];

        return new DriftBaseKernel(
            [
                FrameworkBundle::class,
                WebsocketBundle::class,
            ],
            static::decorateConfiguration($configuration),
            [],
            'dev', false
        );
    }

    /**
     * Decorate configuration.
     *
     * @param array $configuration
     *
     * @return array
     */
    protected static function decorateConfiguration(array $configuration): array
    {
        return $configuration;
    }

    /**
     * Create socket server.
     *
     * @param string $port
     *
     * @return Process
     */
    protected function createSocketServer(string $port): Process
    {
        return static::runAsyncCommand([
            'websocket:run',
            'localhost:'.$port,
            '--route=main',
            '--exchange=events',
        ]);
    }

    /**
     * Connect to socket.
     *
     * @param string $port
     *
     * @return Process
     */
    protected function connectToSocket(string $port): Process
    {
        return static::runAsyncCommand([
            'websocket:connect',
            'ws://localhost:'.$port,
        ]);
    }

    /**
     * Setup infrastructure.
     */
    protected function setupInfrastructure()
    {
        $this->runAsyncCommand(['event-bus:infra:drop', '--force', '--exchange=events']);
        usleep(500000);
        $this->runAsyncCommand(['event-bus:infra:create', '--force', '--exchange=events']);
        usleep(500000);
    }
}
