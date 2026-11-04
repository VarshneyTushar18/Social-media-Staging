<?php

namespace Modules\AppFiles\Support\Google;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleDriveUploader
{
    public function findOrCreateFolder(
        string $accessToken,
        string $name,
        ?string $parentId = null,
        ?string $sharedDriveId = null,
    ): string {
        $escapedName = str_replace("'", "\\'", $name);
        $query = "mimeType='application/vnd.google-apps.folder' and name='{$escapedName}' and trashed=false";

        if ($parentId) {
            $query .= " and '{$parentId}' in parents";
        }

        $listResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(30)
            ->get('https://www.googleapis.com/drive/v3/files', array_filter([
                'q' => $query,
                'fields' => 'files(id,name)',
                'pageSize' => 1,
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
                'corpora' => $sharedDriveId ? 'drive' : null,
                'driveId' => $sharedDriveId,
            ], fn ($value) => $value !== null && $value !== ''));

        if (! $listResponse->successful()) {
            $detail = trim((string) $listResponse->json('error.message', ''));

            throw new RuntimeException(
                $detail !== ''
                    ? 'Could not query Google Drive folders: '.$detail
                    : 'Could not query Google Drive folders.',
            );
        }

        $existingId = trim((string) data_get($listResponse->json(), 'files.0.id', ''));

        if ($existingId !== '') {
            return $existingId;
        }

        $metadata = [
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ];

        if ($parentId) {
            $metadata['parents'] = [$parentId];
        }

        $createResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(30)
            ->post('https://www.googleapis.com/drive/v3/files?'.http_build_query(array_filter([
                'supportsAllDrives' => 'true',
                'fields' => 'id',
            ])), $metadata);

        if (! $createResponse->successful()) {
            $detail = trim((string) $createResponse->json('error.message', ''));

            throw new RuntimeException(
                $detail !== ''
                    ? 'Could not create Google Drive folder: '.$detail
                    : 'Could not create Google Drive folder.',
            );
        }

        $folderId = trim((string) $createResponse->json('id', ''));

        if ($folderId === '') {
            throw new RuntimeException('Google Drive folder creation returned no id.');
        }

        return $folderId;
    }

    public function uploadFile(
        string $accessToken,
        string $parentFolderId,
        string $filename,
        string $mimeType,
        string $contents,
        ?string $sharedDriveId = null,
    ): string {
        $metadata = json_encode([
            'name' => $filename,
            'parents' => [$parentFolderId],
        ], JSON_THROW_ON_ERROR);

        $boundary = 'stackposts_'.bin2hex(random_bytes(8));
        $body = "--{$boundary}\r\n"
            ."Content-Type: application/json; charset=UTF-8\r\n\r\n"
            .$metadata."\r\n"
            ."--{$boundary}\r\n"
            ."Content-Type: {$mimeType}\r\n\r\n"
            .$contents."\r\n"
            ."--{$boundary}--";

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'multipart/related; boundary='.$boundary])
            ->timeout(120)
            ->withBody($body, 'multipart/related; boundary='.$boundary)
            ->post('https://www.googleapis.com/upload/drive/v3/files?'.http_build_query(array_filter([
                'uploadType' => 'multipart',
                'supportsAllDrives' => 'true',
                'fields' => 'id,name',
            ])));

        if (! $response->successful()) {
            $detail = trim((string) $response->json('error.message', ''));

            throw new RuntimeException(
                $detail !== ''
                    ? 'Could not upload file to Google Drive: '.$detail
                    : 'Could not upload file to Google Drive.',
            );
        }

        $fileId = trim((string) $response->json('id', ''));

        if ($fileId === '') {
            throw new RuntimeException('Google Drive upload returned no file id.');
        }

        return $fileId;
    }
}
