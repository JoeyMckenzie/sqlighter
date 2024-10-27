<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter\Tests;

use Illuminate\Support\Facades\App;
use JoeyMcKenzie\Sqlighter\SqlighterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

final class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getEnvironmentSetUp(App $app): void
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_sqlighter_table.php.stub';
        $migration->up();
        */
    }

    protected function getPackageProviders(App $app): array
    {
        return [
            SqlighterServiceProvider::class,
        ];
    }
}
