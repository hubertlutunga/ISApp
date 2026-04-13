<?php

final class EventTableService
{
    public static function normalizeName(string $name): string
    {
        return preg_replace('/\s+/', ' ', trim($name)) ?? '';
    }

    public static function countByEvent(PDO $pdo, int $eventId): int
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tableevent WHERE cod_event = :codevent');
        $stmt->execute(['codevent' => $eventId]);

        return (int) $stmt->fetchColumn();
    }

    public static function findById(PDO $pdo, int $tableId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM tableevent WHERE cod_tab = ?');
        $stmt->execute([$tableId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findNameById(PDO $pdo, ?int $tableId): ?string
    {
        if (!$tableId) {
            return null;
        }

        $table = self::findById($pdo, $tableId);

        return $table['nom_tab'] ?? null;
    }

    public static function listByEvent(PDO $pdo, int $eventId, ?int $excludeTableId = null): array
    {
        $sql = 'SELECT * FROM tableevent WHERE cod_event = :cod_event';
        $params = [':cod_event' => $eventId];

        if ($excludeTableId !== null) {
            $sql .= ' AND cod_tab != :cod_tab';
            $params[':cod_tab'] = $excludeTableId;
        }

        $sql .= ' ORDER BY nom_tab ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function nameExists(PDO $pdo, string $name, int $eventId, ?int $excludeTableId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM tableevent WHERE nom_tab = :nom_tab AND cod_event = :cod_event';
        $params = [
            ':nom_tab' => $name,
            ':cod_event' => $eventId,
        ];

        if ($excludeTableId !== null) {
            $sql .= ' AND cod_tab != :cod_tab';
            $params[':cod_tab'] = $excludeTableId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function nameExistsNormalized(PDO $pdo, string $name, int $eventId, ?int $excludeTableId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM tableevent WHERE cod_event = :cod_event AND LOWER(TRIM(nom_tab)) = LOWER(TRIM(:nom_tab))';
        $params = [
            ':nom_tab' => self::normalizeName($name),
            ':cod_event' => $eventId,
        ];

        if ($excludeTableId !== null) {
            $sql .= ' AND cod_tab != :cod_tab';
            $params[':cod_tab'] = $excludeTableId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(PDO $pdo, string $name, string $planName, int $eventId): bool
    {
        $stmt = $pdo->prepare('INSERT INTO tableevent (nom_tab, plantable, cod_event) VALUES (:nom_tab, :plantable, :cod_event)');

        return $stmt->execute([
            ':nom_tab' => $name,
            ':plantable' => $planName,
            ':cod_event' => $eventId,
        ]);
    }

    public static function update(PDO $pdo, int $tableId, string $name, string $planName): void
    {
        $stmt = $pdo->prepare('UPDATE tableevent SET nom_tab = :nom_tab, plantable = :plantable WHERE cod_tab = :cod_tab');
        $stmt->execute([
            ':nom_tab' => $name,
            ':plantable' => $planName,
            ':cod_tab' => $tableId,
        ]);
    }

    public static function delete(PDO $pdo, int $tableId, int $eventId): int
    {
        $stmt = $pdo->prepare('DELETE FROM tableevent WHERE cod_tab = ? AND cod_event = ?');
        $stmt->execute([$tableId, $eventId]);

        return $stmt->rowCount();
    }
}