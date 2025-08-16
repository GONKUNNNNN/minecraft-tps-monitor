<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $server_id
 * @property float $tps
 * @property float|null $mspt
 * @property float|null $cpu_usage
 * @property float|null $memory_usage
 * @property int|null $players_online
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Pterodactyl\Models\Server $server
 */
class TpsMonitor extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'tps_monitor';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'server_id',
        'tps',
        'mspt',
        'cpu_usage',
        'memory_usage',
        'players_online',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'server_id' => 'integer',
        'tps' => 'float',
        'mspt' => 'float',
        'cpu_usage' => 'float',
        'memory_usage' => 'float',
        'players_online' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the server that this TPS record belongs to.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get TPS status based on value
     */
    public function getTpsStatusAttribute(): string
    {
        if ($this->tps >= 19.5) {
            return 'excellent';
        } elseif ($this->tps >= 18) {
            return 'good';
        } elseif ($this->tps >= 15) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Get TPS color for UI display
     */
    public function getTpsColorAttribute(): string
    {
        switch ($this->tps_status) {
            case 'excellent':
                return 'green';
            case 'good':
                return 'blue';
            case 'fair':
                return 'yellow';
            case 'poor':
                return 'red';
            default:
                return 'gray';
        }
    }

    /**
     * Get MSPT status
     */
    public function getMsptStatusAttribute(): string
    {
        if (!$this->mspt) {
            return 'unknown';
        }
        
        if ($this->mspt <= 50) {
            return 'excellent';
        } elseif ($this->mspt <= 100) {
            return 'good';
        } elseif ($this->mspt <= 150) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Scope to get recent records
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope to get records for a specific server
     */
    public function scopeForServer($query, int $serverId)
    {
        return $query->where('server_id', $serverId);
    }

    /**
     * Get average TPS for a time period
     */
    public static function getAverageTps(int $serverId, int $hours = 24): float
    {
        return static::forServer($serverId)
            ->recent($hours)
            ->avg('tps') ?? 0.0;
    }

    /**
     * Get TPS trend (positive = improving, negative = declining)
     */
    public static function getTpsTrend(int $serverId, int $hours = 24): float
    {
        $records = static::forServer($serverId)
            ->recent($hours)
            ->orderBy('created_at', 'asc')
            ->get(['tps', 'created_at']);
            
        if ($records->count() < 2) {
            return 0.0;
        }
        
        $firstHalf = $records->take($records->count() / 2)->avg('tps');
        $secondHalf = $records->skip($records->count() / 2)->avg('tps');
        
        return $secondHalf - $firstHalf;
    }

    /**
     * Clean up old records
     */
    public static function cleanup(int $days = 7): int
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }
}