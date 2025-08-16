<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\TpsMonitor;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\GetServerRequest;
use Pterodactyl\Repositories\Wings\DaemonCommandRepository;
use Pterodactyl\Exceptions\DisplayException;
use Carbon\Carbon;

class TpsController extends ClientApiController
{
    /**
     * @var \Pterodactyl\Repositories\Wings\DaemonCommandRepository
     */
    private $daemonCommandRepository;

    /**
     * TpsController constructor.
     */
    public function __construct(DaemonCommandRepository $daemonCommandRepository)
    {
        parent::__construct();
        $this->daemonCommandRepository = $daemonCommandRepository;
    }

    /**
     * Get current TPS for the server
     */
    public function index(GetServerRequest $request): JsonResponse
    {
        $server = $request->getModel(Server::class);
        
        // Get latest TPS data
        $latestTps = TpsMonitor::where('server_id', $server->id)
            ->orderBy('created_at', 'desc')
            ->first();
            
        // Get TPS history for the last 24 hours
        $tpsHistory = TpsMonitor::where('server_id', $server->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at', 'asc')
            ->get();
            
        return new JsonResponse([
            'current' => $latestTps,
            'history' => $tpsHistory,
            'server_id' => $server->id
        ]);
    }

    /**
     * Get TPS history for a specific time range
     */
    public function history(GetServerRequest $request): JsonResponse
    {
        $server = $request->getModel(Server::class);
        $hours = $request->query('hours', 24);
        $limit = $request->query('limit', 100);
        
        if ($hours > 168) { // Max 7 days
            throw new DisplayException('Cannot request more than 168 hours of data.');
        }
        
        $tpsData = TpsMonitor::where('server_id', $server->id)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
            
        return new JsonResponse($tpsData);
    }

    /**
     * Get TPS statistics
     */
    public function statistics(GetServerRequest $request): JsonResponse
    {
        $server = $request->getModel(Server::class);
        $hours = $request->query('hours', 24);
        
        $stats = TpsMonitor::where('server_id', $server->id)
            ->where('created_at', '>=', now()->subHours($hours))
            ->selectRaw('
                AVG(tps) as avg_tps,
                MIN(tps) as min_tps,
                MAX(tps) as max_tps,
                AVG(mspt) as avg_mspt,
                MAX(mspt) as max_mspt,
                AVG(cpu_usage) as avg_cpu,
                MAX(cpu_usage) as max_cpu,
                AVG(memory_usage) as avg_memory,
                MAX(memory_usage) as max_memory,
                AVG(players_online) as avg_players,
                MAX(players_online) as max_players,
                COUNT(*) as data_points
            ')
            ->first();
            
        // Calculate performance rating
        $performanceRating = 'excellent';
        if ($stats->avg_tps < 15) {
            $performanceRating = 'poor';
        } elseif ($stats->avg_tps < 18) {
            $performanceRating = 'fair';
        } elseif ($stats->avg_tps < 19.5) {
            $performanceRating = 'good';
        }
        
        $stats->performance_rating = $performanceRating;
        
        return new JsonResponse($stats);
    }

    /**
     * Record TPS data (called by server or external monitoring)
     */
    public function store(GetServerRequest $request): JsonResponse
    {
        $server = $request->getModel(Server::class);
        
        $validated = $request->validate([
            'tps' => 'required|numeric|min:0|max:20',
            'mspt' => 'nullable|numeric|min:0',
            'cpu_usage' => 'nullable|numeric|min:0|max:100',
            'memory_usage' => 'nullable|numeric|min:0',
            'players_online' => 'nullable|integer|min:0'
        ]);
        
        $tpsRecord = TpsMonitor::create([
            'server_id' => $server->id,
            'tps' => $validated['tps'],
            'mspt' => $validated['mspt'] ?? null,
            'cpu_usage' => $validated['cpu_usage'] ?? null,
            'memory_usage' => $validated['memory_usage'] ?? null,
            'players_online' => $validated['players_online'] ?? null,
            'created_at' => now()
        ]);
        
        return new JsonResponse($tpsRecord, 201);
    }

    /**
     * Get real-time TPS by executing server command
     */
    public function realtime(GetServerRequest $request): JsonResponse
    {
        $server = $request->getModel(Server::class);
        
        try {
            // Send TPS command to server
            $this->daemonCommandRepository->setServer($server)->send('tps');
            
            // Note: In a real implementation, you would need to parse the server response
            // This is a simplified version
            return new JsonResponse([
                'message' => 'TPS command sent to server. Check console for results.',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            throw new DisplayException('Failed to get real-time TPS: ' . $e->getMessage());
        }
    }
}