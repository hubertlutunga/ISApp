<?php

final class EventPrintService
{
    public static function listFilesByEvent(PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare('SELECT nom_fichier FROM fichiers_impression WHERE cod_event = ?');
        $stmt->execute([$eventId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function finalizeCreation(PDO $pdo, int $eventId, string $observation, int $userId, ?array $files, string $targetDir): bool
    {
        $stmtInsert = $pdo->prepare('INSERT INTO creaevent (cod_event, observation, date_enreg, cod_user) VALUES (?, ?, NOW(), ?)');
        $stmtInsert->execute([$eventId, $observation, $userId]);

        $creationId = (int) $pdo->lastInsertId();

        $stmtUpdate = $pdo->prepare('UPDATE events SET crea = ? WHERE cod_event = ?');
        $stmtUpdate->execute(['2', $eventId]);

        if (!$files || empty($files['name'][0])) {
            return true;
        }

        $stmtFile = $pdo->prepare('INSERT INTO fichiers_impression (cod_ce, cod_event, nom_fichier) VALUES (?, ?, ?)');

        foreach ($files['tmp_name'] as $key => $tmpName) {
            $originalName = basename((string) ($files['name'][$key] ?? ''));
            if ($originalName === '') {
                continue;
            }

            $targetPath = rtrim($targetDir, '/') . '/' . $originalName;
            if (!move_uploaded_file($tmpName, $targetPath)) {
                return false;
            }

            if (!$stmtFile->execute([$creationId, $eventId, $originalName])) {
                return false;
            }
        }

        return true;
    }
}