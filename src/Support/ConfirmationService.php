<?php

final class ConfirmationService
{
    public static function countSummary(PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN presence = 'oui' THEN 1 ELSE 0 END) AS total_oui,
                SUM(CASE WHEN presence = 'non' THEN 1 ELSE 0 END) AS total_non,
                SUM(CASE WHEN presence = 'plustard' THEN 1 ELSE 0 END) AS total_plustard
            FROM confirmation
            WHERE cod_mar = ?"
        );
        $stmt->execute([$eventId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'oui' => (int) ($row['total_oui'] ?? 0),
            'non' => (int) ($row['total_non'] ?? 0),
            'plustard' => (int) ($row['total_plustard'] ?? 0),
        ];
    }

    public static function countByPresence(PDO $pdo, int $eventId, ?string $presence = null): int
    {
        $query = 'SELECT COUNT(*) FROM confirmation WHERE cod_mar = ?';
        $params = [$eventId];

        if ($presence !== null) {
            $query .= ' AND presence = ?';
            $params[] = $presence;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public static function listByEvent(PDO $pdo, int $eventId, ?string $presence = null): array
    {
        $query = '
            SELECT
                c.cod_conf,
                c.noms,
                c.presence,
                c.note,
                c.phone,
                c.date_enreg,
                GROUP_CONCAT(pr.nom ORDER BY mr.cod_mr SEPARATOR "||") AS meal_names
            FROM confirmation c
            LEFT JOIN menurecolte mr ON mr.cod_conf = c.cod_conf
            LEFT JOIN preference_repas pr ON pr.cod_pr = mr.cod_repas
            WHERE c.cod_mar = ?
        ';
        $params = [$eventId];

        if ($presence !== null) {
            $query .= ' AND c.presence = ?';
            $params[] = $presence;
        }

        $query .= '
            GROUP BY c.cod_conf, c.noms, c.presence, c.note, c.phone, c.date_enreg
            ORDER BY c.cod_conf DESC
        ';

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $meals = [];
            if (!empty($row['meal_names'])) {
                $meals = array_values(array_filter(explode('||', (string) $row['meal_names']), static function ($value) {
                    return $value !== '';
                }));
            }

            $row['meal_names'] = $meals;
            $rows[] = $row;
        }

        return $rows;
    }
}