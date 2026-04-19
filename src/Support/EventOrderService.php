<?php

final class EventOrderService
{
    private const DEFAULT_CURRENCY = 'USD';

    private const DEFAULT_ACCESSORY_CATALOG = [
        '1' => ['label' => 'Invitation imprimée', 'unit_price' => 2.70, 'quantity_mode' => 'variable'],
        '2' => ['label' => 'Invitation électronique', 'unit_price' => 85.00, 'quantity_mode' => 'fixed'],
        '3' => ['label' => 'Chevalet de table', 'unit_price' => 2.10, 'quantity_mode' => 'variable'],
    ];

    private const DEFAULT_PROMO_CODES = [
        ['code' => 'ISWELCOME', 'label' => 'Bienvenue', 'value' => 10.0],
        ['code' => 'MARIAGE5', 'label' => 'Special mariage', 'value' => 5.0],
    ];

    private static ?bool $orderTablesEnsured = null;
    private static ?bool $modelCatalogColumnsEnsured = null;
    private static ?bool $promoCodeTableEnsured = null;

    public static function accessoryCatalog(PDO $pdo): array
    {
        self::ensureModelCatalogColumns($pdo);

        $catalog = self::DEFAULT_ACCESSORY_CATALOG;

        $stmt = $pdo->query(
            'SELECT libelle, AVG(CAST(TRIM(pu) AS DECIMAL(10,2))) AS avg_pu
             FROM details_fact
             WHERE TRIM(COALESCE(pu, "")) <> "" AND CAST(TRIM(pu) AS DECIMAL(10,2)) > 0
             GROUP BY libelle'
        );

        $pricesByLabel = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $label = trim((string) ($row['libelle'] ?? ''));
            $price = isset($row['avg_pu']) ? (float) $row['avg_pu'] : 0.0;
            if ($label !== '' && $price > 0) {
                $pricesByLabel[$label] = round($price, 2);
            }
        }

        $accessoryStmt = $pdo->prepare('SELECT cod_mod, nom, unit_price FROM modele_is WHERE type_mod = :type_mod AND is_active = 1');
        $accessoryStmt->execute([':type_mod' => 'accessoires']);

        foreach ($accessoryStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $code = (string) ($row['cod_mod'] ?? '');
            $label = trim((string) ($row['nom'] ?? ''));

            if ($code === '' || $label === '') {
                continue;
            }

            if (!isset($catalog[$code])) {
                $catalog[$code] = [
                    'label' => $label,
                    'unit_price' => 0.0,
                    'quantity_mode' => 'variable',
                ];
            }

            if (isset($pricesByLabel[$label])) {
                $catalog[$code]['unit_price'] = $pricesByLabel[$label];
            }

            if (isset($row['unit_price']) && $row['unit_price'] !== null && $row['unit_price'] !== '') {
                $catalog[$code]['unit_price'] = round((float) $row['unit_price'], 2);
            }

            $catalog[$code]['label'] = $label;
        }

        return $catalog;
    }

    public static function promoCatalog(?PDO $pdo = null): array
    {
        if ($pdo === null) {
            $fallback = [];
            foreach (self::DEFAULT_PROMO_CODES as $promoCode) {
                $fallback[(string) $promoCode['code']] = [
                    'label' => (string) $promoCode['label'],
                    'type' => 'percent',
                    'value' => (float) $promoCode['value'],
                ];
            }

            return $fallback;
        }

        self::ensurePromoCodeTable($pdo);

        $stmt = $pdo->query(
            'SELECT code, label, reduction_percent
             FROM promo_codes
             WHERE is_active = 1
             ORDER BY code ASC'
        );

        $catalog = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $code = strtoupper(trim((string) ($row['code'] ?? '')));
            if ($code === '') {
                continue;
            }

            $catalog[$code] = [
                'label' => trim((string) ($row['label'] ?? $code)),
                'type' => 'percent',
                'value' => round((float) ($row['reduction_percent'] ?? 0), 2),
            ];
        }

        return $catalog;
    }

    public static function ensureCatalogInfrastructure(PDO $pdo): void
    {
        self::ensureOrderTables($pdo);
        self::ensureModelCatalogColumns($pdo);
        self::ensurePromoCodeTable($pdo);
    }

    public static function loadInvitationModelsByEvent(PDO $pdo, int $eventId): array
    {
        self::ensureOrderTables($pdo);

        if ($eventId <= 0) {
            return [];
        }

        $stmt = $pdo->prepare(
            'SELECT eim.cod_mod, eim.quantite, eim.unit_price, mi.nom, mi.image, mi.unit_price AS current_unit_price
             FROM event_invitation_models eim
             LEFT JOIN modele_is mi ON mi.cod_mod = eim.cod_mod
             WHERE eim.cod_event = ?
             ORDER BY eim.cod_eim ASC'
        );
        $stmt->execute([$eventId]);

        $models = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $unitPrice = $row['unit_price'];
            if ($unitPrice === null || $unitPrice === '') {
                $unitPrice = $row['current_unit_price'] ?? 0;
            }

            $quantity = max(1, (int) ($row['quantite'] ?? 1));
            $row['unit_price'] = round((float) $unitPrice, 2);
            $row['line_total'] = round($row['unit_price'] * $quantity, 2);
            $models[] = $row;
        }

        return $models;
    }

    public static function loadCheckoutByEvent(PDO $pdo, int $eventId): array
    {
        self::ensureOrderTables($pdo);

        if ($eventId <= 0) {
            return [];
        }

        $stmt = $pdo->prepare('SELECT * FROM event_checkout WHERE cod_event = ? LIMIT 1');
        $stmt->execute([$eventId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public static function listCatalogModels(PDO $pdo, ?string $typeMod = null): array
    {
        self::ensureModelCatalogColumns($pdo);

        $sql = 'SELECT cod_mod, nom, image, type_mod, unit_price, is_active FROM modele_is';
        $params = [];

        if ($typeMod !== null) {
            $sql .= ' WHERE type_mod = :type_mod';
            $params[':type_mod'] = $typeMod;
        }

        $sql .= ' ORDER BY type_mod ASC, nom ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function listPromoCodes(PDO $pdo): array
    {
        self::ensurePromoCodeTable($pdo);

        $stmt = $pdo->query(
            'SELECT cod_promo, code, label, reduction_percent, is_active, created_at, updated_at
             FROM promo_codes
             ORDER BY updated_at DESC, cod_promo DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function upsertPromoCode(PDO $pdo, array $payload): int
    {
        self::ensurePromoCodeTable($pdo);

        $id = isset($payload['cod_promo']) ? (int) $payload['cod_promo'] : 0;
        $code = strtoupper(trim((string) ($payload['code'] ?? '')));
        $label = trim((string) ($payload['label'] ?? ''));
        $percentRaw = trim((string) ($payload['reduction_percent'] ?? ''));
        $isActive = isset($payload['is_active']) ? (int) $payload['is_active'] : 1;

        if ($code === '') {
            throw new RuntimeException('Le code promo est obligatoire.');
        }

        if (!preg_match('/^[A-Z0-9_-]+$/', $code)) {
            throw new RuntimeException('Le code promo ne peut contenir que des lettres, chiffres, tirets et underscores.');
        }

        $percent = round((float) str_replace(',', '.', $percentRaw), 2);
        if ($percent <= 0 || $percent > 100) {
            throw new RuntimeException('Le pourcentage de reduction doit etre compris entre 0.01 et 100.');
        }

        if ($label === '') {
            $label = $code;
        }

        if ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE promo_codes
                 SET code = ?, label = ?, reduction_percent = ?, is_active = ?, updated_at = NOW()
                 WHERE cod_promo = ?'
            );
            $stmt->execute([$code, $label, $percent, $isActive === 1 ? 1 : 0, $id]);

            return $id;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO promo_codes (code, label, reduction_percent, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$code, $label, $percent, $isActive === 1 ? 1 : 0]);

        return (int) $pdo->lastInsertId();
    }

    public static function deletePromoCode(PDO $pdo, int $promoId): void
    {
        self::ensurePromoCodeTable($pdo);

        if ($promoId <= 0) {
            return;
        }

        $stmt = $pdo->prepare('DELETE FROM promo_codes WHERE cod_promo = ?');
        $stmt->execute([$promoId]);
    }

    public static function upsertCatalogModel(PDO $pdo, array $payload, ?array $imageFile = null, string $imageTargetDir = '../images/modeleis'): int
    {
        self::ensureModelCatalogColumns($pdo);

        $id = isset($payload['cod_mod']) ? (int) $payload['cod_mod'] : 0;
        $name = trim((string) ($payload['nom'] ?? ''));
        $typeMod = trim((string) ($payload['type_mod'] ?? ''));
        $unitPriceRaw = trim((string) ($payload['unit_price'] ?? ''));
        $isActive = isset($payload['is_active']) ? (int) $payload['is_active'] : 1;

        if ($name === '' || $typeMod === '') {
            throw new RuntimeException('Le nom et le type sont obligatoires.');
        }

        if (!in_array($typeMod, ['accessoires', 'invitation', 'chevalet'], true)) {
            throw new RuntimeException('Type de modele invalide.');
        }

        $unitPrice = null;
        if ($unitPriceRaw !== '') {
            $unitPrice = round((float) str_replace(',', '.', $unitPriceRaw), 2);
        }

        $imageName = null;
        if ($imageFile !== null) {
            $imageName = EventMediaService::storeUploadedImage($imageFile, $imageTargetDir, 'catalog_');
        }

        if ($id > 0) {
            $fields = ['nom = ?', 'type_mod = ?', 'unit_price = ?', 'is_active = ?'];
            $values = [$name, $typeMod, $unitPrice, $isActive === 1 ? 1 : 0];

            if ($imageName !== null) {
                $fields[] = 'image = ?';
                $values[] = $imageName;
            }

            $values[] = $id;

            $stmt = $pdo->prepare('UPDATE modele_is SET ' . implode(', ', $fields) . ' WHERE cod_mod = ?');
            $stmt->execute($values);

            return $id;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO modele_is (nom, image, type_mod, unit_price, is_active) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $name,
            $imageName ?? '',
            $typeMod,
            $unitPrice,
            $isActive === 1 ? 1 : 0,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function paymentOptions(): array
    {
        return [
            'mobile_money' => 'Mobile Money',
            'virement' => 'Virement bancaire',
            'carte' => 'Carte bancaire',
            'especes' => 'Espèces',
        ];
    }

    public static function paymentLabel(?string $paymentType): string
    {
        $options = self::paymentOptions();
        $key = trim((string) $paymentType);

        if ($key !== '' && isset($options[$key])) {
            return $options[$key];
        }

        return $key !== '' ? ucfirst(str_replace('_', ' ', $key)) : 'Non renseigne';
    }

    public static function normalizeInvitationModels(array $selectedModelIds, array $quantities, bool $requiresQuantity): array
    {
        $normalized = [];

        foreach ($selectedModelIds as $modelId) {
            $modelKey = (string) $modelId;
            $modelIdInt = (int) $modelId;

            if ($modelIdInt <= 0 || isset($normalized[$modelKey])) {
                continue;
            }

            $quantity = $requiresQuantity ? (int) ($quantities[$modelKey] ?? 1) : 1;
            $normalized[$modelKey] = [
                'model_id' => $modelIdInt,
                'quantity' => $quantity > 0 ? $quantity : 1,
            ];
        }

        return array_values($normalized);
    }

    public static function hydrateInvitationModels(array $normalizedModels, array $catalogRows): array
    {
        $catalogById = [];
        foreach ($catalogRows as $row) {
            $catalogById[(string) ($row['cod_mod'] ?? '')] = $row;
        }

        $hydrated = [];
        foreach ($normalizedModels as $model) {
            $modelId = (string) ($model['model_id'] ?? '');
            if ($modelId === '') {
                continue;
            }

            $catalogRow = $catalogById[$modelId] ?? [];
            $hydrated[] = [
                'model_id' => (int) $modelId,
                'quantity' => max(1, (int) ($model['quantity'] ?? 1)),
                'label' => trim((string) ($catalogRow['nom'] ?? 'Modele')),
                'unit_price' => round((float) ($catalogRow['unit_price'] ?? 0), 2),
                'image' => (string) ($catalogRow['image'] ?? ''),
            ];
        }

        return $hydrated;
    }

    public static function summarizeSelection(
        array $selectedAccessories,
        array $accessoryQuantities,
        array $invitationModels,
        array $catalog,
        array $promoCatalog,
        string $promoCode
    ): array {
        $lines = [];
        $subtotal = 0.0;
        $selectedAccessoryMap = array_fill_keys(array_map('strval', $selectedAccessories), true);
        $invitationModelCount = count($invitationModels);

        foreach ($selectedAccessories as $accessoryId) {
            $accessoryKey = (string) $accessoryId;
            $accessory = $catalog[$accessoryKey] ?? [
                'label' => 'Accessoire',
                'unit_price' => 0.0,
                'quantity_mode' => 'variable',
            ];

            if ($accessoryKey === '1' && $invitationModelCount > 0) {
                foreach ($invitationModels as $model) {
                    $quantity = max(1, (int) ($model['quantity'] ?? 1));
                    $unitPrice = round((float) ($model['unit_price'] ?? 0), 2);
                    $lineTotal = round($unitPrice * $quantity, 2);
                    $subtotal += $lineTotal;

                    $lines[] = [
                        'accessory_id' => $accessoryKey,
                        'model_id' => (int) ($model['model_id'] ?? 0),
                        'label' => (string) ($accessory['label'] ?? 'Invitation imprimée'),
                        'model_label' => trim((string) ($model['label'] ?? 'Modele')),
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                        'models_count' => $invitationModelCount,
                    ];
                }

                continue;
            }

            $quantity = (int) ($accessoryQuantities[$accessoryKey] ?? 1);
            if (($accessory['quantity_mode'] ?? 'variable') === 'fixed') {
                $quantity = 1;
            }

            if ($quantity < 1) {
                $quantity = 1;
            }

            $unitPrice = (float) ($accessory['unit_price'] ?? 0.0);
            $lineTotal = round($unitPrice * $quantity, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'accessory_id' => $accessoryKey,
                'label' => (string) ($accessory['label'] ?? 'Accessoire'),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'models_count' => $accessoryKey === '1' ? $invitationModelCount : 0,
            ];
        }

        $promo = self::resolvePromo($promoCatalog, $promoCode, $subtotal);

        return [
            'currency' => self::DEFAULT_CURRENCY,
            'lines' => $lines,
            'subtotal' => round($subtotal, 2),
            'discount_amount' => $promo['discount_amount'],
            'promo_code' => $promo['promo_code'],
            'promo_label' => $promo['promo_label'],
            'promo_type' => $promo['promo_type'],
            'promo_value' => $promo['promo_value'],
            'total' => round(max(0, $subtotal - $promo['discount_amount']), 2),
            'has_printed_invitation' => isset($selectedAccessoryMap['1']),
        ];
    }

    public static function buildInvoiceLinesForEvent(PDO $pdo, int $eventId): array
    {
        self::ensureCatalogInfrastructure($pdo);

        if ($eventId <= 0) {
            return [];
        }

        $accessoryCatalog = self::accessoryCatalog($pdo);
        $invitationModels = self::loadInvitationModelsByEvent($pdo, $eventId);
        $accessoryStmt = $pdo->prepare(
            'SELECT ae.cod_accev, ae.cod_acc, ae.quantite, mi.nom
             FROM accessoires_event ae
             LEFT JOIN modele_is mi ON mi.cod_mod = ae.cod_acc
             WHERE ae.cod_event = ?
             ORDER BY ae.cod_accev DESC'
        );
        $accessoryStmt->execute([$eventId]);

        $lines = [];
        foreach ($accessoryStmt->fetchAll(PDO::FETCH_ASSOC) as $accessoryRow) {
            $accessoryId = (string) ($accessoryRow['cod_acc'] ?? '');
            $catalogItem = $accessoryCatalog[$accessoryId] ?? [
                'label' => trim((string) ($accessoryRow['nom'] ?? 'Accessoire')),
                'unit_price' => 0.0,
                'quantity_mode' => 'variable',
            ];

            if ($accessoryId === '1' && $invitationModels !== []) {
                foreach ($invitationModels as $invitationModel) {
                    $quantity = max(1, (int) ($invitationModel['quantite'] ?? 1));
                    $unitPrice = round((float) ($invitationModel['unit_price'] ?? 0), 2);
                    $label = trim('Invitation imprimée (' . (string) ($invitationModel['nom'] ?? 'Modele') . ')');

                    $lines[] = [
                        'label' => $label,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => round($quantity * $unitPrice, 2),
                        'accessory_id' => $accessoryId,
                    ];
                }

                continue;
            }

            $quantity = ($catalogItem['quantity_mode'] ?? 'variable') === 'fixed'
                ? 1
                : max(1, (int) ($accessoryRow['quantite'] ?? 1));
            $unitPrice = round((float) ($catalogItem['unit_price'] ?? 0), 2);

            $lines[] = [
                'label' => trim((string) ($catalogItem['label'] ?? 'Accessoire')),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($quantity * $unitPrice, 2),
                'accessory_id' => $accessoryId,
            ];
        }

        return $lines;
    }

    public static function buildInvoiceSummaryForEvent(PDO $pdo, int $eventId): array
    {
        $lines = self::buildInvoiceLinesForEvent($pdo, $eventId);
        $subtotal = array_reduce(
            $lines,
            static fn(float $carry, array $line): float => $carry + (float) ($line['line_total'] ?? 0),
            0.0
        );
        $checkout = self::loadCheckoutByEvent($pdo, $eventId);
        $currency = (string) ($checkout['devise'] ?? self::DEFAULT_CURRENCY);
        $promoCode = strtoupper(trim((string) ($checkout['promo_code'] ?? '')));
        $promoLabel = self::nullableString($checkout['promo_label'] ?? null);
        $promoType = self::nullableString($checkout['reduction_type'] ?? null);
        $promoValue = isset($checkout['reduction_value']) ? (float) $checkout['reduction_value'] : 0.0;
        $discountAmount = 0.0;

        if ($subtotal > 0 && $promoCode !== '' && $promoType !== null && $promoValue > 0) {
            $discountAmount = $promoType === 'fixed'
                ? min($subtotal, round($promoValue, 2))
                : min($subtotal, round($subtotal * ($promoValue / 100), 2));
        } elseif ($subtotal > 0 && $promoCode !== '') {
            $resolvedPromo = self::resolvePromo(self::promoCatalog($pdo), $promoCode, $subtotal);
            $promoLabel = self::nullableString($resolvedPromo['promo_label'] ?? null);
            $promoType = self::nullableString($resolvedPromo['promo_type'] ?? null);
            $promoValue = (float) ($resolvedPromo['promo_value'] ?? 0.0);
            $discountAmount = (float) ($resolvedPromo['discount_amount'] ?? 0.0);
        } elseif ($subtotal > 0 && isset($checkout['reduction_amount']) && (float) $checkout['reduction_amount'] > 0) {
            $discountAmount = min($subtotal, round((float) $checkout['reduction_amount'], 2));
        }

        $discountAmount = round($discountAmount, 2);

        return [
            'lines' => $lines,
            'currency' => $currency,
            'promo_code' => $promoCode,
            'promo_label' => $promoLabel,
            'promo_type' => $promoType,
            'promo_value' => round($promoValue, 2),
            'subtotal' => round($subtotal, 2),
            'discount_amount' => $discountAmount,
            'total' => round(max(0, $subtotal - $discountAmount), 2),
        ];
    }

    public static function updateCheckoutPromoCode(PDO $pdo, int $eventId, ?string $promoCode): array
    {
        self::ensureOrderTables($pdo);

        if ($eventId <= 0) {
            throw new RuntimeException('Evenement invalide.');
        }

        $lines = self::buildInvoiceLinesForEvent($pdo, $eventId);
        $subtotal = array_reduce(
            $lines,
            static fn(float $carry, array $line): float => $carry + (float) ($line['line_total'] ?? 0),
            0.0
        );
        $checkout = self::loadCheckoutByEvent($pdo, $eventId);
        $currency = (string) ($checkout['devise'] ?? self::DEFAULT_CURRENCY);
        $paymentType = self::nullableString($checkout['type_paiement'] ?? null);
        $normalizedPromoCode = strtoupper(trim((string) $promoCode));

        $promo = self::resolvePromo(self::promoCatalog($pdo), $normalizedPromoCode, $subtotal);
        if ($normalizedPromoCode !== '' && ($promo['promo_type'] === null || (float) ($promo['promo_value'] ?? 0) <= 0)) {
            throw new RuntimeException('Le code promo est introuvable ou inactif.');
        }

        $payload = [
            $normalizedPromoCode !== '' ? $promo['promo_code'] : null,
            $normalizedPromoCode !== '' ? self::nullableString($promo['promo_label'] ?? null) : null,
            $normalizedPromoCode !== '' ? self::nullableString($promo['promo_type'] ?? null) : null,
            $normalizedPromoCode !== '' ? (float) ($promo['promo_value'] ?? 0.0) : 0.0,
            $normalizedPromoCode !== '' ? (float) ($promo['discount_amount'] ?? 0.0) : 0.0,
            round($subtotal, 2),
            round(max(0, $subtotal - (float) ($promo['discount_amount'] ?? 0.0)), 2),
            $currency,
            $paymentType,
        ];

        if ($checkout !== []) {
            $stmt = $pdo->prepare(
                'UPDATE event_checkout
                 SET promo_code = ?, promo_label = ?, reduction_type = ?, reduction_value = ?, reduction_amount = ?,
                     subtotal = ?, total = ?, devise = ?, type_paiement = ?, updated_at = NOW()
                 WHERE cod_event = ?'
            );
            $stmt->execute([...$payload, $eventId]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO event_checkout (
                    cod_event, promo_code, promo_label, reduction_type, reduction_value,
                    reduction_amount, subtotal, total, devise, type_paiement, date_enreg, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
            );
            $stmt->execute([$eventId, ...$payload]);
        }

        return self::buildInvoiceSummaryForEvent($pdo, $eventId);
    }

    public static function replaceDetailsFactForEvent(PDO $pdo, int $eventId): array
    {
        $lines = self::buildInvoiceLinesForEvent($pdo, $eventId);

        $deleteDetails = $pdo->prepare('DELETE FROM details_fact WHERE cod_event = ?');
        $deleteDetails->execute([$eventId]);

        if ($lines === []) {
            return [];
        }

        $insertDetail = $pdo->prepare(
            'INSERT INTO details_fact (cod_event, libelle, qtecom, pu, pt, date_enreg)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );

        foreach ($lines as $line) {
            $insertDetail->execute([
                $eventId,
                (string) ($line['label'] ?? 'Accessoire'),
                max(1, (int) ($line['quantity'] ?? 1)),
                round((float) ($line['unit_price'] ?? 0), 2),
                round((float) ($line['line_total'] ?? 0), 2),
            ]);
        }

        return $lines;
    }

    public static function persistOrderMetadata(PDO $pdo, int $eventId, array $invitationModels, array $checkout): void
    {
        self::ensureOrderTables($pdo);

        $deleteModels = $pdo->prepare('DELETE FROM event_invitation_models WHERE cod_event = ?');
        $deleteModels->execute([$eventId]);

        if ($invitationModels !== []) {
            $insertModel = $pdo->prepare(
                'INSERT INTO event_invitation_models (cod_event, cod_mod, quantite, unit_price, date_enreg) VALUES (?, ?, ?, ?, NOW())'
            );
            foreach ($invitationModels as $model) {
                $insertModel->execute([
                    $eventId,
                    (int) ($model['model_id'] ?? 0),
                    max(1, (int) ($model['quantity'] ?? 1)),
                    round((float) ($model['unit_price'] ?? 0), 2),
                ]);
            }
        }

        $deleteCheckout = $pdo->prepare('DELETE FROM event_checkout WHERE cod_event = ?');
        $deleteCheckout->execute([$eventId]);

        if ($checkout === []) {
            return;
        }

        $insertCheckout = $pdo->prepare(
            'INSERT INTO event_checkout (
                cod_event, promo_code, promo_label, reduction_type, reduction_value,
                reduction_amount, subtotal, total, devise, type_paiement, date_enreg, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );

        $insertCheckout->execute([
            $eventId,
            self::nullableString($checkout['promo_code'] ?? null),
            self::nullableString($checkout['promo_label'] ?? null),
            self::nullableString($checkout['promo_type'] ?? null),
            isset($checkout['promo_value']) ? (float) $checkout['promo_value'] : 0.0,
            isset($checkout['discount_amount']) ? (float) $checkout['discount_amount'] : 0.0,
            isset($checkout['subtotal']) ? (float) $checkout['subtotal'] : 0.0,
            isset($checkout['total']) ? (float) $checkout['total'] : 0.0,
            self::nullableString($checkout['currency'] ?? self::DEFAULT_CURRENCY) ?? self::DEFAULT_CURRENCY,
            self::nullableString($checkout['payment_type'] ?? null),
        ]);
    }

    private static function resolvePromo(array $promoCatalog, string $promoCode, float $subtotal): array
    {
        $normalizedCode = strtoupper(trim($promoCode));
        $definition = $promoCatalog[$normalizedCode] ?? null;

        if ($definition === null || $subtotal <= 0) {
            return [
                'promo_code' => $normalizedCode,
                'promo_label' => null,
                'promo_type' => null,
                'promo_value' => 0.0,
                'discount_amount' => 0.0,
            ];
        }

        $promoType = (string) ($definition['type'] ?? 'percent');
        $promoValue = (float) ($definition['value'] ?? 0.0);
        $discountAmount = $promoType === 'fixed'
            ? min($subtotal, $promoValue)
            : min($subtotal, round($subtotal * ($promoValue / 100), 2));

        return [
            'promo_code' => $normalizedCode,
            'promo_label' => (string) ($definition['label'] ?? $normalizedCode),
            'promo_type' => $promoType,
            'promo_value' => $promoValue,
            'discount_amount' => round($discountAmount, 2),
        ];
    }

    private static function ensureOrderTables(PDO $pdo): void
    {
        if (self::$orderTablesEnsured) {
            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS event_invitation_models (
                cod_eim INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                cod_event INT NOT NULL,
                cod_mod INT NOT NULL,
                quantite INT NOT NULL DEFAULT 1,
                unit_price DECIMAL(10,2) NULL,
                date_enreg DATETIME NOT NULL,
                INDEX idx_event_invitation_models_event (cod_event),
                INDEX idx_event_invitation_models_model (cod_mod)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $eventInvitationColumns = $pdo->query('SHOW COLUMNS FROM event_invitation_models')->fetchAll(PDO::FETCH_ASSOC);
        $eventInvitationColumnNames = array_map(static fn(array $row): string => (string) ($row['Field'] ?? ''), $eventInvitationColumns);
        if (!in_array('unit_price', $eventInvitationColumnNames, true)) {
            $pdo->exec('ALTER TABLE event_invitation_models ADD COLUMN unit_price DECIMAL(10,2) NULL AFTER quantite');
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS event_checkout (
                cod_checkout INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                cod_event INT NOT NULL,
                promo_code VARCHAR(50) NULL,
                promo_label VARCHAR(100) NULL,
                reduction_type VARCHAR(10) NULL,
                reduction_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                reduction_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                devise VARCHAR(5) NOT NULL DEFAULT "USD",
                type_paiement VARCHAR(30) NULL,
                date_enreg DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uniq_event_checkout_event (cod_event)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        self::$orderTablesEnsured = true;
    }

    private static function ensurePromoCodeTable(PDO $pdo): void
    {
        if (self::$promoCodeTableEnsured) {
            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS promo_codes (
                cod_promo INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(50) NOT NULL,
                label VARCHAR(100) NOT NULL,
                reduction_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uniq_promo_codes_code (code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $count = (int) $pdo->query('SELECT COUNT(*) FROM promo_codes')->fetchColumn();
        if ($count === 0) {
            $insert = $pdo->prepare(
                'INSERT INTO promo_codes (code, label, reduction_percent, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, 1, NOW(), NOW())'
            );

            foreach (self::DEFAULT_PROMO_CODES as $promoCode) {
                $insert->execute([
                    $promoCode['code'],
                    $promoCode['label'],
                    $promoCode['value'],
                ]);
            }
        }

        self::$promoCodeTableEnsured = true;
    }

    private static function ensureModelCatalogColumns(PDO $pdo): void
    {
        if (self::$modelCatalogColumnsEnsured) {
            return;
        }

        $columns = $pdo->query('SHOW COLUMNS FROM modele_is')->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_map(static fn(array $row): string => (string) ($row['Field'] ?? ''), $columns);

        if (!in_array('unit_price', $columnNames, true)) {
            $pdo->exec('ALTER TABLE modele_is ADD COLUMN unit_price DECIMAL(10,2) NULL AFTER type_mod');
        }

        if (!in_array('is_active', $columnNames, true)) {
            $pdo->exec('ALTER TABLE modele_is ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER unit_price');
            $pdo->exec('UPDATE modele_is SET is_active = 1 WHERE is_active IS NULL');
        }

        self::$modelCatalogColumnsEnsured = true;
    }

    private static function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}