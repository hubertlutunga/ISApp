<?php

final class GuestConfirmationMailService
{
    public static function sendForConfirmation(PDO $pdo, object $mailer, array $config, int $eventId, int $confirmationId, ?int $senderUserId = null): array
    {
        $confirmation = ConfirmationService::findById($pdo, $eventId, $confirmationId);
        if ($confirmation === null) {
            return [
                'success' => false,
                'message' => 'Confirmation introuvable.',
            ];
        }

        $recipientEmail = trim((string) ($confirmation['email'] ?? ''));
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Aucune adresse email valide n est disponible pour cet invite.',
            ];
        }

        $inviteId = (int) ($confirmation['invite_id'] ?? 0);
        if ($inviteId <= 0) {
            return [
                'success' => false,
                'message' => 'Impossible de rattacher cette confirmation a un invite existant.',
            ];
        }

        $event = self::findEvent($pdo, $eventId);
        if ($event === null) {
            return [
                'success' => false,
                'message' => 'Evenement introuvable.',
            ];
        }

        $accessUrl = self::buildAccessUrl($event, $config, $inviteId, 'oui');
        $publicUrl = EventUrlService::publicUrl($event, $config);
        $qrCodeDataUri = self::buildQrCodeDataUri($accessUrl);
        $eventSummary = self::buildEventSummary($event);
        $eventDateLabel = self::formatEventDate((string) ($event['date_event'] ?? ''));
        $recipientName = self::formatRecipientName((string) ($confirmation['noms'] ?? ''));

        try {
            $message = MailerService::createMessage($mailer, $config);

            if (method_exists($message, 'addAddress')) {
                $message->addAddress($recipientEmail, $recipientName);
            }

            if (method_exists($message, 'isHTML')) {
                $message->isHTML(true);
            }

            $safeRecipientName = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
            $safeEventSummary = htmlspecialchars($eventSummary, ENT_QUOTES, 'UTF-8');
            $safeDate = htmlspecialchars($eventDateLabel, ENT_QUOTES, 'UTF-8');
            $safeLieu = htmlspecialchars(self::fallbackValue((string) ($event['lieu'] ?? '')), ENT_QUOTES, 'UTF-8');
            $safeAdresse = htmlspecialchars(self::fallbackValue((string) ($event['adresse'] ?? '')), ENT_QUOTES, 'UTF-8');
            $safePublicUrl = htmlspecialchars($publicUrl, ENT_QUOTES, 'UTF-8');
            $safeAccessUrl = htmlspecialchars($accessUrl, ENT_QUOTES, 'UTF-8');

            $message->Subject = 'Informations de votre evenement - ' . $eventSummary;
            $message->Body = '
                <div style="font-family:Arial,sans-serif;font-size:15px;line-height:1.7;color:#0f172a;max-width:680px;">
                    <p>Bonjour ' . $safeRecipientName . ',</p>
                    <p>Voici les informations utiles pour votre participation a <strong>' . $safeEventSummary . '</strong>.</p>
                    <div style="padding:18px 20px;border-radius:18px;background:#f8fafc;border:1px solid #e2e8f0;">
                        <p style="margin:0 0 10px;"><strong>Date et heure :</strong> ' . $safeDate . '</p>
                        <p style="margin:0 0 10px;"><strong>Lieu :</strong> ' . $safeLieu . '</p>
                        <p style="margin:0;"><strong>Adresse :</strong> ' . $safeAdresse . '</p>
                    </div>
                    <p style="margin-top:20px;">Vous pouvez consulter l acces de votre table et les informations de verification via ce lien :</p>
                    <p>
                        <a href="' . $safeAccessUrl . '" style="display:inline-block;padding:12px 20px;border-radius:12px;background:#0f766e;color:#ffffff;text-decoration:none;font-weight:700;">
                            Ouvrir mon acces invite
                        </a>
                    </p>
                    <p>Vous pouvez egalement utiliser le QR code ci-dessous :</p>
                    <p><img src="' . $qrCodeDataUri . '" alt="QR code d acces" style="display:block;width:220px;max-width:100%;height:auto;border:1px solid #e2e8f0;border-radius:16px;padding:12px;background:#ffffff;"></p>
                    <p>Page publique de l evenement : <a href="' . $safePublicUrl . '">' . $safePublicUrl . '</a></p>
                    <p>A bientot,<br>Invitation Speciale</p>
                </div>';
            $message->AltBody = "Bonjour {$recipientName},\n\n"
                . "Voici les informations utiles pour votre participation a {$eventSummary}.\n\n"
                . "Date et heure : {$eventDateLabel}\n"
                . "Lieu : " . self::fallbackValue((string) ($event['lieu'] ?? '')) . "\n"
                . "Adresse : " . self::fallbackValue((string) ($event['adresse'] ?? '')) . "\n\n"
                . "Acces invite : {$accessUrl}\n"
                . "Page publique de l evenement : {$publicUrl}\n\n"
                . "Invitation Speciale";
            $message->send();
            ConfirmationService::registerMailSent($pdo, $eventId, $confirmationId, $recipientEmail, $senderUserId);

            return [
                'success' => true,
                'message' => 'Le mail a ete envoye avec succes a ' . $recipientEmail . '.',
            ];
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }

    private static function findEvent(PDO $pdo, int $eventId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM events WHERE cod_event = :cod_event LIMIT 1');
        $stmt->execute([':cod_event' => $eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        return $event ?: null;
    }

    private static function buildAccessUrl(array $event, array $config, int $inviteId, string $presence): string
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://invitationspeciale.com'), '/');

        return $baseUrl
            . '/site/index.php?page=access_cible&cod=' . rawurlencode((string) ($event['cod_event'] ?? ''))
            . '&codinv=' . rawurlencode((string) $inviteId)
            . '&presence=' . rawurlencode($presence !== '' ? $presence : 'oui');
    }

    private static function buildQrCodeDataUri(string $content): string
    {
        require_once dirname(__DIR__, 2) . '/qrscan/phpqrcode/qrlib.php';

        ob_start();
        QRcode::png($content, null, QR_ECLEVEL_M, 6, 2);
        $png = (string) ob_get_clean();

        return 'data:image/png;base64,' . base64_encode($png);
    }

    private static function buildEventSummary(array $event): string
    {
        $eventType = (string) ($event['type_event'] ?? '');

        if ($eventType === '1') {
            $firstName = (string) ($event['prenom_epoux'] ?? '');
            $secondName = (string) ($event['prenom_epouse'] ?? '');
            if ((string) ($event['ordrepri'] ?? '') !== 'm') {
                $firstName = (string) ($event['prenom_epouse'] ?? '');
                $secondName = (string) ($event['prenom_epoux'] ?? '');
            }

            return trim('Mariage ' . trim((string) ($event['type_mar'] ?? '')) . ' de ' . trim($firstName . ' & ' . $secondName));
        }

        if ($eventType === '2') {
            return trim('Anniversaire de ' . (string) ($event['nomfetard'] ?? ''));
        }

        if ($eventType === '3') {
            return trim('Conference ' . (string) ($event['nomfetard'] ?? ''));
        }

        return trim((string) ($event['nomfetard'] ?? 'Evenement'));
    }

    private static function formatEventDate(string $dateValue): string
    {
        $timestamp = strtotime($dateValue);
        if ($timestamp === false) {
            return 'Date non definie';
        }

        return date('d/m/Y a H:i', $timestamp);
    }

    private static function formatRecipientName(string $name): string
    {
        $cleanName = trim($name);
        if ($cleanName === '') {
            return 'cher invite';
        }

        if (function_exists('mb_strtolower')) {
            return ucwords(mb_strtolower($cleanName, 'UTF-8'));
        }

        return ucwords(strtolower($cleanName));
    }

    private static function fallbackValue(string $value): string
    {
        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : 'Non defini';
    }
}