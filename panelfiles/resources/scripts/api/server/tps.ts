import http from '@/api/http';

export interface TpsData {
    id: number;
    server_id: string;
    tps: number;
    mspt: number | null;
    cpu_usage: number | null;
    memory_usage: number | null;
    players_online: number | null;
    created_at: string;
    updated_at: string;
}

export interface TpsStats {
    avg_tps: number;
    min_tps: number;
    max_tps: number;
    avg_mspt: number;
    min_mspt: number;
    max_mspt: number;
    total_records: number;
    period_hours: number;
}

export interface TpsTrend {
    current_avg: number;
    previous_avg: number;
    trend: 'up' | 'down' | 'stable';
    change_percentage: number;
}

/**
 * Get the latest TPS data for a server
 */
export const getTpsData = async (uuid: string): Promise<TpsData | null> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/tps/current`);
    return data.data || null;
};

/**
 * Get TPS history for a server within a specified time range
 */
export const getTpsHistory = async (uuid: string, hours: number = 24): Promise<TpsData[]> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/tps/history`, {
        params: { hours }
    });
    return data.data || [];
};

/**
 * Get TPS statistics for a server within a specified time range
 */
export const getTpsStats = async (uuid: string, hours: number = 24): Promise<TpsStats> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/tps/statistics`, {
        params: { hours }
    });
    return data.data;
};

/**
 * Get TPS trend comparison for a server
 */
export const getTpsTrend = async (uuid: string, hours: number = 24): Promise<TpsTrend> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/tps/trend`, {
        params: { hours }
    });
    return data.data;
};

/**
 * Store new TPS data for a server
 */
export const storeTpsData = async (uuid: string, tpsData: {
    tps: number;
    mspt?: number;
    cpu_usage?: number;
    memory_usage?: number;
    players_online?: number;
}): Promise<TpsData> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/tps`, tpsData);
    return data.data;
};

/**
 * Get real-time TPS by executing server command
 */
export const getRealTimeTps = async (uuid: string): Promise<{
    tps: number;
    mspt: number;
    success: boolean;
    message?: string;
}> => {
    const { data } = await http.post(`/api/client/servers/${uuid}/tps/realtime`);
    return data.data;
};

/**
 * Delete old TPS records for a server
 */
export const cleanupTpsData = async (uuid: string, days: number = 7): Promise<{
    deleted_count: number;
    success: boolean;
}> => {
    const { data } = await http.delete(`/api/client/servers/${uuid}/tps/cleanup`, {
        params: { days }
    });
    return data.data;
};

/**
 * Get TPS alerts configuration for a server
 */
export const getTpsAlerts = async (uuid: string): Promise<{
    enabled: boolean;
    low_tps_threshold: number;
    high_mspt_threshold: number;
    notification_channels: string[];
}> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/tps/alerts`);
    return data.data;
};

/**
 * Update TPS alerts configuration for a server
 */
export const updateTpsAlerts = async (uuid: string, config: {
    enabled: boolean;
    low_tps_threshold: number;
    high_mspt_threshold: number;
    notification_channels: string[];
}): Promise<void> => {
    await http.put(`/api/client/servers/${uuid}/tps/alerts`, config);
};

/**
 * Export TPS data to CSV format
 */
export const exportTpsData = async (uuid: string, hours: number = 168): Promise<Blob> => {
    const response = await http.get(`/api/client/servers/${uuid}/tps/export`, {
        params: { hours, format: 'csv' },
        responseType: 'blob'
    });
    return response.data;
};

/**
 * Get server performance summary
 */
export const getPerformanceSummary = async (uuid: string): Promise<{
    current_status: 'excellent' | 'good' | 'fair' | 'poor' | 'unknown';
    uptime_percentage: number;
    avg_tps_24h: number;
    avg_mspt_24h: number;
    peak_players_24h: number;
    performance_score: number;
    recommendations: string[];
}> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/tps/summary`);
    return data.data;
};