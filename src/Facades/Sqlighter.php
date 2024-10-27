<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JoeyMcKenzie\Sqlighter\Sqlighter
 */
final class Sqlighter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JoeyMcKenzie\Sqlighter\Sqlighter::class;
    }
}
