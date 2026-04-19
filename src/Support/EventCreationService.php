<?php

final class EventCreationService
{
    private static ?bool $accessoiresEventHasQuantite = null;

    public static function createManagedEvent(
        PDO $pdo,
        array $eventData,
        array $accessoires,
        ?array $photos,
        string $photoTargetDir,
        array $config
    ): int {
        $eventUserId = self::normalizeUserId($eventData['cod_user'] ?? null);

        $sql = "INSERT INTO events (
            cod_user, type_event, type_mar, modele_inv, modele_chev, date_event, lieu, adresse,
            prenom_epoux, nom_epoux, prenom_epouse, nom_epouse,
            nom_familleepoux, nom_familleepouse, ordrepri, nomfetard, themeconf,
            autres_precisions, initiale_mar, ajustenom, taillenominv, alignnominv, pagenom, pagebouton, date_enreg, pageqr, hautqr, gaucheqr, tailleqr, version
            , lang
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $eventUserId,
            $eventData['type_event'] ?? null,
            $eventData['type_mar'] ?? null,
            $eventData['modele_inv'] ?? null,
            $eventData['modele_chev'] ?? null,
            $eventData['date_event'] ?? null,
            $eventData['lieu'] ?? null,
            $eventData['adresse'] ?? null,
            $eventData['prenom_epoux'] ?? null,
            $eventData['nom_epoux'] ?? null,
            $eventData['prenom_epouse'] ?? null,
            $eventData['nom_epouse'] ?? null,
            $eventData['nom_familleepoux'] ?? null,
            $eventData['nom_familleepouse'] ?? null,
            $eventData['ordrepri'] ?? null,
            $eventData['nomfetard'] ?? null,
            $eventData['themeconf'] ?? null,
            $eventData['autres_precisions'] ?? null,
            $eventData['initiale_mar'] ?? null,
            $eventData['ajustenom'] ?? '35',
            $eventData['taillenominv'] ?? '20',
            $eventData['alignnominv'] ?? 'center',
            $eventData['pagenom'] ?? '2',
            $eventData['pagebouton'] ?? '6',
            $eventData['pageqr'] ?? '3',
            $eventData['hautqr'] ?? '18',
            $eventData['gaucheqr'] ?? '52',
            $eventData['tailleqr'] ?? '90',
            $eventData['version'] ?? 'N',
            $eventData['lang'] ?? null,
        ]);

        $eventId = (int) $pdo->lastInsertId();

        self::persistShortLink($pdo, $eventId, $eventData, $config);
        self::persistAccessoires($pdo, $eventId, $accessoires, true, (string) ($eventData['modele_inv'] ?? ''), (array) ($eventData['accessoire_quantities'] ?? []));
        EventOrderService::persistOrderMetadata(
            $pdo,
            $eventId,
            (array) ($eventData['invitation_models'] ?? []),
            (array) ($eventData['checkout'] ?? [])
        );
        self::persistPhotos($pdo, $eventId, $photos, $photoTargetDir, (string) ($eventData['initiale_mar'] ?? 'IS'));

        return $eventId;
    }

    public static function createLegacyEvent(
        PDO $pdo,
        array $eventData,
        array $accessoires,
        ?array $photos,
        string $photoTargetDir,
        array $config
    ): int {
        $eventUserId = self::normalizeUserId($eventData['cod_user'] ?? null);

        $sql = "INSERT INTO events (
            cod_user, type_event, type_mar, modele_inv, modele_chev, date_event, lieu, adresse,
            prenom_epoux, nom_epoux, prenom_epouse, nom_epouse,
            nom_familleepoux, nom_familleepouse, ordrepri, nomfetard, themeconf,
            autres_precisions, initiale_mar, date_enreg, lang
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $eventUserId,
            $eventData['type_event'] ?? null,
            $eventData['type_mar'] ?? null,
            $eventData['modele_inv'] ?? null,
            $eventData['modele_chev'] ?? null,
            $eventData['date_event'] ?? null,
            $eventData['lieu'] ?? null,
            $eventData['adresse'] ?? null,
            $eventData['prenom_epoux'] ?? null,
            $eventData['nom_epoux'] ?? null,
            $eventData['prenom_epouse'] ?? null,
            $eventData['nom_epouse'] ?? null,
            $eventData['nom_familleepoux'] ?? null,
            $eventData['nom_familleepouse'] ?? null,
            $eventData['ordrepri'] ?? null,
            $eventData['nomfetard'] ?? null,
            $eventData['themeconf'] ?? null,
            $eventData['autres_precisions'] ?? null,
            $eventData['initiale_mar'] ?? null,
            $eventData['lang'] ?? null,
        ]);

        $eventId = (int) $pdo->lastInsertId();

        self::persistShortLink($pdo, $eventId, $eventData, $config);
        self::persistAccessoires($pdo, $eventId, $accessoires, false, null, (array) ($eventData['accessoire_quantities'] ?? []));
        EventOrderService::persistOrderMetadata(
            $pdo,
            $eventId,
            (array) ($eventData['invitation_models'] ?? []),
            (array) ($eventData['checkout'] ?? [])
        );
        self::persistPhotos($pdo, $eventId, $photos, $photoTargetDir, (string) ($eventData['initiale_mar'] ?? 'IS'));

        return $eventId;
    }

    private static function normalizeUserId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $userId = (int) $value;

        return $userId > 0 ? $userId : null;
    }

    private static function persistShortLink(PDO $pdo, int $eventId, array $eventData, array $config): void
    {
        $payload = [
            'cod_event' => $eventId,
            'type_event' => $eventData['type_event'] ?? null,
            'prenom_epoux' => $eventData['prenom_epoux'] ?? null,
            'prenom_epouse' => $eventData['prenom_epouse'] ?? null,
            'nomfetard' => $eventData['nomfetard'] ?? null,
        ];

        $stmt = $pdo->prepare('INSERT INTO url_shortener (short_code, long_url, cod_event, date_enreg) VALUES (:short_code, :long_url, :cod_event, NOW())');
        $stmt->execute([
            'short_code' => ShortUrlService::buildShortCode($payload, $config),
            'long_url' => EventUrlService::publicUrl($payload, $config),
            'cod_event' => $eventId,
        ]);
    }

    private static function persistAccessoires(PDO $pdo, int $eventId, array $accessoires, bool $withModel, ?string $modeleInv, array $quantities = []): void
    {
        if (empty($accessoires)) {
            return;
        }

        $hasQuantiteColumn = self::accessoiresEventHasQuantiteColumn($pdo);

        if ($withModel) {
            $stmt = $hasQuantiteColumn
                ? $pdo->prepare('INSERT INTO accessoires_event (cod_event, cod_acc, modele_acc, quantite) VALUES (?, ?, ?, ?)')
                : $pdo->prepare('INSERT INTO accessoires_event (cod_event, cod_acc, modele_acc) VALUES (?, ?, ?)');
            foreach ($accessoires as $accessoire) {
                $quantite = self::resolveAccessoireQuantity($accessoire, $quantities);
                $params = $hasQuantiteColumn
                    ? [$eventId, $accessoire, $modeleInv, $quantite]
                    : [$eventId, $accessoire, $modeleInv];
                $stmt->execute($params);
            }
            return;
        }

        $stmt = $hasQuantiteColumn
            ? $pdo->prepare('INSERT INTO accessoires_event (cod_event, cod_acc, quantite) VALUES (?, ?, ?)')
            : $pdo->prepare('INSERT INTO accessoires_event (cod_event, cod_acc) VALUES (?, ?)');
        foreach ($accessoires as $accessoire) {
            $quantite = self::resolveAccessoireQuantity($accessoire, $quantities);
            $params = $hasQuantiteColumn
                ? [$eventId, $accessoire, $quantite]
                : [$eventId, $accessoire];
            $stmt->execute($params);
        }
    }

    private static function resolveAccessoireQuantity(mixed $accessoire, array $quantities): int
    {
        $rawQuantity = $quantities[(string) $accessoire] ?? 1;
        $quantity = (int) $rawQuantity;

        return $quantity > 0 ? $quantity : 1;
    }

    private static function accessoiresEventHasQuantiteColumn(PDO $pdo): bool
    {
        if (self::$accessoiresEventHasQuantite !== null) {
            return self::$accessoiresEventHasQuantite;
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM accessoires_event LIKE 'quantite'");
        self::$accessoiresEventHasQuantite = (bool) $stmt->fetch();

        return self::$accessoiresEventHasQuantite;
    }

    private static function persistPhotos(PDO $pdo, int $eventId, ?array $photos, string $photoTargetDir, string $prefix): void
    {
        EventMediaService::storeEventPhotos($pdo, $eventId, $photos, $photoTargetDir, $prefix);
    }
}