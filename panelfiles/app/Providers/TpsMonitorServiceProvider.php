<?php

namespace Pterodactyl\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Pterodactyl\Models\TpsMonitor;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Pterodactyl\Models\User;

class TpsMonitorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/tps-monitor.php',
            'tps-monitor'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/tps-monitor.php' => config_path('tps-monitor.php'),
            ], 'tps-monitor-config');
        }

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'tps-monitor');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/admin.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api-client.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register scheduled tasks
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $this->scheduleCleanupTasks($schedule);
        });

        // Register view composers
        $this->registerViewComposers();

        // Register gates/policies
        $this->registerGates();

        // Register event listeners
        $this->registerEventListeners();
    }

    /**
     * Schedule cleanup tasks for TPS data.
     */
    protected function scheduleCleanupTasks(Schedule $schedule): void
    {
        if (config('tps-monitor.data_retention.auto_cleanup', true)) {
            $schedule->call(function () {
                $days = config('tps-monitor.data_retention.days', 30);
                $deleted = TpsMonitor::where('created_at', '<', now()->subDays($days))->delete();
                
                if ($deleted > 0) {
                    logger()->info("TPS Monitor: Cleaned up {$deleted} old records");
                }
            })->cron(config('tps-monitor.data_retention.cleanup_schedule', '0 2 * * *'));
        }

        // Schedule data aggregation if enabled
        if (config('tps-monitor.performance.enable_aggregation', true)) {
            $schedule->call(function () {
                $this->aggregateOldData();
            })->daily();
        }
    }

    /**
     * Register view composers for TPS monitor.
     */
    protected function registerViewComposers(): void
    {
        // Add TPS monitor navigation item to admin sidebar
        View::composer('layouts.admin', function ($view) {
            $view->with('tpsMonitorEnabled', true);
        });

        // Share TPS configuration with views
        View::share('tpsConfig', config('tps-monitor'));
    }

    /**
     * Register authorization gates.
     */
    protected function registerGates(): void
    {
        Gate::define('admin.tps.view', function (User $user) {
            return $user->root_admin;
        });

        Gate::define('admin.tps.manage', function (User $user) {
            return $user->root_admin;
        });

        Gate::define('server.tps.view', function (User $user, $server) {
            return $user->can('control.console', $server) || $user->can('control.start', $server);
        });

        Gate::define('server.tps.manage', function (User $user, $server) {
            return $user->can('control.console', $server);
        });
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        // Listen for server status changes to potentially collect TPS data
        // This would require implementing the actual event listeners
        
        // Example: Listen for server start/stop events
        // Event::listen(ServerStateEvent::class, TpsCollectionListener::class);
    }

    /**
     * Aggregate old TPS data into hourly averages.
     */
    protected function aggregateOldData(): void
    {
        $thresholdDays = config('tps-monitor.performance.aggregation_threshold_days', 7);
        $batchSize = config('tps-monitor.performance.batch_size', 1000);
        
        // Get records older than threshold that haven't been aggregated
        $oldRecords = TpsMonitor::where('created_at', '<', now()->subDays($thresholdDays))
            ->whereNull('aggregated_at')
            ->orderBy('created_at')
            ->take($batchSize)
            ->get();

        if ($oldRecords->isEmpty()) {
            return;
        }

        // Group by server and hour
        $grouped = $oldRecords->groupBy(function ($record) {
            return $record->server_id . '_' . $record->created_at->format('Y-m-d H');
        });

        foreach ($grouped as $group) {
            $firstRecord = $group->first();
            
            // Create aggregated record
            TpsMonitor::create([
                'server_id' => $firstRecord->server_id,
                'tps' => $group->avg('tps'),
                'mspt' => $group->avg('mspt'),
                'cpu_usage' => $group->avg('cpu_usage'),
                'memory_usage' => $group->avg('memory_usage'),
                'players_online' => $group->avg('players_online'),
                'created_at' => $firstRecord->created_at->startOfHour(),
                'aggregated_at' => now(),
                'is_aggregated' => true,
                'original_count' => $group->count(),
            ]);

            // Mark original records as aggregated
            TpsMonitor::whereIn('id', $group->pluck('id'))
                ->update(['aggregated_at' => now()]);
        }

        logger()->info("TPS Monitor: Aggregated {$oldRecords->count()} records into hourly averages");
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}