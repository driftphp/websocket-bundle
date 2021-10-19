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

use Clue\React\Stdio\Stdio;
use Drift\Console\OutputPrinter;
use function Ratchet\Client\connect;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConnectToWebsocket.
 */
class ConnectToWebsocket extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'websocket:connect';
    private LoopInterface $loop;

    /**
     * RunWebsocket constructor.
     *
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        parent::__construct();

        $this->loop = $loop;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Run the websocket')
            ->addArgument('path', InputArgument::REQUIRED, 'The server will start listening to this address');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputPrinter = new OutputPrinter($output, false, false);
        $stdio = new Stdio($this->loop);
        $path = $input->getArgument('path');

        connect($path, [], [], $this->loop)
            ->then(function (WebSocket $connection) use ($outputPrinter, $path, $stdio) {
                $outputPrinter->printHeaderLine('Connected to websocket on path - '.$path);
                $connection->on('message', function ($message) use ($outputPrinter) {
                    $outputPrinter->printLine(': '.$message);
                });

                $stdio->on('data', function ($data) use ($connection, $stdio) {
                    $connection->send($data);
                });
            })
            ->otherwise(function (\Exception $exception) use ($outputPrinter, $path) {
                $outputPrinter->printHeaderLine(sprintf(
                    '> Failed to connect websocket on path - %s. Reason was %s',
                    $path,
                    $exception->getMessage()
                ));
            });

        $this->loop->run();

        return 0;
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
