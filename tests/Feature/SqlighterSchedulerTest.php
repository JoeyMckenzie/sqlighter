<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter\Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use JoeyMcKenzie\Sqlighter\SqlighterServiceProvider;

describe(SqlighterServiceProvider::class, function (): void {
    beforeEach(function (): void {
        Config::set('sqlighter.enabled', true);
        $this->app->singleton(Schedule::class, fn (): \Illuminate\Console\Scheduling\Schedule => new Schedule);
    });

    $refreshSchedule = function () {
        $this->app->singleton(Schedule::class, fn (): \Illuminate\Console\Scheduling\Schedule => new Schedule);
        $provider = new SqlighterServiceProvider($this->app);
        $provider->boot();

        return $this->app->make(Schedule::class);
    };

    test('hourly backups can be scheduled', function () use ($refreshSchedule): void {
        // Arrange
        Config::set('sqlighter.frequency', 1);

        // Act
        $schedule = $refreshSchedule->call($this);
        $events = collect($schedule->events());

        // Assert
        expect($events)
            ->and($events->first()->command)
            ->toContain('sqlighter:backup')
            ->and($events->first()->expression)
            ->toBe('0 * * * *');
    });

    test('custom cron expressions can be used for the schedule', function () use ($refreshSchedule): void {
        // Arrange
        Config::set('sqlighter.frequency', '30 2 * * *');

        // Act
        $schedule = $refreshSchedule->call($this);
        $events = collect($schedule->events());

        // Assert
        expect($events)
            ->and($events->first()->command)
            ->toContain('sqlighter:backup')
            ->and($events->first()->expression)
            ->toBe('30 2 * * *');
    });

    test('defaults to every six hours when frequency is invalid', function () use ($refreshSchedule): void {
        // Arrange
        Config::set('sqlighter.frequency', 'invalid');

        // Act
        $schedule = $refreshSchedule->call($this);
        $events = collect($schedule->events());

        // Assert
        expect($events)
            ->and($events->first()->command)
            ->toContain('sqlighter:backup')
            ->and($events->first()->expression)
            ->toBe('0 */6 * * *');
    });

    test('it does not schedule command when disabled', function () use ($refreshSchedule): void {
        // Arrange
        Config::set('sqlighter.enabled', false);

        // Act
        $schedule = $refreshSchedule->call($this);

        // Assert
        expect(collect($schedule->events()))->toBeEmpty();
    });

    test('daily schedules can be scheduled', function () use ($refreshSchedule): void {
        // Arrange
        Config::set('sqlighter.frequency', 24);

        // Act
        $schedule = $refreshSchedule->call($this);
        $events = collect($schedule->events());

        // Assert
        expect($events)
            ->and($events->first()->command)
            ->toContain('sqlighter:backup')
            ->and($events->first()->expression)
            ->toBe('0 0 * * *');
    });

    test('it schedules weekly backup correctly', function () use ($refreshSchedule): void {
        // Arrange
        Config::set('sqlighter.frequency', 168);

        // Act
        $schedule = $refreshSchedule->call($this);
        $events = collect($schedule->events());

        // Assert
        expect($events)
            ->and($events->first()->command)
            ->toContain('sqlighter:backup')
            ->and($events->first()->expression)
            ->toBe('0 0 * * 0');
    });

    test('it configures schedule with proper command settings', function () use ($refreshSchedule): void {
        // Arrange
        Config::set('sqlighter.frequency', 1);

        // Act
        $schedule = $refreshSchedule->call($this);
        $event = collect($schedule->events())->first();

        // Assert
        expect($event)
            ->command->toContain('sqlighter:backup')
            ->expression->toBe('0 * * * *')
            ->timezone->toBe(config('app.timezone'))
            ->withoutOverlapping->toBeTrue()
            ->evenInMaintenanceMode->toBeFalse();
    });
});
