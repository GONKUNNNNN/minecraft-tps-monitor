<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTpsMonitorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tps_monitor', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id'); // Changed to match servers table
            $table->decimal('tps', 4, 2); // TPS value (0.00 to 20.00)
            $table->decimal('mspt', 8, 2)->nullable(); // Milliseconds per tick
            $table->decimal('cpu_usage', 5, 2)->nullable(); // CPU usage percentage
            $table->bigInteger('memory_usage')->nullable(); // Memory usage in bytes
            $table->integer('players_online')->nullable(); // Number of players online
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['server_id', 'created_at']);
            $table->index('created_at');
            $table->index('tps');
        });
        
        // Add foreign key constraint separately to handle potential issues
        Schema::table('tps_monitor', function (Blueprint $table) {
            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tps_monitor');
    }
}