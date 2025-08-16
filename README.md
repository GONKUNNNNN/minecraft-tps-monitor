# Minecraft TPS Monitor for Pterodactyl Panel

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Pterodactyl](https://img.shields.io/badge/Pterodactyl-Compatible-blue.svg)](https://pterodactyl.io/)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net/)
[![React](https://img.shields.io/badge/React-18%2B-blue.svg)](https://reactjs.org/)

A comprehensive TPS (Ticks Per Second) monitoring addon for Pterodactyl Panel that provides real-time performance monitoring for Minecraft servers.

## Features

### 📊 Performance Monitoring
- **Real-time TPS tracking** - Monitor server performance in real-time
- **MSPT (Milliseconds Per Tick) monitoring** - Track server tick processing time
- **Historical data** - View performance trends over time
- **Performance statistics** - Get detailed analytics and insights

### 📈 Visual Analytics
- **Interactive charts** - Beautiful Chart.js powered visualizations
- **Multiple time ranges** - View data from 1 hour to 7 days
- **Performance status indicators** - Color-coded status based on performance
- **Trend analysis** - Compare current vs previous performance

### 🎛️ Admin Dashboard
- **System overview** - Monitor all servers from one dashboard
- **Server-specific views** - Detailed performance data per server
- **Bulk operations** - Manage multiple servers efficiently
- **Data export** - Export performance data in CSV/JSON formats

### 🔧 Advanced Features
- **Automatic data collection** - Configurable collection intervals
- **Data retention policies** - Automatic cleanup of old records
- **Performance alerts** - Get notified when performance drops
- **API endpoints** - Full REST API for external integrations

### 🎨 Modern UI
- **Responsive design** - Works on desktop and mobile devices
- **Dark theme** - Matches Pterodactyl's modern interface
- **Real-time updates** - Auto-refresh capabilities
- **Intuitive navigation** - Easy to use interface

## Screenshots

### Admin Dashboard
![Admin Dashboard](screenshots/admin-dashboard.png)

### Server TPS Monitor
![Server Monitor](screenshots/server-monitor.png)

### Performance Charts
![Performance Charts](screenshots/performance-charts.png)

## Requirements

- **Pterodactyl Panel** v1.11.0 or higher
- **PHP** 8.1 or higher
- **MySQL/MariaDB** 5.7+ or **PostgreSQL** 12+
- **Node.js** 16+ (for frontend compilation)
- **Minecraft Server** with TPS command support (Spigot, Paper, Forge, etc.)

## Installation

### Step 1: Download the Addon

```bash
cd /var/www/pterodactyl
wget https://github.com/your-repo/minecraft-tps-monitor/archive/main.zip
unzip main.zip
cp -r minecraft-tps-monitor-main/panelfiles/* .
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
npm install
```

### Step 3: Database Migration

```bash
# Run database migrations
php artisan migrate

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Step 4: Register Service Provider

Add the service provider to `config/app.php`:

```php
'providers' => [
    // ... other providers
    Pterodactyl\Providers\TpsMonitorServiceProvider::class,
],
```

### Step 5: Publish Configuration

```bash
# Publish configuration file
php artisan vendor:publish --tag=tps-monitor-config

# Edit configuration as needed
nano config/tps-monitor.php
```

### Step 6: Build Frontend Assets

```bash
# Build production assets
npm run build:production

# Or for development
npm run build
```

### Step 7: Set Permissions

```bash
# Set proper permissions
chown -R www-data:www-data /var/www/pterodactyl
chmod -R 755 /var/www/pterodactyl
```

### Step 8: Configure Web Server

Restart your web server (Nginx/Apache) to ensure all changes take effect.

```bash
# For Nginx
sudo systemctl restart nginx

# For Apache
sudo systemctl restart apache2
```

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# TPS Monitor Configuration
TPS_MONITOR_RETENTION_DAYS=30
TPS_MONITOR_AUTO_CLEANUP=true
TPS_MONITOR_COLLECTION_INTERVAL=60
TPS_MONITOR_AUTO_COLLECT=false
TPS_MONITOR_ALERTS_ENABLED=false
TPS_MONITOR_DEFAULT_CHART_HOURS=24
TPS_MONITOR_AUTO_REFRESH_INTERVAL=30
```

### Performance Thresholds

Customize performance classification in `config/tps-monitor.php`:

```php
'thresholds' => [
    'tps' => [
        'excellent' => 18.0,
        'good' => 16.0,
        'fair' => 15.0,
    ],
    'mspt' => [
        'excellent' => 40.0,
        'good' => 50.0,
        'fair' => 100.0,
    ],
],
```

## Usage

### Admin Panel

1. Navigate to **Admin Panel** → **TPS Monitor**
2. View system-wide performance overview
3. Click on individual servers for detailed analysis
4. Use time range controls to view historical data
5. Export data for external analysis

### Server Panel

1. Go to your server's control panel
2. Click on the **TPS Monitor** tab
3. View real-time performance metrics
4. Analyze performance trends with interactive charts
5. Configure alerts and notifications

### API Usage

The addon provides RESTful API endpoints:

```bash
# Get current TPS data
GET /api/client/servers/{server}/tps/current

# Get TPS history
GET /api/client/servers/{server}/tps/history?hours=24

# Get performance statistics
GET /api/client/servers/{server}/tps/statistics?hours=24

# Store TPS data
POST /api/client/servers/{server}/tps
```

## Supported Server Types

- **Spigot/Paper** - Full support with `tps` command
- **Forge** - Support with `forge tps` command
- **Fabric** - Support with Carpet mod (`carpet tps`)
- **Vanilla** - Limited support (requires additional setup)
- **Modded servers** - Support varies by mod availability

## Troubleshooting

### Common Issues

**TPS data not collecting:**
- Ensure your server supports TPS commands
- Check server console permissions
- Verify command configuration in settings

**Charts not displaying:**
- Clear browser cache
- Check JavaScript console for errors
- Ensure Chart.js is loaded properly

**Database errors:**
- Run `php artisan migrate` to ensure tables exist
- Check database permissions
- Verify database connection settings

### Debug Mode

Enable debug mode in configuration:

```php
'debug' => [
    'enabled' => true,
    'log_level' => 'debug',
],
```

### Log Files

Check these log files for errors:
- `storage/logs/laravel.log`
- `storage/logs/tps-monitor.log` (if configured)

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone repository
git clone https://github.com/your-repo/minecraft-tps-monitor.git
cd minecraft-tps-monitor

# Install dependencies
composer install
npm install

# Run development server
npm run dev
```

### Running Tests

```bash
# Run PHP tests
php artisan test

# Run JavaScript tests
npm test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- **Documentation**: [Wiki](https://github.com/your-repo/minecraft-tps-monitor/wiki)
- **Issues**: [GitHub Issues](https://github.com/your-repo/minecraft-tps-monitor/issues)
- **Discord**: [Join our Discord](https://discord.gg/your-invite)
- **Email**: support@yourproject.com

## Acknowledgments

- [Pterodactyl Panel](https://pterodactyl.io/) - The amazing game server management panel
- [Chart.js](https://www.chartjs.org/) - Beautiful charts and graphs
- [Laravel](https://laravel.com/) - The PHP framework powering the backend
- [React](https://reactjs.org/) - The frontend framework

## Roadmap

- [ ] Real-time WebSocket updates
- [ ] Advanced alerting system
- [ ] Grafana integration
- [ ] Mobile app
- [ ] Multi-language support
- [ ] Performance recommendations AI

---

**Made with ❤️ for the Minecraft community**