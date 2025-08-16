<?php
/**
 * Migration Fix Script for TPS Monitor
 * 
 * This script helps fix common migration issues with the TPS Monitor addon.
 * Run this from your Pterodactyl panel root directory.
 * 
 * Usage: php fix-migration.php
 */

require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

echo "TPS Monitor Migration Fix Script\n";
echo "==================================\n\n";

try {
    // Check if we can connect to the database
    echo "Checking database connection...\n";
    DB::connection()->getPdo();
    echo "✓ Database connection successful\n\n";
    
    // Check if tps_monitor table exists
    echo "Checking if tps_monitor table exists...\n";
    if (Schema::hasTable('tps_monitor')) {
        echo "⚠ Table 'tps_monitor' already exists\n";
        echo "Do you want to drop and recreate it? (y/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 'y') {
            echo "Dropping existing table...\n";
            Schema::dropIfExists('tps_monitor');
            echo "✓ Table dropped successfully\n\n";
        } else {
            echo "Skipping table recreation\n\n";
        }
    } else {
        echo "✓ Table does not exist, ready to create\n\n";
    }
    
    // Check migration status
    echo "Checking migration status...\n";
    $exitCode = Artisan::call('migrate:status');
    echo Artisan::output();
    
    // Run the specific migration
    echo "\nRunning TPS Monitor migration...\n";
    $exitCode = Artisan::call('migrate', [
        '--path' => 'database/migrations/2024_01_01_000000_create_tps_monitor_table.php',
        '--force' => true
    ]);
    
    if ($exitCode === 0) {
        echo "✓ Migration completed successfully!\n";
        echo "\nVerifying table structure...\n";
        
        // Verify the table was created correctly
        if (Schema::hasTable('tps_monitor')) {
            $columns = Schema::getColumnListing('tps_monitor');
            echo "✓ Table created with columns: " . implode(', ', $columns) . "\n";
            
            // Check if foreign key exists
            $foreignKeys = DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'tps_monitor' AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (!empty($foreignKeys)) {
                echo "✓ Foreign key constraint created successfully\n";
            } else {
                echo "⚠ Foreign key constraint not found, but table created\n";
            }
        } else {
            echo "✗ Table was not created\n";
        }
    } else {
        echo "✗ Migration failed\n";
        echo Artisan::output();
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting tips:\n";
    echo "1. Make sure you're running this from the Pterodactyl panel root directory\n";
    echo "2. Check your database configuration in .env file\n";
    echo "3. Ensure the database user has proper permissions\n";
    echo "4. Try running 'php artisan config:clear' first\n";
}

echo "\nScript completed.\n";