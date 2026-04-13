<?php

final class EventUrlService
{
    public static function publicUrl(array $event, array $config): string
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $eventId = (string) ($event['cod_event'] ?? '');
        $eventType = (string) ($event['type_event'] ?? '');

        switch ($eventType) {
            case '2':
                return $baseUrl . '/site/anniversaire/index.php?page=accueil&cod=' . $eventId;
            case '3':
                return $baseUrl . '/site/conference/index.php?page=accueil&cod=' . $eventId;
            default:
                return $baseUrl . '/site/index.php?page=accueil&cod=' . $eventId;
        }
    }
}