# Database Backup Strategy - Production

**Comprehensive backup and recovery strategy for the admission system database.**

## Recommended Backup Solution: Laravel Backup Package

### 1. Install Spatie Laravel Backup

```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

### 2. Configure Backup Settings

Edit `config/backup.php`:

```php
return [
    'backup' => [
        'name' => env('APP_NAME', 'laravel-backup'),
        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],
            ],
            'databases' => [
                'mysql' => [
                    'name' => env('DB_DATABASE'),
                    'host' => env('DB_HOST'),
                    'port' => env('DB_PORT'),
                    'username' => env('DB_USERNAME'),
                    'password' => env('DB_PASSWORD'),
                ],
            ],
        ],
        'database_dump_compressor' => null,
        'destination' => [
            'filename_prefix' => '',
            'disks' => [
                'local',
                's3', // Optional: for cloud storage
            ],
        ],
        'temporary_directory' => storage_path('app/backup-temp'),
    ],
    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => ['slack'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => ['mail'],
        ],
        'mail' => [
            'to' => 'your-email@example.com',
        ],
        'slack' => [
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
        ],
    ],
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default' => [
            'keep_all_backups_for_days' => 7,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],
];
```

### 3. Schedule Automated Backups

Edit `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Daily backup at 2 AM
    $schedule->command('backup:run')->dailyAt('02:00');
    
    // Weekly full backup on Sunday at 3 AM
    $schedule->command('backup:run --only-db')->weeklyOn(0, '03:00');
    
    // Cleanup old backups daily at 4 AM
    $schedule->command('backup:clean')->dailyAt('04:00');
}
```

## Manual Backup Methods

### Method 1: Using Laravel Backup Package

```bash
# Full backup (database + files)
php artisan backup:run

# Database only
php artisan backup:run --only-db

# Files only
php artisan backup:run --only-files

# Run backup without notifications
php artisan backup:run --disable-notifications
```

### Method 2: Using mysqldump (Native MySQL)

```bash
# Full database backup
mysqldump -u root -p admission > backup_$(date +%Y%m%d_%H%M%S).sql

# Compressed backup
mysqldump -u root -p admission | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz

# Backup with stored procedures and triggers
mysqldump -u root -p --routines --triggers admission > backup_full.sql

# Backup specific tables
mysqldump -u root -p admission users transactions > backup_critical.sql
```

### Method 3: Using Laravel Artisan Command

Create custom backup command:

```bash
php artisan make:command DatabaseBackup
```

Edit `app/Console/Commands/DatabaseBackup.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup {--filename=}';
    protected $description = 'Backup the database';

    public function handle()
    {
        $filename = $this->option('filename') ?? 'backup_' . date('Y_m_d_His') . '.sql';
        $path = storage_path('app/backups/' . $filename);
        
        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            $path
        );
        
        exec($command);
        
        $this->info("Database backed up to: {$path}");
        
        return Command::SUCCESS;
    }
}
```

Usage:
```bash
php artisan db:backup
php artisan db:backup --filename=custom_backup.sql
```

## Backup Storage Strategy

### Local Storage (Primary)
- Store backups on server: `storage/app/backups/`
- Keep last 7 days daily backups
- Keep last 4 weeks weekly backups
- Keep last 12 months monthly backups

### Cloud Storage (Secondary - Recommended)
- Upload backups to AWS S3, DigitalOcean Spaces, or similar
- Provides off-site protection
- Enables disaster recovery

**Configure S3 in .env:**
```env
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-backup-bucket
AWS_URL=https://your-bucket.s3.amazonaws.com
```

**Add to config/filesystems.php:**
```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
],
```

## Backup Verification

### 1. Automated Verification

Add to schedule:
```php
$schedule->command('backup:list')->dailyAt('05:00');
```

### 2. Manual Verification

```bash
# List all backups
php artisan backup:list

# Check backup integrity
gunzip -c backup.sql.gz | head

# Verify SQL syntax
mysql -u root -p admission < backup.sql --dry-run
```

### 3. Test Restoration (Monthly)

```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE admission_test;"

# Restore backup to test database
mysql -u root -p admission_test < backup.sql

# Verify data integrity
mysql -u root -p admission_test -e "SELECT COUNT(*) FROM users;"
mysql -u root -p admission_test -e "SELECT COUNT(*) FROM transactions;"

# Drop test database
mysql -u root -p -e "DROP DATABASE admission_test;"
```

## Recovery Procedures

### Scenario 1: Complete Database Restoration

```bash
# Stop application
php artisan down

# Restore from latest backup
mysql -u root -p admission < backup_latest.sql

# Or from compressed backup
gunzip < backup_latest.sql.gz | mysql -u root -p admission

# Verify restoration
mysql -u root -p admission -e "SELECT COUNT(*) FROM users;"

# Start application
php artisan up
```

### Scenario 2: Point-in-Time Recovery (if binary logs enabled)

```bash
# Restore full backup
mysql -u root -p admission < full_backup.sql

# Apply binary logs from backup time to failure point
mysqlbinlog --start-datetime="2024-08-18 10:00:00" --stop-datetime="2024-08-18 14:30:00" /var/lib/mysql/mysql-bin.000123 | mysql -u root -p admission
```

### Scenario 3: Single Table Restoration

```bash
# Extract single table from backup
sed -n '/^-- Table structure for table `users`/,/^-- Table structure for table/p' backup.sql > users_table.sql

# Restore single table
mysql -u root -p admission < users_table.sql
```

## Pre-Migration Backup Checklist

Before ANY database change:

- [ ] Run full backup
- [ ] Verify backup file exists and is not empty
- [ ] Test backup integrity
- [ ] Store backup in multiple locations (local + cloud)
- [ ] Document backup filename and location
- [ ] Note exact time of backup
- [ ] Have rollback SQL ready
- [ ] Test rollback procedure in staging

## Backup Retention Policy

### Daily Backups
- Keep for: 7 days
- Purpose: Quick recovery from recent errors
- Storage: Local + Cloud

### Weekly Backups
- Keep for: 4 weeks
- Purpose: Medium-term recovery
- Storage: Cloud only

### Monthly Backups
- Keep for: 12 months
- Purpose: Long-term archival
- Storage: Cloud only

### Yearly Backups
- Keep for: 7 years
- Purpose: Legal compliance
- Storage: Cold storage (AWS Glacier)

## Monitoring and Alerts

### Backup Health Monitoring

```php
// Add to schedule
$schedule->command('backup:monitor')->dailyAt('06:00');
```

Configure alerts in `config/backup.php`:
- Email on backup failure
- Slack notification on backup success
- Alert if backup is older than 24 hours
- Alert if disk space is low

### Manual Check Commands

```bash
# Check last backup time
ls -lt storage/app/backups/ | head -5

# Check disk space
df -h

# Check backup size
du -sh storage/app/backups/
```

## Disaster Recovery Plan

### Scenario: Server Failure

1. **Immediate Actions:**
   - Assess damage
   - Notify stakeholders
   - Initiate disaster recovery

2. **Recovery Steps:**
   - Provision new server
   - Install required software (PHP, MySQL, Laravel)
   - Configure environment
   - Restore latest backup from cloud
   - Verify data integrity
   - Update DNS if needed
   - Monitor for issues

3. **Timeline:**
   - Assessment: 30 minutes
   - Server provisioning: 1-2 hours
   - Data restoration: 1-2 hours
   - Verification: 1 hour
   - Total: 4-6 hours

### Scenario: Data Corruption

1. **Identify corruption:**
   - Check error logs
   - Identify affected tables
   - Determine time of corruption

2. **Recovery:**
   - Restore from backup before corruption
   - Apply transaction logs if available
   - Verify data integrity
   - Identify root cause

## Security Considerations

### Backup Encryption

```bash
# Encrypt backup
gpg --symmetric --cipher-algo AES256 backup.sql

# Decrypt backup
gpg --decrypt backup.sql.gpg > backup.sql
```

### Access Control

- Restrict backup directory permissions: `chmod 700 storage/app/backups/`
- Encrypt backups stored in cloud
- Use separate database user for backups (read-only)
- Rotate encryption keys regularly

### Backup File Naming

Use consistent naming convention:
```
backup_YYYYMMDD_HHMMSS_full.sql.gz
backup_YYYYMMDD_HHMMSS_db_only.sql.gz
backup_YYYYMMDD_HHMMSS_files_only.zip
```

## Testing Schedule

### Weekly
- Verify backup schedule ran successfully
- Check backup file sizes
- Monitor disk space

### Monthly
- Test restoration on staging server
- Verify data integrity
- Update recovery documentation

### Quarterly
- Full disaster recovery drill
- Test cloud backup restoration
- Review and update backup strategy

## Cost Estimation

### Local Storage
- 500 GB SSD: ~$50-100/month
- Sufficient for 2-3 years of backups

### Cloud Storage (AWS S3)
- Standard storage: ~$0.023/GB/month
- 100 GB: ~$2.30/month
- Glacier (cold storage): ~$0.004/GB/month

### Total Monthly Cost
- Local: $50-100
- Cloud: $5-20
- **Total: $55-120/month**

## Quick Reference Commands

```bash
# Backup
php artisan backup:run
mysqldump -u root -p admission > backup.sql

# Restore
mysql -u root -p admission < backup.sql
gunzip < backup.sql.gz | mysql -u root -p admission

# Verify
php artisan backup:list
mysql -u root -p admission -e "SELECT COUNT(*) FROM users;"

# Monitor
df -h
du -sh storage/app/backups/
ls -lt storage/app/backups/ | head -5
```

---

**Last Updated:** August 2026  
**Status:** Production Ready  
**Next Review:** September 2026
