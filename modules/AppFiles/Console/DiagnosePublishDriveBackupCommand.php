<?php

namespace Modules\AppFiles\Console;

use Illuminate\Console\Command;
use Modules\AppFiles\Support\PublishDriveBackupDiagnostics;

class DiagnosePublishDriveBackupCommand extends Command
{
    protected $signature = 'appfiles:diagnose-publish-drive {--secret= : Optional Web client secret to test before saving}';

    protected $description = 'Check Google Drive publish backup configuration (OAuth, folder, queue).';

    public function handle(): int
    {
        $secret = $this->option('secret');
        $result = PublishDriveBackupDiagnostics::run(is_string($secret) && $secret !== '' ? $secret : null);

        foreach ($result['checks'] as $check) {
            $icon = match ($check['status']) {
                'ok' => '<fg=green>OK</>',
                'warn' => '<fg=yellow>WARN</>',
                default => '<fg=red>FAIL</>',
            };

            $this->line(sprintf(' %s  %s — %s', $icon, $check['label'], $check['detail']));
        }

        if ($result['ok']) {
            $this->newLine();
            $this->info('Drive backup looks ready. Publish a post with an image from Files to verify uploads.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->error('Drive backup is not fully working. Fix FAIL items above (invalid client secret → paste correct secret, Save, reconnect Google if needed).');

        return self::FAILURE;
    }
}
