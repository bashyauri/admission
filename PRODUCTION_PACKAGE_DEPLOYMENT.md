# Production Package Deployment Guide (No Git/No Staging)

**Safe workflow for installing packages directly on live production servers without Git or staging environment.**

## Pre-Deployment Checklist

### 1. Local Development Testing
```bash
# On local machine
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
php artisan config:cache
php artisan migrate
php artisan backup:run --only-db
```

**Verify:**
- [ ] Package installed successfully
- [ ] Config files published correctly
- [ ] Migration ran without errors
- [ ] Backup command works
- [ ] No conflicts with existing code
- [ ] Application still functions normally

### 2. Production Backup (CRITICAL)
```bash
# On production server
php artisan down
php artisan backup:run
php artisan up
```

**Verify:**
- [ ] Backup completed successfully
- [ ] Backup file exists
- [ ] Backup file size is reasonable
- [ ] Note backup filename and location

## Deployment Methods

### Method 1: Direct Production Deployment (Recommended for No Git/No Staging)

**Step 1: SSH into Production Server**
```bash
ssh user@your-server.com
cd /var/www/admission
```

**Step 2: Create Full Backup**
```bash
# Put application in maintenance mode
php artisan down

# Create full database backup
mysqldump -u root -p admission > backup_before_package_$(date +%Y%m%d_%H%M%S).sql

# Backup current composer files
cp composer.json composer.json.backup
cp composer.lock composer.lock.backup

# Take application out of maintenance mode
php artisan up
```

**Step 3: Install Package**
```bash
# Install package
composer require spatie/laravel-backup --no-interaction
```

**Step 4: Publish Config**
```bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider" --force
```

**Step 5: Configure Backup Settings**
```bash
nano config/backup.php
```

**Edit these settings:**
```php
'name' => env('APP_NAME', 'admission-backup'),
'destination' => [
    'disks' => ['local'], // Start with local only
],
'cleanup' => [
    'default' => [
        'keep_all_backups_for_days' => 7,
        'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
    ],
],
```

**Step 6: Clear Caches**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**Step 7: Run Migrations**
```bash
php artisan migrate --force
```

**Step 8: Cache Config**
```bash
php artisan config:cache
```

**Step 9: Test Backup Command**
```bash
php artisan backup:run --only-db --disable-notifications
```

**Step 10: Verify Backup**
```bash
# Check if backup was created
ls -lh storage/app/backups/

# Verify backup file has content
head storage/app/backups/*.zip
```

**Step 11: Test Application**
```bash
# Check if site is accessible
curl -I https://your-domain.com

# Check Laravel logs
tail -50 storage/logs/laravel.log
```

**Step 12: Schedule Backup Job**
```bash
# Edit app/Console/Kernel.php
nano app/Console/Kernel.php
```

**Add this to the schedule method:**
```php
protected function schedule(Schedule $schedule)
{
    // Daily backup at 2 AM
    $schedule->command('backup:run')->dailyAt('02:00');
    
    // Cleanup old backups daily at 4 AM
    $schedule->command('backup:clean')->dailyAt('04:00');
}
```

**Step 13: Verify Schedule**
```bash
# Test schedule command
php artisan schedule:list

# Run schedule manually to test
php artisan schedule:run
```

### Method 2: Manual Deployment with Manual Backup (Alternative)

This method is similar to Method 1 but uses manual backup commands instead of maintenance mode.

**Step 1: SSH into Production Server**
```bash
ssh user@your-server.com
cd /var/www/admission
```

**Step 2: Create Manual Backup**
```bash
# Backup database
mysqldump -u root -p admission > backup_manual_$(date +%Y%m%d_%H%M%S).sql

# Backup composer files
cp composer.json composer.json.manual_backup
cp composer.lock composer.lock.manual_backup
```

**Step 3: Install Package**
```bash
composer require spatie/laravel-backup --no-interaction
```

**Step 4: Publish Config**
```bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider" --force
```

**Step 5: Configure and Test**
Follow Steps 5-13 from Method 1

## Post-Deployment Verification

### 1. Check Application Health
```bash
# Check if site is accessible
curl -I https://your-domain.com

# Check Laravel logs
tail -f storage/logs/laravel.log

# Check for errors
php artisan about
```

### 2. Verify Package Functionality
```bash
# Test backup command
php artisan backup:run --only-db

# List backups
php artisan backup:list

# Check backup files
ls -lh storage/app/backups/
```

### 3. Monitor for 24 Hours
- Check application logs regularly
- Monitor server resources
- Verify backups are running automatically
- Check for any user-reported issues

## Rollback Procedure

If deployment fails:

### Option 1: Git Rollback
```bash
# Revert to previous commit
git checkout HEAD~1
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan migrate:rollback --force
```

### Option 2: Restore from Backup
```bash
# If database was affected
mysql -u root -p admission < backup_before_deployment.sql
```

### Option 3: Revert Package Installation
```bash
# Remove package
composer remove spatie/laravel-backup
php artisan config:cache
```

## Best Practices

### 1. Always Deploy During Low Traffic
- Best time: 2 AM - 4 AM local time
- Avoid: Business hours, peak registration periods
- Notify users in advance if maintenance needed

### 2. Use Maintenance Mode for Critical Changes
```bash
php artisan down
# Make changes
php artisan up
```

### 3. Keep Composer Dependencies Updated
```bash
# Regular updates (monthly)
composer update

# Security updates (immediately)
composer update spatie/laravel-backup
```

### 4. Use Environment-Specific Config
```bash
# Production .env
BACKUP_DISK=s3
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_BUCKET=your-backup-bucket

# Local .env
BACKUP_DISK=local
```

### 5. Monitor Disk Space
```bash
# Check before deployment
df -h

# Clean old backups if needed
php artisan backup:clean
```

## Troubleshooting

### Issue: Composer Install Fails
```bash
# Clear composer cache
composer clear-cache

# Try with more memory
COMPOSER_MEMORY_LIMIT=-1 composer install

# Check PHP version
php -v
```

### Issue: Migration Fails
```bash
# Check migration status
php artisan migrate:status

# Force migration (if safe)
php artisan migrate --force

# Rollback if needed
php artisan migrate:rollback --force
```

### Issue: Config Cache Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Re-cache
php artisan config:cache
php artisan route:cache
```

### Issue: Permission Errors
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Quick Reference Commands

```bash
# Backup before deployment
php artisan backup:run

# Install package
composer require package/name

# Publish config
php artisan vendor:publish --provider="Vendor\Package\ServiceProvider"

# Run migrations
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:cache

# Test package
php artisan package:command

# Rollback
git checkout HEAD~1
php artisan migrate:rollback --force
```

## Deployment Checklist

**Before Deployment:**
- [ ] Local testing completed
- [ ] Staging testing completed
- [ ] Production backup created
- [ ] Low traffic period selected
- [ ] Rollback plan documented
- [ ] Team notified

**During Deployment:**
- [ ] Code pulled successfully
- [ ] Dependencies installed
- [ ] Migrations ran successfully
- [ ] Config cached
- [ ] Package tested
- [ ] Application verified

**After Deployment:**
- [ ] Application accessible
- [ ] No errors in logs
- [ ] Package functionality verified
- [ ] Backups running automatically
- [ ] Monitoring for 24 hours
- [ ] Documentation updated

---

**Last Updated:** August 2026  
**Status:** Production Ready  
**Risk Level:** Low (with proper testing)
