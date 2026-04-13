<?php

final class EventDocumentService
{
    public static function storeUploadedFiles(?array $files, string $targetDir): array
    {
        if (!$files || empty($files['name'][0])) {
            return [];
        }

        $storedFiles = [];

        foreach ($files['tmp_name'] as $key => $tmpName) {
            $originalName = basename((string) ($files['name'][$key] ?? ''));
            if ($originalName === '') {
                continue;
            }

            $targetPath = rtrim($targetDir, '/') . '/' . $originalName;
            if (!move_uploaded_file($tmpName, $targetPath)) {
                throw new RuntimeException('Echec du telechargement');
            }

            $storedFiles[] = $originalName;
        }

        return $storedFiles;
    }
}