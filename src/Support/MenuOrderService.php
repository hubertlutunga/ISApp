<?php

final class MenuOrderService
{
    public static function countByEvent(PDO $pdo, int $eventId): int
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM commandemenu WHERE codevent = ?');
        $stmt->execute([$eventId]);

        return (int) $stmt->fetchColumn();
    }

    public static function listByEvent(PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare(
            'SELECT
                cm.codinv,
                cm.codtable,
                cm.codmenu,
                cm.codevent,
                cm.date_enreg,
                i.nom AS invite_nom,
                t.nom_tab AS table_nom,
                m.nom AS menu_nom
            FROM commandemenu cm
            LEFT JOIN invite i ON i.id_inv = cm.codinv
            LEFT JOIN tableevent t ON t.cod_tab = cm.codtable
            LEFT JOIN menuevent m ON m.cod_mev = cm.codmenu
            WHERE cm.codevent = ?
            ORDER BY cm.date_enreg DESC'
        );
        $stmt->execute([$eventId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}