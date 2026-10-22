<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SyncBackupsToGoogleDriveTest extends TestCase
{
    public function test_fails_when_backup_password_is_not_set(): void
    {
        Config::set('backup.backup.password', null);

        $this->artisan('backup:sync-google')
            ->expectsOutput('Set BACKUP_ARCHIVE_PASSWORD before creating an encrypted database backup.')
            ->assertExitCode(1);
    }
}
