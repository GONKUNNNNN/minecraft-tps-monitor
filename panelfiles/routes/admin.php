<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\Admin\TpsMonitorController;

/*
|--------------------------------------------------------------------------
| TPS Monitor Admin Routes
|--------------------------------------------------------------------------
|
| These routes are for the admin TPS monitoring functionality.
| All routes are prefixed with /admin
|
*/

Route::group(['prefix' => 'tps', 'middleware' => ['auth', 'admin']], function () {
    // TPS Monitor Dashboard
    Route::get('/', [TpsMonitorController::class, 'index'])
        ->name('admin.tps.index');
    
    // Get TPS data for specific server (AJAX)
    Route::get('/server/{server}', [TpsMonitorController::class, 'getServerTps'])
        ->name('admin.tps.server');
    
    // Store TPS data (AJAX)
    Route::post('/store', [TpsMonitorController::class, 'store'])
        ->name('admin.tps.store');
    
    // Cleanup old TPS data (AJAX)
    Route::post('/cleanup', [TpsMonitorController::class, 'cleanup'])
        ->name('admin.tps.cleanup');
    
    // Get TPS statistics (AJAX)
    Route::get('/statistics', [TpsMonitorController::class, 'getStatistics'])
        ->name('admin.tps.statistics');
    
    // Server-specific TPS view
    Route::get('/server/{server}/view', [TpsMonitorController::class, 'serverView'])
        ->name('admin.tps.server.view');
    
    // Export TPS data
    Route::get('/export', [TpsMonitorController::class, 'export'])
        ->name('admin.tps.export');
    
    // TPS Monitor Settings
    Route::get('/settings', [TpsMonitorController::class, 'settings'])
        ->name('admin.tps.settings');
    
    // Update TPS Monitor Settings
    Route::post('/settings', [TpsMonitorController::class, 'updateSettings'])
        ->name('admin.tps.settings.update');
    
    // Bulk cleanup for all servers
    Route::post('/bulk-cleanup', [TpsMonitorController::class, 'bulkCleanup'])
        ->name('admin.tps.bulk-cleanup');
    
    // Get system performance overview
    Route::get('/system-overview', [TpsMonitorController::class, 'systemOverview'])
        ->name('admin.tps.system-overview');
});