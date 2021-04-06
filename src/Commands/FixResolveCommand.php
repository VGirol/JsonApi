<?php

namespace VGirol\JsonApi\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

trait FixResolveCommand
{
    protected function resolveCommand($command)
    {
        if (! is_object($command)) {
            if (! class_exists($command)) {
                return $this->getApplication()->find($command);
            }

            $command = $this->laravel->make($command);
        }

        if ($command instanceof SymfonyCommand) {
            $command->setApplication($this->getApplication());
        }

        if ($command instanceof Command) {
            $command->setLaravel($this->getLaravel());
        }

        return $command;
    }
}
