<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use JoeyMcKenzie\Sqlighter\Commands\RunDatabaseBackup;

final class SqlighterServiceProvider extends ServiceProvider
{
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
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $frequency = Config::string('sqlighter.frequency');

                if ($this->isValidCron($frequency)) {
                    $schedule
                        ->command('sqlighter:backup')
                        ->cron($frequency);
                } else {
                    $schedule
                        ->command('sqlighter:backup')
                        ->everySixHours();
                }
            });
        }
    }

    private function isValidCron(string $cron): bool
    {
        $pattern = '#^(?:(?:(?:[0-5]?\d|\*)(?:-[0-5]?\d|(?:,[0-5]?\d)+)?|\*/[0-5]?\d) ){4}(?:(?:[0-7]|\*)(?:-[0-7]|(?:,[0-7])+)?|\*/[0-7])$#';

        return (bool) preg_match($pattern, $cron);
    }
}
