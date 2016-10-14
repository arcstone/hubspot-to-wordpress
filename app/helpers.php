<?php

if (!function_exists('render_console_exception')) {
    function render_console_exception($e)
    {
        app(Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->renderForConsole(
                app(Symfony\Component\Console\Output\ConsoleOutput::class,
                    [\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_DEBUG]
                ), $e);
    }
}
