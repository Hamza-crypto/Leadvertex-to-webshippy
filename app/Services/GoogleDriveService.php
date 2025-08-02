<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveService
{
    protected $service;

    public function __construct()
    {
        $client = new Client();

        $client->setAuthConfig(storage_path('app/google/service-account.json'));
        $client->addScope(Drive::DRIVE);
        $client->useApplicationDefaultCredentials();

        $this->service = new Drive($client);
    }


    public function uploadFile($fullPath, $fileName, $folderId = null)
    {
        try {
            if (!file_exists($fullPath)) {
                throw new \Exception("File not found: " . $fullPath);
            }

            $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
            $content = file_get_contents($fullPath);

            // Step 1: Check if file with same name exists in the folder
            $query = sprintf(
                "name='%s' and mimeType!='application/vnd.google-apps.folder' and trashed=false and '%s' in parents",
                addslashes($fileName),
                $folderId
            );

            $existingFiles = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ]);

            if (count($existingFiles->getFiles()) > 0) {
                // File exists → Update it
                $existingFileId = $existingFiles->getFiles()[0]->getId();

                $updated = $this->service->files->update(
                    $existingFileId,
                    new DriveFile(),
                    [
                        'data' => $content,
                        'mimeType' => $mimeType,
                        'uploadType' => 'media',
                        'supportsAllDrives' => true,
                    ]
                );

                return [
                    'id' => $updated->id,
                    'name' => $fileName,
                    'updated' => true,
                ];
            }

            // File doesn't exist → Create new
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => $folderId ? [$folderId] : [],
            ]);

            $created = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'supportsAllDrives' => true,
                'fields' => 'id',
            ]);

            return [
                'id' => $created->id,
                'name' => $fileName,
                'created' => true,
            ];

        } catch (\Exception $e) {
            \Log::error("Google Drive upload failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function getOrCreateFolder($folderName, $parentFolderId = null)
    {
        $query = sprintf("mimeType='application/vnd.google-apps.folder' and name='%s'", $folderName);
        if ($parentFolderId) {
            $query .= " and '$parentFolderId' in parents";
        }

        $results = $this->service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        if (count($results->getFiles()) > 0) {
            return $results->getFiles()[0]->getId();
        }

        $fileMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => $parentFolderId ? [$parentFolderId] : [],
        ]);

        $folder = $this->service->files->create($fileMetadata, [
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);

        return $folder->id;
    }

}
