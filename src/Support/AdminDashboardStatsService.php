<?php

final class AdminDashboardStatsService
{
    public static function build(PDO $pdo, array $session): array
    {
        $stats = [
            'datanbevent' => self::count($pdo, 'SELECT COUNT(*) FROM events'),
            'datarealise' => self::count($pdo, "SELECT COUNT(*) FROM events WHERE fact IS NOT NULL AND crea = '2'"),
            'dataincomple' => self::count($pdo, 'SELECT COUNT(*) FROM events WHERE fact IS NULL'),
            'dataattente' => self::count($pdo, "SELECT COUNT(*) FROM events WHERE fact IS NOT NULL AND (crea IS NULL OR crea = '1')"),
            'finanneemp' => self::sumFacture($pdo, 'montant_paye', 'YEAR(date_enreg) = YEAR(CURRENT_DATE())'),
            'finannee' => self::sumFacture($pdo, 'montant_total', 'YEAR(date_enreg) = YEAR(CURRENT_DATE())'),
            'comannee' => self::formatCountLabel(self::count($pdo, "SELECT COUNT(*) FROM events WHERE fact IS NOT NULL AND YEAR(date_enreg) = YEAR(CURRENT_DATE())"), 'evenement', 'evenements'),
            'comtermannee' => self::formatCountLabel(self::count($pdo, "SELECT COUNT(*) FROM events WHERE crea = '2' AND YEAR(date_enreg) = YEAR(CURRENT_DATE())"), 'terminé', 'terminés'),
            'finanneedigital' => self::sumDetailsFact($pdo, 'YEAR(date_enreg) = YEAR(CURRENT_DATE())'),
            'finmoismp' => self::sumFacture($pdo, 'montant_paye', 'MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())'),
            'finmois' => self::sumFacture($pdo, 'montant_total', 'MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())'),
            'commois' => self::formatCountLabel(self::count($pdo, "SELECT COUNT(*) FROM events WHERE fact IS NOT NULL AND MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())"), 'evenement', 'evenements'),
            'comtermmois' => self::formatCountLabel(self::count($pdo, "SELECT COUNT(*) FROM events WHERE crea = '2' AND MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())"), 'terminé', 'terminés'),
            'finmoisdigital' => self::sumDetailsFact($pdo, 'MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())'),
            'finjourmp' => self::sumFacture($pdo, 'montant_paye', 'DATE(date_enreg) = CURRENT_DATE()'),
            'finjour' => self::sumFacture($pdo, 'montant_total', 'DATE(date_enreg) = CURRENT_DATE()'),
            'comjour' => self::formatCountLabel(self::count($pdo, "SELECT COUNT(*) FROM events WHERE fact IS NOT NULL AND DAY(date_enreg) = DAY(CURRENT_DATE()) AND MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())"), 'evenement', 'evenements'),
            'comtermjour' => self::formatCountLabel(self::count($pdo, "SELECT COUNT(*) FROM events WHERE crea = '2' AND DAY(date_enreg) = DAY(CURRENT_DATE()) AND MONTH(date_enreg) = MONTH(CURRENT_DATE()) AND YEAR(date_enreg) = YEAR(CURRENT_DATE())"), 'terminé', 'terminés'),
            'finjourdigital' => self::sumDetailsFact($pdo, 'DATE(date_enreg) = CURRENT_DATE()'),
            'fintotalmp' => self::sumFacture($pdo, 'montant_paye'),
            'fintotal' => self::sumFacture($pdo, 'montant_total'),
            'comtotal' => self::formatCountLabel(self::count($pdo, 'SELECT COUNT(*) FROM events'), 'evenement', 'evenements'),
            'comtermtotal' => self::formatCountLabel(self::count($pdo, "SELECT COUNT(*) FROM events WHERE crea = '2'"), 'terminé', 'terminés'),
            'fintotaldigital' => self::sumDetailsFact($pdo),
            'finance' => (($session['type_user'] ?? null) == '3') ? 'display:none;' : '',
        ];

        $stats['restannee'] = self::formatMoneyValue((float) $stats['finannee'] - (float) $stats['finanneemp']);
        $stats['restmois'] = self::formatMoneyValue((float) $stats['finmois'] - (float) $stats['finmoismp']);
        $stats['restjour'] = self::formatMoneyValue((float) $stats['finjour'] - (float) $stats['finjourmp']);
        $stats['resttotal'] = self::formatMoneyValue((float) $stats['fintotal'] - (float) $stats['fintotalmp']);

        return $stats;
    }

    private static function count(PDO $pdo, string $sql): int
    {
        return (int) $pdo->query($sql)->fetchColumn();
    }

    private static function sumFacture(PDO $pdo, string $column, ?string $where = null): string
    {
        $sql = 'SELECT SUM(' . $column . ') FROM facture';
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }

        return self::formatMoneyValue((float) ($pdo->query($sql)->fetchColumn() ?: 0));
    }

    private static function sumDetailsFact(PDO $pdo, ?string $where = null): string
    {
        $sql = "SELECT SUM(pt) FROM details_fact WHERE libelle = 'Invitation électronique'";
        if ($where) {
            $sql .= ' AND ' . $where;
        }

        return self::formatMoneyValue((float) ($pdo->query($sql)->fetchColumn() ?: 0));
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