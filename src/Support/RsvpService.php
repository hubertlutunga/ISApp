<?php

final class RsvpService
{
    public static function cleanText(?string $text): string
    {
        return html_entity_decode(trim((string) $text), ENT_QUOTES, 'UTF-8');
    }

    public static function findInviteById(PDO $pdo, int $inviteId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM invite WHERE id_inv = :id_inv LIMIT 1');
        $stmt->execute([':id_inv' => $inviteId]);
        $invite = $stmt->fetch();

        return $invite ?: null;
    }

    public static function buildInviteDisplayName(array $invite): string
    {
        $prefix = match ((string) ($invite['sing'] ?? '')) {
            'C' => 'Couple',
            'Mr' => 'Monsieur',
            'Mme' => 'Madame',
            default => '',
        };

        $name = self::cleanText((string) ($invite['nom'] ?? ''));

        return trim(($prefix !== '' ? $prefix . ' ' : '') . ucfirst($name));
    }

    public static function normalizeConfirmationName(string $displayName): string
    {
        $normalized = preg_replace('/^(Couple|Monsieur|Madame)\s+/i', '', self::cleanText($displayName));

        return self::cleanText((string) $normalized);
    }

    public static function findConfirmation(PDO $pdo, string $codMar, string $name): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM confirmation WHERE cod_mar = :cod_mar AND noms = :noms LIMIT 1');
        $stmt->execute([
            ':cod_mar' => $codMar,
            ':noms' => $name,
        ]);

        $confirmation = $stmt->fetch();

        return $confirmation ?: null;
    }

    public static function registerConfirmation(PDO $pdo, array $payload): bool
    {
        $sql = '
            INSERT INTO confirmation (cod_mar, noms, email, phone, presence, note, date_enreg)
            SELECT :cod_mar, :noms, :email, :phone, :presence, :note, NOW()
            FROM DUAL
            WHERE NOT EXISTS (
                SELECT 1 FROM confirmation WHERE cod_mar = :cod_mar_check AND noms = :noms_check
            )
        ';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cod_mar' => $payload['cod_mar'],
            ':noms' => $payload['noms'],
            ':email' => $payload['email'] ?? '',
            ':phone' => $payload['phone'] ?? '',
            ':presence' => $payload['presence'],
            ':note' => $payload['note'] ?? '',
            ':cod_mar_check' => $payload['cod_mar'],
            ':noms_check' => $payload['noms'],
        ]);

        return $stmt->rowCount() > 0;
    }
}