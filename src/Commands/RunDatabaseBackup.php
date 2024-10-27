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
        $folderPath = Config::string('sqlighter.storage_path');
        $databaseName = Config::string('sqlighter.database_name');
        $filename = "$prefix-".now()->timestamp.'.sql';

        File::copy(database_path($databaseName), database_path($folderPath.$filename));

        $glob = File::glob(database_path("$folderPath*.sql"));
        $copiesToMaintain = Config::integer('sqlighter.copies_to_maintain');

        $this->comment("Backup complete, removing the previous $copiesToMaintain database copies");

        collect($glob)->sort()->reverse()->slice($copiesToMaintain)->each(
            fn (string $backup): bool => $this->deleteDatabaseBackup($backup),
        );

        $this->comment('Backup copies removed');

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
