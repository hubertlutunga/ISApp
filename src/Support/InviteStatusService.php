<?php

final class InviteStatusService
{
    public static function sentInviteIdsIndex(PDO $pdo, string $eventCode): array
    {
        if (trim($eventCode) === '') {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT DISTINCT invite_id
                 FROM whatsapp_message_logs
                 WHERE event_code = :event_code
                   AND send_status = :send_status
                   AND invite_id IS NOT NULL'
            );
            $stmt->execute([
                ':event_code' => $eventCode,
                ':send_status' => 'sent',
            ]);
        } catch (Throwable $exception) {
            return [];
        }

        $index = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $inviteId) {
            $normalizedId = (int) $inviteId;
            if ($normalizedId > 0) {
                $index[$normalizedId] = true;
            }
        }

        return $index;
    }

    public static function confirmedNamesIndex(PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare('SELECT noms FROM confirmation WHERE cod_mar = ?');
        $stmt->execute([$eventId]);

        $index = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
            $normalized = self::normalizeName((string) $name);
            if ($normalized !== '') {
                $index[$normalized] = true;
            }
        }

        return $index;
    }

    public static function lastSentWhatsappNumberByInvite(PDO $pdo, string $eventCode): array
    {
        if (trim($eventCode) === '') {
            return [];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT invite_id, recipient_number
                 FROM whatsapp_message_logs
                 WHERE event_code = :event_code
                   AND send_status = :send_status
                   AND invite_id IS NOT NULL
                 ORDER BY sent_at DESC, id DESC'
            );
            $stmt->execute([
                ':event_code' => $eventCode,
                ':send_status' => 'sent',
            ]);
        } catch (Throwable $exception) {
            return [];
        }

        $index = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $inviteId = (int) ($row['invite_id'] ?? 0);
            if ($inviteId <= 0 || isset($index[$inviteId])) {
                continue;
            }

            $normalizedNumber = self::normalizeWhatsappNumberForInput((string) ($row['recipient_number'] ?? ''));
            if ($normalizedNumber !== '') {
                $index[$inviteId] = $normalizedNumber;
            }
        }

        return $index;
    }

    public static function confirmationLabel(bool $confirmed, ?string $inviteType): string
    {
        if (!$confirmed) {
            return '<em style="color:#ddd;">Reponse en attente</em>';
        }

        if ($inviteType === 'C') {
            return '<em style="color:#28a745;">Ont repondu</em>';
        }

        return '<em style="color:#28a745;">A repondu</em>';
    }

    public static function invitationStatusLabel(bool $confirmed, bool $sent, ?string $inviteType): string
    {
        if ($confirmed) {
            if ($inviteType === 'C') {
                return '<em style="color:#198754;">Ont repondu</em>';
            }

            return '<em style="color:#198754;">A repondu</em>';
        }

        if ($sent) {
            return '<em style="color:#b26a00;">Reponse en attente</em>';
        }

        return '<em style="color:#6c757d;">Invitation non envoyee</em>';
    }

    public static function normalizeName(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? ''), 'UTF-8');
    }

    private static function normalizeWhatsappNumberForInput(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (stripos($value, 'whatsapp:') === 0) {
            $value = substr($value, 9);
        }

        $value = preg_replace('/[\s\-().]/', '', $value) ?? '';

        if (strpos($value, '00') === 0) {
            $value = '+' . substr($value, 2);
        }

        if ($value !== '' && $value[0] !== '+' && preg_match('/^[1-9]\d{7,14}$/', $value)) {
            $value = '+' . $value;
        }

        if (!preg_match('/^\+[1-9]\d{7,14}$/', $value)) {
            return '';
        }

        return $value;
    }
}