<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter\Tests;

use JoeyMcKenzie\Sqlighter\SqlighterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

final class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_sqlighter_table.php.stub';
        $migration->up();
        */
    }

    /**
     * @return class-string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            SqlighterServiceProvider::class,
        ];
    }
}
