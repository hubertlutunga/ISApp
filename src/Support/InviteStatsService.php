<?php

final class InviteStatsService
{
    public static function weightedCount(PDO $pdo, int $eventId, ?string $access = null, bool $accessIsNull = false): int
    {
        $query = "
            SELECT SUM(CASE
                WHEN sing = 'C' THEN 2
                WHEN sing = 'Mr' THEN 1
                WHEN sing = 'Mme' THEN 1
                WHEN sing = 'S' THEN 1
                WHEN sing IS NULL THEN 1
                ELSE 0
            END) as total_inv
            FROM invite
            WHERE cod_mar = ?
        ";
        $params = [$eventId];

        if ($accessIsNull) {
            $query .= ' AND acces IS NULL';
        } elseif ($access !== null) {
            $query .= ' AND acces = ?';
            $params[] = $access;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return (int) ($stmt->fetchColumn() ?: 0);
    }
}