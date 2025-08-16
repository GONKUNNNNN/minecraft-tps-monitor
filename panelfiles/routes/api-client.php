<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Api\Client\Servers\TpsController;

/*
|--------------------------------------------------------------------------
| TPS Monitor API Routes
|--------------------------------------------------------------------------
|
| These routes are for the TPS monitoring functionality.
| All routes are prefixed with /api/client/servers/{server}
|
*/

Route::group(['prefix' => '/servers/{server}', 'middleware' => ['auth:sanctum', 'throttle:api']], function () {
    Route::group(['prefix' => '/tps'], function () {
        // Get current TPS data
        Route::get('/current', [TpsController::class, 'current'])
            ->name('api.client.servers.tps.current');
        
        // Get TPS history
        Route::get('/history', [TpsController::class, 'history'])
            ->name('api.client.servers.tps.history');
        
        // Get TPS statistics
        Route::get('/statistics', [TpsController::class, 'statistics'])
            ->name('api.client.servers.tps.statistics');
        
        // Get TPS trend
        Route::get('/trend', [TpsController::class, 'trend'])
            ->name('api.client.servers.tps.trend');
        
        // Store TPS data
        Route::post('/', [TpsController::class, 'store'])
            ->name('api.client.servers.tps.store');
        
        // Get real-time TPS (execute server command)
        Route::post('/realtime', [TpsController::class, 'realtime'])
            ->name('api.client.servers.tps.realtime');
        
        // Cleanup old TPS data
        Route::delete('/cleanup', [TpsController::class, 'cleanup'])
            ->name('api.client.servers.tps.cleanup');
        
        // Get TPS alerts configuration
        Route::get('/alerts', [TpsController::class, 'getAlerts'])
            ->name('api.client.servers.tps.alerts.get');
        
        // Update TPS alerts configuration
        Route::put('/alerts', [TpsController::class, 'updateAlerts'])
            ->name('api.client.servers.tps.alerts.update');
        
        // Export TPS data
        Route::get('/export', [TpsController::class, 'export'])
            ->name('api.client.servers.tps.export');
        
        // Get performance summary
        Route::get('/summary', [TpsController::class, 'summary'])
            ->name('api.client.servers.tps.summary');
    });
});