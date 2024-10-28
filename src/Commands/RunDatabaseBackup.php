<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

final class RunDatabaseBackup extends Command
{
    public $signature = 'sqlighter:backup';

    public $description = 'Performs a database backup of your SQLite database.';

    public function handle(): int
    {
        if (Config::string('database.default') !== 'sqlite') {
            $this->warn('The configured database is not using SQLite, bypassing database backup');

            return self::INVALID;
        }

        if (! Config::boolean('sqlighter.enabled')) {
            $this->info('Database backups are not enabled, bypassing file copy');

            return self::SUCCESS;
        }

        $prefix = Config::string('sqlighter.file_prefix');
        $folderPath = Config::string('sqlighter.storage_folder');
        $databaseName = Config::string('sqlighter.database_filename');
        $filename = "$prefix-".now()->timestamp.'.sql';
        $backupDirectory = database_path($folderPath);

        if (! File::exists($backupDirectory)) {
            $this->info("Creating backup directory at $backupDirectory");
            $gitignorePath = $backupDirectory.'/.gitignore';

            File::makeDirectory($backupDirectory, 0755, true);

            $this->info("Creating .gitignore file at $gitignorePath");

            File::put($gitignorePath, "$prefix-*.sql\n");
        }

        File::copy(database_path($databaseName), $backupDirectory.$filename);

        $glob = File::glob($backupDirectory."/$prefix-*.sql");
        $copiesToMaintain = Config::integer('sqlighter.copies_to_maintain');

        $this->info("Backup complete, removing the previous $copiesToMaintain database copies");

        collect($glob)
            ->sort()
            ->reverse()
            ->slice($copiesToMaintain)
            ->each(fn (string $backup): bool => $this->deleteDatabaseBackup($backup));

        $this->info(sprintf(
            'Backup complete, maintaining %d most recent copies',
            count(File::glob($backupDirectory."/$prefix-*.sql"))
        ));

        return self::SUCCESS;
    }

    public function deleteDatabaseBackup(string $filePath): bool
    {
        $this->info("Removing database backup file $filePath");

        $deleted = File::delete($filePath);

        $this->info("$filePath successfully removed");

        return $deleted;
    }
}
