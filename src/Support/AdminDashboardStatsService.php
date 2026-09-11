<?php

final class AdminDashboardStatsService
{
    public static function build(PDO $pdo, array $session): array
    {
        $eventStats = self::eventStats($pdo);
        $invoiceStats = self::invoiceStats($pdo);
        $digitalStats = self::digitalStats($pdo);

        $stats = [
            'datanbevent' => (int) $eventStats['datanbevent'],
            'datarealise' => (int) $eventStats['datarealise'],
            'dataincomple' => (int) $eventStats['dataincomple'],
            'dataattente' => (int) $eventStats['dataattente'],
            'finanneemp' => self::formatMoneyValue((float) $invoiceStats['finanneemp']),
            'finannee' => self::formatMoneyValue((float) $invoiceStats['finannee']),
            'comannee' => self::formatCountLabel((int) $eventStats['comannee'], 'evenement', 'evenements'),
            'comtermannee' => self::formatCountLabel((int) $eventStats['comtermannee'], 'terminé', 'terminés'),
            'finanneedigital' => self::formatMoneyValue((float) $digitalStats['finanneedigital']),
            'finmoismp' => self::formatMoneyValue((float) $invoiceStats['finmoismp']),
            'finmois' => self::formatMoneyValue((float) $invoiceStats['finmois']),
            'commois' => self::formatCountLabel((int) $eventStats['commois'], 'evenement', 'evenements'),
            'comtermmois' => self::formatCountLabel((int) $eventStats['comtermmois'], 'terminé', 'terminés'),
            'finmoisdigital' => self::formatMoneyValue((float) $digitalStats['finmoisdigital']),
            'finjourmp' => self::formatMoneyValue((float) $invoiceStats['finjourmp']),
            'finjour' => self::formatMoneyValue((float) $invoiceStats['finjour']),
            'comjour' => self::formatCountLabel((int) $eventStats['comjour'], 'evenement', 'evenements'),
            'comtermjour' => self::formatCountLabel((int) $eventStats['comtermjour'], 'terminé', 'terminés'),
            'finjourdigital' => self::formatMoneyValue((float) $digitalStats['finjourdigital']),
            'fintotalmp' => self::formatMoneyValue((float) $invoiceStats['fintotalmp']),
            'fintotal' => self::formatMoneyValue((float) $invoiceStats['fintotal']),
            'comtotal' => self::formatCountLabel((int) $eventStats['datanbevent'], 'evenement', 'evenements'),
            'comtermtotal' => self::formatCountLabel((int) $eventStats['comtermtotal'], 'terminé', 'terminés'),
            'fintotaldigital' => self::formatMoneyValue((float) $digitalStats['fintotaldigital']),
            'finance' => (($session['type_user'] ?? null) == '3') ? 'display:none;' : '',
        ];

        $stats['restannee'] = self::formatMoneyValue((float) $stats['finannee'] - (float) $stats['finanneemp']);
        $stats['restmois'] = self::formatMoneyValue((float) $stats['finmois'] - (float) $stats['finmoismp']);
        $stats['restjour'] = self::formatMoneyValue((float) $stats['finjour'] - (float) $stats['finjourmp']);
        $stats['resttotal'] = self::formatMoneyValue((float) $stats['fintotal'] - (float) $stats['fintotalmp']);

        return $stats;
    }

    private static function eventStats(PDO $pdo): array
    {
        $row = $pdo->query(
            'SELECT
                COUNT(*) AS datanbevent,
                COALESCE(SUM(CASE WHEN fact IS NOT NULL AND crea = "2" THEN 1 ELSE 0 END), 0) AS datarealise,
                COALESCE(SUM(CASE WHEN fact IS NULL THEN 1 ELSE 0 END), 0) AS dataincomple,
                COALESCE(SUM(CASE WHEN fact IS NOT NULL AND (crea IS NULL OR crea = "1") THEN 1 ELSE 0 END), 0) AS dataattente,
                COALESCE(SUM(CASE WHEN fact IS NOT NULL AND date_enreg >= DATE_FORMAT(CURRENT_DATE(), "%Y-01-01 00:00:00") AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 YEAR), "%Y-01-01 00:00:00") THEN 1 ELSE 0 END), 0) AS comannee,
                COALESCE(SUM(CASE WHEN crea = "2" AND date_enreg >= DATE_FORMAT(CURRENT_DATE(), "%Y-01-01 00:00:00") AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 YEAR), "%Y-01-01 00:00:00") THEN 1 ELSE 0 END), 0) AS comtermannee,
                COALESCE(SUM(CASE WHEN fact IS NOT NULL AND date_enreg >= DATE_FORMAT(CURRENT_DATE(), "%Y-%m-01 00:00:00") AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH), "%Y-%m-01 00:00:00") THEN 1 ELSE 0 END), 0) AS commois,
                COALESCE(SUM(CASE WHEN crea = "2" AND date_enreg >= DATE_FORMAT(CURRENT_DATE(), "%Y-%m-01 00:00:00") AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH), "%Y-%m-01 00:00:00") THEN 1 ELSE 0 END), 0) AS comtermmois,
                COALESCE(SUM(CASE WHEN fact IS NOT NULL AND date_enreg >= CURRENT_DATE() AND date_enreg < DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END), 0) AS comjour,
                COALESCE(SUM(CASE WHEN crea = "2" AND date_enreg >= CURRENT_DATE() AND date_enreg < DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END), 0) AS comtermjour,
                COALESCE(SUM(CASE WHEN crea = "2" THEN 1 ELSE 0 END), 0) AS comtermtotal
             FROM events'
        )->fetch(PDO::FETCH_ASSOC) ?: [];

        return $row + [
            'datanbevent' => 0,
            'datarealise' => 0,
            'dataincomple' => 0,
            'dataattente' => 0,
            'comannee' => 0,
            'comtermannee' => 0,
            'commois' => 0,
            'comtermmois' => 0,
            'comjour' => 0,
            'comtermjour' => 0,
            'comtermtotal' => 0,
        ];
    }

    private static function invoiceStats(PDO $pdo): array
    {
        $row = $pdo->query(
            'SELECT
                COALESCE(SUM(CASE WHEN date_enreg >= DATE_FORMAT(CURRENT_DATE(), "%Y-01-01 00:00:00") AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 YEAR), "%Y-01-01 00:00:00") THEN CAST(NULLIF(TRIM(montant_paye), "") AS DECIMAL(12,2)) ELSE 0 END), 0) AS finanneemp,
                COALESCE(SUM(CASE WHEN date_enreg >= DATE_FORMAT(CURRENT_DATE(), "%Y-01-01 00:00:00") AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 YEAR), "%Y-01-01 00:00:00") THEN CAST(NULLIF(TRIM(montant_total), "") AS DECIMAL(12,2)) ELSE 0 END), 0) AS finannee,
                COALESCE(SUM(CASE WHEN date_enreg >= DATE_FORMAT(CURRENT_DATE(), "%Y-%m-01 00:00:00") AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH), "%Y-%m-01 00:00:00") THEN CAST(NULLIF(TRIM(montant_paye), "") AS DECIMAL(12,2)) ELSE 0 END), 0) AS finmoismp,
                COALESCE(SUM(CASE WHEN date_enreg >= DATE_FORMAT(CURRENT_DATE(), "%Y-%m-01 00:00:00") AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH), "%Y-%m-01 00:00:00") THEN CAST(NULLIF(TRIM(montant_total), "") AS DECIMAL(12,2)) ELSE 0 END), 0) AS finmois,
                COALESCE(SUM(CASE WHEN date_enreg >= CURRENT_DATE() AND date_enreg < DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY) THEN CAST(NULLIF(TRIM(montant_paye), "") AS DECIMAL(12,2)) ELSE 0 END), 0) AS finjourmp,
                COALESCE(SUM(CASE WHEN date_enreg >= CURRENT_DATE() AND date_enreg < DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY) THEN CAST(NULLIF(TRIM(montant_total), "") AS DECIMAL(12,2)) ELSE 0 END), 0) AS finjour,
                COALESCE(SUM(CAST(NULLIF(TRIM(montant_paye), "") AS DECIMAL(12,2))), 0) AS fintotalmp,
                COALESCE(SUM(CAST(NULLIF(TRIM(montant_total), "") AS DECIMAL(12,2))), 0) AS fintotal
             FROM facture'
        )->fetch(PDO::FETCH_ASSOC) ?: [];

        return $row + [
            'finanneemp' => 0,
            'finannee' => 0,
            'finmoismp' => 0,
            'finmois' => 0,
            'finjourmp' => 0,
            'finjour' => 0,
            'fintotalmp' => 0,
            'fintotal' => 0,
        ];
    }

    private static function digitalStats(PDO $pdo): array
    {
        $row = $pdo->query(
            'SELECT
                COALESCE(SUM(CASE WHEN date_enreg >= DATE_FORMAT(CURRENT_DATE(), "%Y-01-01 00:00:00") AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 YEAR), "%Y-01-01 00:00:00") THEN CAST(NULLIF(TRIM(pt), "") AS DECIMAL(12,2)) ELSE 0 END), 0) AS finanneedigital,
                COALESCE(SUM(CASE WHEN date_enreg >= DATE_FORMAT(CURRENT_DATE(), "%Y-%m-01 00:00:00") AND date_enreg < DATE_FORMAT(DATE_ADD(CURRENT_DATE(), INTERVAL 1 MONTH), "%Y-%m-01 00:00:00") THEN CAST(NULLIF(TRIM(pt), "") AS DECIMAL(12,2)) ELSE 0 END), 0) AS finmoisdigital,
                COALESCE(SUM(CASE WHEN date_enreg >= CURRENT_DATE() AND date_enreg < DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY) THEN CAST(NULLIF(TRIM(pt), "") AS DECIMAL(12,2)) ELSE 0 END), 0) AS finjourdigital,
                COALESCE(SUM(CAST(NULLIF(TRIM(pt), "") AS DECIMAL(12,2))), 0) AS fintotaldigital
             FROM details_fact
             WHERE libelle = "Invitation électronique"'
        )->fetch(PDO::FETCH_ASSOC) ?: [];

        return $row + [
            'finanneedigital' => 0,
            'finmoisdigital' => 0,
            'finjourdigital' => 0,
            'fintotaldigital' => 0,
        ];
    }

    private static function formatMoneyValue(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private static function formatCountLabel(int $count, string $singular, string $plural): string
    {
        return $count . ' ' . ($count > 1 ? $plural : $singular);
    }
}