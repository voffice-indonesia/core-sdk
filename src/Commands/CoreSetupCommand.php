<?php

namespace VoxDev\Core\Commands;

use Illuminate\Console\Command;

class CoreSetupCommand extends Command
{
    public $signature = 'core:setup';

    public $description = 'Setup the core package by publishing files and showing instructions.';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
