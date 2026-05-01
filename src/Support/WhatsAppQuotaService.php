<?php

final class WhatsAppQuotaService
{
    public const DEFAULT_EVENT_QUOTA = 500;

    private static bool $tableEnsured = false;
    private static bool $logTableEnsured = false;

    public static function ensureTable(PDO $pdo): void
    {
        if (self::$tableEnsured) {
            return;
        }

        self::ensureMessageLogTable($pdo);

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS whatsapp_event_credits (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                event_code VARCHAR(191) NOT NULL,
                client_user_id INT UNSIGNED NOT NULL,
                base_quota INT UNSIGNED NOT NULL DEFAULT 500,
                bonus_quota INT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_whatsapp_event_client (event_code, client_user_id),
                KEY idx_whatsapp_credit_client (client_user_id),
                KEY idx_whatsapp_credit_event (event_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        self::$tableEnsured = true;
    }

    public static function ensureMessageLogTable(PDO $pdo): void
    {
        if (self::$logTableEnsured) {
            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS whatsapp_message_logs (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                event_code VARCHAR(191) NOT NULL,
                invite_id INT NULL,
                recipient_number VARCHAR(64) NOT NULL,
                recipient_name VARCHAR(191) NOT NULL,
                send_mode VARCHAR(32) NOT NULL,
                template_sid VARCHAR(64) NOT NULL,
                content_variables_json JSON NULL,
                media_filename VARCHAR(255) NULL,
                media_url TEXT NULL,
                twilio_message_sid VARCHAR(64) NULL,
                send_status VARCHAR(32) NOT NULL,
                error_message TEXT NULL,
                sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_whatsapp_logs_event_code (event_code),
                INDEX idx_whatsapp_logs_invite_id (invite_id),
                INDEX idx_whatsapp_logs_status (send_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        self::$logTableEnsured = true;
    }

    public static function resolveClientUserId(array $event, int $fallbackUserId = 0): int
    {
        $primaryUserId = (int) ($event['cod_user'] ?? 0);
        if ($primaryUserId > 0) {
            return $primaryUserId;
        }

        $secondaryUserId = (int) ($event['cod_user2'] ?? 0);
        if ($secondaryUserId > 0) {
            return $secondaryUserId;
        }

        return max(0, $fallbackUserId);
    }

    public static function ensureEventCredit(PDO $pdo, string $eventCode, int $clientUserId): void
    {
        self::ensureTable($pdo);

        if (trim($eventCode) === '' || $clientUserId <= 0) {
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO whatsapp_event_credits (event_code, client_user_id, base_quota, bonus_quota)
             VALUES (:event_code, :client_user_id, :base_quota, 0)
             ON DUPLICATE KEY UPDATE updated_at = updated_at'
        );
        $stmt->execute([
            ':event_code' => $eventCode,
            ':client_user_id' => $clientUserId,
            ':base_quota' => self::DEFAULT_EVENT_QUOTA,
        ]);
        $stmt->closeCursor();
    }

    public static function getEventQuota(PDO $pdo, string $eventCode, int $clientUserId): array
    {
        if (trim($eventCode) === '' || $clientUserId <= 0) {
            return self::emptyQuota($eventCode, $clientUserId);
        }

        self::ensureEventCredit($pdo, $eventCode, $clientUserId);

        $stmt = $pdo->prepare(
            'SELECT c.base_quota, c.bonus_quota, COALESCE(l.sent_count, 0) AS sent_count
             FROM whatsapp_event_credits c
             LEFT JOIN (
                SELECT event_code, COUNT(*) AS sent_count
                FROM whatsapp_message_logs
                WHERE send_status = :send_status
                GROUP BY event_code
             ) l ON l.event_code = c.event_code
             WHERE c.event_code = :event_code AND c.client_user_id = :client_user_id
             LIMIT 1'
        );
        $stmt->execute([
            ':send_status' => 'sent',
            ':event_code' => $eventCode,
            ':client_user_id' => $clientUserId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $stmt->closeCursor();

        $baseQuota = (int) ($row['base_quota'] ?? self::DEFAULT_EVENT_QUOTA);
        $bonusQuota = (int) ($row['bonus_quota'] ?? 0);
        $sentCount = (int) ($row['sent_count'] ?? 0);
        $totalQuota = $baseQuota + $bonusQuota;

        return [
            'event_code' => $eventCode,
            'client_user_id' => $clientUserId,
            'base_quota' => $baseQuota,
            'bonus_quota' => $bonusQuota,
            'total_quota' => $totalQuota,
            'sent_count' => $sentCount,
            'remaining_quota' => max(0, $totalQuota - $sentCount),
        ];
    }

    public static function assertQuotaAvailable(PDO $pdo, string $eventCode, int $clientUserId): array
    {
        $quota = self::getEventQuota($pdo, $eventCode, $clientUserId);

        if (($quota['remaining_quota'] ?? 0) <= 0) {
            throw new RuntimeException('Votre quota WhatsApp pour cet evenement est epuise. Contactez l administrateur pour augmenter votre credit.');
        }

        return $quota;
    }

    public static function addBonusQuota(PDO $pdo, string $eventCode, int $clientUserId, int $bonusToAdd): array
    {
        self::ensureEventCredit($pdo, $eventCode, $clientUserId);

        if ($bonusToAdd === 0) {
            return self::getEventQuota($pdo, $eventCode, $clientUserId);
        }

        $stmt = $pdo->prepare(
            'UPDATE whatsapp_event_credits
             SET bonus_quota = GREATEST(0, bonus_quota + :bonus_quota)
             WHERE event_code = :event_code AND client_user_id = :client_user_id'
        );
        $stmt->execute([
            ':bonus_quota' => $bonusToAdd,
            ':event_code' => $eventCode,
            ':client_user_id' => $clientUserId,
        ]);
        $stmt->closeCursor();

        return self::getEventQuota($pdo, $eventCode, $clientUserId);
    }

    public static function listClientEventStats(PDO $pdo, int $clientUserId): array
    {
        self::ensureTable($pdo);

        if ($clientUserId <= 0) {
            return [];
        }

        $stmt = $pdo->prepare(
            'SELECT e.*, c.base_quota, c.bonus_quota, COALESCE(l.sent_count, 0) AS sent_count
             FROM events e
             LEFT JOIN whatsapp_event_credits c
               ON c.event_code = e.cod_event AND c.client_user_id = :client_user_id
             LEFT JOIN (
                SELECT event_code, COUNT(*) AS sent_count
                FROM whatsapp_message_logs
                WHERE send_status = :send_status
                GROUP BY event_code
             ) l ON l.event_code = e.cod_event
             WHERE e.cod_user = :client_user_id
             ORDER BY e.cod_event DESC'
        );
        $stmt->execute([
            ':client_user_id' => $clientUserId,
            ':send_status' => 'sent',
        ]);

        $stats = [];
        while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $eventCode = (string) ($event['cod_event'] ?? '');
            self::ensureEventCredit($pdo, $eventCode, $clientUserId);
            $baseQuota = (int) (($event['base_quota'] ?? null) !== null ? $event['base_quota'] : self::DEFAULT_EVENT_QUOTA);
            $bonusQuota = (int) ($event['bonus_quota'] ?? 0);
            $sentCount = (int) ($event['sent_count'] ?? 0);
            $totalQuota = $baseQuota + $bonusQuota;
            $stats[] = [
                'event_code' => $eventCode,
                'event_label' => self::formatEventLabel($event),
                'base_quota' => $baseQuota,
                'bonus_quota' => $bonusQuota,
                'total_quota' => $totalQuota,
                'sent_count' => $sentCount,
                'remaining_quota' => max(0, $totalQuota - $sentCount),
            ];
        }
        $stmt->closeCursor();

        return $stats;
    }

    public static function getClientOverview(PDO $pdo, int $clientUserId): array
    {
        $eventStats = self::listClientEventStats($pdo, $clientUserId);
        $overview = [
            'event_count' => count($eventStats),
            'sent_count' => 0,
            'total_quota' => 0,
            'remaining_quota' => 0,
            'events' => $eventStats,
        ];

        foreach ($eventStats as $eventStat) {
            $overview['sent_count'] += (int) ($eventStat['sent_count'] ?? 0);
            $overview['total_quota'] += (int) ($eventStat['total_quota'] ?? 0);
            $overview['remaining_quota'] += (int) ($eventStat['remaining_quota'] ?? 0);
        }

        return $overview;
    }

    public static function buildAdminOverview(PDO $pdo): array
    {
        $stmt = $pdo->prepare('SELECT cod_user, noms, email, phone, type_user FROM is_users WHERE type_user = :type_user ORDER BY cod_user DESC');
        $stmt->execute([':type_user' => '2']);

        $clients = [];
        $totals = [
            'client_count' => 0,
            'event_count' => 0,
            'sent_count' => 0,
            'total_quota' => 0,
            'remaining_quota' => 0,
        ];

        while ($client = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clientId = (int) ($client['cod_user'] ?? 0);
            $overview = self::getClientOverview($pdo, $clientId);

            $client['quota_overview'] = $overview;
            $clients[] = $client;

            $totals['client_count']++;
            $totals['event_count'] += (int) $overview['event_count'];
            $totals['sent_count'] += (int) $overview['sent_count'];
            $totals['total_quota'] += (int) $overview['total_quota'];
            $totals['remaining_quota'] += (int) $overview['remaining_quota'];
        }
        $stmt->closeCursor();

        return [
            'totals' => $totals,
            'clients' => $clients,
        ];
    }

    private static function formatEventLabel(array $event): string
    {
        $eventType = (string) ($event['type_event'] ?? '');

        if ($eventType === '1') {
            $firstName = trim((string) ($event['prenom_epoux'] ?? ''));
            $secondName = trim((string) ($event['prenom_epouse'] ?? ''));
            if ((string) ($event['ordrepri'] ?? '') !== 'm') {
                $firstName = trim((string) ($event['prenom_epouse'] ?? ''));
                $secondName = trim((string) ($event['prenom_epoux'] ?? ''));
            }

            $names = trim($firstName . ' & ' . $secondName, ' &');
            $typeMar = trim((string) ($event['type_mar'] ?? ''));
            $label = trim('Mariage ' . $typeMar);

            return trim($label . ($names !== '' ? ' • ' . $names : ''));
        }

        $eventName = trim((string) ($event['nom_event'] ?? $event['titre_event'] ?? ''));
        if ($eventName !== '') {
            return $eventName;
        }

        $hostName = trim((string) ($event['nomfetard'] ?? ''));
        if ($hostName !== '') {
            return $hostName;
        }

        return 'Evenement #' . (string) ($event['cod_event'] ?? '');
    }

    private static function emptyQuota(string $eventCode, int $clientUserId): array
    {
        return [
            'event_code' => $eventCode,
            'client_user_id' => $clientUserId,
            'base_quota' => self::DEFAULT_EVENT_QUOTA,
            'bonus_quota' => 0,
            'total_quota' => self::DEFAULT_EVENT_QUOTA,
            'sent_count' => 0,
            'remaining_quota' => self::DEFAULT_EVENT_QUOTA,
        ];
    }
}