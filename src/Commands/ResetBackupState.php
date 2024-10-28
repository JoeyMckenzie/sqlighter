<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class ResetBackupState extends Command
{
    protected $signature = 'sqlighter:reset';

    protected $description = 'Reset SQLighter backup state and clear cached run times';

    public function handle(): int
    {
        Cache::forget('sqligther.last_run');

        $this->info('SQLighter backup state has been reset.');
        $this->info('Next backup will run at the next scheduled time.');

        return self::SUCCESS;
    }
}
