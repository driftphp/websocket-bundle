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
use Drift\EventBus\Subscriber\EventBusSubscriber;
use Drift\Websocket\Connection\WebsocketServer;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunWebsocket.
 */
class RunWebsocket extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'websocket:run';

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var WebsocketServer
     */
    private $websocketServer;

    /**
     * @var EventBusSubscriber
     */
    private $eventBusSubscriber;

    /**
     * RunWebsocket constructor.
     *
     * @param LoopInterface      $loop
     * @param WebsocketServer    $websocketServer
     * @param EventBusSubscriber $eventBusSubscriber
     */
    public function __construct(
        LoopInterface $loop,
        WebsocketServer $websocketServer,
        EventBusSubscriber $eventBusSubscriber
    ) {
        parent::__construct();

        $this->loop = $loop;
        $this->websocketServer = $websocketServer;
        $this->eventBusSubscriber = $eventBusSubscriber;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Run the websocket')
            ->addArgument('path', InputArgument::REQUIRED, 'The server will start listening to this address')
            ->addOption('route', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Routes to listen')
            ->addOption(
                'exchange',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Exchanges to listen'
            );
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
        $outputPrinter = new OutputPrinter($output);
        list($host, $port) = explode(':', $input->getArgument('path'));

        WebsocketHeaderPrinter::print(
            $input,
            $host,
            $port,
            $outputPrinter
        );

        $this
            ->websocketServer
            ->createServer(
                $host,
                (int) $port,
                $input->getOption('route'),
                $outputPrinter
            );

        $this->eventBusSubscriber->subscribeToExchanges(
            $this->buildQueueArray($input),
            $outputPrinter
        );

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
