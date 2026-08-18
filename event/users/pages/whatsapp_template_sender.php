<?php

if (!class_exists('WhatsAppQuotaService')) {
    require_once dirname(__DIR__, 3) . '/src/Support/WhatsAppQuotaService.php';
}

if (!class_exists('GeneratedInvitationCleanupService')) {
    require_once dirname(__DIR__, 3) . '/src/Support/GeneratedInvitationCleanupService.php';
}

if (!class_exists('AdminClientManagementService')) {
    require_once dirname(__DIR__, 3) . '/src/Support/AdminClientManagementService.php';
}

if (!defined('ISAPP_TWILIO_WHATSAPP_FROM')) {
    define('ISAPP_TWILIO_WHATSAPP_FROM', 'whatsapp:+14787726313');
}

if (!defined('ISAPP_TWILIO_WHATSAPP_TEMPLATE_SID')) {
    define('ISAPP_TWILIO_WHATSAPP_TEMPLATE_SID', 'HX19ec61e298a83f99ec815a184b9d9a0e');
}

if (!function_exists('isapp_whatsapp_sender_base_url')) {
    function isapp_whatsapp_sender_base_url(): string
    {
        return rtrim((string) (getenv('ISAPP_PUBLIC_BASE_URL') ?: 'https://invitationspeciale.com'), '/');
    }
}

if (!function_exists('isapp_whatsapp_sender_public_event_url')) {
    function isapp_whatsapp_sender_public_event_url(string $relativePath): string
    {
        return isapp_whatsapp_sender_base_url() . '/event/' . ltrim($relativePath, '/');
    }
}

if (!function_exists('isapp_whatsapp_sender_fetch_event')) {
    function isapp_whatsapp_sender_fetch_event(PDO $pdo, $eventCode): array
    {
        $stmt = $pdo->prepare('SELECT * FROM events WHERE cod_event = :cod_event LIMIT 1');
        $stmt->execute([':cod_event' => $eventCode]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $stmt->closeCursor();

        return $event;
    }
}

if (!function_exists('isapp_whatsapp_sender_fetch_invite')) {
    function isapp_whatsapp_sender_fetch_invite(PDO $pdo, $inviteId, $eventCode = null): array
    {
        if ($inviteId === null || $inviteId === '') {
            return [];
        }

        if ($eventCode !== null && $eventCode !== '') {
            $stmt = $pdo->prepare('SELECT * FROM invite WHERE id_inv = :id_inv AND cod_mar = :cod_mar LIMIT 1');
            $stmt->execute([
                ':id_inv' => $inviteId,
                ':cod_mar' => $eventCode,
            ]);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM invite WHERE id_inv = :id_inv LIMIT 1');
            $stmt->execute([':id_inv' => $inviteId]);
        }
        $invite = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $stmt->closeCursor();

        return $invite;
    }
}

if (!function_exists('isapp_whatsapp_sender_invite_pdf_link')) {
    function isapp_whatsapp_sender_invite_pdf_link($eventCode, $inviteId): string
    {
        $eventCode = trim((string) $eventCode);
        $inviteId = trim((string) $inviteId);

        if ($eventCode === '' || $inviteId === '') {
            return '';
        }

        return 'pages/invitation_speciale.php?cod=' . rawurlencode($inviteId) . '&event=' . rawurlencode($eventCode);
    }
}

if (!function_exists('isapp_whatsapp_sender_display_name')) {
    function isapp_whatsapp_sender_display_name(array $invite, string $fallback = 'Invite'): string
    {
        $name = trim((string) ($invite['nom'] ?? ''));
        if ($name === '') {
            $name = trim($fallback);
        }

        return $name !== '' ? trim(strip_tags($name)) : 'Invite';
    }
}

if (!function_exists('isapp_whatsapp_sender_invite_prefix')) {
    function isapp_whatsapp_sender_invite_prefix(array $invite): string
    {
        $salutation = trim((string) ($invite['sing'] ?? ''));

        if ($salutation === 'C') {
            return 'Couple';
        }

        if ($salutation === 'Mr' || $salutation === 'M') {
            return 'Monsieur';
        }

        if ($salutation === 'Mme') {
            return 'Madame';
        }

        return '';
    }
}

if (!function_exists('isapp_whatsapp_sender_full_invite_name')) {
    function isapp_whatsapp_sender_full_invite_name(array $invite, string $fallback = 'Invite'): string
    {
        $displayName = isapp_whatsapp_sender_display_name($invite, $fallback);
        $prefix = isapp_whatsapp_sender_invite_prefix($invite);

        return trim($prefix . ' ' . $displayName);
    }
}

if (!function_exists('isapp_whatsapp_sender_normalize_text')) {
    function isapp_whatsapp_sender_normalize_text(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text);

        return $text;
    }
}

if (!function_exists('isapp_whatsapp_sender_normalize_wedding_type')) {
    function isapp_whatsapp_sender_normalize_wedding_type(string $weddingType): string
    {
        $normalized = mb_strtolower(isapp_whatsapp_sender_normalize_text($weddingType), 'UTF-8');

        if ($normalized === '') {
            return 'benediction';
        }

        if (strpos($normalized, 'coutum') !== false) {
            return 'coutumier';
        }

        if (strpos($normalized, 'civil') !== false) {
            return 'civil';
        }

        if (strpos($normalized, 'soir') !== false || strpos($normalized, 'dans') !== false) {
            return 'soiree_dansante';
        }

        if (strpos($normalized, 'diner') !== false || strpos($normalized, 'dîner') !== false || strpos($normalized, 'din') !== false || strpos($normalized, 'dîn') !== false) {
            return 'diner';
        }

        if (strpos($normalized, 'bened') !== false || strpos($normalized, 'bénéd') !== false || strpos($normalized, 'nupt') !== false || strpos($normalized, 'relig') !== false) {
            return 'benediction';
        }

        return $normalized;
    }
}

if (!function_exists('isapp_whatsapp_sender_wedding_couple_name')) {
    function isapp_whatsapp_sender_wedding_couple_name(array $event): string
    {
        $groomName = trim((string) ($event['prenom_epoux'] ?? ''));
        $brideName = trim((string) ($event['prenom_epouse'] ?? ''));
        $nameOrder = mb_strtolower(trim((string) ($event['ordrepri'] ?? '')), 'UTF-8');

        $firstName = $nameOrder === 'm' ? $groomName : $brideName;
        $secondName = $nameOrder === 'm' ? $brideName : $groomName;

        return trim($firstName . ' & ' . $secondName, ' &');
    }
}

if (!function_exists('isapp_whatsapp_sender_wedding_couple_name_with_and')) {
    function isapp_whatsapp_sender_wedding_couple_name_with_and(array $event): string
    {
        $groomName = trim((string) ($event['prenom_epoux'] ?? ''));
        $brideName = trim((string) ($event['prenom_epouse'] ?? ''));
        $nameOrder = mb_strtolower(trim((string) ($event['ordrepri'] ?? '')), 'UTF-8');

        $firstName = $nameOrder === 'm' ? $groomName : $brideName;
        $secondName = $nameOrder === 'm' ? $brideName : $groomName;

        $names = array_values(array_filter([$firstName, $secondName], static fn(string $name): bool => $name !== ''));

        return implode(' et ', $names);
    }
}

if (!function_exists('isapp_whatsapp_sender_starts_with_vowel')) {
    function isapp_whatsapp_sender_starts_with_vowel(string $value): bool
    {
        $firstCharacter = mb_substr(trim($value), 0, 1, 'UTF-8');
        $firstCharacter = mb_strtolower($firstCharacter, 'UTF-8');

        return in_array($firstCharacter, ['a', 'à', 'â', 'e', 'é', 'è', 'ê', 'ë', 'i', 'î', 'ï', 'o', 'ô', 'u', 'ù', 'û', 'ü', 'y'], true);
    }
}

if (!function_exists('isapp_whatsapp_sender_signature')) {
    function isapp_whatsapp_sender_signature(array $event): string
    {
        $eventType = (string) ($event['type_event'] ?? '');

        if ($eventType === '12') {
            return 'Invitation Spéciale';
        }

        if ($eventType === '1') {
            return isapp_whatsapp_sender_wedding_couple_name($event) ?: 'Les organisateurs';
        }

        $hostName = trim((string) ($event['nomfetard'] ?? ''));
        if ($hostName !== '') {
            return $hostName;
        }

        $eventName = trim((string) ($event['nom_event'] ?? $event['titre_event'] ?? ''));

        return $eventName !== '' ? $eventName : 'Les organisateurs';
    }
}

if (!function_exists('isapp_whatsapp_sender_template_signature')) {
    function isapp_whatsapp_sender_template_signature(array $event, string $fallbackSignature): string
    {
        if ((string) ($event['type_event'] ?? '') === '1') {
            $weddingType = isapp_whatsapp_sender_normalize_wedding_type((string) ($event['type_mar'] ?? ''));
            if ($weddingType === 'soiree_dansante') {
                return isapp_whatsapp_sender_wedding_couple_name_with_and($event) ?: $fallbackSignature;
            }
        }

        return $fallbackSignature;
    }
}

if (!function_exists('isapp_whatsapp_sender_event_label')) {
    function isapp_whatsapp_sender_event_label(array $event): string
    {
        $eventType = (string) ($event['type_event'] ?? '');
        $theme = isapp_whatsapp_sender_normalize_text((string) ($event['themeconf'] ?? ''));
        $hostName = isapp_whatsapp_sender_normalize_text((string) ($event['nomfetard'] ?? ''));

        if ($eventType === '1') {
            $weddingType = isapp_whatsapp_sender_normalize_wedding_type((string) ($event['type_mar'] ?? ''));

            if ($weddingType === 'coutumier') {
                return 'à la soirée du mariage coutumier';
            }

            if ($weddingType === 'civil') {
                return 'à la cérémonie du mariage civil';
            }

            if ($weddingType === 'soiree_dansante') {
                return 'à la soirée de gala à l\'occasion du mariage';
            }

            if ($weddingType === 'diner') {
                return 'au dîner à l\'occasion du mariage';
            }

            return 'à la cérémonie de la bénédiction nuptiale';
        }

        if ($eventType === '2') {
            return $hostName !== '' ? 'à l’anniversaire de ' . $hostName : 'à l’anniversaire';
        }

        if ($eventType === '3') {
            return $theme !== '' ? 'à la conférence « ' . $theme . ' »' : 'à la conférence';
        }

        if ($eventType === '5') {
            return $theme !== '' ? 'au concert « ' . $theme . ' »' : 'au concert';
        }

        if ($eventType === '6') {
            return $hostName !== '' ? 'au baptême de ' . $hostName : 'au baptême';
        }

        if ($eventType === '7') {
            return $theme !== '' ? 'à la collation « ' . $theme . ' »' : 'à la collation';
        }

        if ($eventType === '8') {
            return $theme !== '' ? 'à la soirée de gala « ' . $theme . ' »' : 'à la soirée de gala';
        }

        if ($eventType === '9') {
            return $theme !== '' ? 'à la formation « ' . $theme . ' »' : 'à la formation';
        }

        if ($eventType === '10') {
            return $theme !== '' ? 'à la soirée de charité « ' . $theme . ' »' : 'à la soirée de charité';
        }

        if ($eventType === '11') {
            return $theme !== '' ? 'à l’inauguration de « ' . $theme . ' »' : 'à l’inauguration';
        }

        if ($eventType === '12') {
            return $theme !== '' ? 'au vernissage du livre ' . $theme : 'au vernissage du livre';
        }

        $eventName = trim((string) ($event['nom_event'] ?? $event['titre_event'] ?? ''));
        if ($eventName !== '') {
            return $eventName;
        }

        return 'notre evenement';
    }
}

if (!function_exists('isapp_whatsapp_sender_invitation_sentence')) {
    function isapp_whatsapp_sender_invitation_sentence(array $event): string
    {
        $eventLabel = isapp_whatsapp_sender_event_label($event);
        $signature = isapp_whatsapp_sender_signature($event);
        $eventType = (string) ($event['type_event'] ?? '');

        if ($eventType === '12') {
            return 'Nous avons le plaisir de vous transmettre votre invitation ' . $eventLabel . '.';
        }

        if ($eventType === '1') {
            $weddingType = isapp_whatsapp_sender_normalize_wedding_type((string) ($event['type_mar'] ?? ''));

            if ($weddingType === 'soiree_dansante') {
                $coupleWithAnd = isapp_whatsapp_sender_wedding_couple_name_with_and($event) ?: $signature;
                $prefix = isapp_whatsapp_sender_starts_with_vowel($coupleWithAnd) ? "d'" : 'de ';

                return 'Nous avons le plaisir de vous transmettre votre invitation à la soirée de gala à l\'occasion du mariage ' . $prefix . $coupleWithAnd . '.';
            }

            if ($weddingType === 'diner') {
                return 'Nous avons le plaisir de vous transmettre votre invitation au dîner à l\'occasion du mariage de ' . $signature . '.';
            }
        }

        return 'Nous avons le plaisir de vous transmettre votre invitation ' . $eventLabel . ' de ' . $signature . '.';
    }
}

if (!function_exists('isapp_whatsapp_sender_preview_context')) {
    function isapp_whatsapp_sender_preview_context(PDO $pdo, $eventCode): array
    {
        $event = isapp_whatsapp_sender_fetch_event($pdo, $eventCode);
        $eventLabel = isapp_whatsapp_sender_event_label($event);
        $signature = isapp_whatsapp_sender_signature($event);
        $eventType = (string) ($event['type_event'] ?? '');

        return [
            'event_label' => $eventLabel,
            'signature' => $signature,
            'invitation_sentence' => isapp_whatsapp_sender_invitation_sentence($event),
        ];
    }
}

if (!function_exists('isapp_whatsapp_sender_wedding_file_label')) {
    function isapp_whatsapp_sender_wedding_file_label(array $event): string
    {
        $weddingType = isapp_whatsapp_sender_normalize_wedding_type((string) ($event['type_mar'] ?? ''));

        if ($weddingType === 'coutumier') {
            return 'MARIAGE COUTUMIER';
        }

        if ($weddingType === 'civil') {
            return 'MARIAGE CIVIL';
        }

        if ($weddingType === 'soiree_dansante') {
            return 'SOIREE DANSANTE';
        }

        if ($weddingType === 'diner') {
            return 'DINER';
        }

        return 'BENEDICTION NUPTIALE';
    }
}

if (!function_exists('isapp_whatsapp_sender_filename_event_suffix')) {
    function isapp_whatsapp_sender_filename_event_suffix(array $event): string
    {
        $eventCode = trim((string) ($event['cod_event'] ?? ''));

        return $eventCode !== '' ? 'EV' . $eventCode : '';
    }
}

if (!function_exists('isapp_whatsapp_sender_filename_base')) {
    function isapp_whatsapp_sender_filename_base(array $event, array $invite, string $fallbackInviteName): string
    {
        $displayName = isapp_whatsapp_sender_full_invite_name($invite, $fallbackInviteName);
        $eventSuffix = isapp_whatsapp_sender_filename_event_suffix($event);

        $eventType = (string) ($event['type_event'] ?? '');
        if ($eventType === '1') {
            $signature = isapp_whatsapp_sender_wedding_couple_name($event);
            $weddingFileLabel = isapp_whatsapp_sender_wedding_file_label($event);

            return implode(' - ', array_values(array_filter([
                $signature,
                $weddingFileLabel,
                'INVITATION ' . $displayName,
                $eventSuffix,
            ], static fn(string $part): bool => trim($part) !== '')));
        }

        if ((string) ($event['type_event'] ?? '') === '12') {
            $bookTitle = trim((string) ($event['themeconf'] ?? ''));
            if ($bookTitle !== '') {
                return implode(' - ', array_values(array_filter([
                    'VERNISSAGE DU LIVRE ' . $bookTitle,
                    'INVITATION ' . $displayName,
                    $eventSuffix,
                ], static fn(string $part): bool => trim($part) !== '')));
            }
        }

        $hostName = trim((string) ($event['nomfetard'] ?? '')) ?: trim((string) ($event['nom_event'] ?? $event['titre_event'] ?? 'EVENEMENT'));

        return implode(' - ', array_values(array_filter([
            $hostName,
            'INVITATION ' . $displayName,
            $eventSuffix,
        ], static fn(string $part): bool => trim($part) !== '')));
    }
}

if (!function_exists('isapp_whatsapp_sender_sanitize_filename')) {
    function isapp_whatsapp_sender_sanitize_filename(string $filename): string
    {
        $filename = trim($filename);
        $filename = preg_replace('/\.pdf$/i', '', $filename);
        $filename = rawurldecode($filename);
        $filename = preg_replace('/[\/:*?"<>|]/', '', $filename);
        $filename = mb_convert_encoding($filename, 'UTF-8', 'UTF-8');
        $asciiFilename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
        if ($asciiFilename !== false && trim($asciiFilename) !== '') {
            $filename = $asciiFilename;
        }

        $filename = preg_replace('/\s+/', ' ', $filename);
        $filename = strtoupper(trim($filename));

        return $filename !== '' ? $filename : 'INVITATION';
    }
}

if (!function_exists('preparerNomFichierPdfPourTwilio')) {
    function preparerNomFichierPdfPourTwilio($nomFichier): string
    {
        $nomFichier = trim((string) $nomFichier);
        $nomFichier = preg_replace('/\.pdf$/i', '', $nomFichier);
        $nomFichier = rawurldecode($nomFichier);

        return rawurlencode($nomFichier);
    }
}

if (!function_exists('isapp_whatsapp_sender_encoded_stem')) {
    function isapp_whatsapp_sender_encoded_stem(string $filenameBase): string
    {
        return preparerNomFichierPdfPourTwilio(isapp_whatsapp_sender_sanitize_filename($filenameBase));
    }
}

if (!function_exists('isapp_whatsapp_sender_disk_stem')) {
    function isapp_whatsapp_sender_disk_stem(string $filenameBase): string
    {
        return isapp_whatsapp_sender_sanitize_filename($filenameBase);
    }
}

if (!function_exists('isapp_whatsapp_sender_absolute_file_path')) {
    function isapp_whatsapp_sender_absolute_file_path(string $diskStem): string
    {
        return dirname(__DIR__, 2) . '/pages/fichiers/' . $diskStem . '.pdf';
    }
}

if (!function_exists('isapp_whatsapp_sender_public_media_url')) {
    function isapp_whatsapp_sender_public_media_url(string $encodedStem): string
    {
        return isapp_whatsapp_sender_base_url() . '/event/pages/fichiers/' . $encodedStem . '.pdf';
    }
}

if (!function_exists('isapp_whatsapp_sender_delete_public_pdf')) {
    function isapp_whatsapp_sender_delete_public_pdf(string $diskStem): void
    {
        $targetPath = isapp_whatsapp_sender_absolute_file_path($diskStem);
        $baseDirectory = realpath(dirname($targetPath));
        $realTargetPath = realpath($targetPath);

        if ($baseDirectory === false || $realTargetPath === false) {
            return;
        }

        if (strpos($realTargetPath, $baseDirectory . DIRECTORY_SEPARATOR) !== 0 || !is_file($realTargetPath)) {
            return;
        }

        @unlink($realTargetPath);
    }
}

if (!function_exists('isapp_whatsapp_sender_cleanup_old_public_pdfs')) {
    function isapp_whatsapp_sender_cleanup_old_public_pdfs(PDO $pdo): void
    {
        try {
            GeneratedInvitationCleanupService::cleanup(
                $pdo,
                dirname(__DIR__, 2) . '/pages/fichiers',
                GeneratedInvitationCleanupService::DEFAULT_MIN_AGE_SECONDS
            );
        } catch (\Throwable $exception) {
            // Le nettoyage ne doit jamais bloquer l'envoi WhatsApp.
        }
    }
}

if (!function_exists('isapp_whatsapp_sender_download_pdf')) {
    function isapp_whatsapp_sender_download_pdf(string $sourceUrl): string
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'ignore_errors' => true,
                'user_agent' => 'ISApp-WhatsApp-Template/1.0',
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $pdfContent = @file_get_contents($sourceUrl, false, $context);
        if ($pdfContent === false || $pdfContent === '') {
            throw new RuntimeException('Impossible de recuperer le PDF public a envoyer sur WhatsApp.');
        }

        $headers = isset($http_response_header) && is_array($http_response_header) ? $http_response_header : [];
        $contentType = '';
        foreach ($headers as $headerLine) {
            if (stripos($headerLine, 'Content-Type:') === 0) {
                $contentType = trim(substr($headerLine, 13));
                break;
            }
        }

        if ($contentType !== '' && stripos($contentType, 'application/pdf') === false) {
            throw new RuntimeException('Le document recupere ne semble pas etre un PDF valide.');
        }

        return $pdfContent;
    }
}

if (!function_exists('isapp_whatsapp_sender_ensure_public_pdf')) {
    function isapp_whatsapp_sender_ensure_public_pdf(string $relativePdfLink, string $diskStem, string $encodedStem): string
    {
        if ($relativePdfLink === '') {
            throw new RuntimeException('Le lien du PDF est introuvable.');
        }

        $relativePdfLink = preg_replace('#^\.\./#', '', $relativePdfLink);
        $sourceUrl = isapp_whatsapp_sender_public_event_url(ltrim($relativePdfLink, '/'));
        $targetPath = isapp_whatsapp_sender_absolute_file_path($diskStem);
        $targetDirectory = dirname($targetPath);

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException('Impossible de creer le dossier des PDF WhatsApp.');
        }

        $pdfContent = isapp_whatsapp_sender_download_pdf($sourceUrl);
        $pdfSize = strlen($pdfContent);
        if ($pdfSize > 16 * 1024 * 1024) {
            throw new RuntimeException('Le PDF depasse la limite WhatsApp de 16 MB.');
        }

        if (file_put_contents($targetPath, $pdfContent) === false) {
            throw new RuntimeException('Impossible de publier le PDF pour le template WhatsApp.');
        }

        $mediaUrl = isapp_whatsapp_sender_public_media_url($encodedStem);
        $publicHeaders = @get_headers($mediaUrl);
        if ($publicHeaders === false) {
            throw new RuntimeException('Impossible de verifier l’URL publique finale du PDF WhatsApp.');
        }

        $statusLine = (string) ($publicHeaders[0] ?? '');
        if (stripos($statusLine, '200') === false) {
            throw new RuntimeException('L’URL publique finale du PDF WhatsApp n’est pas accessible: ' . $mediaUrl);
        }

        return $mediaUrl;
    }
}

if (!function_exists('isapp_whatsapp_sender_normalize_recipient')) {
    function isapp_whatsapp_sender_normalize_recipient(string $phone): string
    {
        $phone = trim($phone);
        if (stripos($phone, 'whatsapp:') === 0) {
            $phone = substr($phone, 9);
        }
        $phone = preg_replace('/[\s\-().]/', '', $phone);

        if (!preg_match('/^\+[1-9]\d{7,14}$/', $phone)) {
            throw new RuntimeException('Le numero WhatsApp doit etre saisi au format international complet, par exemple +243XXXXXXXXX.');
        }

        return 'whatsapp:' . $phone;
    }
}

if (!function_exists('isapp_whatsapp_sender_ensure_log_table')) {
    function isapp_whatsapp_sender_ensure_log_table(PDO $pdo): void
    {
        static $tableEnsured = false;

        if ($tableEnsured) {
            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS whatsapp_message_logs (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                event_code VARCHAR(191) NOT NULL,
                invite_id INT NULL,
                recipient_number VARCHAR(64) NOT NULL,
                recipient_name VARCHAR(191) NOT NULL,
                send_mode VARCHAR(32) NOT NULL,
                template_sid VARCHAR(64) NOT NULL,
                content_variables_json JSON NULL,
                media_filename VARCHAR(255) NULL,
                media_url TEXT NULL,
                twilio_message_sid VARCHAR(64) NULL,
                send_status VARCHAR(32) NOT NULL,
                error_message TEXT NULL,
                sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_whatsapp_logs_event_code (event_code),
                INDEX idx_whatsapp_logs_invite_id (invite_id),
                INDEX idx_whatsapp_logs_status (send_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $tableEnsured = true;
    }
}

if (!function_exists('isapp_whatsapp_sender_log_result')) {
    function isapp_whatsapp_sender_log_result(PDO $pdo, array $payload): void
    {
        isapp_whatsapp_sender_ensure_log_table($pdo);

        $stmt = $pdo->prepare(
            'INSERT INTO whatsapp_message_logs (
                event_code,
                invite_id,
                recipient_number,
                recipient_name,
                send_mode,
                template_sid,
                content_variables_json,
                media_filename,
                media_url,
                twilio_message_sid,
                send_status,
                error_message,
                sent_at
            ) VALUES (
                :event_code,
                :invite_id,
                :recipient_number,
                :recipient_name,
                :send_mode,
                :template_sid,
                :content_variables_json,
                :media_filename,
                :media_url,
                :twilio_message_sid,
                :send_status,
                :error_message,
                NOW()
            )'
        );

        $stmt->execute([
            ':event_code' => (string) ($payload['event_code'] ?? ''),
            ':invite_id' => $payload['invite_id'] !== null && $payload['invite_id'] !== '' ? (int) $payload['invite_id'] : null,
            ':recipient_number' => (string) ($payload['recipient_number'] ?? ''),
            ':recipient_name' => (string) ($payload['recipient_name'] ?? ''),
            ':send_mode' => (string) ($payload['send_mode'] ?? 'template'),
            ':template_sid' => (string) ($payload['template_sid'] ?? ''),
            ':content_variables_json' => (string) ($payload['content_variables_json'] ?? '{}'),
            ':media_filename' => (string) ($payload['media_filename'] ?? ''),
            ':media_url' => (string) ($payload['media_url'] ?? ''),
            ':twilio_message_sid' => (string) ($payload['twilio_message_sid'] ?? ''),
            ':send_status' => (string) ($payload['send_status'] ?? 'failed'),
            ':error_message' => (string) ($payload['error_message'] ?? ''),
        ]);
        $stmt->closeCursor();
    }
}

if (!function_exists('isapp_whatsapp_sender_invite_tracking_columns')) {
    function isapp_whatsapp_sender_invite_tracking_columns(PDO $pdo): array
    {
        static $columns = null;

        if (is_array($columns)) {
            return $columns;
        }

        $columns = [];
        foreach (['statut_envoi_whatsapp', 'twilio_message_sid', 'date_envoi_whatsapp', 'erreur_twilio'] as $columnName) {
            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM invite LIKE '" . str_replace("'", "''", $columnName) . "'");
                $columns[$columnName] = $stmt !== false && $stmt->fetch(PDO::FETCH_ASSOC) !== false;
                if ($stmt !== false) {
                    $stmt->closeCursor();
                }
            } catch (\Throwable $exception) {
                $columns[$columnName] = false;
            }
        }

        return $columns;
    }
}

if (!function_exists('isapp_whatsapp_sender_update_invite_tracking')) {
    function isapp_whatsapp_sender_update_invite_tracking(PDO $pdo, $inviteId, array $payload): void
    {
        $inviteId = (int) $inviteId;
        if ($inviteId <= 0) {
            return;
        }

        $availableColumns = isapp_whatsapp_sender_invite_tracking_columns($pdo);
        $setClauses = [];
        $params = [':invite_id' => $inviteId];

        if (!empty($availableColumns['statut_envoi_whatsapp'])) {
            $setClauses[] = 'statut_envoi_whatsapp = :statut_envoi_whatsapp';
            $params[':statut_envoi_whatsapp'] = (string) ($payload['statut_envoi_whatsapp'] ?? 'echoue');
        }

        if (!empty($availableColumns['twilio_message_sid'])) {
            $setClauses[] = 'twilio_message_sid = :twilio_message_sid';
            $params[':twilio_message_sid'] = (string) ($payload['twilio_message_sid'] ?? '');
        }

        if (!empty($availableColumns['date_envoi_whatsapp'])) {
            $setClauses[] = 'date_envoi_whatsapp = :date_envoi_whatsapp';
            $params[':date_envoi_whatsapp'] = (string) ($payload['date_envoi_whatsapp'] ?? date('Y-m-d H:i:s'));
        }

        if (!empty($availableColumns['erreur_twilio'])) {
            $setClauses[] = 'erreur_twilio = :erreur_twilio';
            $params[':erreur_twilio'] = (string) ($payload['erreur_twilio'] ?? '');
        }

        if ($setClauses === []) {
            return;
        }

        $stmt = $pdo->prepare('UPDATE invite SET ' . implode(', ', $setClauses) . ' WHERE id_inv = :invite_id LIMIT 1');
        $stmt->execute($params);
        $stmt->closeCursor();
    }
}

if (!function_exists('isapp_whatsapp_sender_template_sid')) {
    function isapp_whatsapp_sender_template_sid(): string
    {
        $templateSid = trim(ISAPP_TWILIO_WHATSAPP_TEMPLATE_SID);

        if (!preg_match('/^HX[0-9a-fA-F]{32}$/', $templateSid)) {
            throw new RuntimeException('Le SID du template Twilio WhatsApp est invalide. Il doit commencer par HX et contenir 34 caracteres.');
        }

        return $templateSid;
    }
}

if (!function_exists('isapp_whatsapp_sender_from_number')) {
    function isapp_whatsapp_sender_from_number(): string
    {
        $from = trim(ISAPP_TWILIO_WHATSAPP_FROM);

        if (!preg_match('/^whatsapp:\+[1-9]\d{7,14}$/', $from)) {
            throw new RuntimeException('Le numero expéditeur WhatsApp Twilio est invalide.');
        }

        return $from;
    }
}

if (!function_exists('isapp_whatsapp_sender_messaging_service_sid')) {
    function isapp_whatsapp_sender_messaging_service_sid(): string
    {
        $messagingServiceSid = trim((string) getenv('TWILIO_MESSAGING_SERVICE_SID'));

        if ($messagingServiceSid === '') {
            throw new RuntimeException('Le Messaging Service SID Twilio est introuvable. Renseignez TWILIO_MESSAGING_SERVICE_SID pour l’envoi des templates WhatsApp.');
        }

        if (!preg_match('/^MG[0-9a-fA-F]{32}$/', $messagingServiceSid)) {
            throw new RuntimeException('Le Messaging Service SID Twilio est invalide. Il doit commencer par MG et contenir 34 caracteres.');
        }

        return $messagingServiceSid;
    }
}

if (!function_exists('isapp_whatsapp_sender_required_env')) {
    function isapp_whatsapp_sender_required_env(string $envName, string $label): string
    {
        $value = trim((string) getenv($envName));
        if ($value === '') {
            throw new RuntimeException($label . ' est introuvable. Renseignez la variable d\'environnement ' . $envName . '.');
        }

        return $value;
    }
}

if (!function_exists('isapp_whatsapp_sender_sanitize_error_message')) {
    function isapp_whatsapp_sender_sanitize_error_message(string $message): string
    {
        $message = preg_replace('/Authorization:\s*Basic\s+[A-Za-z0-9+\/=._-]+/i', 'Authorization: [REDACTED]', $message) ?? $message;
        $message = preg_replace('/"Authorization"\s*:\s*"[^"]+"/i', '"Authorization":"[REDACTED]"', $message) ?? $message;

        $secrets = [
            trim((string) getenv('TWILIO_AUTH_TOKEN')),
        ];

        foreach ($secrets as $secret) {
            if ($secret !== '') {
                $message = str_replace($secret, '[REDACTED]', $message);
            }
        }

        return trim($message);
    }
}

if (!function_exists('isapp_whatsapp_send_template_invitation')) {
    function isapp_whatsapp_send_template_invitation(PDO $pdo, array $options): array
    {
        require_once dirname(__DIR__, 3) . '/twilio-php-main/src/Twilio/autoload.php';

        $eventCode = (string) ($options['event_code'] ?? '');
        $inviteId = $options['invite_id'] ?? null;
        $phone = (string) ($options['phone'] ?? '');
        $fallbackInviteName = (string) ($options['invite_name'] ?? 'Invite');
        $successRedirect = (string) ($options['success_redirect'] ?? 'index.php?page=mb_accueil');

        $event = isapp_whatsapp_sender_fetch_event($pdo, $eventCode);
        if ($event === []) {
            throw new RuntimeException('Evenement introuvable pour cet envoi WhatsApp.');
        }

        $clientUserId = WhatsAppQuotaService::resolveClientUserId($event, (int) ($options['client_user_id'] ?? 0));
        if ($clientUserId > 0) {
            AdminClientManagementService::assertClientInvitationsEnabled($pdo, $clientUserId);
        }
        $quotaBeforeSend = WhatsAppQuotaService::assertQuotaAvailable($pdo, $eventCode, $clientUserId);

        $invite = isapp_whatsapp_sender_fetch_invite($pdo, $inviteId, $eventCode);
        if ($invite === []) {
            throw new RuntimeException('Invite introuvable pour cet evenement. Veuillez rouvrir la liste de cet evenement avant de relancer l’envoi WhatsApp.');
        }

        $relativePdfLink = isapp_whatsapp_sender_invite_pdf_link($eventCode, $inviteId);
        $recipientName = isapp_whatsapp_sender_display_name($invite, $fallbackInviteName);
        $eventLabel = isapp_whatsapp_sender_event_label($event);
        $signature = isapp_whatsapp_sender_signature($event);
        $templateSignature = isapp_whatsapp_sender_template_signature($event, $signature);
        $filenameBase = isapp_whatsapp_sender_filename_base($event, $invite, $recipientName);
        $diskStem = isapp_whatsapp_sender_disk_stem($filenameBase);
        $encodedStem = isapp_whatsapp_sender_encoded_stem($filenameBase);
        isapp_whatsapp_sender_cleanup_old_public_pdfs($pdo);
        $mediaUrl = isapp_whatsapp_sender_ensure_public_pdf($relativePdfLink, $diskStem, $encodedStem);

        $contentSid = isapp_whatsapp_sender_template_sid();
        $twilioSid = isapp_whatsapp_sender_required_env('TWILIO_ACCOUNT_SID', 'Le compte Twilio');
        $twilioToken = isapp_whatsapp_sender_required_env('TWILIO_AUTH_TOKEN', 'Le jeton Twilio');
        $twilioFrom = isapp_whatsapp_sender_from_number();
        $messagingServiceSid = isapp_whatsapp_sender_messaging_service_sid();

        $contentVariables = [
            '1' => $recipientName,
            '2' => $eventLabel,
            '3' => $templateSignature,
            '4' => $encodedStem,
        ];

        $client = new \Twilio\Rest\Client($twilioSid, $twilioToken);
        $sendStatus = 'failed';
        $twilioMessageSid = '';
        $errorMessage = '';

        try {
            $message = $client->messages->create(
                isapp_whatsapp_sender_normalize_recipient($phone),
                [
                    'from' => $twilioFrom,
                    'messagingServiceSid' => $messagingServiceSid,
                    'contentSid' => $contentSid,
                    'contentVariables' => json_encode($contentVariables, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ]
            );

            $sendStatus = 'sent';
            $twilioMessageSid = (string) ($message->sid ?? '');
        } catch (\Throwable $exception) {
            $errorMessage = isapp_whatsapp_sender_sanitize_error_message((string) $exception->getMessage());
        }

        isapp_whatsapp_sender_log_result($pdo, [
            'event_code' => $eventCode,
            'invite_id' => $inviteId,
            'recipient_number' => isapp_whatsapp_sender_normalize_recipient($phone),
            'recipient_name' => $recipientName,
            'send_mode' => 'template',
            'template_sid' => $contentSid,
            'content_variables_json' => json_encode($contentVariables, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'media_filename' => $encodedStem . '.pdf',
            'media_url' => $mediaUrl,
            'twilio_message_sid' => $twilioMessageSid,
            'send_status' => $sendStatus,
            'error_message' => $errorMessage,
        ]);

        isapp_whatsapp_sender_update_invite_tracking($pdo, $inviteId, [
            'statut_envoi_whatsapp' => $sendStatus === 'sent' ? 'envoye' : 'echoue',
            'twilio_message_sid' => $twilioMessageSid,
            'date_envoi_whatsapp' => date('Y-m-d H:i:s'),
            'erreur_twilio' => $errorMessage,
        ]);

        if ($sendStatus !== 'sent') {
            throw new RuntimeException($errorMessage !== '' ? $errorMessage : 'Echec de l’envoi de l’invitation WhatsApp.');
        }

        $quotaAfterSend = WhatsAppQuotaService::getEventQuota($pdo, $eventCode, $clientUserId);
        $successMessage = 'L’invitation a bien ete envoyee sur WhatsApp.';
        if ($clientUserId > 0) {
            $successMessage .= ' Il vous reste ' . (int) ($quotaAfterSend['remaining_quota'] ?? max(0, ((int) ($quotaBeforeSend['remaining_quota'] ?? 1)) - 1)) . ' envois pour cet evenement.';
        }

        return [
            'success_message' => $successMessage,
            'success_redirect' => $successRedirect,
            'twilio_sid' => $twilioMessageSid,
            'media_url' => $mediaUrl,
            'content_variables' => $contentVariables,
            'quota' => $quotaAfterSend,
        ];
    }
}