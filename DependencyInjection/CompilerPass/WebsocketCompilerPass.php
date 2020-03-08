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

namespace Drift\Websocket\DependencyInjection\CompilerPass;

use Drift\EventBus\Bus\InlineEventBus;
use Drift\EventBus\Subscriber\EventBusSubscriber;
use Drift\Websocket\Connection\Connections;
use Drift\Websocket\Connection\WebsocketApp;
use Drift\Websocket\Connection\WebsocketApps;
use Drift\Websocket\Connection\WebsocketServer;
use Drift\Websocket\Console\ConnectToWebsocket;
use Drift\Websocket\Console\RunWebsocket;
use React\EventLoop\LoopInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class WebsocketCompilerPass.
 */
class WebsocketCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $websocketsApps = new Definition(WebsocketApps::class);

        foreach ($container->getParameter('websocket.routes') as $routeName => $config) {
            $this->createRouteApp(
                $container,
                $routeName,
                $config,
                $websocketsApps
            );
        }

        $container->setDefinition(WebsocketApps::class, $websocketsApps);
        $container->setDefinition(WebsocketServer::class, (new Definition(
            WebsocketServer::class,
            [
                new Reference(LoopInterface::class),
                new Reference(WebsocketApps::class),
            ]
        ))->setPublic(true));

        $this->createRunCommand($container);
        $this->createConnectCommand($container);
    }

    /**
     * Create route.
     *
     * @param ContainerBuilder $container
     * @param string           $routeName
     * @param array            $config,
     * @param Definition       $websocketsApps
     */
    private function createRouteApp(
        ContainerBuilder $container,
        string $routeName,
        array $config,
        Definition $websocketsApps
    ) {
        $appName = sprintf('websocket.%s_app', $routeName);
        $appParameter = "$routeName app";
        $connectionsName = sprintf('websocket.%s_connections', $routeName);
        $connectionsParameter = "$routeName connections";

        $container->setDefinition($connectionsName, new Definition(Connections::class));
        $container->registerAliasForArgument($connectionsName, Connections::class, $connectionsParameter);
        $container->setDefinition($appName, new Definition(WebsocketApp::class, [
            $routeName,
            new Reference($connectionsName),
            new Reference(InlineEventBus::class),
        ]));
        $container->registerAliasForArgument($appName, WebsocketApp::class, $appParameter);

        $websocketsApps->addMethodCall('addApp', [
            $routeName,
            new Reference($appName),
            $config,
        ]);
    }

    /**
     * Create run command.
     *
     * @param ContainerBuilder $container
     */
    private function createRunCommand(ContainerBuilder $container)
    {
        $container->setDefinition(RunWebsocket::class, (new Definition(
            RunWebsocket::class, [
                new Reference(LoopInterface::class),
                new Reference(WebsocketServer::class),
                new Reference(EventBusSubscriber::class),
            ]
        ))->addTag('console.command'));
    }

    /**
     * Create connect command.
     *
     * @param ContainerBuilder $container
     */
    private function createConnectCommand(ContainerBuilder $container)
    {
        $container->setDefinition(ConnectToWebsocket::class, (new Definition(
            ConnectToWebsocket::class, [
                new Reference(LoopInterface::class),
            ]
        ))->addTag('console.command'));
    }
}
