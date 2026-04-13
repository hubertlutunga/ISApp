<?php

final class UserAccountService
{
    public static function currentSessionUser(PDO $pdo): ?array
    {
        $phone = $_SESSION['user_phone'] ?? null;
        if (!$phone) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM is_users WHERE phone = ? LIMIT 1');
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function dashboardUrl(array $user, array $config): string
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');

        switch ((string) ($user['type_user'] ?? '')) {
            case '1':
                return $baseUrl . '/event/users/index.php?page=admin_accueil';
            case '3':
                return $baseUrl . '/event/users/index.php?page=crea_accueil';
            case '2':
            default:
                return $baseUrl . '/event/users/index.php?page=mb_accueil';
        }
    }

    public static function authenticate(PDO $pdo, ?string $identifiant, ?string $password): array
    {
        $identifiant = trim((string) $identifiant);
        $password = (string) $password;

        if ($identifiant === '' || $password === '') {
            return [
                'success' => false,
                'message' => 'Identifiant ou mot de passe incorrect.',
            ];
        }

        $stmt = $pdo->prepare('SELECT * FROM is_users WHERE email = ? OR phone = ? LIMIT 1');
        $stmt->execute([$identifiant, $identifiant]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, (string) ($user['password'] ?? ''))) {
            return [
                'success' => false,
                'message' => 'Identifiant ou mot de passe incorrect.',
            ];
        }

        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_email'] = $user['email'];

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    public static function registerCustomer(PDO $pdo, array $input): array
    {
        $name = trim((string) ($input['noms'] ?? ''));
        $phone = preg_replace('/\s+/', '', (string) ($input['phone'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $confirmPassword = (string) ($input['confirm_password'] ?? '');
        $typeUser = (string) ($input['type_user'] ?? '2');

        if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
            return [
                'success' => false,
                'message' => 'Veuillez remplir tous les champs obligatoires.',
            ];
        }

        if ($password !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Les mots de passe ne correspondent pas.',
            ];
        }

        if (!preg_match('/^\+\d{1,3}\d{9,}$/', (string) $phone)) {
            return [
                'success' => false,
                'message' => 'Veuillez entrer un numero de telephone valide au format international. (Ex: +243810678785)',
            ];
        }

        $stmtPhone = $pdo->prepare('SELECT COUNT(*) FROM is_users WHERE phone = ?');
        $stmtPhone->execute([$phone]);
        if ((int) $stmtPhone->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Ce numero de telephone existe deja.',
            ];
        }

        $stmtEmail = $pdo->prepare('SELECT COUNT(*) FROM is_users WHERE email = ?');
        $stmtEmail->execute([$email]);
        if ((int) $stmtEmail->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Cette adresse email existe deja.',
            ];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO is_users (type_user, noms, phone, email, password, recpass) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$typeUser, $name, $phone, $email, $hashedPassword, null]);

        self::sendRegistrationEmail($name, $email, $phone);

        return [
            'success' => true,
            'user' => [
                'type_user' => $typeUser,
                'noms' => $name,
                'phone' => $phone,
                'email' => $email,
            ],
        ];
    }

    private static function sendRegistrationEmail(string $name, string $email, string $phone): void
    {
        $subject = 'Compte chez Invitation Speciale';
        $message = "Bonjour {$name},\n\nVotre compte est cree avec succes.\n\nIdentifiant : {$email} ou {$phone}\n\nPar securite, votre mot de passe n'est pas envoye par email. Utilisez celui que vous avez saisi lors de l'inscription.";
        $headers = "From: contact@invitationspeciale.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";

        @mail($email, $subject, $message, $headers);
    }
}