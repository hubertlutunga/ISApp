<?php

final class ShortUrlService
{
    public static function slugifyFragment(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($normalized === false) {
            $normalized = $value;
        }

        $normalized = preg_replace('/[^a-zA-Z0-9]+/', '', $normalized) ?: '';

        return strtolower($normalized);
    }

    public static function buildShortCode(array $event, array $config): string
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $prefix = self::slugifyFragment($event['prenom_epoux'] ?? '');
        $suffix = self::slugifyFragment($event['prenom_epouse'] ?? '');

        if ($prefix === '' && !empty($event['nomfetard'])) {
            $prefix = self::slugifyFragment((string) $event['nomfetard']);
        }

        return $baseUrl . '/?site=' . $prefix . (string) ($event['cod_event'] ?? '') . $suffix;
    }

    public static function findLongUrl(PDO $pdo, string $shortCode): ?string
    {
        $stmt = $pdo->prepare('SELECT long_url FROM url_shortener WHERE short_code = :short_code LIMIT 1');
        $stmt->execute(['short_code' => $shortCode]);
        $row = $stmt->fetch();

        return $row['long_url'] ?? null;
    }
}