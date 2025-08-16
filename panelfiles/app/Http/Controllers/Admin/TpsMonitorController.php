<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Pterodactyl\Models\TpsMonitor;

class TpsMonitorController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \Pterodactyl\Contracts\Repository\SettingsRepositoryInterface
     */
    private $settings;

    /**
     * TpsMonitorController constructor.
     * @param AlertsMessageBag $alert
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(AlertsMessageBag $alert, SettingsRepositoryInterface $settings)
    {
        $this->alert = $alert;
        $this->settings = $settings;
    }

    /**
     * Display TPS monitoring dashboard
     */
    public function index(): View
    {
        $servers = DB::table('servers')
            ->join('eggs', 'servers.egg_id', '=', 'eggs.id')
            ->where('eggs.name', 'like', '%minecraft%')
            ->orWhere('eggs.name', 'like', '%spigot%')
            ->orWhere('eggs.name', 'like', '%paper%')
            ->orWhere('eggs.name', 'like', '%bukkit%')
            ->select('servers.*')
            ->get();

        $tpsData = TpsMonitor::with('server')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('admin.tps.index', [
            'servers' => $servers,
            'tpsData' => $tpsData
        ]);
    }

    /**
     * Get TPS data for a specific server
     */
    public function getServerTps(Request $request, int $serverId)
    {
        $hours = $request->get('hours', 24);
        
        $tpsData = TpsMonitor::where('server_id', $serverId)
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($tpsData);
    }

    /**
     * Store TPS data
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'server_id' => 'required|exists:servers,id',
            'tps' => 'required|numeric|min:0|max:20',
            'mspt' => 'nullable|numeric|min:0',
            'cpu_usage' => 'nullable|numeric|min:0|max:100',
            'memory_usage' => 'nullable|numeric|min:0',
            'players_online' => 'nullable|integer|min:0'
        ]);

        TpsMonitor::create([
            'server_id' => $request->server_id,
            'tps' => $request->tps,
            'mspt' => $request->mspt,
            'cpu_usage' => $request->cpu_usage,
            'memory_usage' => $request->memory_usage,
            'players_online' => $request->players_online,
            'created_at' => now()
        ]);

        $this->alert->success('TPS data recorded successfully!')->flash();
        return redirect()->route('admin.tps.index');
    }

    /**
     * Delete old TPS data
     */
    public function cleanup(Request $request): RedirectResponse
    {
        $days = $request->get('days', 7);
        
        $deleted = TpsMonitor::where('created_at', '<', now()->subDays($days))->delete();
        
        $this->alert->success("Cleaned up {$deleted} old TPS records.")->flash();
        return redirect()->route('admin.tps.index');
    }

    /**
     * Get TPS statistics
     */
    public function statistics(Request $request)
    {
        $serverId = $request->get('server_id');
        $hours = $request->get('hours', 24);
        
        $query = TpsMonitor::where('created_at', '>=', now()->subHours($hours));
        
        if ($serverId) {
            $query->where('server_id', $serverId);
        }
        
        $stats = $query->selectRaw('
            AVG(tps) as avg_tps,
            MIN(tps) as min_tps,
            MAX(tps) as max_tps,
            AVG(mspt) as avg_mspt,
            AVG(cpu_usage) as avg_cpu,
            AVG(memory_usage) as avg_memory,
            AVG(players_online) as avg_players
        ')->first();
        
        return response()->json($stats);
    }
}