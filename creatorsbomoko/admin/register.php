<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/database.php';

function cb_admin_register_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cb_admin_register_clean(string $value, int $maxLength = 300): string
{
    $value = trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    return substr($value, 0, $maxLength);
}

function cb_admin_register_ensure_users_table(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users_cbomoko (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_cbomoko_email (email),
    KEY idx_users_cbomoko_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
}

function cb_admin_register_is_logged_in(): bool
{
    return isset($_SESSION['cbomoko_admin_user_id']);
}

function cb_admin_register_redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

try {
    cb_admin_register_ensure_users_table($pdo);
    $setupError = '';
} catch (Throwable $exception) {
    error_log('[Creators Bomoko Register Admin] ' . $exception->getMessage());
    $setupError = 'Impossible de préparer la table users_cbomoko.';
}

if (empty($_SESSION['cbomoko_admin_register_csrf'])) {
    $_SESSION['cbomoko_admin_register_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = (string) $_SESSION['cbomoko_admin_register_csrf'];

$userCount = 0;
$admins = [];
if ($setupError === '') {
    $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users_cbomoko')->fetchColumn();
    if (cb_admin_register_is_logged_in()) {
        $admins = $pdo->query('SELECT id, full_name, email, role, is_active, last_login_at, created_at FROM users_cbomoko ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
}

if ($userCount > 0 && !cb_admin_register_is_logged_in()) {
    cb_admin_register_redirect('index.php');
}

$errors = [];
$success = '';
$isFirstAdmin = $userCount === 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
        $errors['global'] = 'Session expirée. Veuillez réessayer.';
    }

    $fullName = cb_admin_register_clean((string) ($_POST['full_name'] ?? ''), 160);
    $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_SANITIZE_EMAIL);
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
    $role = cb_admin_register_clean((string) ($_POST['role'] ?? 'admin'), 30);
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($fullName === '') {
        $errors['full_name'] = 'Indiquez le nom complet.';
    }
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Indiquez une adresse e-mail valide.';
    }
    if (!in_array($role, ['admin', 'super_admin'], true)) {
        $role = 'admin';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($password !== $passwordConfirm) {
        $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
    }

    if ($errors === []) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users_cbomoko WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors['email'] = 'Cette adresse e-mail existe déjà.';
        }
    }

    if ($errors === []) {
        $stmt = $pdo->prepare('INSERT INTO users_cbomoko (full_name, email, password_hash, role, is_active) VALUES (:full_name, :email, :password_hash, :role, :is_active)');
        $stmt->execute([
            ':full_name' => $fullName,
            ':email' => (string) $email,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $role,
            ':is_active' => $isActive,
        ]);

        $newUserId = (int) $pdo->lastInsertId();
        $_SESSION['cbomoko_admin_register_csrf'] = bin2hex(random_bytes(32));

        if ($isFirstAdmin) {
            $_SESSION['cbomoko_admin_user_id'] = $newUserId;
            $_SESSION['cbomoko_admin_name'] = $fullName;
            $_SESSION['cbomoko_admin_email'] = (string) $email;
            $_SESSION['cbomoko_admin_role'] = $role;
            cb_admin_register_redirect('index.php');
        }

        $success = 'Administrateur enregistré avec succès.';
        $admins = $pdo->query('SELECT id, full_name, email, role, is_active, last_login_at, created_at FROM users_cbomoko ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscrire un administrateur | Creators Bomoko</title>
    <link rel="icon" type="image/png" href="../images/favicom.png">
    <style>
        :root{--ink:#25140b;--muted:#725c45;--paper:#fffaf1;--cream:#fff4df;--wood:#8b4a1f;--wood-dark:#35180b;--blue:#0a3a73;--red:#d7354a;--line:#ead7bd;--shadow:0 24px 80px rgba(67,36,15,.16)}
        *{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--ink);background:radial-gradient(circle at 10% 0%,rgba(22,181,168,.16),transparent 30rem),radial-gradient(circle at 90% 0%,rgba(215,53,74,.12),transparent 28rem),linear-gradient(180deg,#fff9ee,#f4e6d2);min-height:100vh}a{color:inherit}.shell{width:min(980px,100%);margin:0 auto;padding:28px clamp(16px,4vw,46px) 54px}.top{display:flex;justify-content:space-between;gap:18px;align-items:center;margin-bottom:24px}.brand{display:flex;align-items:center;gap:18px}.brand img{width:clamp(320px,38vw,540px);height:auto;object-fit:contain;background:linear-gradient(180deg,#fff,#fffaf1);border-radius:28px;padding:0;box-shadow:0 18px 48px rgba(53,24,11,.2)}h1{font-size:clamp(30px,4vw,48px);line-height:1;margin:0;letter-spacing:-.06em}.muted{color:var(--muted);font-weight:700}.card{background:rgba(255,250,241,.94);border:1px solid rgba(139,74,31,.14);border-radius:28px;box-shadow:var(--shadow);padding:24px;margin-bottom:20px}label{display:block;font-weight:900;margin:0 0 8px}input,select{width:100%;border:1px solid #d9c5a8;border-radius:14px;background:#fff;padding:12px 13px;font:inherit;color:var(--ink)}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.field{margin-bottom:16px}.check{display:flex;gap:10px;align-items:center;font-weight:900}.check input{width:auto}.btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:999px;padding:12px 18px;font-weight:900;cursor:pointer;text-decoration:none}.btn-primary{background:linear-gradient(135deg,var(--red),var(--wood),var(--blue));color:#fff}.btn-soft{background:#f3dfc2;color:var(--wood-dark)}.alert{margin:0 0 18px;padding:13px 15px;border-radius:16px;font-weight:800}.alert-error{background:#fff1f0;color:#b42318;border:1px solid #ffccc7}.alert-ok{background:#ecfdf5;color:#0f766e;border:1px solid #a7f3d0}.error{color:#b42318;font-weight:800;font-size:13px;margin-top:7px}.table-wrap{overflow:auto;border-radius:20px;border:1px solid var(--line);background:#fff}table{width:100%;border-collapse:collapse;min-width:760px}th,td{padding:13px;border-bottom:1px solid #f0dfc8;text-align:left}th{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#745139;background:#fff8ed}.badge{display:inline-flex;padding:6px 10px;border-radius:999px;background:#f3dfc2;color:var(--wood-dark);font-weight:900;font-size:12px}.admin-footer{margin:28px auto 0;padding:18px 10px;color:var(--muted);display:grid;justify-items:center;gap:12px;text-align:center;font-weight:400}.footer-separator{width:100%;border:0;border-top:1px solid var(--line);margin:0 0 4px}.admin-footer__logos{display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap}.admin-footer__logos img{width:86px;height:64px;object-fit:contain}.admin-footer__logos img.is-footer-logo{width:150px}@media(max-width:760px){.top,.brand,.grid{grid-template-columns:1fr;display:grid}.brand img{width:min(100%,430px)}.btn{width:100%}.admin-footer__logos img.is-footer-logo{width:130px}}
    </style>
</head>
<body>
<main class="shell">
    <header class="top">
        <div class="brand">
            <img src="../images/CB Horizontal White BG.png" alt="Creators Bomoko">
            <div>
                <h1><?php echo $isFirstAdmin ? 'Premier admin' : 'Nouvel admin'; ?></h1>
                <div class="muted">Table users_cbomoko</div>
            </div>
        </div>
        <a class="btn btn-soft" href="index.php">Retour au backoffice</a>
    </header>

    <section class="card">
        <?php if ($setupError !== ''): ?><div class="alert alert-error"><?php echo cb_admin_register_h($setupError); ?></div><?php endif; ?>
        <?php if (isset($errors['global'])): ?><div class="alert alert-error"><?php echo cb_admin_register_h($errors['global']); ?></div><?php endif; ?>
        <?php if ($success !== ''): ?><div class="alert alert-ok"><?php echo cb_admin_register_h($success); ?></div><?php endif; ?>

        <form method="post" action="register.php">
            <input type="hidden" name="csrf_token" value="<?php echo cb_admin_register_h($csrfToken); ?>">
            <div class="grid">
                <div class="field">
                    <label for="full_name">Nom complet</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo cb_admin_register_h((string) ($_POST['full_name'] ?? '')); ?>" required>
                    <?php if (isset($errors['full_name'])): ?><div class="error"><?php echo cb_admin_register_h($errors['full_name']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo cb_admin_register_h((string) ($_POST['email'] ?? '')); ?>" required>
                    <?php if (isset($errors['email'])): ?><div class="error"><?php echo cb_admin_register_h($errors['email']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" minlength="8" required>
                    <?php if (isset($errors['password'])): ?><div class="error"><?php echo cb_admin_register_h($errors['password']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" minlength="8" required>
                    <?php if (isset($errors['password_confirm'])): ?><div class="error"><?php echo cb_admin_register_h($errors['password_confirm']); ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label for="role">Rôle</label>
                    <select id="role" name="role">
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super admin</option>
                    </select>
                </div>
                <div class="field" style="display:flex;align-items:end">
                    <label class="check"><input type="checkbox" name="is_active" value="1" checked> Compte actif</label>
                </div>
            </div>
            <button class="btn btn-primary" type="submit"><?php echo $isFirstAdmin ? 'Créer et se connecter' : 'Inscrire l’administrateur'; ?></button>
        </form>
    </section>

    <?php if (cb_admin_register_is_logged_in()): ?>
        <section class="card">
            <h2 style="margin-top:0">Administrateurs enregistrés</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>État</th><th>Dernière connexion</th><th>Créé le</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?php echo cb_admin_register_h((string) $admin['full_name']); ?></td>
                            <td><?php echo cb_admin_register_h((string) $admin['email']); ?></td>
                            <td><span class="badge"><?php echo cb_admin_register_h((string) $admin['role']); ?></span></td>
                            <td><?php echo (int) $admin['is_active'] === 1 ? 'Actif' : 'Inactif'; ?></td>
                            <td><?php echo cb_admin_register_h((string) ($admin['last_login_at'] ?? '—')); ?></td>
                            <td><?php echo cb_admin_register_h((string) $admin['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
    <footer class="admin-footer">
        <hr class="footer-separator">
        <div>©2026 Creators Bomoko powered by U.S Embassy Kinshasa · Designed by Hubert Solutions</div>
    </footer>
</main>
</body>
</html>
