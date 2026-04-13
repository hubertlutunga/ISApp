<?php

final class EventUpdateService
{
    public static function findEventById(PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM events WHERE cod_event = ?');
        $stmt->execute([$eventId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public static function buildEditContext(PDO $pdo, int $eventId): array
    {
        $event = self::findEventById($pdo, $eventId);
        $typeEvent = (string) ($event['type_event'] ?? '');

        return [
            'event' => $event,
            'type_event' => $typeEvent,
            'event_label' => self::findEventTypeLabel($pdo, $typeEvent),
        ];
    }

    public static function updateFromRequest(PDO $pdo, int $eventId, array $request): void
    {
        $payload = self::normalizeRequest($request);

        $columns = [
            'type_event = ?',
            'date_event = ?',
            'lieu = ?',
            'adresse = ?',
            'autres_precisions = ?',
            'initiale_mar = ?',
        ];
        $values = [
            $payload['type_event'],
            $payload['date_event'],
            $payload['lieu'],
            $payload['adresse'],
            $payload['autres_precisions'],
            $payload['initiale_mar'],
        ];

        if ($payload['type_event'] === '1') {
            $columns = array_merge($columns, [
                'type_mar = ?',
                'prenom_epoux = ?',
                'nom_epoux = ?',
                'prenom_epouse = ?',
                'nom_epouse = ?',
            ]);
            $values = array_merge($values, [
                $payload['type_mar'],
                $payload['prenom_epoux'],
                $payload['nom_epoux'],
                $payload['prenom_epouse'],
                $payload['nom_epouse'],
            ]);
        }

        if ($payload['type_event'] === '2') {
            $columns[] = 'nomfetard = ?';
            $values[] = $payload['nomfetard'];
        }

        if ($payload['type_event'] === '3') {
            $columns[] = 'themeconf = ?';
            $values[] = $payload['themeconf'];
        }

        $values[] = $eventId;

        $sql = 'UPDATE events SET ' . implode(', ', $columns) . ' WHERE cod_event = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }

    public static function buildInitialeFromRequest(array $request): ?string
    {
        $typeEvent = trim((string) ($request['event'] ?? ''));
        $prenomEpoux = self::nullableString($request['prenomEpoux'] ?? null);
        $prenomEpouse = self::nullableString($request['prenomEpouse'] ?? null);
        $nomsFetard = self::nullableString($request['nomsfetard'] ?? null);
        $themeConf = self::nullableString($request['themeConf'] ?? null);

        return self::buildInitiale($typeEvent, $prenomEpoux, $prenomEpouse, $nomsFetard, $themeConf);
    }

    public static function updateInvitationTemplate(PDO $pdo, int $eventId, array $settings): void
    {
        $columnMap = [
            'invit_religieux' => $settings['invit_religieux'] ?? null,
            'ajustenom' => self::nullableString($settings['ajustenom'] ?? null),
            'taillenominv' => self::nullableString($settings['taillenominv'] ?? null),
            'alignnominv' => self::nullableString($settings['alignnominv'] ?? null),
            'pagenom' => self::nullableString($settings['pagenom'] ?? null),
            'pagebouton' => self::nullableString($settings['pagebouton'] ?? null),
            'colornom' => self::nullableString($settings['colornom'] ?? null),
            'bordgauchenominv' => self::nullableString($settings['bordgauchenominv'] ?? null),
            'qrcode' => self::nullableString($settings['qrcode'] ?? null),
            'pageqr' => self::nullableString($settings['pageqr'] ?? null),
            'hautqr' => self::nullableString($settings['hautqr'] ?? null),
            'gaucheqr' => self::nullableString($settings['gaucheqr'] ?? null),
            'tailleqr' => self::nullableString($settings['tailleqr'] ?? null),
            'lang' => self::nullableString($settings['lang'] ?? null),
        ];

        $existingColumns = self::findExistingEventColumns($pdo, array_keys($columnMap));
        $assignments = [];
        $values = [];

        foreach ($columnMap as $column => $value) {
            if (!in_array($column, $existingColumns, true)) {
                continue;
            }

            $assignments[] = $column . ' = ?';
            $values[] = $value;
        }

        if ($assignments === []) {
            return;
        }

        $values[] = $eventId;

        $stmt = $pdo->prepare('UPDATE events SET ' . implode(', ', $assignments) . ' WHERE cod_event = ?');
        $stmt->execute($values);
    }

    private static function findExistingEventColumns(PDO $pdo, array $columns): array
    {
        static $cache = null;

        if ($cache === null) {
            $stmt = $pdo->query('SHOW COLUMNS FROM events');
            $cache = array_map(
                static fn(array $row): string => (string) ($row['Field'] ?? ''),
                $stmt->fetchAll(PDO::FETCH_ASSOC)
            );
        }

        return array_values(array_intersect($cache, $columns));
    }

    private static function normalizeRequest(array $request): array
    {
        $typeEvent = trim((string) ($request['event'] ?? ''));
        $prenomEpoux = self::nullableString($request['prenomEpoux'] ?? null);
        $prenomEpouse = self::nullableString($request['prenomEpouse'] ?? null);
        $nomsFetard = self::nullableString($request['nomsfetard'] ?? null);
        $themeConf = self::nullableString($request['themeConf'] ?? null);

        return [
            'type_event' => $typeEvent,
            'type_mar' => self::nullableString($request['weddingType'] ?? null),
            'date_event' => self::nullableString($request['dateHeure'] ?? null),
            'lieu' => self::nullableString($request['lieu'] ?? null),
            'adresse' => self::nullableString($request['adresse'] ?? null),
            'prenom_epoux' => $prenomEpoux,
            'nom_epoux' => self::nullableString($request['nomEpoux'] ?? null),
            'prenom_epouse' => $prenomEpouse,
            'nom_epouse' => self::nullableString($request['nomEpouse'] ?? null),
            'nomfetard' => $nomsFetard,
            'themeconf' => $themeConf,
            'autres_precisions' => self::nullableString($request['details'] ?? null),
            'initiale_mar' => self::buildInitiale($typeEvent, $prenomEpoux, $prenomEpouse, $nomsFetard, $themeConf),
        ];
    }

    private static function buildInitiale(
        string $typeEvent,
        ?string $prenomEpoux,
        ?string $prenomEpouse,
        ?string $nomsFetard,
        ?string $themeConf
    ): ?string {
        if ($typeEvent === '1') {
            $initialeEpouse = self::firstCharacter($prenomEpouse);
            $initialeEpoux = self::firstCharacter($prenomEpoux);

            if ($initialeEpouse === null && $initialeEpoux === null) {
                return null;
            }

            return ($initialeEpouse ?? '') . '&' . ($initialeEpoux ?? '');
        }

        if ($typeEvent === '2') {
            return $nomsFetard;
        }

        if ($typeEvent === '3') {
            return $nomsFetard ?? $themeConf;
        }

        return $nomsFetard ?? $themeConf;
    }

    private static function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function firstCharacter(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return function_exists('mb_substr') ? mb_substr($value, 0, 1) : substr($value, 0, 1);
    }

    private static function findEventTypeLabel(PDO $pdo, string $typeEvent): string
    {
        static $cache = [];

        if ($typeEvent === '') {
            return '';
        }

        if (!array_key_exists($typeEvent, $cache)) {
            $stmt = $pdo->prepare('SELECT nom FROM evenement WHERE cod_event = ?');
            $stmt->execute([$typeEvent]);
            $cache[$typeEvent] = (string) ($stmt->fetchColumn() ?: '');
        }

        return $cache[$typeEvent];
    }
}