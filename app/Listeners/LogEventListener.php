<?php

namespace H2W\Listeners;

use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A convenient way to log events to both the console and the log file
 * Usage: event('log.error', ["Error Message", $context]);
 *
 * @package H2W\Listeners
 */
class LogEventListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Command
     */
    private $command;

    /**
     * Create the event listener.
     *
     * @param LoggerInterface $logger
     * @param Command         $command
     */
    public function __construct(LoggerInterface $logger, $command)
    {
        $this->logger  = $logger;
        $this->command = $command;
    }

    /**
     * Handle the event.
     *
     * @param  mixed $data
     * @throws \Exception
     */
    public function handle($data)
    {
        $level = str_replace_first('log.', '', \Event::firing());

        if (is_array($data)) {
            list($msg, $context) = $data;
        } else {
            $msg     = $data;
            $context = [];
        }

        $logMap = [
            // Common levels
            'info'      => 'info',
            'error'     => 'error',
            'warn'      => 'warn',
            // Symfony Console levels
            'line'      => 'info',
            'comment'   => 'info',
            'question'  => 'info',
            // Monolog-specific Levels
            'debug'     => 'debug',
            'notice'    => 'notice',
            'warning'   => 'warning',
            'err'       => 'err',
            'crit'      => 'crit',
            'critical'  => 'critical',
            'alert'     => 'alert',
            'emerg'     => 'emerg',
            'emergency' => 'emergency',
        ];

        if (!in_array($level, array_keys($logMap))) {
            throw new \Exception("Invalid log level: $level\nMessage: $msg", 500);
        }

        $this->logger->log($logMap[$level], $msg, $context);

        $verbosity = OutputInterface::VERBOSITY_NORMAL;

        if ($level === 'debug') {
            $verbosity = OutputInterface::VERBOSITY_VERY_VERBOSE;
        }

        if (in_array($level, ['info', 'line', 'comment', 'question', 'notice'])) {
            $verbosity = OutputInterface::VERBOSITY_VERBOSE;
        }

        // If for some reason we don't have an output instance, do it the old-fashioned way.
        if (is_null($this->command->getOutput())) {
            echo(strtoupper($level) . ": $data");

            return;
        }

        $consoleMap = [
            // Common levels
            'info'      => 'info',
            'error'     => 'error',
            'warn'      => 'warn',
            // Symfony Console levels
            'line'      => 'line',
            'comment'   => 'comment',
            'question'  => 'question',
            // Monolog-specific Levels
            'debug'     => 'line',
            'notice'    => 'info',
            'warning'   => 'warn',
            'err'       => 'error',
            'crit'      => 'error',
            'critical'  => 'error',
            'alert'     => 'error',
            'emerg'     => 'error',
            'emergency' => 'error',
        ];

        // ANSI codes to display above a 3-line progress bar
        $this->command->{$consoleMap[$level]}("\033[1G\033[2A$msg\033[K\n\n", $verbosity ?? null);
    }
}
