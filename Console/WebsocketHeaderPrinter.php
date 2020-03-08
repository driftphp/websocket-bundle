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

namespace Drift\Websocket\Console;

use Drift\Console\OutputPrinter;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class WebsocketHeaderPrinter.
 */
class WebsocketHeaderPrinter
{
    /**
     * Print header.
     *
     * @param InputInterface $input
     * @param string         $host
     * @param string         $port
     * @param OutputPrinter  $outputPrinter
     */
    public static function print(
        InputInterface $input,
        string $host,
        string $port,
        OutputPrinter $outputPrinter
    ) {
        if ($input->getOption('quiet')) {
            return;
        }

        $outputPrinter->printLine();
        $outputPrinter->printHeaderLine();
        $outputPrinter->printHeaderLine('ReactPHP Websocket Client for DriftPHP');
        $outputPrinter->printHeaderLine('  by Marc Morera (@mmoreram)');
        $outputPrinter->printHeaderLine();
        $outputPrinter->printHeaderLine("Host: $host");
        $outputPrinter->printHeaderLine("Port: $port");
        $outputPrinter->printHeaderLine("Environment: {$input->getOption('env')}");
        $outputPrinter->printHeaderLine('Debug: '.($input->getOption('no-debug') ? 'disabled' : 'enabled'));
        $outputPrinter->printHeaderLine();
        $outputPrinter->printLine();
    }
}
