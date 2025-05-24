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
        
        $this->service = new Drive($client);
    }


    public function uploadFile($storagePath, $fileName, $folderId = null)
{
    try {

        $fullPath = storage_path('app/' . $storagePath);

        if (!file_exists($fullPath)) {
            throw new \Exception("File not found: " . $storagePath);
        }

        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => $folderId ? [$folderId] : []
        ]);

        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw new \Exception("Failed to read file contents");
        }

        $mimeType = mime_content_type($fullPath) ?: 'text/html';

        $file = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id,name,webViewLink'
        ]);

        return [
            'id' => $file->id,
            'name' => $file->name,
            'url' => $file->webViewLink ?? null
        ];

    } catch (\Exception $e) {
        \Log::error("Google Drive upload failed: " . $e->getMessage());
        throw $e; 
    }
}

}