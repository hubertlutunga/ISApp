<?php

final class UserAccountService
{
    public static function ensurePasswordResetInfrastructure(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS is_user_password_resets (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token_hash CHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                used_at DATETIME NULL,
                requested_ip VARCHAR(45) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_is_user_password_resets_user_id (user_id),
                UNIQUE KEY uniq_is_user_password_resets_token_hash (token_hash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public static function currentSessionUser(PDO $pdo): ?array
    {
        $phone = $_SESSION['user_phone'] ?? null;
        if (!$phone) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM is_users WHERE phone = ? LIMIT 1');
        $stmt->execute([$phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

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

        self::clearImpersonation();

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    public static function isImpersonating(): bool
    {
        return isset($_SESSION['impersonator_phone'], $_SESSION['impersonator_email']);
    }

    public static function impersonatorSessionUser(PDO $pdo): ?array
    {
        $phone = $_SESSION['impersonator_phone'] ?? null;
        if (!$phone) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT * FROM is_users WHERE phone = ? LIMIT 1');
        $stmt->execute([$phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function startImpersonation(PDO $pdo, int $targetUserId): array
    {
        $currentUser = self::currentSessionUser($pdo);
        if (!$currentUser) {
            return [
                'success' => false,
                'message' => 'Session invalide.',
            ];
        }

        if ((string) ($currentUser['type_user'] ?? '') !== '1') {
            return [
                'success' => false,
                'message' => 'Seul un administrateur peut acceder a cette fonctionnalite.',
            ];
        }

        $stmt = $pdo->prepare('SELECT * FROM is_users WHERE cod_user = ? LIMIT 1');
        $stmt->execute([$targetUserId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        if ($targetUser === []) {
            return [
                'success' => false,
                'message' => 'Client introuvable.',
            ];
        }

        if ((string) ($targetUser['cod_user'] ?? '') === (string) ($currentUser['cod_user'] ?? '')) {
            return [
                'success' => false,
                'message' => 'Vous etes deja connecte sur ce compte.',
            ];
        }

        if (!self::isImpersonating()) {
            $_SESSION['impersonator_phone'] = $currentUser['phone'] ?? null;
            $_SESSION['impersonator_email'] = $currentUser['email'] ?? null;
        }

        $_SESSION['user_phone'] = $targetUser['phone'] ?? null;
        $_SESSION['user_email'] = $targetUser['email'] ?? null;

        return [
            'success' => true,
            'user' => $targetUser,
            'message' => 'Vous etes maintenant connecte comme ce client.',
        ];
    }

    public static function stopImpersonation(PDO $pdo): array
    {
        if (!self::isImpersonating()) {
            return [
                'success' => false,
                'message' => 'Aucune usurpation active.',
            ];
        }

        $adminUser = self::impersonatorSessionUser($pdo);
        if (!$adminUser || (string) ($adminUser['type_user'] ?? '') !== '1') {
            self::clearImpersonation();

            return [
                'success' => false,
                'message' => 'Session administrateur introuvable.',
            ];
        }

        $_SESSION['user_phone'] = $adminUser['phone'];
        $_SESSION['user_email'] = $adminUser['email'];
        self::clearImpersonation();

        return [
            'success' => true,
            'user' => $adminUser,
            'message' => 'Retour au compte administrateur effectue.',
        ];
    }

    public static function clearImpersonation(): void
    {
        unset($_SESSION['impersonator_phone'], $_SESSION['impersonator_email']);
    }

    public static function updateProfile(PDO $pdo, int $userId, array $input): array
    {
        $name = trim((string) ($input['noms'] ?? ''));
        $phone = preg_replace('/\s+/', '', (string) ($input['phone'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));

        if ($userId <= 0) {
            return [
                'success' => false,
                'message' => 'Utilisateur introuvable.',
            ];
        }

        if ($name === '' || $phone === '' || $email === '') {
            return [
                'success' => false,
                'message' => 'Veuillez remplir tous les champs obligatoires.',
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Veuillez entrer une adresse email valide.',
            ];
        }

        if (!preg_match('/^\+\d{1,3}\d{9,}$/', $phone)) {
            return [
                'success' => false,
                'message' => 'Veuillez entrer un numero de telephone valide au format international. (Ex: +243810678785)',
            ];
        }

        $duplicatePhoneStmt = $pdo->prepare('SELECT COUNT(*) FROM is_users WHERE phone = ? AND cod_user <> ?');
        $duplicatePhoneStmt->execute([$phone, $userId]);
        if ((int) $duplicatePhoneStmt->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Ce numero de telephone est deja utilise.',
            ];
        }

        $duplicateEmailStmt = $pdo->prepare('SELECT COUNT(*) FROM is_users WHERE email = ? AND cod_user <> ?');
        $duplicateEmailStmt->execute([$email, $userId]);
        if ((int) $duplicateEmailStmt->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Cette adresse email est deja utilisee.',
            ];
        }

        $stmt = $pdo->prepare('UPDATE is_users SET noms = ?, phone = ?, email = ? WHERE cod_user = ? LIMIT 1');
        $stmt->execute([$name, $phone, $email, $userId]);

        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_email'] = $email;

        return [
            'success' => true,
            'message' => 'Vos informations ont ete mises a jour.',
        ];
    }

    public static function changePassword(PDO $pdo, int $userId, ?string $currentPassword, ?string $newPassword, ?string $confirmPassword): array
    {
        $currentPassword = (string) $currentPassword;
        $newPassword = (string) $newPassword;
        $confirmPassword = (string) $confirmPassword;

        if ($userId <= 0) {
            return [
                'success' => false,
                'message' => 'Utilisateur introuvable.',
            ];
        }

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return [
                'success' => false,
                'message' => 'Veuillez remplir tous les champs du mot de passe.',
            ];
        }

        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Les nouveaux mots de passe ne correspondent pas.',
            ];
        }

        if (strlen($newPassword) < 8) {
            return [
                'success' => false,
                'message' => 'Le nouveau mot de passe doit contenir au moins 8 caracteres.',
            ];
        }

        $stmt = $pdo->prepare('SELECT cod_user, password FROM is_users WHERE cod_user = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        if ($user === [] || !password_verify($currentPassword, (string) ($user['password'] ?? ''))) {
            return [
                'success' => false,
                'message' => 'Le mot de passe actuel est incorrect.',
            ];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare('UPDATE is_users SET password = ?, recpass = NULL WHERE cod_user = ? LIMIT 1');
        $updateStmt->execute([$hashedPassword, $userId]);

        return [
            'success' => true,
            'message' => 'Votre mot de passe a ete modifie avec succes.',
        ];
    }

    public static function requestPasswordReset(PDO $pdo, object $mailer, array $config, ?string $email): array
    {
        self::ensurePasswordResetInfrastructure($pdo);

        $email = trim((string) $email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Veuillez entrer une adresse email valide.',
            ];
        }

        $stmt = $pdo->prepare('SELECT cod_user, noms, email FROM is_users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        if ($user !== []) {
            $pdo->prepare('DELETE FROM is_user_password_resets WHERE user_id = ? OR expires_at < NOW()')->execute([(int) $user['cod_user']]);

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new DateTimeImmutable('+60 minutes'))->format('Y-m-d H:i:s');
            $requestedIp = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));

            $insertStmt = $pdo->prepare('INSERT INTO is_user_password_resets (user_id, token_hash, expires_at, requested_ip) VALUES (?, ?, ?, ?)');
            $insertStmt->execute([(int) $user['cod_user'], $tokenHash, $expiresAt, $requestedIp !== '' ? $requestedIp : null]);

            $resetUrl = rtrim((string) ($config['base_url'] ?? ''), '/') . '/event/index.php?page=reset_password&token=' . urlencode($token);
            $mailResult = MailerService::sendPasswordReset(
                $mailer,
                $config,
                (string) $user['email'],
                trim((string) ($user['noms'] ?? '')),
                $resetUrl
            );

            if (empty($mailResult['success'])) {
                return [
                    'success' => false,
                    'message' => 'La demande a ete enregistree, mais l email n a pas pu etre envoye. Verifiez la configuration mail.',
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Si cette adresse email existe dans notre base, un lien de reinitialisation vient d etre envoye.',
        ];
    }

    public static function resetPasswordWithToken(PDO $pdo, ?string $token, ?string $newPassword, ?string $confirmPassword): array
    {
        self::ensurePasswordResetInfrastructure($pdo);

        $token = trim((string) $token);
        $newPassword = (string) $newPassword;
        $confirmPassword = (string) $confirmPassword;

        if ($token === '') {
            return [
                'success' => false,
                'message' => 'Le lien de reinitialisation est invalide.',
            ];
        }

        if ($newPassword === '' || $confirmPassword === '') {
            return [
                'success' => false,
                'message' => 'Veuillez renseigner et confirmer votre nouveau mot de passe.',
            ];
        }

        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Les nouveaux mots de passe ne correspondent pas.',
            ];
        }

        if (strlen($newPassword) < 8) {
            return [
                'success' => false,
                'message' => 'Le nouveau mot de passe doit contenir au moins 8 caracteres.',
            ];
        }

        $tokenHash = hash('sha256', $token);
        $stmt = $pdo->prepare(
            'SELECT r.id, r.user_id
             FROM is_user_password_resets r
             WHERE r.token_hash = ?
               AND r.used_at IS NULL
               AND r.expires_at >= NOW()
             LIMIT 1'
        );
        $stmt->execute([$tokenHash]);
        $resetRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        if ($resetRow === []) {
            return [
                'success' => false,
                'message' => 'Le lien de reinitialisation est invalide ou a expire.',
            ];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE is_users SET password = ?, recpass = NULL WHERE cod_user = ? LIMIT 1')->execute([$hashedPassword, (int) $resetRow['user_id']]);
        $pdo->prepare('UPDATE is_user_password_resets SET used_at = NOW() WHERE id = ? LIMIT 1')->execute([(int) $resetRow['id']]);
        $pdo->prepare('DELETE FROM is_user_password_resets WHERE user_id = ? AND id <> ?')->execute([(int) $resetRow['user_id'], (int) $resetRow['id']]);

        return [
            'success' => true,
            'message' => 'Votre mot de passe a ete reinitialise avec succes.',
        ];
    }

    public static function registerCustomer(PDO $pdo, array $input, ?object $mailer = null, array $config = []): array
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

        $mailSent = true;
        if ($mailer !== null) {
            $mailResult = MailerService::sendRegistrationWelcome($mailer, $config, $email, $name, $phone);
            $mailSent = !empty($mailResult['success']);
        }

        return [
            'success' => true,
            'message' => $mailSent
                ? 'Votre compte est cree avec succes. Vos informations de connexion ont ete envoyees par email.'
                : 'Votre compte est cree avec succes, mais l email de bienvenue n a pas pu etre envoye.',
            'user' => [
                'type_user' => $typeUser,
                'noms' => $name,
                'phone' => $phone,
                'email' => $email,
            ],
        ];
    }

}