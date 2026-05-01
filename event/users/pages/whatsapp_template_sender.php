<?php

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
    function isapp_whatsapp_sender_fetch_invite(PDO $pdo, $inviteId): array
    {
        if ($inviteId === null || $inviteId === '') {
            return [];
        }

        $stmt = $pdo->prepare('SELECT * FROM invite WHERE id_inv = :id_inv LIMIT 1');
        $stmt->execute([':id_inv' => $inviteId]);
        $invite = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $stmt->closeCursor();

        return $invite;
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
            return 'religieux';
        }

        if (strpos($normalized, 'coutum') !== false) {
            return 'coutumier';
        }

        if (strpos($normalized, 'civil') !== false) {
            return 'civil';
        }

        if (strpos($normalized, 'bened') !== false || strpos($normalized, 'bénéd') !== false) {
            return 'benediction';
        }

        if (strpos($normalized, 'relig') !== false || strpos($normalized, 'nupt') !== false) {
            return 'religieux';
        }

        return $normalized;
    }
}

if (!function_exists('isapp_whatsapp_sender_signature')) {
    function isapp_whatsapp_sender_signature(array $event): string
    {
        $eventType = (string) ($event['type_event'] ?? '');

        if ($eventType === '1') {
            $firstName = trim((string) ($event['prenom_epoux'] ?? ''));
            $secondName = trim((string) ($event['prenom_epouse'] ?? ''));
            if ((string) ($event['ordrepri'] ?? '') !== 'm') {
                $firstName = trim((string) ($event['prenom_epouse'] ?? ''));
                $secondName = trim((string) ($event['prenom_epoux'] ?? ''));
            }

            return trim($firstName . ' & ' . $secondName, ' &') ?: 'Les organisateurs';
        }

        $hostName = trim((string) ($event['nomfetard'] ?? ''));
        if ($hostName !== '') {
            return $hostName;
        }

        $eventName = trim((string) ($event['nom_event'] ?? $event['titre_event'] ?? ''));

        return $eventName !== '' ? $eventName : 'Les organisateurs';
    }
}

if (!function_exists('isapp_whatsapp_sender_event_label')) {
    function isapp_whatsapp_sender_event_label(array $event): string
    {
        $eventType = (string) ($event['type_event'] ?? '');

        if ($eventType === '1') {
            $weddingType = isapp_whatsapp_sender_normalize_wedding_type((string) ($event['type_mar'] ?? ''));

            if ($weddingType === 'coutumier') {
                return 'a la soiree du mariage coutumier';
            }

            if ($weddingType === 'civil') {
                return 'a la ceremonie du mariage civil';
            }

            if ($weddingType === 'religieux') {
                return 'a la soiree du mariage religieux';
            }

            return 'a la benediction nuptiale';
        }

        if ($eventType === '2') {
            return 'a l\'anniversaire';
        }

        if ($eventType === '3') {
            return 'a la conference';
        }

        $eventName = trim((string) ($event['nom_event'] ?? $event['titre_event'] ?? ''));
        if ($eventName !== '') {
            return $eventName;
        }

        return 'notre evenement';
    }
}

if (!function_exists('isapp_whatsapp_sender_preview_context')) {
    function isapp_whatsapp_sender_preview_context(PDO $pdo, $eventCode): array
    {
        $event = isapp_whatsapp_sender_fetch_event($pdo, $eventCode);

        return [
            'event_label' => isapp_whatsapp_sender_event_label($event),
            'signature' => isapp_whatsapp_sender_signature($event),
        ];
    }
}

if (!function_exists('isapp_whatsapp_sender_filename_base')) {
    function isapp_whatsapp_sender_filename_base(array $event, array $invite, string $fallbackInviteName): string
    {
        $displayName = isapp_whatsapp_sender_full_invite_name($invite, $fallbackInviteName);

        $eventType = (string) ($event['type_event'] ?? '');
        if ($eventType === '1') {
            $firstName = trim((string) ($event['prenom_epoux'] ?? ''));
            $secondName = trim((string) ($event['prenom_epouse'] ?? ''));
            if ((string) ($event['ordrepri'] ?? '') !== 'm') {
                $firstName = trim((string) ($event['prenom_epouse'] ?? ''));
                $secondName = trim((string) ($event['prenom_epoux'] ?? ''));
            }

            $signature = trim($firstName . ' & ' . $secondName, ' &');

            return trim($signature . ' - INVITATION ' . $displayName);
        }

        $hostName = trim((string) ($event['nomfetard'] ?? '')) ?: trim((string) ($event['nom_event'] ?? $event['titre_event'] ?? 'EVENEMENT'));

        return trim($hostName . ' - INVITATION ' . $displayName);
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

if (!function_exists('isapp_whatsapp_sender_encoded_stem')) {
    function isapp_whatsapp_sender_encoded_stem(string $filenameBase): string
    {
        return rawurlencode(isapp_whatsapp_sender_sanitize_filename($filenameBase));
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
        $phone = preg_replace('/\s+/', '', $phone);

        if (!preg_match('/^\+243\d{9}$/', $phone)) {
            throw new RuntimeException('Le numero WhatsApp doit commencer par +243 et contenir 9 chiffres apres l indicatif.');
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

if (!function_exists('isapp_whatsapp_send_template_invitation')) {
    function isapp_whatsapp_send_template_invitation(PDO $pdo, array $options): array
    {
        require_once dirname(__DIR__, 3) . '/twilio-php-main/src/Twilio/autoload.php';

        $eventCode = (string) ($options['event_code'] ?? '');
        $inviteId = $options['invite_id'] ?? null;
        $phone = (string) ($options['phone'] ?? '');
        $fallbackInviteName = (string) ($options['invite_name'] ?? 'Invite');
        $relativePdfLink = trim((string) ($options['pdf_link'] ?? ''));
        $successRedirect = (string) ($options['success_redirect'] ?? 'index.php?page=mb_accueil');

        $event = isapp_whatsapp_sender_fetch_event($pdo, $eventCode);
        if ($event === []) {
            throw new RuntimeException('Evenement introuvable pour cet envoi WhatsApp.');
        }

        $invite = isapp_whatsapp_sender_fetch_invite($pdo, $inviteId);
        $recipientName = isapp_whatsapp_sender_display_name($invite, $fallbackInviteName);
        $eventLabel = isapp_whatsapp_sender_event_label($event);
        $signature = isapp_whatsapp_sender_signature($event);
        $filenameBase = isapp_whatsapp_sender_filename_base($event, $invite, $recipientName);
        $diskStem = isapp_whatsapp_sender_disk_stem($filenameBase);
        $encodedStem = isapp_whatsapp_sender_encoded_stem($filenameBase);
        $mediaUrl = isapp_whatsapp_sender_ensure_public_pdf($relativePdfLink, $diskStem, $encodedStem);

        $contentSid = (string) (getenv('TWILIO_WHATSAPP_TEMPLATE_SID') ?: 'HX9e9fd770e34bf0241af9f803e0e009b8');
        $twilioSid = (string) (getenv('TWILIO_ACCOUNT_SID') ?: 'AC5cbb94f85695ce16d97ce2ca2c3f7db0');
        $twilioToken = (string) (getenv('TWILIO_AUTH_TOKEN') ?: '2fc99f87d42f61c691c01df995fb8290');
        $twilioFrom = (string) (getenv('TWILIO_WHATSAPP_FROM') ?: 'whatsapp:+17167403177');

        $contentVariables = [
            '1' => $recipientName,
            '2' => $eventLabel,
            '3' => $signature,
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
                    'contentSid' => $contentSid,
                    'contentVariables' => json_encode($contentVariables, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ]
            );

            $sendStatus = 'sent';
            $twilioMessageSid = (string) ($message->sid ?? '');
        } catch (\Throwable $exception) {
            $errorMessage = (string) $exception->getMessage();
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

        if ($sendStatus !== 'sent') {
            throw new RuntimeException($errorMessage !== '' ? $errorMessage : 'Echec de l’envoi de l’invitation WhatsApp.');
        }

        return [
            'success_message' => 'L’invitation a bien ete envoyee sur WhatsApp.',
            'success_redirect' => $successRedirect,
            'twilio_sid' => $twilioMessageSid,
            'media_url' => $mediaUrl,
            'content_variables' => $contentVariables,
        ];
    }
}