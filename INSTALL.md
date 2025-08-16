# Minecraft TPS Monitor - Installation Guide

This guide provides detailed step-by-step instructions for installing the Minecraft TPS Monitor addon on your Pterodactyl Panel.

## Pre-Installation Checklist

Before starting the installation, ensure you have:

- [ ] Root or sudo access to your server
- [ ] Pterodactyl Panel v1.11.0 or higher installed
- [ ] PHP 8.1 or higher
- [ ] Composer installed
- [ ] Node.js 16+ and npm installed
- [ ] Database access (MySQL/MariaDB/PostgreSQL)
- [ ] Web server (Nginx/Apache) configured

## Installation Methods

### Method 1: Manual Installation (Recommended)

#### Step 1: Backup Your Panel

**⚠️ IMPORTANT: Always backup your panel before installing any addon!**

```bash
# Backup database
mysqldump -u root -p panel > panel_backup_$(date +%Y%m%d_%H%M%S).sql

# Backup panel files
tar -czf pterodactyl_backup_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/pterodactyl
```

#### Step 2: Download and Extract

```bash
# Navigate to pterodactyl directory
cd /var/www/pterodactyl

# Download the addon (replace with actual download URL)
wget https://github.com/your-repo/minecraft-tps-monitor/archive/main.zip

# Extract the addon
unzip main.zip

# Copy files to panel directory
cp -r minecraft-tps-monitor-main/panelfiles/* .

# Remove temporary files
rm -rf main.zip minecraft-tps-monitor-main
```

#### Step 3: Install PHP Dependencies

```bash
# Install/update composer dependencies
composer install --no-dev --optimize-autoloader

# If you encounter any issues, try:
composer update --no-dev --optimize-autoloader
```

#### Step 4: Install Node.js Dependencies

```bash
# Install npm dependencies
npm install

# If you encounter permission issues:
sudo npm install --unsafe-perm=true --allow-root
```

#### Step 5: Database Migration

```bash
# Run database migrations
php artisan migrate

# If migration fails, check your database connection in .env
# Then try again:
php artisan migrate --force
```

#### Step 6: Register Service Provider

Edit `config/app.php` and add the service provider:

```php
'providers' => [
    // ... existing providers ...
    
    /*
     * TPS Monitor Service Provider
     */
    Pterodactyl\Providers\TpsMonitorServiceProvider::class,
],
```

#### Step 7: Publish Configuration

```bash
# Publish configuration file
php artisan vendor:publish --tag=tps-monitor-config

# Edit configuration if needed
nano config/tps-monitor.php
```

#### Step 8: Configure Environment

Add these variables to your `.env` file:

```env
# TPS Monitor Settings
TPS_MONITOR_RETENTION_DAYS=30
TPS_MONITOR_AUTO_CLEANUP=true
TPS_MONITOR_COLLECTION_INTERVAL=60
TPS_MONITOR_AUTO_COLLECT=false
TPS_MONITOR_ALERTS_ENABLED=false
TPS_MONITOR_DEFAULT_CHART_HOURS=24
TPS_MONITOR_AUTO_REFRESH_INTERVAL=30
TPS_MONITOR_DEBUG=false
```

#### Step 9: Build Frontend Assets

```bash
# Build production assets
npm run build:production

# For development environment:
# npm run build
```

#### Step 10: Clear Caches

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Step 11: Set Permissions

```bash
# Set ownership
chown -R www-data:www-data /var/www/pterodactyl

# Set permissions
chmod -R 755 /var/www/pterodactyl
chmod -R 775 /var/www/pterodactyl/storage
chmod -R 775 /var/www/pterodactyl/bootstrap/cache
```

#### Step 12: Restart Services

```bash
# Restart web server
sudo systemctl restart nginx
# OR for Apache:
# sudo systemctl restart apache2

# Restart PHP-FPM (if using)
sudo systemctl restart php8.1-fpm

# Restart queue workers (if using)
sudo systemctl restart pteroq
```

### Method 2: Automated Installation Script

**⚠️ Use at your own risk. Always review scripts before running.**

```bash
# Download and run installation script
wget https://raw.githubusercontent.com/your-repo/minecraft-tps-monitor/main/install.sh
chmod +x install.sh
sudo ./install.sh
```

## Post-Installation Setup

### 1. Admin Panel Configuration

1. Log in to your Pterodactyl admin panel
2. Navigate to **Admin** → **TPS Monitor**
3. Configure default settings
4. Test the dashboard functionality

### 2. Server Configuration

1. Go to a Minecraft server in your panel
2. Look for the **TPS Monitor** tab
3. Verify the component loads correctly
4. Test TPS data collection

### 3. API Testing

Test the API endpoints:

```bash
# Replace {server_uuid} with actual server UUID
# Replace {api_token} with your API token

curl -H "Authorization: Bearer {api_token}" \
     -H "Accept: application/json" \
     "https://your-panel.com/api/client/servers/{server_uuid}/tps/current"
```

## Verification

### Check Installation Status

```bash
# Check if migration ran successfully
php artisan migrate:status | grep tps_monitor

# Check if service provider is registered
php artisan route:list | grep tps

# Check if assets are built
ls -la public/assets/ | grep tps
```

### Test Functionality

1. **Admin Dashboard**: Visit `/admin/tps` and verify the dashboard loads
2. **Server Panel**: Go to any Minecraft server and check for TPS Monitor tab
3. **API Endpoints**: Test API calls return proper responses
4. **Database**: Check if `tps_monitor` table exists and is accessible

## Troubleshooting

### Common Installation Issues

#### Issue: Migration Fails

```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# If connection works, try:
php artisan migrate:refresh
php artisan migrate --path=database/migrations/2024_01_01_000000_create_tps_monitor_table.php
```

#### Issue: Assets Not Building

```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Try building again
npm run build:production
```

#### Issue: Service Provider Not Loading

```bash
# Check if provider is in config/app.php
grep -n "TpsMonitorServiceProvider" config/app.php

# Clear config cache
php artisan config:clear
php artisan config:cache
```

#### Issue: Permission Denied

```bash
# Fix ownership and permissions
sudo chown -R www-data:www-data /var/www/pterodactyl
sudo chmod -R 755 /var/www/pterodactyl
sudo chmod -R 775 /var/www/pterodactyl/storage
sudo chmod -R 775 /var/www/pterodactyl/bootstrap/cache
```

#### Issue: 500 Internal Server Error

```bash
# Check error logs
tail -f /var/log/nginx/error.log
# OR
tail -f /var/log/apache2/error.log

# Check Laravel logs
tail -f storage/logs/laravel.log

# Enable debug mode temporarily
# In .env file:
APP_DEBUG=true
```

### Getting Help

If you encounter issues:

1. Check the [troubleshooting section](README.md#troubleshooting) in README
2. Search [existing issues](https://github.com/your-repo/minecraft-tps-monitor/issues)
3. Create a new issue with:
   - Your Pterodactyl version
   - PHP version
   - Error messages
   - Installation steps you followed

## Uninstallation

If you need to remove the addon:

```bash
# Remove database tables
php artisan migrate:rollback --path=database/migrations/2024_01_01_000000_create_tps_monitor_table.php

# Remove service provider from config/app.php
# Remove configuration file
rm config/tps-monitor.php

# Remove addon files (be careful!)
# This is manual process - remove only TPS monitor related files

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Rebuild assets
npm run build:production
```

## Security Considerations

- Always backup before installation
- Keep your panel updated
- Use strong database passwords
- Limit API access appropriately
- Monitor logs for suspicious activity
- Use HTTPS in production

## Performance Optimization

After installation, consider:

- Enable Redis for caching
- Configure queue workers for background tasks
- Set up proper database indexing
- Use CDN for static assets
- Enable gzip compression

---

**Installation complete!** 🎉

Your Minecraft TPS Monitor should now be ready to use. Visit your admin panel to start monitoring your servers' performance.