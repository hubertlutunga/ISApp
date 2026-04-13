<?php

final class EventMediaService
{
    public static function storeEventPhotos(PDO $pdo, int $eventId, ?array $photos, string $photoTargetDir, string $prefix): void
    {
        if (!$photos || !isset($photos['tmp_name']) || !is_array($photos['tmp_name'])) {
            return;
        }

        $insert = $pdo->prepare('INSERT INTO photos_event (cod_event, nom_photo) VALUES (?, ?)');

        foreach ($photos['tmp_name'] as $key => $tmpName) {
            if (!is_uploaded_file($tmpName) || (($photos['error'][$key] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)) {
                continue;
            }

            $extension = pathinfo((string) ($photos['name'][$key] ?? ''), PATHINFO_EXTENSION);
            $baseName = pathinfo((string) ($photos['name'][$key] ?? ''), PATHINFO_FILENAME);
            $sanitizedBaseName = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $baseName) ?: 'photo';
            $fileName = $prefix . time() . '_' . $key . '_' . $sanitizedBaseName . ($extension !== '' ? '.' . $extension : '');
            $targetPath = rtrim($photoTargetDir, '/') . '/' . $fileName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $insert->execute([$eventId, $fileName]);
            }
        }
    }

    public static function storeUploadedImage(array $file, string $targetDir, string $prefix = null, ?int $maxBytes = null): ?string
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Probleme de telechargement de photo');
        }

        if ($maxBytes !== null && (int) ($file['size'] ?? 0) > $maxBytes) {
            throw new RuntimeException('Fichier trop volumineux');
        }

        $extension = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, ['gif', 'jpg', 'jpeg', 'png'], true)) {
            throw new RuntimeException('Type de fichier non autorise');
        }

        $prefix = $prefix ?? (rand(100, 999) . '_IS_');
        $baseName = basename((string) ($file['name'] ?? 'image'));
        $fileName = $prefix . $baseName;
        $targetPath = rtrim($targetDir, '/') . '/' . $fileName;

        if (!move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Impossible d\'enregistrer le fichier');
        }

        return $fileName;
    }

    public static function storeCompressedJpeg(array $file, string $targetDir, string $prefix = 'photo_is_', int $quality = 75): ?string
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Probleme de telechargement de photo');
        }

        $sourcePath = (string) ($file['tmp_name'] ?? '');
        $extension = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        if (!in_array($extension, ['gif', 'jpg', 'jpeg', 'png'], true)) {
            throw new RuntimeException('Type de fichier non autorise');
        }

        if ($extension === 'jpg' || $extension === 'jpeg') {
            $sourceImage = @imagecreatefromjpeg($sourcePath);
        } elseif ($extension === 'png') {
            $sourceImage = @imagecreatefrompng($sourcePath);
        } else {
            $sourceImage = @imagecreatefromgif($sourcePath);
        }

        if (!$sourceImage) {
            throw new RuntimeException('Impossible de traiter l\'image envoyee');
        }

        $fileName = $prefix . uniqid('', true) . '.jpg';
        $targetPath = rtrim($targetDir, '/') . '/' . $fileName;

        $written = imagejpeg($sourceImage, $targetPath, $quality);
        imagedestroy($sourceImage);

        if (!$written) {
            throw new RuntimeException('Impossible d\'enregistrer l\'image compressee');
        }

        return $fileName;
    }

    public static function updateEventFields(PDO $pdo, int $eventId, array $fields): void
    {
        if ($eventId <= 0 || $fields === []) {
            return;
        }

        $assignments = [];
        $values = [];

        foreach ($fields as $column => $value) {
            $assignments[] = $column . ' = ?';
            $values[] = $value;
        }

        $values[] = $eventId;

        $stmt = $pdo->prepare('UPDATE events SET ' . implode(', ', $assignments) . ' WHERE cod_event = ?');
        $stmt->execute($values);
    }

    public static function upsertWebsiteGeneralText(PDO $pdo, int $eventId, string $column, ?string $value): void
    {
        if ($eventId <= 0) {
            return;
        }

        $allowedColumns = ['text_sdd'];
        if (!in_array($column, $allowedColumns, true)) {
            throw new InvalidArgumentException('Colonne websitewedgeneral non autorisee');
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM websitewedgeneral WHERE cod_event = ?');
        $stmt->execute([$eventId]);
        $exists = (bool) $stmt->fetchColumn();

        if ($exists) {
            $sql = 'UPDATE websitewedgeneral SET ' . $column . ' = :value WHERE cod_event = :cod_event';
        } else {
            $sql = 'INSERT INTO websitewedgeneral (cod_event, ' . $column . ') VALUES (:cod_event, :value)';
        }

        $write = $pdo->prepare($sql);
        $write->bindValue(':cod_event', $eventId, PDO::PARAM_INT);
        $write->bindValue(':value', $value);
        $write->execute();
    }
}