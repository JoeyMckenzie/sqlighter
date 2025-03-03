<?php

declare(strict_types=1);

namespace Sqlighter;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Override;
use Sqlighter\Commands\RunDatabaseBackup;

final class SqlighterServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sqlighter.php', 'sqlighter'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RunDatabaseBackup::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/sqlighter.php' => config_path('sqlighter.php'),
            ], 'sqligther-config');
        }

        // Register the scheduled task only if backups are enabled
        if (Config::boolean('sqlighter.enabled')) {
            $this->app->booted(function (): void {
                $schedule = $this->app->make(Schedule::class);
                $frequency = Config::get('sqlighter.frequency');
                $this->scheduleBackup($schedule, $frequency);
            });
        }
    }

    private function scheduleBackup(Schedule $schedule, mixed $frequency): void
    {
        $command = $schedule->command('sqlighter:backup')
            ->timezone(Config::string('app.timezone'))
            ->withoutOverlapping();

        // If it's a valid cron expression, use it directly
        if (is_string($frequency) && $this->isValidCron($frequency)) {
            $command->cron($frequency);

            return;
        }

        // If it's a number, treat it as hours
        if (is_numeric($frequency)) {
            $hours = (int) $frequency;

            match ($hours) {
                1 => $command->hourly(),
                12 => $command->twiceDaily(0, 12),
                24 => $command->daily(),
                168 => $command->weekly(),
                default => $command->cron("0 */$hours * * *")
            };

            return;
        }

        // Default fallback
        $command->everySixHours();
    }

    private function isValidCron(string $cron): bool
    {
        $pattern = '#^(?:(?:(?:[0-5]?\d|\*)(?:-[0-5]?\d|(?:,[0-5]?\d)+)?|\*/[0-5]?\d) ){4}(?:(?:[0-7]|\*)(?:-[0-7]|(?:,[0-7])+)?|\*/[0-7])$#';

        return (bool) preg_match($pattern, $cron);
    }
}
