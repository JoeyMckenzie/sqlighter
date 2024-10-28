<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use JoeyMcKenzie\Sqlighter\Commands\RunDatabaseBackup;

beforeEach(function () {
    $this->backupPath = database_path('backups/');
    $this->databasePath = database_path('database.sqlite');
});

it('creates backup directory if it does not exist', function () {
    // Arrange
    expect(File::exists($this->backupPath))->toBeFalse();

    // Act
    $this->artisan(RunDatabaseBackup::class)
        ->assertSuccessful();

    // Assert
    expect(File::exists($this->backupPath))->toBeTrue();
    expect(File::get($this->backupPath.'/.gitignore'))->toContain('backup-*.sql');
});

it('creates backup with correct filename pattern', function () {
    // Act
    $this->artisan(RunDatabaseBackup::class)
        ->assertSuccessful();

    // Act & Assert
    $files = File::files($this->backupPath);
    expect(count($files))->toBe(1);

    $filename = basename($files[0]->getFilename());
    $this->assertMatchesRegularExpression('/backup-\d+\.sql/', $filename);
});

test('maintains correct number of backup copies', function () {
    // Arrange
    Config::set('sqlighter.copies_to_maintain', 2);

    // Act - Run backup command multiple times
    for ($i = 0; $i < 4; $i++) {
        $this->artisan(RunDatabaseBackup::class)
            ->assertSuccessful();

        // Add small delay to ensure different timestamps
        sleep(1);
    }

    // Assert
    $files = File::files($this->backupPath);
    expect(count($files))->toBe(2);
});

it('ensure backups are skipped when not using sqlite', function () {
    // Arrange
    Config::set('database.default', 'mysql');

    // Act & Assert
    $this->artisan(RunDatabaseBackup::class)
        ->expectsOutput('The configured database is not using SQLite, bypassing database backup')
        ->assertFailed();
});

it('ensure backups are skipped when configuration option is disabled', function () {
    // Arrange
    Config::set('sqlighter.enabled', false);

    // Act & Assert
    $this->artisan(RunDatabaseBackup::class)
        ->expectsOutput('Database backups are not enabled, bypassing file copy')
        ->assertSuccessful();

    $this->assertFalse(File::exists($this->backupPath));
});
