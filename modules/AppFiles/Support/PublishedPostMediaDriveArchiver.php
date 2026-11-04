<?php

namespace Modules\AppFiles\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\AdminSettings\Support\OptionStore;
use Modules\AppChannels\Models\SocialAccount;
use Modules\AppFiles\Models\AppFile;
use Modules\AppFiles\Support\Google\GoogleDriveAccess;
use Modules\AppFiles\Support\Google\GoogleDriveUploader;
use Modules\AppPublishing\Models\PublishingPost;
use Modules\AppPublishing\Support\PublishingMediaUrlResolver;
use RuntimeException;
use Throwable;

class PublishedPostMediaDriveArchiver
{
    public function __construct(
        protected OptionStore $options,
        protected PublishingMediaUrlResolver $mediaResolver,
        protected GoogleDriveUploader $driveUploader,
        protected GoogleDriveAccess $driveAccess,
    ) {}

    public function enabled(): bool
    {
        return (string) $this->options->get('appfiles_publish_drive_archive', '0') === '1'
            && $this->driveAccess->canAuthenticate();
    }

    public function archive(PublishingPost $post): void
    {
        if (! $this->enabled()) {
            return;
        }

        if ((int) $post->status !== PublishingPost::STATUS_PUBLISHED) {
            return;
        }

        $result = is_array($post->result) ? $post->result : [];

        if (filled(data_get($result, 'drive_archive.completed_at'))) {
            return;
        }

        $account = $post->account;

        if (! $account) {
            return;
        }

        $postData = is_array($post->data) ? $post->data : [];
        $mediaItems = (array) ($postData['medias'] ?? []);
        $files = $this->publishableMediaFiles($mediaItems);

        if ($files->isEmpty()) {
            return;
        }

        $parentFolderId = trim((string) $this->options->get('appfiles_publish_drive_parent_folder_id', ''));
        $parentFolderId = $parentFolderId !== '' ? $parentFolderId : null;

        if ($parentFolderId === null) {
            return;
        }

        try {
            $accessToken = $this->driveAccess->getAccessToken();

            $accountFolderId = $this->driveUploader->findOrCreateFolder(
                $accessToken,
                $this->accountFolderName($account),
                $parentFolderId,
                null,
            );

            $dateFolderName = $this->publishDateFolderName($post);

            $dateFolderId = $this->driveUploader->findOrCreateFolder(
                $accessToken,
                $dateFolderName,
                $accountFolderId,
                null,
            );

            $uploaded = [];

            foreach ($files as $file) {
                $uploaded[] = $this->uploadAppFile(
                    $accessToken,
                    $dateFolderId,
                    $file,
                    $post,
                    null,
                );
            }

            $result['drive_archive'] = [
                'completed_at' => now()->toIso8601String(),
                'account_folder' => $this->accountFolderName($account),
                'date_folder' => $dateFolderName,
                'auth' => 'oauth',
                'uploaded' => $uploaded,
            ];

            $post->forceFill(['result' => $result])->save();
        } catch (Throwable $exception) {
            logger()->warning('appfiles.publish_drive_archive_failed', [
                'post_id' => $post->id,
                'error' => $exception->getMessage(),
            ]);

            $result['drive_archive'] = [
                'failed_at' => now()->toIso8601String(),
                'error' => $exception->getMessage(),
            ];

            $post->forceFill(['result' => $result])->save();
        }
    }

    /**
     * @return array<string, string>
     */
    protected function uploadAppFile(
        string $accessToken,
        string $parentFolderId,
        AppFile $file,
        PublishingPost $post,
        ?string $sharedDriveId,
    ): array {
        $path = trim((string) $file->path);
        $disk = (string) $file->disk;

        if ($path === '' || ! Storage::disk($disk)->exists($path)) {
            throw new RuntimeException('Published media file is missing from storage.');
        }

        $contents = Storage::disk($disk)->get($path);
        $mimeType = trim((string) ($file->mime_type ?: 'application/octet-stream'));
        $filename = $this->sanitizeFileName((string) ($file->name ?: basename($path)));
        $filename = 'post-'.$post->id.'-'.$filename;

        $driveFileId = $this->driveUploader->uploadFile(
            $accessToken,
            $parentFolderId,
            $filename,
            $mimeType,
            $contents,
            $sharedDriveId,
        );

        return [
            'file_id' => (string) $file->id,
            'drive_file_id' => $driveFileId,
            'name' => $filename,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $mediaItems
     */
    protected function publishableMediaFiles(array $mediaItems): Collection
    {
        return $this->mediaResolver
            ->resolveFiles($mediaItems)
            ->filter(fn (AppFile $file): bool => $this->isPublishableMedia($file))
            ->values();
    }

    protected function isPublishableMedia(AppFile $file): bool
    {
        if ($file->is_folder) {
            return false;
        }

        if ($file->is_image) {
            return true;
        }

        return str_starts_with(strtolower((string) $file->mime_type), 'video/');
    }

    protected function accountFolderName(SocialAccount $account): string
    {
        $providerLabel = $this->providerLabel((string) $account->provider_key);
        $accountName = trim((string) ($account->display_name ?: $account->username ?: $account->external_id ?: 'Account'));

        return $this->sanitizeFolderName($providerLabel.' - '.$accountName);
    }

    protected function publishDateFolderName(PublishingPost $post): string
    {
        $timezone = config('app.timezone') ?: 'UTC';
        $timestamp = (int) ($post->time_post ?: 0);

        $date = $timestamp > 0
            ? Carbon::createFromTimestamp($timestamp, $timezone)
            : now($timezone);

        return $date->format('Y-m-d');
    }

    protected function providerLabel(string $providerKey): string
    {
        $providers = function_exists('integration_items') ? integration_items() : [];
        $label = trim((string) data_get($providers, "{$providerKey}.label", ''));

        if ($label !== '') {
            return $label;
        }

        return Str::headline(str_replace('_', ' ', $providerKey));
    }

    protected function sanitizeFolderName(string $name): string
    {
        $name = preg_replace('/[\\\\\\/:*?"<>|]+/', '-', $name) ?? $name;
        $name = trim($name, " \t\n\r\0\x0B.");

        return $name !== '' ? Str::limit($name, 120, '') : 'Account';
    }

    protected function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[\\\\\\/:*?"<>|]+/', '-', $name) ?? $name;
        $name = trim($name);

        return $name !== '' ? Str::limit($name, 180, '') : 'media.bin';
    }
}
