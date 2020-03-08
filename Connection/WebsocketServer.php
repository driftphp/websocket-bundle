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
use Ratchet\App;
use React\EventLoop\LoopInterface;

/**
 * Class WebsocketServer.
 */
class WebsocketServer
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var WebsocketApps
     */
    private $apps;

    /**
     * WebsocketServer constructor.
     *
     * @param LoopInterface $loop
     * @param WebsocketApps $apps
     */
    public function __construct(LoopInterface $loop, WebsocketApps $apps)
    {
        $this->loop = $loop;
        $this->apps = $apps;
    }

    /**
     * Create a server from configuration.
     *
     * @param string        $httpHost
     * @param int           $port
     * @param OutputPrinter $outputPrinter
     * @param string[]      $connectionsName
     * @param string        $address
     */
    public function createServer(
        string $httpHost,
        int $port,
        array $connectionsName,
        OutputPrinter $outputPrinter,
        string $address = '0.0.0.0'
    ) {
        $server = new App($httpHost, $port, $address, $this->loop);
        $apps = $this
            ->apps
            ->getByNames($connectionsName);

        foreach ($apps as list($app, $configuration)) {
            $app->setOutputPrinter($outputPrinter);
            $server->route(
                $configuration['path'],
                $app,
                $configuration['allowed_origins'] ?? ['*']
            );
        }
    }
}
