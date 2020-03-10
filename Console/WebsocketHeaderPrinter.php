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
        $outputPrinter->printHeaderLine('ReactPHP Websocket Server for DriftPHP');
        $outputPrinter->printHeaderLine('  by Marc Morera (@mmoreram)');
        $outputPrinter->printHeaderLine();
        $outputPrinter->printHeaderLine("Host: $host");
        $outputPrinter->printHeaderLine("Port: $port");
        $outputPrinter->printHeaderLine("Environment: {$input->getOption('env')}");
        $outputPrinter->printHeaderLine('Debug: '.($input->getOption('no-debug') ? 'disabled' : 'enabled'));
        $outputPrinter->printHeaderLine('Exchanges subscribed: '.(!empty($input->getOption('exchange'))
                ? implode(', ', static::getPlainExchanges($input))
                : 'disabled'
            ));
        $outputPrinter->printHeaderLine();
        $outputPrinter->printLine();
    }

    /**
     * @param InputInterface $input
     *
     * @return array
     */
    public static function getPlainExchanges(InputInterface $input): array
    {
        $exchanges = static::buildQueueArray($input);
        $array = [];
        foreach ($exchanges as $exchange => $queue) {
            $array[] = trim("$exchange:$queue", ':');
        }

        return $array;
    }

    /**
     * Build queue architecture from array of strings.
     *
     * @param InputInterface $input
     *
     * @return array
     */
    private static function buildQueueArray(InputInterface $input): array
    {
        if (!$input->hasOption('exchange')) {
            return [];
        }

        $exchanges = [];
        foreach ($input->getOption('exchange') as $exchange) {
            $exchangeParts = explode(':', $exchange, 2);
            $exchanges[$exchangeParts[0]] = $exchangeParts[1] ?? '';
        }

        return $exchanges;
    }
}
