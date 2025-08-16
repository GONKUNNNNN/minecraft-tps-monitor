<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TPS Monitor Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the TPS Monitor addon.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Configure how long TPS data should be retained in the database.
    |
    */
    'data_retention' => [
        // Number of days to keep TPS records (default: 30 days)
        'days' => env('TPS_MONITOR_RETENTION_DAYS', 30),
        
        // Enable automatic cleanup of old records
        'auto_cleanup' => env('TPS_MONITOR_AUTO_CLEANUP', true),
        
        // Cleanup schedule (cron expression)
        'cleanup_schedule' => env('TPS_MONITOR_CLEANUP_SCHEDULE', '0 2 * * *'), // Daily at 2 AM
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Collection
    |--------------------------------------------------------------------------
    |
    | Configure how TPS data is collected and stored.
    |
    */
    'collection' => [
        // Interval between TPS data collection (in seconds)
        'interval' => env('TPS_MONITOR_COLLECTION_INTERVAL', 60),
        
        // Enable automatic TPS data collection
        'auto_collect' => env('TPS_MONITOR_AUTO_COLLECT', false),
        
        // Maximum number of records to store per hour per server
        'max_records_per_hour' => env('TPS_MONITOR_MAX_RECORDS_PER_HOUR', 60),
        
        // Enable collection of additional metrics
        'collect_cpu_usage' => env('TPS_MONITOR_COLLECT_CPU', true),
        'collect_memory_usage' => env('TPS_MONITOR_COLLECT_MEMORY', true),
        'collect_player_count' => env('TPS_MONITOR_COLLECT_PLAYERS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Thresholds
    |--------------------------------------------------------------------------
    |
    | Define thresholds for performance classification.
    |
    */
    'thresholds' => [
        'tps' => [
            'excellent' => env('TPS_MONITOR_EXCELLENT_TPS', 18.0),
            'good' => env('TPS_MONITOR_GOOD_TPS', 16.0),
            'fair' => env('TPS_MONITOR_FAIR_TPS', 15.0),
            // Below fair is considered poor
        ],
        
        'mspt' => [
            'excellent' => env('TPS_MONITOR_EXCELLENT_MSPT', 40.0),
            'good' => env('TPS_MONITOR_GOOD_MSPT', 50.0),
            'fair' => env('TPS_MONITOR_FAIR_MSPT', 100.0),
            // Above fair is considered poor
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerts and Notifications
    |--------------------------------------------------------------------------
    |
    | Configure alert system for TPS monitoring.
    |
    */
    'alerts' => [
        // Enable alert system
        'enabled' => env('TPS_MONITOR_ALERTS_ENABLED', false),
        
        // Default alert thresholds
        'default_low_tps_threshold' => env('TPS_MONITOR_ALERT_LOW_TPS', 15.0),
        'default_high_mspt_threshold' => env('TPS_MONITOR_ALERT_HIGH_MSPT', 100.0),
        
        // Alert cooldown (prevent spam alerts)
        'cooldown_minutes' => env('TPS_MONITOR_ALERT_COOLDOWN', 10),
        
        // Notification channels
        'channels' => [
            'discord' => env('TPS_MONITOR_DISCORD_WEBHOOK', null),
            'email' => env('TPS_MONITOR_EMAIL_ALERTS', false),
            'database' => env('TPS_MONITOR_DATABASE_ALERTS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chart and Display Options
    |--------------------------------------------------------------------------
    |
    | Configure how TPS data is displayed in charts and tables.
    |
    */
    'display' => [
        // Default time range for charts (in hours)
        'default_chart_hours' => env('TPS_MONITOR_DEFAULT_CHART_HOURS', 24),
        
        // Available time range options
        'chart_time_options' => [1, 6, 24, 168], // 1h, 6h, 24h, 7d
        
        // Number of records to show in recent records table
        'recent_records_limit' => env('TPS_MONITOR_RECENT_RECORDS_LIMIT', 50),
        
        // Auto-refresh interval for dashboard (in seconds)
        'auto_refresh_interval' => env('TPS_MONITOR_AUTO_REFRESH_INTERVAL', 30),
        
        // Enable real-time updates via WebSocket
        'realtime_updates' => env('TPS_MONITOR_REALTIME_UPDATES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Server Commands
    |--------------------------------------------------------------------------
    |
    | Configure server commands used to retrieve TPS data.
    |
    */
    'commands' => [
        // Command to get TPS (varies by server type)
        'tps_commands' => [
            'spigot' => 'tps',
            'paper' => 'tps',
            'forge' => 'forge tps',
            'fabric' => 'carpet tps',
            'vanilla' => 'debug start', // Limited support
        ],
        
        // Timeout for command execution (in seconds)
        'command_timeout' => env('TPS_MONITOR_COMMAND_TIMEOUT', 10),
        
        // Enable command-based TPS collection
        'enable_command_collection' => env('TPS_MONITOR_ENABLE_COMMAND_COLLECTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API endpoints and rate limiting.
    |
    */
    'api' => [
        // Rate limiting for TPS API endpoints
        'rate_limit' => [
            'per_minute' => env('TPS_MONITOR_API_RATE_LIMIT', 60),
            'burst' => env('TPS_MONITOR_API_BURST_LIMIT', 10),
        ],
        
        // Enable public API endpoints (requires authentication)
        'public_endpoints' => env('TPS_MONITOR_PUBLIC_API', false),
        
        // API response caching (in seconds)
        'cache_duration' => env('TPS_MONITOR_API_CACHE', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Options
    |--------------------------------------------------------------------------
    |
    | Configure data export functionality.
    |
    */
    'export' => [
        // Available export formats
        'formats' => ['csv', 'json', 'xlsx'],
        
        // Maximum number of records per export
        'max_records' => env('TPS_MONITOR_EXPORT_MAX_RECORDS', 10000),
        
        // Export file storage path
        'storage_path' => env('TPS_MONITOR_EXPORT_PATH', 'exports/tps'),
        
        // Temporary file cleanup (in hours)
        'cleanup_hours' => env('TPS_MONITOR_EXPORT_CLEANUP_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization
    |--------------------------------------------------------------------------
    |
    | Configure performance optimization settings.
    |
    */
    'performance' => [
        // Enable database query caching
        'enable_query_cache' => env('TPS_MONITOR_QUERY_CACHE', true),
        
        // Cache duration for statistics queries (in minutes)
        'stats_cache_duration' => env('TPS_MONITOR_STATS_CACHE_DURATION', 5),
        
        // Enable data aggregation for old records
        'enable_aggregation' => env('TPS_MONITOR_ENABLE_AGGREGATION', true),
        
        // Aggregate records older than X days into hourly averages
        'aggregation_threshold_days' => env('TPS_MONITOR_AGGREGATION_THRESHOLD', 7),
        
        // Batch size for data processing
        'batch_size' => env('TPS_MONITOR_BATCH_SIZE', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    |
    | Configure integration with other systems.
    |
    */
    'integrations' => [
        // Grafana integration
        'grafana' => [
            'enabled' => env('TPS_MONITOR_GRAFANA_ENABLED', false),
            'endpoint' => env('TPS_MONITOR_GRAFANA_ENDPOINT', null),
            'api_key' => env('TPS_MONITOR_GRAFANA_API_KEY', null),
        ],
        
        // Prometheus metrics export
        'prometheus' => [
            'enabled' => env('TPS_MONITOR_PROMETHEUS_ENABLED', false),
            'endpoint' => env('TPS_MONITOR_PROMETHEUS_ENDPOINT', '/metrics/tps'),
        ],
        
        // InfluxDB integration
        'influxdb' => [
            'enabled' => env('TPS_MONITOR_INFLUXDB_ENABLED', false),
            'host' => env('TPS_MONITOR_INFLUXDB_HOST', 'localhost'),
            'port' => env('TPS_MONITOR_INFLUXDB_PORT', 8086),
            'database' => env('TPS_MONITOR_INFLUXDB_DATABASE', 'pterodactyl_tps'),
            'username' => env('TPS_MONITOR_INFLUXDB_USERNAME', null),
            'password' => env('TPS_MONITOR_INFLUXDB_PASSWORD', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug and Logging
    |--------------------------------------------------------------------------
    |
    | Configure debug and logging options.
    |
    */
    'debug' => [
        // Enable debug logging
        'enabled' => env('TPS_MONITOR_DEBUG', false),
        
        // Log level (debug, info, warning, error)
        'log_level' => env('TPS_MONITOR_LOG_LEVEL', 'info'),
        
        // Log channel
        'log_channel' => env('TPS_MONITOR_LOG_CHANNEL', 'tps-monitor'),
        
        // Enable query logging
        'log_queries' => env('TPS_MONITOR_LOG_QUERIES', false),
    ],
];