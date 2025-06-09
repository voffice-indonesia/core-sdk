<?php

namespace VoxDev\Core\Commands;

use Illuminate\Console\Command;

class CoreCommand extends Command
{
    public $signature = 'core-sdk';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
