<?php

final class MailerService
{
    private static function cloneMailer(object $mailer, array $config): object
    {
        $message = clone $mailer;

        if (method_exists($message, 'clearAllRecipients')) {
            $message->clearAllRecipients();
        }
        if (method_exists($message, 'clearAttachments')) {
            $message->clearAttachments();
        }
        if (method_exists($message, 'clearCustomHeaders')) {
            $message->clearCustomHeaders();
        }
        if (method_exists($message, 'clearReplyTos')) {
            $message->clearReplyTos();
        }

        self::prepare($message, $config);

        return $message;
    }

    public static function prepare(object $mailer, array $config): bool
    {
        $mailConfig = (array) ($config['mail'] ?? []);
        if ($mailConfig === []) {
            return false;
        }

        if (property_exists($mailer, 'CharSet')) {
            $mailer->CharSet = 'UTF-8';
        }

        if (method_exists($mailer, 'setFrom')) {
            $mailer->setFrom(
                (string) ($mailConfig['from_address'] ?? 'support@invitationspeciale.com'),
                (string) ($mailConfig['from_name'] ?? 'Invitation Speciale')
            );
        }

        if (method_exists($mailer, 'addReplyTo') && !empty($mailConfig['reply_to'])) {
            $mailer->addReplyTo((string) $mailConfig['reply_to'], (string) ($mailConfig['from_name'] ?? 'Invitation Speciale'));
        }

        if (
            !empty($mailConfig['transport'])
            && strtolower((string) $mailConfig['transport']) === 'smtp'
            && method_exists($mailer, 'isSMTP')
            && trim((string) ($mailConfig['host'] ?? '')) !== ''
            && trim((string) ($mailConfig['username'] ?? '')) !== ''
            && trim((string) ($mailConfig['password'] ?? '')) !== ''
        ) {
            $mailer->isSMTP();
            $mailer->Host = (string) $mailConfig['host'];
            $mailer->Port = (int) ($mailConfig['port'] ?? 587);
            $mailer->SMTPAuth = true;
            $mailer->Username = (string) $mailConfig['username'];
            $mailer->Password = (string) ($mailConfig['password'] ?? '');
            $mailer->SMTPSecure = (string) ($mailConfig['encryption'] ?? 'tls');
        }

        return true;
    }

    public static function createMessage(object $mailer, array $config): object
    {
        return self::cloneMailer($mailer, $config);
    }

    public static function sendPasswordReset(object $mailer, array $config, string $recipientEmail, string $recipientName, string $resetUrl): array
    {
        try {
            $message = self::cloneMailer($mailer, $config);

            if (method_exists($message, 'addAddress')) {
                $message->addAddress($recipientEmail, $recipientName);
            }

            if (method_exists($message, 'isHTML')) {
                $message->isHTML(true);
            }

            $safeName = htmlspecialchars($recipientName !== '' ? $recipientName : 'cher client', ENT_QUOTES, 'UTF-8');
            $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
            $message->Subject = 'Reinitialisation de votre mot de passe';
            $message->Body = '
                <div style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#0f172a;">
                    <p>Bonjour ' . $safeName . ',</p>
                    <p>Vous avez demande la reinitialisation de votre mot de passe Invitation Speciale.</p>
                    <p>
                        <a href="' . $safeUrl . '" style="display:inline-block;padding:12px 20px;border-radius:10px;background:#0f766e;color:#ffffff;text-decoration:none;font-weight:700;">
                            Reinitialiser mon mot de passe
                        </a>
                    </p>
                    <p>Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :</p>
                    <p><a href="' . $safeUrl . '">' . $safeUrl . '</a></p>
                    <p>Ce lien expire dans 60 minutes.</p>
                    <p>Si vous n etes pas a l origine de cette demande, ignorez simplement ce message.</p>
                </div>';
            $message->AltBody = "Bonjour {$recipientName},\n\nVous avez demande la reinitialisation de votre mot de passe Invitation Speciale.\n\nUtilisez ce lien : {$resetUrl}\n\nCe lien expire dans 60 minutes.\n\nSi vous n etes pas a l origine de cette demande, ignorez simplement ce message.";
            $message->send();

            return [
                'success' => true,
            ];
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public static function sendRegistrationWelcome(object $mailer, array $config, string $recipientEmail, string $recipientName, string $phone): array
    {
        try {
            $message = self::cloneMailer($mailer, $config);

            if (method_exists($message, 'addAddress')) {
                $message->addAddress($recipientEmail, $recipientName);
            }

            if (method_exists($message, 'isHTML')) {
                $message->isHTML(true);
            }

            $safeName = htmlspecialchars($recipientName !== '' ? $recipientName : 'cher client', ENT_QUOTES, 'UTF-8');
            $safeEmail = htmlspecialchars($recipientEmail, ENT_QUOTES, 'UTF-8');
            $safePhone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
            $message->Subject = 'Bienvenue sur Invitation Speciale';
            $message->Body = '
                <div style="font-family:Arial,sans-serif;font-size:15px;line-height:1.6;color:#0f172a;">
                    <p>Bonjour ' . $safeName . ',</p>
                    <p>Votre compte client Invitation Speciale a ete cree avec succes.</p>
                    <p>Vous pouvez vous connecter avec :</p>
                    <ul>
                        <li>Email : ' . $safeEmail . '</li>
                        <li>Telephone : ' . $safePhone . '</li>
                    </ul>
                    <p>Pour des raisons de securite, votre mot de passe n est jamais envoye par email.</p>
                </div>';
            $message->AltBody = "Bonjour {$recipientName},\n\nVotre compte client Invitation Speciale a ete cree avec succes.\n\nVous pouvez vous connecter avec :\n- Email : {$recipientEmail}\n- Telephone : {$phone}\n\nPour des raisons de securite, votre mot de passe n est jamais envoye par email.";
            $message->send();

            return [
                'success' => true,
            ];
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }
}