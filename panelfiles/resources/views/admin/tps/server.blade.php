@extends('layouts.admin')

@section('title')
    TPS Monitor - {{ $server->name }}
@endsection

@section('content-header')
    <h1>{{ $server->name }} <small>TPS Performance Details</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.tps.index') }}">TPS Monitor</a></li>
        <li class="active">{{ $server->name }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Server Information</h3>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Server Name:</dt>
                    <dd>{{ $server->name }}</dd>
                    <dt>UUID:</dt>
                    <dd><code>{{ $server->uuid }}</code></dd>
                    <dt>Node:</dt>
                    <dd>{{ $server->node->name ?? 'Unknown' }}</dd>
                    <dt>Allocation:</dt>
                    <dd>{{ $server->allocation->ip ?? 'Unknown' }}:{{ $server->allocation->port ?? 'Unknown' }}</dd>
                    <dt>Status:</dt>
                    <dd>
                        @if($latestTps)
                            @if($latestTps->tps >= 18)
                                <span class="label label-success">Excellent Performance</span>
                            @elseif($latestTps->tps >= 15)
                                <span class="label label-warning">Fair Performance</span>
                            @else
                                <span class="label label-danger">Poor Performance</span>
                            @endif
                        @else
                            <span class="label label-default">No Data Available</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Current Performance Metrics</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-sm btn-success" onclick="refreshMetrics()">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="box-body">
                @if($latestTps)
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-{{ $latestTps->tps >= 18 ? 'green' : ($latestTps->tps >= 15 ? 'yellow' : 'red') }}">
                                <i class="fa fa-tachometer"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">TPS</span>
                                <span class="info-box-number">{{ number_format($latestTps->tps, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-{{ $latestTps->mspt <= 50 ? 'green' : ($latestTps->mspt <= 100 ? 'yellow' : 'red') }}">
                                <i class="fa fa-clock-o"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">MSPT</span>
                                <span class="info-box-number">{{ $latestTps->mspt ? number_format($latestTps->mspt, 2) . 'ms' : '--' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-blue">
                                <i class="fa fa-users"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Players</span>
                                <span class="info-box-number">{{ $latestTps->players_online ?? '--' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-purple">
                                <i class="fa fa-memory"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Memory</span>
                                <span class="info-box-number">{{ $latestTps->memory_usage ? number_format($latestTps->memory_usage, 1) . '%' : '--' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <p class="text-muted">Last updated: {{ $latestTps->created_at->format('Y-m-d H:i:s') }} ({{ $latestTps->created_at->diffForHumans() }})</p>
                    </div>
                </div>
                @else
                <div class="alert alert-warning">
                    <i class="fa fa-warning"></i> No TPS data available for this server.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">TPS History Chart</h3>
                <div class="box-tools pull-right">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-default" onclick="loadChart(1)">1H</button>
                        <button type="button" class="btn btn-sm btn-default" onclick="loadChart(6)">6H</button>
                        <button type="button" class="btn btn-sm btn-default active" onclick="loadChart(24)">24H</button>
                        <button type="button" class="btn btn-sm btn-default" onclick="loadChart(168)">7D</button>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <canvas id="tpsHistoryChart" width="400" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Recent TPS Records</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>TPS</th>
                            <th>MSPT</th>
                            <th>CPU Usage</th>
                            <th>Memory Usage</th>
                            <th>Players Online</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTps as $tps)
                            <tr>
                                <td>{{ $tps->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <span class="label label-{{ $tps->tps >= 18 ? 'success' : ($tps->tps >= 15 ? 'warning' : 'danger') }}">
                                        {{ number_format($tps->tps, 2) }}
                                    </span>
                                </td>
                                <td>{{ $tps->mspt ? number_format($tps->mspt, 2) . 'ms' : '--' }}</td>
                                <td>{{ $tps->cpu_usage ? number_format($tps->cpu_usage, 1) . '%' : '--' }}</td>
                                <td>{{ $tps->memory_usage ? number_format($tps->memory_usage, 1) . '%' : '--' }}</td>
                                <td>{{ $tps->players_online ?? '--' }}</td>
                                <td>
                                    @if($tps->tps >= 18)
                                        <span class="label label-success">Excellent</span>
                                    @elseif($tps->tps >= 15)
                                        <span class="label label-warning">Fair</span>
                                    @else
                                        <span class="label label-danger">Poor</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No TPS records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentTps->hasPages())
                <div class="box-footer clearfix">
                    {{ $recentTps->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let tpsChart = null;
        
        function refreshMetrics() {
            location.reload();
        }
        
        function loadChart(hours) {
            // Update active button
            document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            fetch('/admin/tps/server/{{ $server->id }}?hours=' + hours)
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('tpsHistoryChart').getContext('2d');
                    
                    if (tpsChart) {
                        tpsChart.destroy();
                    }
                    
                    const labels = data.map(item => {
                        const date = new Date(item.created_at);
                        if (hours <= 24) {
                            return date.toLocaleTimeString();
                        } else {
                            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
                        }
                    });
                    
                    tpsChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'TPS',
                                data: data.map(item => item.tps),
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.1,
                                fill: true
                            }, {
                                label: 'MSPT (÷10)',
                                data: data.map(item => item.mspt ? item.mspt / 10 : null),
                                borderColor: 'rgb(255, 99, 132)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                tension: 0.1,
                                fill: false
                            }]
                        },
                        options: {
                            responsive: true,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Time'
                                    }
                                },
                                y: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'TPS / MSPT(÷10)'
                                    },
                                    min: 0,
                                    max: 20
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'TPS and MSPT Over Time (Last ' + hours + ' Hours)'
                                },
                                legend: {
                                    display: true
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                    alert('Error loading chart data: ' + error.message);
                });
        }
        
        // Load initial chart on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadChart(24);
        });
    </script>
@endsection