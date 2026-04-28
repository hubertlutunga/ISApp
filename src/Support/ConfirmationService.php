<?php

final class ConfirmationService
{
    private static bool $mailLogTableChecked = false;

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
        $mailLogAvailable = self::ensureMailLogTable($pdo);
        $query = '
            SELECT
                c.cod_conf,
                c.noms,
                c.email,
                c.presence,
                c.note,
                c.phone,
                c.date_enreg,
                (
                    SELECT i.id_inv
                    FROM invite i
                    WHERE i.cod_mar = c.cod_mar AND i.nom = c.noms
                    ORDER BY i.id_inv ASC
                    LIMIT 1
                ) AS invite_id,
        ';

        if ($mailLogAvailable) {
            $query .= '
                (
                    SELECT COUNT(*)
                    FROM confirmation_mail_log cml
                    WHERE cml.confirmation_id = c.cod_conf
                ) AS mail_send_count,
                (
                    SELECT MAX(cml.sent_at)
                    FROM confirmation_mail_log cml
                    WHERE cml.confirmation_id = c.cod_conf
                ) AS last_mail_sent_at,
                (
                    SELECT cml.recipient_email
                    FROM confirmation_mail_log cml
                    WHERE cml.confirmation_id = c.cod_conf
                    ORDER BY cml.sent_at DESC, cml.id DESC
                    LIMIT 1
                ) AS last_mail_recipient,
            ';
        } else {
            $query .= '
                0 AS mail_send_count,
                NULL AS last_mail_sent_at,
                NULL AS last_mail_recipient,
            ';
        }

        $query .= '
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
            GROUP BY c.cod_conf, c.noms, c.email, c.presence, c.note, c.phone, c.date_enreg
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

    public static function registerMailSent(PDO $pdo, int $eventId, int $confirmationId, string $recipientEmail, ?int $senderUserId = null): void
    {
        if (!self::ensureMailLogTable($pdo)) {
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO confirmation_mail_log (event_id, confirmation_id, recipient_email, sender_user_id, sent_at)
             VALUES (:event_id, :confirmation_id, :recipient_email, :sender_user_id, NOW())'
        );
        $stmt->execute([
            ':event_id' => $eventId,
            ':confirmation_id' => $confirmationId,
            ':recipient_email' => $recipientEmail,
            ':sender_user_id' => $senderUserId,
        ]);
    }

    public static function deleteById(PDO $pdo, int $eventId, int $confirmationId): bool
    {
        $pdo->beginTransaction();

        try {
            if (self::ensureMailLogTable($pdo)) {
                $stmtLogs = $pdo->prepare('DELETE FROM confirmation_mail_log WHERE event_id = :event_id AND confirmation_id = :confirmation_id');
                $stmtLogs->execute([
                    ':event_id' => $eventId,
                    ':confirmation_id' => $confirmationId,
                ]);
            }

            $stmtMenu = $pdo->prepare('DELETE FROM menurecolte WHERE cod_conf = :cod_conf');
            $stmtMenu->execute([':cod_conf' => $confirmationId]);

            $stmtConfirmation = $pdo->prepare('DELETE FROM confirmation WHERE cod_mar = :event_id AND cod_conf = :confirmation_id');
            $stmtConfirmation->execute([
                ':event_id' => $eventId,
                ':confirmation_id' => $confirmationId,
            ]);

            $deleted = $stmtConfirmation->rowCount() > 0;
            $pdo->commit();

            return $deleted;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    public static function findById(PDO $pdo, int $eventId, int $confirmationId): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT
                c.cod_conf,
                c.cod_mar,
                c.noms,
                c.email,
                c.phone,
                c.presence,
                c.note,
                c.date_enreg,
                (
                    SELECT i.id_inv
                    FROM invite i
                    WHERE i.cod_mar = c.cod_mar AND i.nom = c.noms
                    ORDER BY i.id_inv ASC
                    LIMIT 1
                ) AS invite_id
            FROM confirmation c
            WHERE c.cod_mar = :cod_mar AND c.cod_conf = :cod_conf
            LIMIT 1'
        );
        $stmt->execute([
            ':cod_mar' => $eventId,
            ':cod_conf' => $confirmationId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    private static function ensureMailLogTable(PDO $pdo): bool
    {
        if (self::$mailLogTableChecked) {
            return true;
        }

        try {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS confirmation_mail_log (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    event_id INT NOT NULL,
                    confirmation_id INT NOT NULL,
                    recipient_email VARCHAR(190) NOT NULL,
                    sender_user_id INT NULL,
                    sent_at DATETIME NOT NULL,
                    INDEX idx_confirmation_mail_log_event_confirmation (event_id, confirmation_id),
                    INDEX idx_confirmation_mail_log_sent_at (sent_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
            self::$mailLogTableChecked = true;

            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }
}