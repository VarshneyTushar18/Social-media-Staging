<?php

namespace Modules\AppFiles\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppFiles\Support\Google\PublishDriveOAuthService;
use RuntimeException;
use Throwable;

class PublishDriveBackupDiagnostics
{
    /**
     * @return array{ok: bool, checks: list<array{label: string, status: string, detail: string}>}
     */
    public static function run(?string $clientSecretOverride = null): array
    {
        $options = app(OptionStore::class);
        $previousSecret = (string) $options->get('appfiles_publish_drive_oauth_client_secret', '');
        $checks = [];

        if ($clientSecretOverride !== null && trim($clientSecretOverride) !== '') {
            $options->set('appfiles_publish_drive_oauth_client_secret', trim($clientSecretOverride));
        }

        try {
            $oauth = app(PublishDriveOAuthService::class);

            $archiveOn = (string) $options->get('appfiles_publish_drive_archive', '0') === '1';
            $checks[] = [
                'label' => 'Backup enabled',
                'status' => $archiveOn ? 'ok' : 'warn',
                'detail' => $archiveOn ? __('Yes') : __('Turn on “Back up published media” and save.'),
            ];

            $clientId = $oauth->clientId();
            $checks[] = [
                'label' => 'OAuth Client ID',
                'status' => $clientId !== '' ? 'ok' : 'fail',
                'detail' => $clientId !== '' ? __('Set in File Manager (Google Drive).') : __('Missing Client ID.'),
            ];

            $secret = $oauth->clientSecret();
            $checks[] = [
                'label' => 'Web client secret',
                'status' => $secret !== '' ? 'ok' : 'fail',
                'detail' => $secret !== '' ? __('Present in settings.') : __('Paste OAuth Web client secret (not Browser API key).'),
            ];

            $connected = $oauth->hasRefreshToken();
            $checks[] = [
                'label' => 'Google account connected',
                'status' => $connected ? 'ok' : 'fail',
                'detail' => $connected
                    ? ($oauth->connectedEmail() !== '' ? $oauth->connectedEmail() : __('Refresh token stored.'))
                    : __('Use Connect Google account in File Manager.'),
            ];

            $parentId = trim((string) $options->get('appfiles_publish_drive_parent_folder_id', ''));
            $checks[] = [
                'label' => 'Parent folder ID',
                'status' => $parentId !== '' ? 'ok' : 'fail',
                'detail' => $parentId !== '' ? $parentId : __('Required when backup is enabled.'),
            ];

            $queue = (string) config('queue.default', 'sync');
            $pendingJobs = Schema::hasTable('jobs') ? (int) DB::table('jobs')->count() : null;
            $queueOk = $queue === 'sync' || ($pendingJobs !== null && $pendingJobs < 50);
            $checks[] = [
                'label' => 'Queue',
                'status' => $queue === 'sync' ? 'ok' : ($queueOk ? 'warn' : 'fail'),
                'detail' => $queue === 'sync'
                    ? __('QUEUE_CONNECTION=sync — backup runs right after publish.')
                    : sprintf(
                        __('QUEUE_CONNECTION=%s — run php artisan queue:work. Pending jobs: %s'),
                        $queue,
                        $pendingJobs !== null ? (string) $pendingJobs : '?'
                    ),
            ];

            if (! $oauth->isReady()) {
                throw new RuntimeException(__('Connect Google and set Client ID + Web client secret first.'));
            }

            $token = $oauth->getAccessToken();
            $checks[] = [
                'label' => 'OAuth access token',
                'status' => 'ok',
                'detail' => __('Refresh token and client secret are valid.'),
            ];

            if ($parentId !== '') {
                $response = Http::withToken($token)
                    ->acceptJson()
                    ->timeout(20)
                    ->get('https://www.googleapis.com/drive/v3/files/'.$parentId, [
                        'fields' => 'id,name,mimeType,trashed',
                        'supportsAllDrives' => 'true',
                    ]);

                if (! $response->successful()) {
                    $message = trim((string) $response->json('error.message', ''));

                    throw new RuntimeException(
                        $message !== ''
                            ? __('Cannot access parent folder: :message', ['message' => $message])
                            : __('Cannot access parent folder in Drive.'),
                    );
                }

                if ((bool) data_get($response->json(), 'trashed', false)) {
                    throw new RuntimeException(__('Parent folder is in Google Drive trash.'));
                }

                $checks[] = [
                    'label' => 'Parent folder in Drive',
                    'status' => 'ok',
                    'detail' => (string) data_get($response->json(), 'name', $parentId),
                ];
            }

            return ['ok' => true, 'checks' => $checks];
        } catch (Throwable $exception) {
            $checks[] = [
                'label' => 'Error',
                'status' => 'fail',
                'detail' => $exception->getMessage(),
            ];

            return ['ok' => false, 'checks' => $checks];
        } finally {
            $options->set('appfiles_publish_drive_oauth_client_secret', $previousSecret);
        }
    }
}
