<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use Throwable;

class SyncBackupsToGoogleDrive extends Command
{
    protected $signature = 'backup:sync-google';

    protected $description = 'Create an encrypted database backup and upload it with user uploads to Google Drive using rclone';

    public function handle(): int
    {
        $remote = trim((string) config('backup.rclone.remote'));
        $binary = trim((string) config('backup.rclone.binary', 'rclone'));
        $password = config('backup.backup.password');

        if (blank($password)) {
            $this->error('Set BACKUP_ARCHIVE_PASSWORD before creating an encrypted database backup.');

            return self::FAILURE;
        }

        $timestamp = now()->format('Ymd-His');
        $filename = "database-{$timestamp}.zip";
        $archivePath = null;

        try {
            $exitCode = Artisan::call('backup:run', [
                '--only-db' => true,
                '--only-to-disk' => 'local',
                '--filename' => $filename,
            ]);

            $rawOutput = Artisan::output();
            if ($rawOutput !== '') {
                $this->output->write($rawOutput);
            }

            if ($exitCode !== self::SUCCESS) {
                $cleanOutput = trim(preg_replace('/\x1b\[[0-9;]*[a-zA-Z]/', '', $rawOutput) ?? $rawOutput);
                $errorReason = $cleanOutput !== ''
                    ? "Database backup failed (exit code {$exitCode}):\n{$cleanOutput}"
                    : 'The encrypted database archive was not created at the expected path.';

                throw new \RuntimeException($errorReason);
            }

            $archivePath = $this->findArchive($filename);

            if ($archivePath === null) {
                throw new \RuntimeException("The encrypted database archive could not be found in the local backup directory ({$filename}).");
            }

            if ($remote === '' || $binary === '') {
                throw new \RuntimeException('Set BACKUP_RCLONE_REMOTE and RCLONE_BINARY to enable Google Drive uploads. The local database archive has been kept.');
            }

            $rclone = new Process([$binary, 'version']);
            $rclone->run();

            if (!$rclone->isSuccessful()) {
                $rcloneError = trim($rclone->getErrorOutput() ?: $rclone->getOutput());
                $details = $rcloneError !== '' ? " ({$rcloneError})" : '';
                throw new \RuntimeException("rclone is unavailable to the PHP CLI user. Check RCLONE_BINARY and its permissions{$details}. The local database archive has been kept.");
            }

            $this->copyToRemote($binary, $archivePath, $this->remotePath($remote, 'database'));
            $this->copyToRemote($binary, storage_path('app/public'), $this->remotePath($remote, 'uploads/public'));

            $senateEvidencePath = storage_path('app/senate-grade-corrections');
            if (is_dir($senateEvidencePath)) {
                $this->copyToRemote($binary, $senateEvidencePath, $this->remotePath($remote, 'uploads/senate-grade-corrections'));
            } else {
                $this->line('No Senate correction evidence folder exists yet; skipping it.');
            }

            $this->info('Database archive and uploaded files were copied to the configured rclone remote.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Google Drive backup sync failed.', [
                'message' => $exception->getMessage(),
                'archive' => $archivePath,
            ]);

            $this->notifyFailure($exception);

            $this->error('Backup sync failed: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }

    private function findArchive(string $filename): ?string
    {
        $backupName = config('backup.backup.name', 'laravel-backup');
        $directory = storage_path('app/' . $backupName);

        if (!is_dir($directory)) {
            $directory = storage_path('app');
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->isFile() && $file->getFilename() === $filename) {
                return $file->getPathname();
            }
        }

        return null;
    }

    private function notifyFailure(Throwable $exception): void
    {
        $recipient = config('backup.notifications.mail.to');

        if (blank($recipient)) {
            return;
        }

        try {
            Mail::raw(
                'The scheduled database and uploads backup did not finish syncing to Google Drive. '
                    . 'The local database archive and uploads have been kept. Error: '
                    . $exception->getMessage(),
                fn ($message) => $message->to($recipient)->subject('Admission system backup sync failed')
            );
        } catch (Throwable $mailException) {
            Log::error('Could not send the backup failure email.', ['message' => $mailException->getMessage()]);
        }
    }

    private function copyToRemote(string $binary, string $source, string $destination): void
    {
        if (!is_dir($source) && !is_file($source)) {
            throw new \RuntimeException("Backup source does not exist: {$source}");
        }

        $process = new Process([
            $binary,
            'copy',
            $source,
            $destination,
            '--transfers=2',
            '--checkers=4',
            '--drive-chunk-size=16M',
        ]);
        $process->setTimeout(null);
        $process->run(function (string $type, string $output): void {
            $this->output->write($output);
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()) ?: 'rclone copy failed.');
        }
    }

    private function remotePath(string $remote, string $path): string
    {
        $remote = rtrim($remote, '/');

        return (str_ends_with($remote, ':') ? $remote : $remote . '/') . $path;
    }
}
