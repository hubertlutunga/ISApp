<?php

final class AdminClientManagementService
{
    public const TWILIO_UNIT_COST_USD = 0.005;

    private static bool $controlTableEnsured = false;

    public static function ensureControlTable(PDO $pdo): void
    {
        if (self::$controlTableEnsured) {
            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS admin_client_controls (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                client_user_id INT UNSIGNED NOT NULL,
                account_status VARCHAR(16) NOT NULL DEFAULT "active",
                invitation_sending_suspended TINYINT(1) NOT NULL DEFAULT 0,
                suspension_reason VARCHAR(255) NULL,
                blocked_at DATETIME NULL,
                blocked_by_user_id INT NULL,
                invitation_suspended_at DATETIME NULL,
                invitation_suspended_by_user_id INT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_admin_client_control_user (client_user_id),
                KEY idx_admin_client_control_status (account_status),
                KEY idx_admin_client_control_invitation_suspended (invitation_sending_suspended)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        self::$controlTableEnsured = true;
    }

    public static function ensureClientControl(PDO $pdo, int $clientUserId): void
    {
        self::ensureControlTable($pdo);

        if ($clientUserId <= 0) {
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO admin_client_controls (client_user_id, account_status, invitation_sending_suspended)
             VALUES (:client_user_id, :account_status, 0)
             ON DUPLICATE KEY UPDATE updated_at = updated_at'
        );
        $stmt->execute([
            ':client_user_id' => $clientUserId,
            ':account_status' => 'active',
        ]);
        $stmt->closeCursor();
    }

    public static function getClientControl(PDO $pdo, int $clientUserId): array
    {
        if ($clientUserId <= 0) {
            return self::defaultControl($clientUserId);
        }

        self::ensureClientControl($pdo, $clientUserId);

        $stmt = $pdo->prepare(
            'SELECT client_user_id, account_status, invitation_sending_suspended, suspension_reason,
                    blocked_at, blocked_by_user_id, invitation_suspended_at, invitation_suspended_by_user_id
             FROM admin_client_controls
             WHERE client_user_id = :client_user_id
             LIMIT 1'
        );
        $stmt->execute([':client_user_id' => $clientUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $stmt->closeCursor();

        if ($row === []) {
            return self::defaultControl($clientUserId);
        }

        return [
            'client_user_id' => (int) ($row['client_user_id'] ?? $clientUserId),
            'account_status' => ((string) ($row['account_status'] ?? 'active')) === 'blocked' ? 'blocked' : 'active',
            'invitation_sending_suspended' => (int) ($row['invitation_sending_suspended'] ?? 0) === 1,
            'suspension_reason' => trim((string) ($row['suspension_reason'] ?? '')),
            'blocked_at' => (string) ($row['blocked_at'] ?? ''),
            'blocked_by_user_id' => (int) ($row['blocked_by_user_id'] ?? 0),
            'invitation_suspended_at' => (string) ($row['invitation_suspended_at'] ?? ''),
            'invitation_suspended_by_user_id' => (int) ($row['invitation_suspended_by_user_id'] ?? 0),
        ];
    }

    public static function listClientControlsByIds(PDO $pdo, array $clientUserIds): array
    {
        self::ensureControlTable($pdo);

        $clientUserIds = array_values(array_unique(array_filter(array_map('intval', $clientUserIds), static fn(int $id): bool => $id > 0)));
        if ($clientUserIds === []) {
            return [];
        }

        foreach ($clientUserIds as $clientUserId) {
            self::ensureClientControl($pdo, $clientUserId);
        }

        $placeholders = implode(',', array_fill(0, count($clientUserIds), '?'));
        $stmt = $pdo->prepare(
            'SELECT client_user_id, account_status, invitation_sending_suspended, suspension_reason,
                    blocked_at, blocked_by_user_id, invitation_suspended_at, invitation_suspended_by_user_id
             FROM admin_client_controls
             WHERE client_user_id IN (' . $placeholders . ')'
        );
        $stmt->execute($clientUserIds);

        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clientUserId = (int) ($row['client_user_id'] ?? 0);
            if ($clientUserId <= 0) {
                continue;
            }

            $map[$clientUserId] = [
                'client_user_id' => $clientUserId,
                'account_status' => ((string) ($row['account_status'] ?? 'active')) === 'blocked' ? 'blocked' : 'active',
                'invitation_sending_suspended' => (int) ($row['invitation_sending_suspended'] ?? 0) === 1,
                'suspension_reason' => trim((string) ($row['suspension_reason'] ?? '')),
                'blocked_at' => (string) ($row['blocked_at'] ?? ''),
                'blocked_by_user_id' => (int) ($row['blocked_by_user_id'] ?? 0),
                'invitation_suspended_at' => (string) ($row['invitation_suspended_at'] ?? ''),
                'invitation_suspended_by_user_id' => (int) ($row['invitation_suspended_by_user_id'] ?? 0),
            ];
        }
        $stmt->closeCursor();

        foreach ($clientUserIds as $clientUserId) {
            if (!isset($map[$clientUserId])) {
                $map[$clientUserId] = self::defaultControl($clientUserId);
            }
        }

        return $map;
    }

    public static function setClientBlocked(PDO $pdo, int $adminUserId, int $clientUserId, bool $blocked, string $reason = ''): array
    {
        self::ensureClientControl($pdo, $clientUserId);

        $stmt = $pdo->prepare(
            'UPDATE admin_client_controls
             SET account_status = :account_status,
                 suspension_reason = :suspension_reason,
                 blocked_at = :blocked_at,
                 blocked_by_user_id = :blocked_by_user_id
             WHERE client_user_id = :client_user_id
             LIMIT 1'
        );
        $stmt->execute([
            ':account_status' => $blocked ? 'blocked' : 'active',
            ':suspension_reason' => trim($reason),
            ':blocked_at' => $blocked ? date('Y-m-d H:i:s') : null,
            ':blocked_by_user_id' => $blocked ? max(0, $adminUserId) : null,
            ':client_user_id' => $clientUserId,
        ]);
        $stmt->closeCursor();

        return self::getClientControl($pdo, $clientUserId);
    }

    public static function setInvitationSuspended(PDO $pdo, int $adminUserId, int $clientUserId, bool $suspended, string $reason = ''): array
    {
        self::ensureClientControl($pdo, $clientUserId);

        $stmt = $pdo->prepare(
            'UPDATE admin_client_controls
             SET invitation_sending_suspended = :invitation_sending_suspended,
                 suspension_reason = :suspension_reason,
                 invitation_suspended_at = :invitation_suspended_at,
                 invitation_suspended_by_user_id = :invitation_suspended_by_user_id
             WHERE client_user_id = :client_user_id
             LIMIT 1'
        );
        $stmt->execute([
            ':invitation_sending_suspended' => $suspended ? 1 : 0,
            ':suspension_reason' => trim($reason),
            ':invitation_suspended_at' => $suspended ? date('Y-m-d H:i:s') : null,
            ':invitation_suspended_by_user_id' => $suspended ? max(0, $adminUserId) : null,
            ':client_user_id' => $clientUserId,
        ]);
        $stmt->closeCursor();

        return self::getClientControl($pdo, $clientUserId);
    }

    public static function isClientBlocked(PDO $pdo, int $clientUserId): bool
    {
        $control = self::getClientControl($pdo, $clientUserId);

        return (string) ($control['account_status'] ?? 'active') === 'blocked';
    }

    public static function assertClientInvitationsEnabled(PDO $pdo, int $clientUserId): void
    {
        $control = self::getClientControl($pdo, $clientUserId);

        if ((string) ($control['account_status'] ?? 'active') === 'blocked') {
            throw new RuntimeException('Votre compte client est actuellement bloque. Contactez l administration.');
        }

        if (!empty($control['invitation_sending_suspended'])) {
            throw new RuntimeException('L envoi des invitations est temporairement suspendu sur votre compte. Contactez l administration.');
        }
    }

    public static function addBonusQuotaToAllClientEvents(PDO $pdo, int $clientUserId, int $bonusToAdd): array
    {
        if ($clientUserId <= 0) {
            throw new RuntimeException('Client invalide.');
        }

        if ($bonusToAdd === 0) {
            return [
                'affected_events' => 0,
                'total_bonus_added' => 0,
                'overview' => WhatsAppQuotaService::getClientOverview($pdo, $clientUserId),
            ];
        }

        $stmt = $pdo->prepare('SELECT cod_event FROM events WHERE cod_user = :client_user_id OR cod_user2 = :client_user_id ORDER BY cod_event DESC');
        $stmt->execute([':client_user_id' => $clientUserId]);
        $eventCodes = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $stmt->closeCursor();

        $affected = 0;
        foreach ($eventCodes as $eventCode) {
            $eventCode = trim((string) $eventCode);
            if ($eventCode === '') {
                continue;
            }

            WhatsAppQuotaService::addBonusQuota($pdo, $eventCode, $clientUserId, $bonusToAdd);
            $affected++;
        }

        return [
            'affected_events' => $affected,
            'total_bonus_added' => $affected * $bonusToAdd,
            'overview' => WhatsAppQuotaService::getClientOverview($pdo, $clientUserId),
        ];
    }

    public static function buildInvitationAnalytics(PDO $pdo, int $clientUserId = 0): array
    {
        $bindings = [];
        $scopeJoin = ' LEFT JOIN events e ON e.cod_event = logs.event_code ';
        $scopeWhere = ' WHERE logs.send_status = :send_status ';
        $bindings[':send_status'] = 'sent';

        if ($clientUserId > 0) {
            $scopeWhere .= ' AND COALESCE(NULLIF(e.cod_user, 0), NULLIF(e.cod_user2, 0)) = :scope_client_user_id ';
            $bindings[':scope_client_user_id'] = $clientUserId;
        }

        $totalsStmt = $pdo->prepare(
            'SELECT
                SUM(CASE WHEN DATE(logs.sent_at) = CURRENT_DATE() THEN 1 ELSE 0 END) AS sent_today,
                SUM(CASE WHEN YEAR(logs.sent_at) = YEAR(CURRENT_DATE()) AND MONTH(logs.sent_at) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END) AS sent_month,
                COUNT(*) AS sent_total
             FROM whatsapp_message_logs logs '
             . $scopeJoin
             . $scopeWhere
        );
        foreach ($bindings as $name => $value) {
            $totalsStmt->bindValue($name, $value, $name === ':scope_client_user_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $totalsStmt->execute();
        $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $totalsStmt->closeCursor();

        $sentToday = (int) ($totals['sent_today'] ?? 0);
        $sentMonth = (int) ($totals['sent_month'] ?? 0);
        $sentTotal = (int) ($totals['sent_total'] ?? 0);

        $dailyStmt = $pdo->prepare(
            'SELECT DATE(logs.sent_at) AS day_key, COUNT(*) AS sent_count
             FROM whatsapp_message_logs logs '
             . $scopeJoin
             . $scopeWhere
             . ' AND logs.sent_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 29 DAY)
               GROUP BY DATE(logs.sent_at)
               ORDER BY day_key ASC'
        );
        foreach ($bindings as $name => $value) {
            $dailyStmt->bindValue($name, $value, $name === ':scope_client_user_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $dailyStmt->execute();
        $dailyRows = $dailyStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $dailyStmt->closeCursor();

        $monthlyStmt = $pdo->prepare(
            'SELECT DATE_FORMAT(logs.sent_at, "%Y-%m") AS month_key, COUNT(*) AS sent_count
             FROM whatsapp_message_logs logs '
             . $scopeJoin
             . $scopeWhere
             . ' AND logs.sent_at >= DATE_FORMAT(DATE_SUB(CURRENT_DATE(), INTERVAL 11 MONTH), "%Y-%m-01")
               GROUP BY DATE_FORMAT(logs.sent_at, "%Y-%m")
               ORDER BY month_key ASC'
        );
        foreach ($bindings as $name => $value) {
            $monthlyStmt->bindValue($name, $value, $name === ':scope_client_user_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $monthlyStmt->execute();
        $monthlyRows = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $monthlyStmt->closeCursor();

        $clientsStmt = $pdo->query('SELECT cod_user, noms FROM is_users WHERE type_user = "2" ORDER BY noms ASC');
        $clients = $clientsStmt ? ($clientsStmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
        if ($clientsStmt) {
            $clientsStmt->closeCursor();
        }

        $topClients = [];
        if ($clientUserId <= 0) {
            $topStmt = $pdo->prepare(
                'SELECT
                    COALESCE(NULLIF(e.cod_user, 0), NULLIF(e.cod_user2, 0)) AS client_user_id,
                    COALESCE(u.noms, "Client inconnu") AS client_name,
                    COUNT(*) AS sent_count
                 FROM whatsapp_message_logs logs
                 LEFT JOIN events e ON e.cod_event = logs.event_code
                 LEFT JOIN is_users u ON u.cod_user = COALESCE(NULLIF(e.cod_user, 0), NULLIF(e.cod_user2, 0))
                 WHERE logs.send_status = :send_status
                 GROUP BY COALESCE(NULLIF(e.cod_user, 0), NULLIF(e.cod_user2, 0)), COALESCE(u.noms, "Client inconnu")
                 HAVING client_user_id IS NOT NULL
                 ORDER BY sent_count DESC, client_name ASC
                 LIMIT 8'
            );
            $topStmt->execute([':send_status' => 'sent']);
            $topClients = $topStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $topStmt->closeCursor();
        }

        return [
            'scope_client_user_id' => $clientUserId,
            'unit_cost_usd' => self::TWILIO_UNIT_COST_USD,
            'sent_today' => $sentToday,
            'sent_month' => $sentMonth,
            'sent_total' => $sentTotal,
            'cost_today_usd' => self::formatUsd($sentToday * self::TWILIO_UNIT_COST_USD),
            'cost_month_usd' => self::formatUsd($sentMonth * self::TWILIO_UNIT_COST_USD),
            'cost_total_usd' => self::formatUsd($sentTotal * self::TWILIO_UNIT_COST_USD),
            'daily_rows' => array_map(static function (array $row): array {
                $sentCount = (int) ($row['sent_count'] ?? 0);

                return [
                    'day_key' => (string) ($row['day_key'] ?? ''),
                    'sent_count' => $sentCount,
                    'cost_usd' => self::formatUsd($sentCount * self::TWILIO_UNIT_COST_USD),
                ];
            }, $dailyRows),
            'monthly_rows' => array_map(static function (array $row): array {
                $sentCount = (int) ($row['sent_count'] ?? 0);

                return [
                    'month_key' => (string) ($row['month_key'] ?? ''),
                    'sent_count' => $sentCount,
                    'cost_usd' => self::formatUsd($sentCount * self::TWILIO_UNIT_COST_USD),
                ];
            }, $monthlyRows),
            'clients' => $clients,
            'top_clients' => array_map(static function (array $row): array {
                $sentCount = (int) ($row['sent_count'] ?? 0);

                return [
                    'client_user_id' => (int) ($row['client_user_id'] ?? 0),
                    'client_name' => (string) ($row['client_name'] ?? 'Client inconnu'),
                    'sent_count' => $sentCount,
                    'cost_usd' => self::formatUsd($sentCount * self::TWILIO_UNIT_COST_USD),
                ];
            }, $topClients),
        ];
    }

    public static function buildClientConsumptionStats(PDO $pdo, int $lowQuotaThreshold = 50): array
    {
        $stmt = $pdo->query(
            'SELECT
                u.cod_user,
                u.noms,
                u.email,
                u.phone,
                COALESCE(SUM(CASE WHEN logs.send_status = "sent" AND DATE(logs.sent_at) = CURRENT_DATE() THEN 1 ELSE 0 END), 0) AS sent_today,
                COALESCE(SUM(CASE WHEN logs.send_status = "sent" AND YEAR(logs.sent_at) = YEAR(CURRENT_DATE()) AND MONTH(logs.sent_at) = MONTH(CURRENT_DATE()) THEN 1 ELSE 0 END), 0) AS sent_month,
                COALESCE(SUM(CASE WHEN logs.send_status = "sent" THEN 1 ELSE 0 END), 0) AS sent_total
             FROM is_users u
             LEFT JOIN events e ON (e.cod_user = u.cod_user OR e.cod_user2 = u.cod_user)
             LEFT JOIN whatsapp_message_logs logs ON logs.event_code = e.cod_event
             WHERE u.type_user = "2"
             GROUP BY u.cod_user, u.noms, u.email, u.phone
             ORDER BY u.noms ASC, u.cod_user DESC'
        );
        $rows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
        if ($stmt) {
            $stmt->closeCursor();
        }

        $controlMap = self::listClientControlsByIds(
            $pdo,
            array_map(static fn(array $row): int => (int) ($row['cod_user'] ?? 0), $rows)
        );

        $result = [];
        foreach ($rows as $row) {
            $clientUserId = (int) ($row['cod_user'] ?? 0);
            if ($clientUserId <= 0) {
                continue;
            }

            $sentToday = (int) ($row['sent_today'] ?? 0);
            $sentMonth = (int) ($row['sent_month'] ?? 0);
            $sentTotal = (int) ($row['sent_total'] ?? 0);
            $quotaOverview = WhatsAppQuotaService::getClientOverview($pdo, $clientUserId);
            $control = $controlMap[$clientUserId] ?? self::defaultControl($clientUserId);

            $result[] = [
                'client_user_id' => $clientUserId,
                'client_name' => (string) ($row['noms'] ?? 'Client'),
                'email' => (string) ($row['email'] ?? ''),
                'phone' => (string) ($row['phone'] ?? ''),
                'event_count' => (int) ($quotaOverview['event_count'] ?? 0),
                'total_quota' => (int) ($quotaOverview['total_quota'] ?? 0),
                'remaining_quota' => (int) ($quotaOverview['remaining_quota'] ?? 0),
                'sent_today' => $sentToday,
                'sent_month' => $sentMonth,
                'sent_total' => $sentTotal,
                'cost_today_usd' => self::formatUsd($sentToday * self::TWILIO_UNIT_COST_USD),
                'cost_month_usd' => self::formatUsd($sentMonth * self::TWILIO_UNIT_COST_USD),
                'cost_total_usd' => self::formatUsd($sentTotal * self::TWILIO_UNIT_COST_USD),
                'account_status' => (string) ($control['account_status'] ?? 'active'),
                'invitation_sending_suspended' => !empty($control['invitation_sending_suspended']),
                'is_low_quota' => (int) ($quotaOverview['remaining_quota'] ?? 0) <= max(1, $lowQuotaThreshold),
            ];
        }

        return $result;
    }

    public static function buildLowQuotaNotifications(PDO $pdo, int $lowQuotaThreshold = 50): array
    {
        $rows = self::buildClientConsumptionStats($pdo, $lowQuotaThreshold);
        $alerts = array_values(array_filter($rows, static function (array $row): bool {
            return !empty($row['is_low_quota']);
        }));

        usort($alerts, static function (array $left, array $right): int {
            $leftRemaining = (int) ($left['remaining_quota'] ?? 0);
            $rightRemaining = (int) ($right['remaining_quota'] ?? 0);

            if ($leftRemaining === $rightRemaining) {
                return strcasecmp((string) ($left['client_name'] ?? ''), (string) ($right['client_name'] ?? ''));
            }

            return $leftRemaining <=> $rightRemaining;
        });

        return $alerts;
    }

    private static function defaultControl(int $clientUserId): array
    {
        return [
            'client_user_id' => $clientUserId,
            'account_status' => 'active',
            'invitation_sending_suspended' => false,
            'suspension_reason' => '',
            'blocked_at' => '',
            'blocked_by_user_id' => 0,
            'invitation_suspended_at' => '',
            'invitation_suspended_by_user_id' => 0,
        ];
    }

    private static function formatUsd(float $amount): string
    {
        return number_format($amount, 3, '.', '');
    }
}
