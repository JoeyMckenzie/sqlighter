<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use JoeyMcKenzie\Sqlighter\SqlighterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Override;

final class TestCase extends Orchestra
{
    private string $backupPath;

    private string $databasePath;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Set up test paths
        $this->backupPath = database_path('backups/');
        $this->databasePath = database_path('database.sqlite');

        // Create test database file
        if (! File::exists($this->databasePath)) {
            File::put($this->databasePath, '');
        }

        // Clear any existing backups
        if (File::exists($this->backupPath)) {
            File::deleteDirectory($this->backupPath);
        }
    }

    #[Override]
    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists($this->backupPath)) {
            File::deleteDirectory($this->backupPath);
        }

        if (File::exists($this->databasePath)) {
            File::delete($this->databasePath);
        }

        parent::tearDown();
    }

    #[Override]
    protected function getPackageProviders($app): array
    {
        return [
            SqlighterServiceProvider::class,
        ];
    }

    #[Override]
    protected function defineEnvironment($app): void
    {
        // Configure test environment
        Config::set('database.default', 'sqlite');
        Config::set('sqlighter.enabled', true);
        Config::set('sqlighter.database_filename', 'database.sqlite');
        Config::set('sqlighter.storage_folder', 'backups/');
        Config::set('sqlighter.file_prefix', 'backup');
        Config::set('sqlighter.copies_to_maintain', 5);
    }
}
