<?php

final class AdminClientMessageService
{
    private const TWILIO_WHATSAPP_FROM = 'whatsapp:+14787726313';

    public static function templates(): array
    {
        return [
            'excuse_retard' => [
                'key' => 'excuse_retard',
                'label' => 'Excuse retard de livraison',
                'sid' => 'HX7d4e03f0b72e1515ba5f94e5593c08c8',
                'message' => "Bonjour {{1}},\n\nNous vous présentons nos sincères excuses pour le retard constaté dans la livraison de votre commande.\n\nNotre équipe met tout en œuvre afin de finaliser votre demande dans les meilleures conditions et de vous garantir un résultat à la hauteur de vos attentes.\n\nNous vous remercions pour votre patience et votre compréhension.\n\nCordialement,\nInvitation Spéciale",
            ],
            'rappel_payment' => [
                'key' => 'rappel_payment',
                'label' => 'Rappel paiement facture',
                'sid' => 'HX1f6a4663e5a11b0ccf720a883e8b2465',
                'message' => "Bonjour {{1}},\n\nNous vous rappelons que le règlement de votre facture est nécessaire afin de nous permettre d’amorcer le traitement de votre commande.\n\nDès confirmation du paiement, notre équipe pourra procéder au lancement de votre prestation.\n\nNous vous remercions pour votre confiance et restons à votre disposition en cas de besoin.\n\nCordialement,\nInvitation Spéciale",
            ],
            'excuse_system' => [
                'key' => 'excuse_system',
                'label' => 'Excuse dysfonctionnement système',
                'sid' => 'HX2077fae9414a4be6b812c77d4e1d3966',
                'message' => "Bonjour {{1}},\n\nNous vous présentons nos sincères excuses pour les récents dysfonctionnements rencontrés sur notre système.\n\nNous avons le plaisir de vous informer que la situation a été résolue et que l’ensemble de nos services est désormais pleinement fonctionnel.\n\nNous vous remercions pour votre patience, votre compréhension et votre confiance.\n\nCordialement,\nInvitation Spéciale",
            ],
            'merci_client' => [
                'key' => 'merci_client',
                'label' => 'Remerciement client',
                'sid' => 'HXc9e980a7f08b1063347f5ad5e83abb7f',
                'message' => "Bonjour {{1}},\n\nNous tenons à vous remercier sincèrement d’avoir fait confiance à Invitation Spéciale pour la réalisation de votre commande.\n\nCe fut un réel plaisir de vous accompagner, et nous espérons avoir contribué à rendre votre événement encore plus spécial.\n\nVotre confiance nous encourage à continuer à vous offrir un service de qualité.\n\nAu plaisir de vous accompagner à nouveau.\n\nCordialement,\nInvitation Spéciale",
            ],
        ];
    }

    public static function ensureLogTable(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS admin_client_message_logs (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                admin_user_id INT NULL,
                client_user_id INT NULL,
                client_name VARCHAR(191) NOT NULL,
                recipient_number VARCHAR(64) NOT NULL,
                template_key VARCHAR(64) NOT NULL,
                template_sid VARCHAR(64) NOT NULL,
                content_variables_json JSON NULL,
                twilio_message_sid VARCHAR(64) NULL,
                send_status VARCHAR(32) NOT NULL,
                error_message TEXT NULL,
                sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_admin_client_messages_client (client_user_id),
                INDEX idx_admin_client_messages_template (template_key),
                INDEX idx_admin_client_messages_status (send_status),
                INDEX idx_admin_client_messages_sent_at (sent_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public static function listClients(PDO $pdo): array
    {
        $stmt = $pdo->prepare(
            'SELECT cod_user, noms, email, phone, type_user
             FROM is_users
             WHERE type_user = :type_user
             ORDER BY noms ASC, cod_user DESC'
        );
        $stmt->execute([':type_user' => '2']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function recentLogs(PDO $pdo, int $limit = 30): array
    {
        self::ensureLogTable($pdo);

        $stmt = $pdo->prepare(
            'SELECT id, admin_user_id, client_user_id, client_name, recipient_number, template_key,
                    template_sid, twilio_message_sid, send_status, error_message, sent_at
             FROM admin_client_message_logs
             ORDER BY sent_at DESC, id DESC
             LIMIT :limit_rows'
        );
        $stmt->bindValue(':limit_rows', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function sendBulk(PDO $pdo, int $adminUserId, array $clientIds, string $templateKey): array
    {
        self::ensureLogTable($pdo);

        $templates = self::templates();
        if (!isset($templates[$templateKey])) {
            throw new InvalidArgumentException('Template de message invalide.');
        }

        $clientIds = array_values(array_unique(array_filter(array_map('intval', $clientIds), static fn(int $id): bool => $id > 0)));
        if ($clientIds === []) {
            throw new InvalidArgumentException('Selectionnez au moins un client.');
        }

        $clients = self::clientsByIds($pdo, $clientIds);
        if ($clients === []) {
            throw new RuntimeException('Aucun client valide trouve pour cet envoi.');
        }

        require_once dirname(__DIR__, 2) . '/twilio-php-main/src/Twilio/autoload.php';

        $twilioSid = self::requiredEnv('TWILIO_ACCOUNT_SID', 'Le compte Twilio');
        $twilioToken = self::requiredEnv('TWILIO_AUTH_TOKEN', 'Le jeton Twilio');
        $twilioFrom = self::fromNumber();
        $messagingServiceSid = self::messagingServiceSid();
        $template = $templates[$templateKey];
        $contentSid = (string) $template['sid'];
        $twilioClient = new \Twilio\Rest\Client($twilioSid, $twilioToken);

        $sent = 0;
        $failed = 0;
        $results = [];

        foreach ($clients as $client) {
            $clientUserId = (int) ($client['cod_user'] ?? 0);
            $clientName = self::displayName($client);
            $sendStatus = 'failed';
            $twilioMessageSid = '';
            $errorMessage = '';
            $recipientNumber = '';
            $contentVariables = ['1' => $clientName];

            try {
                $recipientNumber = self::normalizeRecipient((string) ($client['phone'] ?? ''));
                $message = $twilioClient->messages->create(
                    $recipientNumber,
                    [
                        'from' => $twilioFrom,
                        'messagingServiceSid' => $messagingServiceSid,
                        'contentSid' => $contentSid,
                        'contentVariables' => json_encode($contentVariables, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ]
                );

                $sendStatus = 'sent';
                $twilioMessageSid = (string) ($message->sid ?? '');
                $sent++;
            } catch (\Throwable $exception) {
                $errorMessage = self::sanitizeErrorMessage((string) $exception->getMessage());
                $failed++;
            }

            self::logResult($pdo, [
                'admin_user_id' => $adminUserId,
                'client_user_id' => $clientUserId,
                'client_name' => $clientName,
                'recipient_number' => $recipientNumber !== '' ? $recipientNumber : (string) ($client['phone'] ?? ''),
                'template_key' => $templateKey,
                'template_sid' => $contentSid,
                'content_variables_json' => json_encode($contentVariables, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'twilio_message_sid' => $twilioMessageSid,
                'send_status' => $sendStatus,
                'error_message' => $errorMessage,
            ]);

            $results[] = [
                'client_id' => $clientUserId,
                'client_name' => $clientName,
                'phone' => (string) ($client['phone'] ?? ''),
                'status' => $sendStatus,
                'twilio_sid' => $twilioMessageSid,
                'error' => $errorMessage,
            ];
        }

        return [
            'template' => $template,
            'selected' => count($clientIds),
            'processed' => count($clients),
            'sent' => $sent,
            'failed' => $failed,
            'results' => $results,
        ];
    }

    private static function clientsByIds(PDO $pdo, array $clientIds): array
    {
        $placeholders = implode(',', array_fill(0, count($clientIds), '?'));
        $stmt = $pdo->prepare(
            'SELECT cod_user, noms, email, phone, type_user
             FROM is_users
             WHERE type_user = \'2\' AND cod_user IN (' . $placeholders . ')
             ORDER BY noms ASC, cod_user DESC'
        );
        $stmt->execute($clientIds);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private static function logResult(PDO $pdo, array $payload): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO admin_client_message_logs (
                admin_user_id,
                client_user_id,
                client_name,
                recipient_number,
                template_key,
                template_sid,
                content_variables_json,
                twilio_message_sid,
                send_status,
                error_message,
                sent_at
            ) VALUES (
                :admin_user_id,
                :client_user_id,
                :client_name,
                :recipient_number,
                :template_key,
                :template_sid,
                :content_variables_json,
                :twilio_message_sid,
                :send_status,
                :error_message,
                NOW()
            )'
        );
        $stmt->execute([
            ':admin_user_id' => (int) ($payload['admin_user_id'] ?? 0),
            ':client_user_id' => (int) ($payload['client_user_id'] ?? 0),
            ':client_name' => (string) ($payload['client_name'] ?? ''),
            ':recipient_number' => (string) ($payload['recipient_number'] ?? ''),
            ':template_key' => (string) ($payload['template_key'] ?? ''),
            ':template_sid' => (string) ($payload['template_sid'] ?? ''),
            ':content_variables_json' => (string) ($payload['content_variables_json'] ?? '{}'),
            ':twilio_message_sid' => (string) ($payload['twilio_message_sid'] ?? ''),
            ':send_status' => (string) ($payload['send_status'] ?? 'failed'),
            ':error_message' => (string) ($payload['error_message'] ?? ''),
        ]);
    }

    private static function displayName(array $client): string
    {
        $name = trim((string) ($client['noms'] ?? ''));
        return $name !== '' ? $name : 'Client';
    }

    private static function normalizeRecipient(string $phone): string
    {
        $phone = trim($phone);
        if (stripos($phone, 'whatsapp:') === 0) {
            $phone = substr($phone, 9);
        }

        $phone = preg_replace('/[\s\-().]/', '', $phone) ?? $phone;
        if (!preg_match('/^\+[1-9]\d{7,14}$/', $phone)) {
            throw new RuntimeException('Numero WhatsApp invalide: ' . $phone);
        }

        return 'whatsapp:' . $phone;
    }

    private static function requiredEnv(string $envName, string $label): string
    {
        $value = trim((string) getenv($envName));
        if ($value === '') {
            throw new RuntimeException($label . ' est introuvable. Renseignez ' . $envName . '.');
        }

        return $value;
    }

    private static function messagingServiceSid(): string
    {
        $messagingServiceSid = trim((string) getenv('TWILIO_MESSAGING_SERVICE_SID'));
        if ($messagingServiceSid === '') {
            throw new RuntimeException('Le Messaging Service SID Twilio est introuvable.');
        }
        if (!preg_match('/^MG[0-9a-fA-F]{32}$/', $messagingServiceSid)) {
            throw new RuntimeException('Le Messaging Service SID Twilio est invalide.');
        }

        return $messagingServiceSid;
    }

    private static function fromNumber(): string
    {
        $from = self::TWILIO_WHATSAPP_FROM;
        if (!preg_match('/^whatsapp:\+[1-9]\d{7,14}$/', $from)) {
            throw new RuntimeException('Le numero expediteur WhatsApp Twilio est invalide.');
        }

        return $from;
    }

    private static function sanitizeErrorMessage(string $message): string
    {
        $message = preg_replace('/Authorization:\s*Basic\s+[A-Za-z0-9+\/=._-]+/i', 'Authorization: [REDACTED]', $message) ?? $message;
        $secrets = [trim((string) getenv('TWILIO_AUTH_TOKEN'))];

        foreach ($secrets as $secret) {
            if ($secret !== '') {
                $message = str_replace($secret, '[REDACTED]', $message);
            }
        }

        return trim($message);
    }
}
