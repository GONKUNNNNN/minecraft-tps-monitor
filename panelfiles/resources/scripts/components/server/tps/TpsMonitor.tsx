import React, { useState, useEffect } from 'react';
import { ServerContext } from '@/state/server';
import { httpErrorToHuman } from '@/api/http';
import { useStoreState } from 'easy-peasy';
import TitleBar from '@/components/elements/TitleBar';
import FlashMessageRender from '@/components/FlashMessageRender';
import Spinner from '@/components/elements/Spinner';
import { Line } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import { getTpsData, getTpsHistory, getTpsStats } from '@/api/server/tps';
import { format } from 'date-fns';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

interface TpsData {
    id: number;
    server_id: string;
    tps: number;
    mspt: number | null;
    cpu_usage: number | null;
    memory_usage: number | null;
    players_online: number | null;
    created_at: string;
}

interface TpsStats {
    avg_tps: number;
    min_tps: number;
    max_tps: number;
    avg_mspt: number;
    total_records: number;
}

const TpsMonitor = () => {
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [currentTps, setCurrentTps] = useState<TpsData | null>(null);
    const [tpsHistory, setTpsHistory] = useState<TpsData[]>([]);
    const [tpsStats, setTpsStats] = useState<TpsStats | null>(null);
    const [timeRange, setTimeRange] = useState(24); // hours
    const [autoRefresh, setAutoRefresh] = useState(true);
    
    const uuid = useStoreState((state) => state.server.data!.uuid);
    const name = useStoreState((state) => state.server.data!.name);

    const loadTpsData = async () => {
        try {
            setError('');
            const [current, history, stats] = await Promise.all([
                getTpsData(uuid),
                getTpsHistory(uuid, timeRange),
                getTpsStats(uuid, timeRange)
            ]);
            
            setCurrentTps(current);
            setTpsHistory(history);
            setTpsStats(stats);
        } catch (error: any) {
            console.error('Failed to load TPS data:', error);
            setError(httpErrorToHuman(error));
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadTpsData();
    }, [uuid, timeRange]);

    useEffect(() => {
        if (!autoRefresh) return;
        
        const interval = setInterval(() => {
            loadTpsData();
        }, 30000); // Refresh every 30 seconds
        
        return () => clearInterval(interval);
    }, [autoRefresh, uuid, timeRange]);

    const getTpsColor = (tps: number): string => {
        if (tps >= 18) return 'text-green-400';
        if (tps >= 15) return 'text-yellow-400';
        return 'text-red-400';
    };

    const getTpsStatus = (tps: number): string => {
        if (tps >= 18) return 'Excellent';
        if (tps >= 15) return 'Fair';
        return 'Poor';
    };

    const getMsptColor = (mspt: number): string => {
        if (mspt <= 50) return 'text-green-400';
        if (mspt <= 100) return 'text-yellow-400';
        return 'text-red-400';
    };

    const chartData = {
        labels: tpsHistory.map(data => format(new Date(data.created_at), timeRange <= 24 ? 'HH:mm' : 'MM/dd HH:mm')),
        datasets: [
            {
                label: 'TPS',
                data: tpsHistory.map(data => data.tps),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                fill: true,
                tension: 0.4,
            },
            {
                label: 'MSPT (÷10)',
                data: tpsHistory.map(data => data.mspt ? data.mspt / 10 : null),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: false,
                tension: 0.4,
            },
        ],
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top' as const,
                labels: {
                    color: 'rgb(156, 163, 175)',
                },
            },
            title: {
                display: true,
                text: `TPS History (Last ${timeRange}h)`,
                color: 'rgb(156, 163, 175)',
            },
        },
        scales: {
            x: {
                ticks: {
                    color: 'rgb(156, 163, 175)',
                },
                grid: {
                    color: 'rgba(156, 163, 175, 0.1)',
                },
            },
            y: {
                min: 0,
                max: 20,
                ticks: {
                    color: 'rgb(156, 163, 175)',
                },
                grid: {
                    color: 'rgba(156, 163, 175, 0.1)',
                },
            },
        },
    };

    if (loading) {
        return (
            <>
                <TitleBar title={'TPS Monitor'} />
                <div className={'flex justify-center items-center h-64'}>
                    <Spinner size={'large'} />
                </div>
            </>
        );
    }

    const server = useStoreState(state => state.server.data!);

    return (
        <>
            <TitleBar title={'TPS Monitor'} />
                    <FlashMessageRender byKey={'server:tps'} />
                    
                    {error && (
                        <div className={'bg-red-500 p-3 rounded mb-4'}>
                            <p className={'text-sm text-white'}>{error}</p>
                        </div>
                    )}

                    {/* Control Panel */}
                    <div className={'bg-gray-700 rounded p-4 mb-6'}>
                        <div className={'flex flex-wrap items-center justify-between gap-4'}>
                            <div className={'flex items-center gap-4'}>
                                <button
                                    onClick={() => loadTpsData()}
                                    className={'bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors'}
                                >
                                    Refresh
                                </button>
                                
                                <label className={'flex items-center gap-2 text-sm text-gray-300'}>
                                    <input
                                        type="checkbox"
                                        checked={autoRefresh}
                                        onChange={(e) => setAutoRefresh(e.target.checked)}
                                        className={'rounded'}
                                    />
                                    Auto Refresh (30s)
                                </label>
                            </div>
                            
                            <div className={'flex items-center gap-2'}>
                                <span className={'text-sm text-gray-300'}>Time Range:</span>
                                {[1, 6, 24, 168].map((hours) => (
                                    <button
                                        key={hours}
                                        onClick={() => setTimeRange(hours)}
                                        className={`px-3 py-1 rounded text-sm transition-colors ${
                                            timeRange === hours
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-gray-600 text-gray-300 hover:bg-gray-500'
                                        }`}
                                    >
                                        {hours === 168 ? '7d' : `${hours}h`}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Current Stats */}
                    <div className={'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6'}>
                        <div className={'bg-gray-700 rounded p-4'}>
                            <div className={'text-sm text-gray-400 mb-1'}>Current TPS</div>
                            <div className={`text-2xl font-bold ${currentTps ? getTpsColor(currentTps.tps) : 'text-gray-400'}`}>
                                {currentTps ? currentTps.tps.toFixed(2) : '--'}
                            </div>
                            <div className={'text-xs text-gray-500'}>
                                {currentTps ? getTpsStatus(currentTps.tps) : 'No Data'}
                            </div>
                        </div>
                        
                        <div className={'bg-gray-700 rounded p-4'}>
                            <div className={'text-sm text-gray-400 mb-1'}>MSPT</div>
                            <div className={`text-2xl font-bold ${currentTps?.mspt ? getMsptColor(currentTps.mspt) : 'text-gray-400'}`}>
                                {currentTps?.mspt ? `${currentTps.mspt.toFixed(2)}ms` : '--'}
                            </div>
                            <div className={'text-xs text-gray-500'}>
                                {currentTps?.mspt ? (currentTps.mspt <= 50 ? 'Good' : currentTps.mspt <= 100 ? 'Fair' : 'Poor') : 'No Data'}
                            </div>
                        </div>
                        
                        <div className={'bg-gray-700 rounded p-4'}>
                            <div className={'text-sm text-gray-400 mb-1'}>Players Online</div>
                            <div className={'text-2xl font-bold text-blue-400'}>
                                {currentTps?.players_online ?? '--'}
                            </div>
                            <div className={'text-xs text-gray-500'}>Active Players</div>
                        </div>
                        
                        <div className={'bg-gray-700 rounded p-4'}>
                            <div className={'text-sm text-gray-400 mb-1'}>Memory Usage</div>
                            <div className={'text-2xl font-bold text-purple-400'}>
                                {currentTps?.memory_usage ? `${currentTps.memory_usage.toFixed(1)}%` : '--'}
                            </div>
                            <div className={'text-xs text-gray-500'}>RAM Usage</div>
                        </div>
                    </div>

                    {/* Statistics */}
                    {tpsStats && (
                        <div className={'bg-gray-700 rounded p-4 mb-6'}>
                            <h3 className={'text-lg font-semibold text-gray-200 mb-3'}>Statistics (Last {timeRange}h)</h3>
                            <div className={'grid grid-cols-2 md:grid-cols-5 gap-4 text-sm'}>
                                <div>
                                    <div className={'text-gray-400'}>Average TPS</div>
                                    <div className={'text-white font-semibold'}>{tpsStats.avg_tps.toFixed(2)}</div>
                                </div>
                                <div>
                                    <div className={'text-gray-400'}>Min TPS</div>
                                    <div className={'text-white font-semibold'}>{tpsStats.min_tps.toFixed(2)}</div>
                                </div>
                                <div>
                                    <div className={'text-gray-400'}>Max TPS</div>
                                    <div className={'text-white font-semibold'}>{tpsStats.max_tps.toFixed(2)}</div>
                                </div>
                                <div>
                                    <div className={'text-gray-400'}>Average MSPT</div>
                                    <div className={'text-white font-semibold'}>{tpsStats.avg_mspt.toFixed(2)}ms</div>
                                </div>
                                <div>
                                    <div className={'text-gray-400'}>Records</div>
                                    <div className={'text-white font-semibold'}>{tpsStats.total_records}</div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Chart */}
                    <div className={'bg-gray-700 rounded p-4 mb-6'}>
                        <div className={'h-64'}>
                            {tpsHistory.length > 0 ? (
                                <Line data={chartData} options={chartOptions} />
                            ) : (
                                <div className={'flex items-center justify-center h-full text-gray-400'}>
                                    No TPS history data available
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Recent Records */}
                    <div className={'bg-gray-700 rounded p-4'}>
                        <h3 className={'text-lg font-semibold text-gray-200 mb-3'}>Recent Records</h3>
                        <div className={'overflow-x-auto'}>
                            <table className={'w-full text-sm'}>
                                <thead>
                                    <tr className={'text-gray-400 border-b border-gray-600'}>
                                        <th className={'text-left py-2'}>Time</th>
                                        <th className={'text-left py-2'}>TPS</th>
                                        <th className={'text-left py-2'}>MSPT</th>
                                        <th className={'text-left py-2'}>Players</th>
                                        <th className={'text-left py-2'}>Memory</th>
                                        <th className={'text-left py-2'}>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {tpsHistory.slice(0, 10).map((record) => (
                                        <tr key={record.id} className={'border-b border-gray-600'}>
                                            <td className={'py-2 text-gray-300'}>
                                                {format(new Date(record.created_at), 'MM/dd HH:mm:ss')}
                                            </td>
                                            <td className={`py-2 font-semibold ${getTpsColor(record.tps)}`}>
                                                {record.tps.toFixed(2)}
                                            </td>
                                            <td className={'py-2 text-gray-300'}>
                                                {record.mspt ? `${record.mspt.toFixed(2)}ms` : '--'}
                                            </td>
                                            <td className={'py-2 text-gray-300'}>
                                                {record.players_online ?? '--'}
                                            </td>
                                            <td className={'py-2 text-gray-300'}>
                                                {record.memory_usage ? `${record.memory_usage.toFixed(1)}%` : '--'}
                                            </td>
                                            <td className={'py-2'}>
                                                <span className={`px-2 py-1 rounded text-xs ${
                                                    record.tps >= 18 ? 'bg-green-600 text-white' :
                                                    record.tps >= 15 ? 'bg-yellow-600 text-white' :
                                                    'bg-red-600 text-white'
                                                }`}>
                                                    {getTpsStatus(record.tps)}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            {tpsHistory.length === 0 && (
                                <div className={'text-center py-8 text-gray-400'}>
                                    No TPS records available
                                </div>
                            )}
                        </div>
                    </div>
        </>
    );
};

export default TpsMonitor;