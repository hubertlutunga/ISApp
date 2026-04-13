<?php

final class MenuCatalogService
{
    public static function findById(PDO $pdo, int $menuId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM menuevent WHERE cod_mev = ? LIMIT 1');
        $stmt->execute([$menuId]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        return $menu ?: null;
    }

    public static function listCategoryIdsByEvent(PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare('SELECT DISTINCT TRIM(cat_menu) AS cat_menu FROM menuevent WHERE cod_event = ? ORDER BY cat_menu ASC');
        $stmt->execute([$eventId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public static function listCategories(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT * FROM categorie_menu ORDER BY nom ASC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findCategoryName(PDO $pdo, string $categoryId): string
    {
        $stmt = $pdo->prepare('SELECT nom FROM categorie_menu WHERE cod_cm = ?');
        $stmt->execute([$categoryId]);

        return (string) ($stmt->fetchColumn() ?: '');
    }

    public static function existsByCategoryAndName(PDO $pdo, int $eventId, string $categoryId, string $name): bool
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM menuevent WHERE cod_event = ? AND cat_menu = ? AND nom = ?');
        $stmt->execute([$eventId, $categoryId, $name]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(PDO $pdo, int $eventId, string $categoryId, string $name, string $description, string $hostUserId): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO menuevent (cat_menu, nom, prix, desc_menu, cod_event, date_enreg, hote)
             VALUES (?, ?, ?, ?, ?, NOW(), ?)'
        );
        $stmt->execute([$categoryId, $name, '0.00', $description, $eventId, $hostUserId]);
    }

    public static function update(PDO $pdo, int $menuId, int $eventId, string $categoryId, string $name, string $description): void
    {
        $stmt = $pdo->prepare(
            'UPDATE menuevent
             SET cat_menu = ?, nom = ?, desc_menu = ?
             WHERE cod_mev = ? AND cod_event = ?'
        );
        $stmt->execute([$categoryId, $name, $description, $menuId, $eventId]);
    }

    public static function delete(PDO $pdo, int $menuId, int $eventId): void
    {
        $stmt = $pdo->prepare('DELETE FROM menuevent WHERE cod_mev = ? AND cod_event = ?');
        $stmt->execute([$menuId, $eventId]);
    }

    public static function listByEvent(PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare(
            'SELECT m.*, c.nom AS categorie_nom
             FROM menuevent m
             LEFT JOIN categorie_menu c ON c.cod_cm = m.cat_menu
             WHERE m.cod_event = ?
             ORDER BY m.cod_mev DESC'
        );
        $stmt->execute([$eventId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function listByEventAndCategory(PDO $pdo, int $eventId, string $categoryId): array
    {
        $stmt = $pdo->prepare(
            'SELECT * FROM menuevent WHERE cod_event = ? AND cat_menu = ? ORDER BY cod_mev ASC'
        );
        $stmt->execute([$eventId, $categoryId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}