<?php

namespace VoxDev\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \VoxDev\Core\Core
 */
class Core extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \VoxDev\Core\Core::class;
    }
}
