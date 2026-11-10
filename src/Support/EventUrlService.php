<?php

final class EventUrlService
{
    public static function publicUrl(array $event, array $config): string
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $eventId = (string) ($event['cod_event'] ?? '');
        $eventType = (string) ($event['type_event'] ?? '');

        switch ($eventType) {
            case '1':
                return $baseUrl . '/site/index.php?page=accueil&cod=' . $eventId;
            case '2':
                return $baseUrl . '/site/anniversaire/index.php?page=accueil&cod=' . $eventId;
            case '12':
                return $baseUrl . '/site/vernisage/index.php?page=accueil&cod=' . $eventId;
            case '3':
            case '4':
            case '5':
            case '13':
                return $baseUrl . '/site/conference/index.php?page=accueil&cod=' . $eventId;
            default:
                // Tout type sans site dedie bascule vers le template conference (type_event=5).
                return $baseUrl . '/site/conference/index.php?page=accueil&cod=' . $eventId;
        }
    }
}