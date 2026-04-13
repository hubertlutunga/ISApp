<?php

final class InviteStatusService
{
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

    public static function normalizeName(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? ''), 'UTF-8');
    }
}