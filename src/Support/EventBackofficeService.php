<?php

final class EventBackofficeService
{
    public static function findPaginatedEvents(PDO $pdo, array $request, array $session, int $limit = 50): array
    {
        $page = isset($request['page_i']) ? max(1, (int) $request['page_i']) : 1;
        $offset = ($page - 1) * $limit;
        $search = trim((string) ($request['search'] ?? ''));
        $whereSql = self::buildWhereSql($request, $session, $search !== '');

        $sql = "
SELECT
  e.cod_event, e.cod_user, e.type_event, e.type_mar, e.crea, e.fact, e.date_event, e.date_livraison,
  e.prenom_epoux, e.nom_epoux, e.prenom_epouse, e.nom_epouse,
  e.nomfetard, e.themeconf, e.autres_precisions, e.lieu, e.adresse, e.date_enreg,
    e.modele_inv, e.modele_chev,
    e.invit_religieux, e.ajustenom, e.taillenominv, e.alignnominv, e.pagenom, e.pagebouton,
        e.colornom, e.bordgauchenominv, e.qrcode, e.pageqr, e.hautqr, e.gaucheqr, e.tailleqr, e.lang, e.ordrepri,
    u.cod_user AS client_code, u.type_user AS client_type_user, u.noms AS client_nom, u.phone AS client_phone, u.email AS client_email, u.recpass AS client_recpass,
  f.montant_total, f.montant_paye,
  us.short_code
FROM events e
LEFT JOIN is_users u       ON u.cod_user = e.cod_user
LEFT JOIN (
    SELECT
        reference,
        MAX(montant_total) AS montant_total,
        LEAST(SUM(COALESCE(montant_paye, 0)), MAX(COALESCE(montant_total, 0))) AS montant_paye
    FROM facture
    GROUP BY reference
) f                        ON f.reference = e.cod_event
LEFT JOIN url_shortener us ON us.cod_event = e.cod_event
$whereSql
ORDER BY e.date_enreg DESC
LIMIT :limit OFFSET :offset
";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        if ($search !== '') {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }

        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countSql = "SELECT COUNT(*) FROM events e $whereSql";
        $countStmt = $pdo->prepare($countSql);

        if ($search !== '') {
            $countStmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }

        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        return [
            'events' => $events,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $limit)),
            'total' => $total,
        ];
    }

    public static function decorateEvent(PDO $pdo, array $event, IntlDateFormatter $formatter, array $config): array
    {
        $clientName = trim((string) ($event['client_nom'] ?? ''));
        $phone = trim((string) ($event['client_phone'] ?? ''));
        $email = trim((string) ($event['client_email'] ?? ''));
        $clientCode = trim((string) ($event['client_code'] ?? ''));

        if ($clientName !== '' && $phone !== '') {
            $client = $clientName . ' (' . $phone . ')';
        } elseif ($clientName !== '') {
            $client = $clientName;
        } elseif ($phone !== '') {
            $client = $phone;
        } elseif ($email !== '') {
            $client = $email;
        } elseif ($clientCode !== '') {
            $client = 'Client #' . $clientCode;
        } else {
            $client = 'Inconnu';
        }

        $paymentMeta = self::resolvePaymentMeta($pdo, $event);
        $paye = $paymentMeta['paid_label'];
        $reste = $paymentMeta['remaining_label'];

        $badgeState = self::resolveBadgeState($pdo, $event);
        $eventIdentity = self::resolveEventIdentity($pdo, $event);

        $date = !empty($event['date_event']) ? new DateTime($event['date_event']) : new DateTime();
        $formattedDate = ucfirst($formatter->format($date));
        $publicUrl = self::buildPublicUrl($event, $config);
        $qrFile = self::ensureQrCode($event, $publicUrl);

        return [
            'client' => $client,
            'phone' => $phone,
            'paye' => $paye,
            'reste' => $reste,
            'badge' => $badgeState['badge'],
            'icon' => $badgeState['icon'],
            'color2' => $badgeState['color2'],
            'badgepaie' => self::resolvePaymentBadge($event, $paymentMeta),
            'typeevent' => $eventIdentity['typeevent'],
            'fetard' => $eventIdentity['fetard'],
            'displayvue' => $eventIdentity['displayvue'],
            'formatted_date' => $formattedDate,
            'publicUrl' => $publicUrl,
            'qrFile' => $qrFile,
            'payment' => $paymentMeta,
        ];
    }

    public static function resolvePaymentMeta(PDO $pdo, array $event): array
    {
        $eventId = (string) ($event['cod_event'] ?? '');

        $total = array_key_exists('montant_total', $event) && $event['montant_total'] !== null
            ? (float) $event['montant_total']
            : null;
        $paid = array_key_exists('montant_paye', $event) && $event['montant_paye'] !== null
            ? (float) $event['montant_paye']
            : null;

        if ($eventId !== '' && ($total === null || $paid === null)) {
            $summary = self::fetchPaymentSummary($pdo, $eventId);
            $total = $summary['total'];
            $paid = $summary['paid'];
        }

        $total = $total ?? 0.0;
        $paid = $paid ?? 0.0;
        $remaining = max($total - $paid, 0.0);
        $hasInvoice = $total > 0.0;
        $hasPayment = $paid > 0.0;
        $isFullyPaid = $hasInvoice && $remaining < 0.01;
        $isPartiallyPaid = $hasPayment && !$isFullyPaid;

        return [
            'total' => $total,
            'paid' => $paid,
            'remaining' => $remaining,
            'has_invoice' => $hasInvoice,
            'has_payment' => $hasPayment,
            'is_fully_paid' => $isFullyPaid,
            'is_partially_paid' => $isPartiallyPaid,
            'paid_label' => $hasInvoice
                ? number_format($paid, 2, ',', ' ') . ' $'
                : '<em>Paiement en attente</em>',
            'remaining_label' => $hasInvoice
                ? number_format($remaining, 2, ',', ' ') . ' $'
                : '<em>Paiement en attente</em>',
            'payment_action_url' => 'index.php?page=' . ($isPartiallyPaid ? 'paiement_fin' : 'paiement') . '&cod=' . rawurlencode($eventId),
            'payment_action_label' => $isPartiallyPaid ? 'Encaisser' : 'Confirmer',
            'invoice_pdf_url' => $hasPayment
                ? 'pages/pdf/facture_hs.php?cod=' . rawurlencode($eventId) . '&type=facture'
                : null,
        ];
    }

    private static function buildWhereSql(array $request, array $session, bool $hasSearch): string
    {
        $condition = self::resolveBaseCondition($request, $session);
        $whereParts = [];

        if ($condition !== '') {
            $whereParts[] = preg_replace('/^\s*WHERE\s+/i', '', trim($condition));
        }

        if ($hasSearch) {
            $whereParts[] = '(e.prenom_epoux LIKE :search OR e.prenom_epouse LIKE :search)';
        }

        return $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';
    }

    private static function resolveBaseCondition(array $request, array $session): string
    {
        if (($request['page'] ?? null) === 'admin_filcom') {
            if (($request['type'] ?? null) === 'npaye') {
                return 'WHERE e.fact IS NULL';
            }

            if (($request['type'] ?? null) === 'enattente') {
                return "WHERE e.fact = 'oui' AND (e.crea IS NULL OR e.crea = '4')";
            }

            if (($request['type'] ?? null) === 'realise') {
                return "WHERE e.fact = 'oui' AND e.crea = '2'";
            }
        }

        if (($session['type_user'] ?? null) == '2') {
            return 'WHERE e.fact IS NOT NULL';
        }

        if (($session['type_user'] ?? null) == '1') {
            return '';
        }

        return 'WHERE e.fact IS NOT NULL';
    }

    private static function resolveBadgeState(PDO $pdo, array $event): array
    {
        if (($event['crea'] ?? null) === '2') {
            return [
                'badge' => "<span class='badge-flag badge-done'>Terminé</span>",
                'icon' => '<i class="fas fa-check fs-24 l-h-50" style="color:#34A37B;"></i>',
                'color2' => 'color:#34A37B',
            ];
        }

        if (($event['crea'] ?? null) === '4') {
            $amorceur = self::findAmorceurLabel($pdo, (string) ($event['cod_event'] ?? ''));

            return [
                'badge' => "<span class='badge-flag badge-progress'><i class='fa fa-spinner fa-spin'></i> En cours de conception {$amorceur}</span>",
                'icon' => '<i class="fas fa-check fs-24 l-h-50" style="color:#34A37B;"></i>',
                'color2' => 'color:#34A37B',
            ];
        }

        return [
            'badge' => "<span class='badge-flag badge-new'>Nouveaux</span>",
            'icon' => '',
            'color2' => '',
        ];
    }

    private static function resolvePaymentBadge(array $event, array $paymentMeta): string
    {
        if (!$paymentMeta['has_payment']) {
            return "<span class='badge-unpaid'>non payé</span>";
        }

        if ($paymentMeta['is_partially_paid']) {
            $partialBadge = "<span class='badge-partial'>partiellement payé</span>";

            if (!empty($event['date_livraison'])) {
                return $partialBadge . " <em class='badge-livr'>à livré le " . date('d/m/Y', strtotime($event['date_livraison'])) . '</em>';
            }

            return $partialBadge;
        }

        if (!empty($event['date_livraison'])) {
            return "<em class='badge-livr'>à livré le " . date('d/m/Y', strtotime($event['date_livraison'])) . '</em>';
        }

        return "<em style='color: rgb(0, 50, 149); font-size: 14px; font-weight: bold;'>Livraison non disponible</em>";
    }

    private static function fetchPaymentSummary(PDO $pdo, string $eventId): array
    {
        $stmt = $pdo->prepare(
            'SELECT MAX(montant_total) AS montant_total, LEAST(SUM(COALESCE(montant_paye, 0)), MAX(COALESCE(montant_total, 0))) AS montant_paye FROM facture WHERE reference = ?'
        );
        $stmt->execute([$eventId]);

        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => isset($summary['montant_total']) ? (float) $summary['montant_total'] : 0.0,
            'paid' => isset($summary['montant_paye']) ? (float) $summary['montant_paye'] : 0.0,
        ];
    }

    private static function resolveEventIdentity(PDO $pdo, array $event): array
    {
        if (($event['type_event'] ?? null) == '1') {
            $firstName = trim((string) ($event['prenom_epouse'] ?? ''));
            $secondName = trim((string) ($event['prenom_epoux'] ?? ''));

            if (($event['ordrepri'] ?? null) === 'm') {
                $firstName = trim((string) ($event['prenom_epoux'] ?? ''));
                $secondName = trim((string) ($event['prenom_epouse'] ?? ''));
            }

            $fetard = trim($firstName . ' & ' . $secondName, ' &');

            return [
                'typeevent' => 'Mariage ' . ($event['type_mar'] ?? ''),
                'fetard' => $fetard !== '' ? $fetard : 'Inconnu',
                'displayvue' => 'display:block;',
            ];
        }

        return [
            'typeevent' => self::findEventTypeName($pdo, (string) ($event['type_event'] ?? '')),
            'fetard' => $event['nomfetard'] ?? 'Inconnu',
            'displayvue' => 'display:none;',
        ];
    }

    private static function findEventTypeName(PDO $pdo, string $typeEvent): string
    {
        static $cache = [];

        if ($typeEvent === '') {
            return 'Événement';
        }

        if (!array_key_exists($typeEvent, $cache)) {
            $stmt = $pdo->prepare('SELECT nom FROM evenement WHERE cod_event = ?');
            $stmt->execute([$typeEvent]);
            $cache[$typeEvent] = $stmt->fetchColumn() ?: 'Événement';
        }

        return $cache[$typeEvent];
    }

    private static function findAmorceurLabel(PDO $pdo, string $eventId): string
    {
        static $cache = [];

        if ($eventId === '') {
            return '';
        }

        if (!array_key_exists($eventId, $cache)) {
            $stmt = $pdo->prepare('SELECT cod_user FROM amorcage_dossier WHERE cod_event = ?');
            $stmt->execute([$eventId]);
            $codUser = $stmt->fetchColumn();

            if (!$codUser) {
                $cache[$eventId] = '';
            } else {
                $stmtUser = $pdo->prepare('SELECT noms FROM is_users WHERE cod_user = ?');
                $stmtUser->execute([$codUser]);
                $name = (string) $stmtUser->fetchColumn();
                $firstName = strtok($name, ' ');
                $cache[$eventId] = $firstName ? 'par ' . $firstName . ' ' : '';
            }
        }

        return $cache[$eventId];
    }

    private static function buildPublicUrl(array $event, array $config): string
    {
        if (!empty($event['short_code'])) {
            $shortCode = trim((string) $event['short_code']);

            if (preg_match('#^https?://#i', $shortCode)) {
                return $shortCode;
            }

            $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://invitationspeciale.com'), '/');
            return $baseUrl . '/u/' . ltrim($shortCode, '/');
        }

        return EventUrlService::publicUrl($event, $config);
    }

    private static function ensureQrCode(array $event, string $publicUrl): string
    {
        $rootPath = dirname(__DIR__, 2);
        $directories = [
            [
                'relative_dir' => 'mesqrcode',
                'absolute_dir' => $rootPath . '/event/users/mesqrcode',
            ],
            [
                'relative_dir' => 'qrscan/phpqrcode/cache/generated',
                'absolute_dir' => $rootPath . '/qrscan/phpqrcode/cache/generated',
            ],
        ];

        $selectedDirectory = null;

        foreach ($directories as $directory) {
            if (!is_dir($directory['absolute_dir'])) {
                @mkdir($directory['absolute_dir'], 0777, true);
            }

            if (is_dir($directory['absolute_dir']) && (is_writable($directory['absolute_dir']) || @chmod($directory['absolute_dir'], 0777))) {
                $selectedDirectory = $directory;
                break;
            }
        }

        if ($selectedDirectory === null) {
            $selectedDirectory = $directories[0];
        }

        $fileName = 'qr_' . ($event['cod_event'] ?? 'event') . '_' . md5($publicUrl) . '.png';
        $absoluteDir = $selectedDirectory['absolute_dir'];
        $absolutePath = $absoluteDir . '/' . $fileName;
        $relativePath = $selectedDirectory['relative_dir'] . '/' . $fileName;

        $oldFiles = glob($absoluteDir . '/qr_' . ($event['cod_event'] ?? 'event') . '_*.png');
        if ($oldFiles !== false) {
            foreach ($oldFiles as $oldFile) {
                if ($oldFile !== $absolutePath) {
                    @unlink($oldFile);
                }
            }
        }

        if (!file_exists($absolutePath)) {
            if (!class_exists('QRcode')) {
                require_once $rootPath . '/qrscan/phpqrcode/qrlib.php';
            }

            QRcode::png($publicUrl, $absolutePath, 'H', 10, 3);
        }

        return $relativePath;
    }
}