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
    $this->assertFalse(File::exists≤($this->backupPath));

    // Act
    $this->artisan(RunDatabaseBackup::class)
        ->assertSuccessful();

    // Assert
    $this->assertTrue(File::exists($this->backupPath));
    $this->assertTrue(File::exists($this->backupPath . '/.gitignore'));
    $this->assertStringContainsString(
        'backup-*.sql',
        File::get($this->backupPath . '/.gitignore')
    );
});


it('creates backup with correct filename pattern', function () {
    // Act
    $this->artisan(RunDatabaseBackup::class)
        ->assertSuccessful();

    // Assert
    $files = File::files($this->backupPath);
    $this->assertCount(1, $files);

    $filename = basename($files[0]);
    $this->assertMatchesRegularExpression('/backup-\d+\.sql/', $filename);
});

it('maintains correct number of backup copies', function () {
    // Arrange
    Config::set('sqlighter.copies_to_maintain', 2);

    // Act - Run backup command multiple times
    for ($i = 0; $i < 4; $i++) {
        $this->artisan(RunDatabaseBackup::class)
            ->assertSuccessful();

        // Add small delay to ensure different timestamps
        usleep(1000);
    }

    // Assert
    $files = File::files($this->backupPath);
    $this->assertCount(2, $files);
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
