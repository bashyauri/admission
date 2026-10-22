# Production Backup & Google Drive Sync Guide

**Complete, End-to-End Setup Guide for Automated Encrypted Database & Uploads Backup via Rclone**

---

## 1. System Architecture Overview

This production backup system uses a **hybrid approach** to eliminate timeouts and memory exhaustion:

```text
┌────────────────────────────────────────────────────────────────────────┐
│                        Laravel Production Server                       │
│                                                                        │
│  1. Scheduled Job (01:00 AM)                                           │
│     php artisan backup:sync-google                                     │
│          │                                                             │
│          ▼                                                             │
│  2. Spatie Backup (Local Database Archive)                             │
│     • Dumps MySQL database                                             │
│     • Encrypts archive with AES-256 (Password protected)               │
│     • Stores archive in: storage/app/DHS Wufpbk/database-*.zip        │
│          │                                                             │
│          ▼                                                             │
│  3. Rclone Process Runner (Streaming Sync)                             │
│     • Copies database-*.zip ───► Google Drive: database/              │
│     • Copies storage/app/public/ ─► Google Drive: uploads/public/     │
│     • Copies senate-corrections/ ─► Google Drive: uploads/senate/     │
└────────────────────────────────────────────────────────────────────────┘
```

### Server Specifications for this Deployment
* **Server Management:** WHM & cPanel (with root SSH access)
* **cPanel User:** `wufpbked`
* **Application Root:** `/home/wufpbked/new-dhs`
* **Web Public Directory:** `/home/wufpbked/public_html` (symlinked `storage` points to `/home/wufpbked/new-dhs/storage/app/public`)
* **Target Cloud Storage:** Google Drive (via `rclone`)

---

## STAGE 1: Install Rclone System-Wide as Root

Log into your server via SSH as **`root`** (or open **Terminal** in WHM).

### 1.1 Run the official Rclone installer
```bash
curl https://rclone.org/install.sh | bash
```

### 1.2 Verify the installation
```bash
/usr/bin/rclone version
```
*Expected output:* `rclone v1.6x.x` (or newer) installed at `/usr/bin/rclone`.

---

## STAGE 2: Generate the Google Drive OAuth Token

Because production Linux servers have no desktop browser for Google's interactive login, generate the authentication token on your local computer and copy it over.

### 2.1 On Your Local Computer (Windows / macOS)
1. Download Rclone for your local OS from [https://rclone.org/downloads/](https://rclone.org/downloads/).
2. Unzip the folder (for example to `C:\rclone`).
3. Open PowerShell or Command Prompt inside that folder:
   ```cmd
   cd C:\rclone
   .\rclone.exe config
   ```

### 2.2 Walk Through the Interactive Setup
Follow these exact prompts in the terminal:

| Prompt | What to Enter |
| :--- | :--- |
| `n/s/q>` | `n` (New remote) |
| `name>` | `gdrive` |
| `Storage>` | `drive` (Google Drive) |
| `client_id>` | *(Press Enter to leave blank)* |
| `client_secret>` | *(Press Enter to leave blank)* |
| `scope>` | `1` (Full access to all files) |
| `service_account_file>` | *(Press Enter to leave blank)* |
| `Edit advanced config?>` | `n` (No) |
| `Use web browser to authenticate?>` | `y` (Yes) |

4. Your web browser will open automatically. Sign in with the Google Account that will store the backups and click **Allow**.
5. Back in the terminal:
   * `Configure this as a Shared Drive (Team Drive)?>` Enter `n`
   * `Keep this "gdrive" remote?>` Enter `y`
   * `q>` Enter `q` to quit the config wizard.

### 2.3 Locate and Copy the Generated Config
Run:
```cmd
.\rclone.exe config file
```
Open the file path displayed (e.g. `C:\Users\<YourUser>\AppData\Roaming\rclone\rclone.conf`) in Notepad.

You will see a block like this:
```ini
[gdrive]
type = drive
scope = drive
token = {"access_token":"ya29...","token_type":"Bearer","refresh_token":"1//...","expiry":"2026-..."}
```
**Copy this entire block.**

---

## STAGE 3: Configure Rclone for User `wufpbked`

Return to your server SSH terminal as **`root`**.

### 3.1 Create the configuration directory for `wufpbked`
```bash
mkdir -p /home/wufpbked/.config/rclone
```

### 3.2 Create and edit `rclone.conf`
```bash
nano /home/wufpbked/.config/rclone/rclone.conf
```
Paste the `[gdrive]` configuration block you copied from your local machine.  
*Press `Ctrl + O`, hit `Enter` to save, then `Ctrl + X` to exit.*

### 3.3 Set Strict Permissions and Ownership
Rclone will refuse to read config files that are accessible to other users:
```bash
chown -R wufpbked:wufpbked /home/wufpbked/.config
chmod 700 /home/wufpbked/.config/rclone
chmod 600 /home/wufpbked/.config/rclone/rclone.conf
```

### 3.4 Test Google Drive Access as User `wufpbked`
Switch to the user `wufpbked` to confirm they can access Google Drive:
```bash
su - wufpbked
rclone lsd gdrive:
```
*If you see your Google Drive folders listed without errors, authentication is complete!*

Return to the root terminal:
```bash
exit
```

---

## STAGE 4: Verify PHP `proc_open` in WHM

The `SyncBackupsToGoogleDrive` command uses Symfony's `Process` component to launch `rclone`, which requires PHP's `proc_open` function.

1. Log into **WHM** as `root` (`https://YOUR_SERVER_IP:2087`).
2. In the top-left search box, type **MultiPHP INI Editor** and open it.
3. Click the **Editor Mode** tab.
4. From the dropdown, select the PHP version your site uses (e.g., `ea-php82` or `ea-php83`).
5. Look for the `disable_functions` line:
   ```ini
   disable_functions = passthru,shell_exec,system
   ```
6. **Ensure that `proc_open` and `proc_close` are NOT listed in `disable_functions`**. If they are present, delete them and click **Save**.

---

## STAGE 5: Configure Laravel `.env`

Navigate to your application directory:
```bash
cd /home/wufpbked/new-dhs
nano .env
```

Add or verify the following configuration keys:

```env
# --------------------------------------------------------------------------
# Automated Backups & Rclone Google Drive Sync
# --------------------------------------------------------------------------
# Password used for AES-256 database archive encryption (MANDATORY)
BACKUP_ARCHIVE_PASSWORD=ChooseAStrongBackupPasswordHere2026!

# Admin email for failure notifications
BACKUP_NOTIFICATION_EMAIL=admin@yourinstitution.edu.ng

# Absolute path to the rclone binary on the server
RCLONE_BINARY=/usr/bin/rclone

# Remote name configured in rclone.conf (must include trailing colon)
BACKUP_RCLONE_REMOTE=gdrive:
```

### Clear and Cache Configuration
```bash
cd /home/wufpbked/new-dhs
php artisan config:clear
php artisan config:cache
```

---

## STAGE 6: Test the Backup & Sync Manually

Run the sync command directly as `wufpbked` (or via root inside the app folder):

```bash
cd /home/wufpbked/new-dhs
php artisan backup:sync-google
```

### What Happens During the Run:
1. Spatie dumps the MySQL database and creates an AES-256 encrypted zip file in:
   `/home/wufpbked/new-dhs/storage/app/DHS Wufpbk/database-YYYYmmdd-His.zip`
2. `rclone` copies the database zip to `gdrive:database/`.
3. `rclone` copies all user documents and images from `/home/wufpbked/new-dhs/storage/app/public/` to `gdrive:uploads/public/`.
4. If private Senate correction evidence exists in `/home/wufpbked/new-dhs/storage/app/senate-grade-corrections/`, it syncs to `gdrive:uploads/senate-grade-corrections/`.
5. The command finishes with:
   ```text
   Database archive and uploaded files were copied to the configured rclone remote.
   ```

Check your Google Drive in a web browser to verify the files and folders are present.

---

## STAGE 7: Configure Automated Daily Schedule (Cron Job)

Laravel handles the backup schedule internally via `app/Console/Kernel.php`:
* **01:00 AM:** `backup:sync-google` (Creates encrypted DB archive and uploads files to Google Drive)
* **02:00 AM:** `backup:clean` (Cleans up local database archives older than retention rules)
* **02:30 AM:** `backup:monitor` (Validates backup health and verifies the newest backup is < 24 hours old)

### Add the Laravel Scheduler to cPanel Cron Jobs
1. Log into **cPanel** as `wufpbked`.
2. Navigate to **Advanced > Cron Jobs**.
3. Under **Add New Cron Job**, set the interval to **Once Per Minute** (`* * * * *`).
4. Set the command to:
   ```bash
   cd /home/wufpbked/new-dhs && php artisan schedule:run >> /dev/null 2>&1
   ```
5. Click **Add New Cron Job**.

---

## STAGE 8: Retention Policy & Health Checks

### 8.1 Retention Policy (Configured in `config/backup.php`)
* **Daily backups:** Kept for 16 days
* **Weekly backups:** Kept for 8 weeks
* **Monthly backups:** Kept for 4 months
* **Yearly backups:** Kept for 2 years
* **Maximum storage cap:** 5,000 MB (oldest local archives automatically removed if limit is reached)

### 8.2 Check Backup Status at Any Time
From the command line:
```bash
cd /home/wufpbked/new-dhs
php artisan backup:list
```
*Outputs a status table with reachable disks, healthy flags, and last backup dates.*

---

## STAGE 9: Disaster Recovery (Restoration Procedure)

If disaster strikes (hardware failure, database corruption, or accidental deletion), follow this recovery sequence:

### 9.1 Download the Latest Database Archive
1. From Google Drive (or local `/home/wufpbked/new-dhs/storage/app/DHS Wufpbk/`), locate the latest `database-YYYYmmdd-His.zip`.
2. Extract the archive using the password defined in `BACKUP_ARCHIVE_PASSWORD`:
   ```bash
   unzip -P "YOUR_BACKUP_ARCHIVE_PASSWORD" database-YYYYmmdd-His.zip
   ```
3. This extracts the uncompressed SQL dump: `db-dumps/mysql-admission.sql`.

### 9.2 Restore the Database
1. Put the application in maintenance mode:
   ```bash
   cd /home/wufpbked/new-dhs
   php artisan down
   ```
2. Import the SQL dump into MySQL:
   ```bash
   mysql -u DB_USERNAME -p DB_DATABASE < db-dumps/mysql-admission.sql
   ```
3. Bring the application back online:
   ```bash
   php artisan up
   ```

### 9.3 Restore Uploaded Files (If Storage was Lost)
Use `rclone` to pull files back from Google Drive into storage:
```bash
/usr/bin/rclone copy gdrive:uploads/public /home/wufpbked/new-dhs/storage/app/public
php artisan storage:link
```

---

## STAGE 10: Troubleshooting Common Issues

| Issue / Error | Root Cause | Solution |
| :--- | :--- | :--- |
| `proc_open() has been disabled for security reasons` | WHM disabled `proc_open` in `php.ini` | In WHM > MultiPHP INI Editor, remove `proc_open` from `disable_functions`. |
| `rclone: command not found` | Binary missing or wrong path in `.env` | Ensure `/usr/bin/rclone` exists and `RCLONE_BINARY=/usr/bin/rclone` is in `.env`. |
| `Failed to load config file: permission denied` | Wrong permissions on `rclone.conf` | Run `chown -R wufpbked:wufpbked /home/wufpbked/.config` and `chmod 600 /home/wufpbked/.config/rclone/rclone.conf`. |
| `Token has been expired or revoked` | Google OAuth refresh token invalidated | Repeat Stage 2 on your local PC and update the `token = ...` line in `rclone.conf`. |
| `Set BACKUP_ARCHIVE_PASSWORD before creating...` | Missing password in `.env` | Set `BACKUP_ARCHIVE_PASSWORD=...` in `/home/wufpbked/new-dhs/.env` and run `php artisan config:cache`. |
| Backup tasks do not run at 01:00 AM | Cron not firing or missing password | Verify the cPanel cron job (`* * * * * cd /home/wufpbked/new-dhs && php artisan schedule:run`) is active and `BACKUP_ARCHIVE_PASSWORD` is non-empty. |
