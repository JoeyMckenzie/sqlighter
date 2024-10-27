<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter\Tests;

use JoeyMcKenzie\Sqlighter\SqlighterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_sqlighter_table.php.stub';
        $migration->up();
        */
    }

    protected function setUp(): void
    {
        parent::setUp();
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
