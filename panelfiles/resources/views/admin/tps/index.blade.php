@extends('layouts.admin')

@section('title')
    TPS Monitor
@endsection

@section('content-header')
    <h1>TPS Monitor <small>Minecraft Server Performance Monitoring</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">TPS Monitor</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Server Performance Overview</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-sm btn-success" onclick="refreshData()">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="cleanupData()">
                        <i class="fa fa-trash"></i> Cleanup Old Data
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fa fa-server"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Minecraft Servers</span>
                                <span class="info-box-number">{{ count($servers) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-blue"><i class="fa fa-line-chart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">TPS Records</span>
                                <span class="info-box-number">{{ count($tpsData) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Avg TPS (24h)</span>
                                <span class="info-box-number" id="avg-tps">--</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Low TPS Alerts</span>
                                <span class="info-box-number" id="low-tps-count">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Server List</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Server</th>
                            <th>Current TPS</th>
                            <th>MSPT</th>
                            <th>Players</th>
                            <th>Status</th>
                            <th>Last Update</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servers as $server)
                            @php
                                $latestTps = $tpsData->where('server_id', $server->id)->first();
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $server->name }}</strong><br>
                                    <small class="text-muted">{{ $server->uuid }}</small>
                                </td>
                                <td>
                                    @if($latestTps)
                                        <span class="label label-{{ $latestTps->tps >= 18 ? 'success' : ($latestTps->tps >= 15 ? 'warning' : 'danger') }}">
                                            {{ number_format($latestTps->tps, 2) }}
                                        </span>
                                    @else
                                        <span class="label label-default">No Data</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latestTps && $latestTps->mspt)
                                        {{ number_format($latestTps->mspt, 2) }}ms
                                    @else
                                        --
                                    @endif
                                </td>
                                <td>
                                    @if($latestTps && $latestTps->players_online !== null)
                                        {{ $latestTps->players_online }}
                                    @else
                                        --
                                    @endif
                                </td>
                                <td>
                                    @if($latestTps)
                                        @if($latestTps->tps >= 18)
                                            <span class="label label-success">Excellent</span>
                                        @elseif($latestTps->tps >= 15)
                                            <span class="label label-warning">Fair</span>
                                        @else
                                            <span class="label label-danger">Poor</span>
                                        @endif
                                    @else
                                        <span class="label label-default">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latestTps)
                                        {{ $latestTps->created_at->diffForHumans() }}
                                    @else
                                        Never
                                    @endif
                                </td>
                                <td>
                                    <a href="#" class="btn btn-xs btn-primary" onclick="viewServerDetails({{ $server->id }})">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                    <a href="#" class="btn btn-xs btn-info" onclick="showChart({{ $server->id }})">
                                        <i class="fa fa-line-chart"></i> Chart
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No Minecraft servers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- TPS Chart Modal -->
<div class="modal fade" id="tpsChartModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">TPS Chart</h4>
            </div>
            <div class="modal-body">
                <canvas id="tpsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let tpsChart = null;
        
        function refreshData() {
            location.reload();
        }
        
        function cleanupData() {
            if (confirm('Are you sure you want to cleanup old TPS data? This will remove records older than 7 days.')) {
                fetch('{{ route("admin.tps.cleanup") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ days: 7 })
                })
                .then(response => response.json())
                .then(data => {
                    alert('Cleanup completed successfully!');
                    location.reload();
                })
                .catch(error => {
                    alert('Error during cleanup: ' + error.message);
                });
            }
        }
        
        function viewServerDetails(serverId) {
            // Redirect to server details page
            window.open('/admin/servers/view/' + serverId, '_blank');
        }
        
        function showChart(serverId) {
            fetch('/admin/tps/server/' + serverId + '?hours=24')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('tpsChart').getContext('2d');
                    
                    if (tpsChart) {
                        tpsChart.destroy();
                    }
                    
                    tpsChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.map(item => new Date(item.created_at).toLocaleTimeString()),
                            datasets: [{
                                label: 'TPS',
                                data: data.map(item => item.tps),
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 20
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'TPS Over Time (Last 24 Hours)'
                                }
                            }
                        }
                    });
                    
                    $('#tpsChartModal').modal('show');
                })
                .catch(error => {
                    alert('Error loading chart data: ' + error.message);
                });
        }
        
        // Load statistics on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/admin/tps/statistics?hours=24')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('avg-tps').textContent = data.avg_tps ? data.avg_tps.toFixed(2) : '--';
                    
                    // Count low TPS records (< 15)
                    const lowTpsCount = {{ $tpsData->where('tps', '<', 15)->count() }};
                    document.getElementById('low-tps-count').textContent = lowTpsCount;
                })
                .catch(error => {
                    console.error('Error loading statistics:', error);
                });
        });
    </script>
@endsection