<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

drc_boot($pdo);
$logoPaths = drc_public_logo_paths();
$csrfKey = 'drcalumni_admin_register_csrf';
$csrfToken = drc_ensure_csrf($csrfKey);

$currentUser = drc_current_user();
$userCount = (int) $pdo->query('SELECT COUNT(*) FROM drcalumni_users')->fetchColumn();
$canManage = $userCount === 0 || ($currentUser !== null && $currentUser['role'] === DRCALUMNI_ROLE_SUPER_ADMIN);
if (!$canManage) {
    header('Location: ' . drc_url('admin/index.php'));
    exit;
}

$errors = [];
$success = '';
$editUser = null;
$protectedAdminEmail = 'superadmin@drcalumni.com';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? 'create_user') === 'create_user') {
    if (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
        $errors['global'] = 'Session expirée.';
    }

    $fullName = drc_clean_text((string) ($_POST['full_name'] ?? ''), 180);
    $email = drc_normalize_email((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
    $role = drc_clean_text((string) ($_POST['role'] ?? DRCALUMNI_ROLE_REVIEWER), 30);
    $isActive = (string) ($_POST['is_active'] ?? '1') === '1' ? 1 : 0;

    if ($fullName === '') $errors['full_name'] = 'Nom requis.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'E-mail invalide.';
    if (strlen($password) < 8) $errors['password'] = 'Minimum 8 caractères.';
    if ($password !== $passwordConfirm) $errors['password_confirm'] = 'Les mots de passe diffèrent.';
    if (!array_key_exists($role, drc_roles())) $role = DRCALUMNI_ROLE_REVIEWER;
    if ($userCount === 0) $role = DRCALUMNI_ROLE_SUPER_ADMIN;

    if ($errors === []) {
        $exists = $pdo->prepare('SELECT id FROM drcalumni_users WHERE email = :email LIMIT 1');
        $exists->execute([':email' => $email]);
        if ($exists->fetch(PDO::FETCH_ASSOC)) {
            $errors['email'] = 'Cet e-mail existe déjà.';
        }
    }

    if ($errors === []) {
        $insert = $pdo->prepare(
            'INSERT INTO drcalumni_users (full_name, email, password_hash, role, is_active)
             VALUES (:full_name, :email, :password_hash, :role, :is_active)'
        );
        $insert->execute([
            ':full_name' => $fullName,
            ':email' => $email,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $role,
            ':is_active' => $isActive,
        ]);
        $newUserId = (int) $pdo->lastInsertId();
        $actorId = is_array($currentUser) ? (int) $currentUser['id'] : null;
        drc_audit($pdo, $actorId, 'user.created', 'user', (string) $newUserId, ['role' => $role]);
        if ($userCount === 0) {
            $first = $pdo->query('SELECT * FROM drcalumni_users WHERE id = ' . $newUserId . ' LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if (is_array($first)) {
                drc_login_user($first);
                header('Location: ' . drc_url('admin/index.php'));
                exit;
            }
        }
        $success = 'Utilisateur créé.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['form_action'] ?? '') === 'update_user') {
    if (!drc_validate_csrf($csrfKey, (string) ($_POST['csrf_token'] ?? ''))) {
        $errors['global'] = 'Session expirée.';
    } else {
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $targetStmt = $pdo->prepare('SELECT * FROM drcalumni_users WHERE id = :id LIMIT 1');
        $targetStmt->execute([':id' => $targetUserId]);
        $targetUser = $targetStmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($targetUser)) {
            $errors['global'] = 'Utilisateur introuvable.';
        } elseif (drc_normalize_email((string) $targetUser['email']) === $protectedAdminEmail) {
            $errors['global'] = 'Le compte superadmin@drcalumni.com est protégé et ne peut pas être modifié.';
        } else {
            $fullName = drc_clean_text((string) ($_POST['edit_full_name'] ?? ''), 180);
            $role = drc_clean_text((string) ($_POST['edit_role'] ?? DRCALUMNI_ROLE_REVIEWER), 30);
            $isActive = (string) ($_POST['edit_is_active'] ?? '0') === '1' ? 1 : 0;
            $newPassword = (string) ($_POST['edit_password'] ?? '');
            $newPasswordConfirm = (string) ($_POST['edit_password_confirm'] ?? '');

            if ($fullName === '') {
                $errors['global'] = 'Nom requis.';
            }
            if (!array_key_exists($role, drc_roles())) {
                $role = DRCALUMNI_ROLE_REVIEWER;
            }
            if ($newPassword !== '' && strlen($newPassword) < 8) {
                $errors['global'] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
            }
            if ($newPassword !== '' && $newPassword !== $newPasswordConfirm) {
                $errors['global'] = 'Les mots de passe de modification ne correspondent pas.';
            }

            if ($errors === []) {
                if ($newPassword !== '') {
                    $update = $pdo->prepare(
                        'UPDATE drcalumni_users
                         SET full_name = :full_name, role = :role, is_active = :is_active, password_hash = :password_hash
                         WHERE id = :id'
                    );
                    $update->execute([
                        ':full_name' => $fullName,
                        ':role' => $role,
                        ':is_active' => $isActive,
                        ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                        ':id' => $targetUserId,
                    ]);
                } else {
                    $update = $pdo->prepare(
                        'UPDATE drcalumni_users
                         SET full_name = :full_name, role = :role, is_active = :is_active
                         WHERE id = :id'
                    );
                    $update->execute([
                        ':full_name' => $fullName,
                        ':role' => $role,
                        ':is_active' => $isActive,
                        ':id' => $targetUserId,
                    ]);
                }

                drc_audit($pdo, (int) ($currentUser['id'] ?? 0), 'user.updated', 'user', (string) $targetUserId, ['role' => $role, 'active' => $isActive]);
                $success = 'Utilisateur modifié.';
            }
        }
    }
}

$users = [];
if ($currentUser !== null && $currentUser['role'] === DRCALUMNI_ROLE_SUPER_ADMIN) {
    $users = $pdo->query('SELECT id, full_name, email, role, is_active, last_login_at, created_at FROM drcalumni_users ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
}

$editId = (int) ($_GET['edit'] ?? ($_POST['user_id'] ?? 0));
if ($editId > 0) {
    foreach ($users as $existingUser) {
        if ((int) $existingUser['id'] === $editId) {
            $editUser = $existingUser;
            break;
        }
    }
}
$swalPayload = null;
if (isset($errors['global'])) {
    $swalPayload = [
        'icon' => 'error',
        'title' => 'Action échouée',
        'text' => (string) $errors['global'],
        'confirmButtonColor' => '#002868',
    ];
} elseif ($success !== '') {
    $swalPayload = [
        'icon' => 'success',
        'title' => 'Utilisateur créé',
        'text' => $success,
        'confirmButtonColor' => '#002868',
    ];
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion utilisateurs | <?php echo drc_h(DRCALUMNI_EVENT_NAME); ?></title>
    <link rel="icon" type="image/png" href="<?php echo drc_h(drc_asset($logoPaths['favicon'])); ?>">
    <link rel="stylesheet" href="<?php echo drc_h(drc_asset('assets/css/admin.css')); ?>">
</head>
<body>
<?php if ($currentUser !== null): ?>
<header class="admin-topbar">
    <div class="admin-topbar-inner" data-admin-topbar>
        <div class="admin-brand">
            <img class="admin-brand-logo" src="<?php echo drc_h(drc_asset('images/use-kinshasa-flag-logo-vertical-color-whitetext.png')); ?>" alt="Ambassade des États-Unis à Kinshasa">
            <img class="admin-brand-logo-secondary" src="<?php echo drc_h(drc_asset('images/use-kinshasa-seal-color.png')); ?>" alt="U.S. Embassy Kinshasa Seal">
            <strong><?php echo drc_h(DRCALUMNI_EVENT_TOPBAR_NAME); ?></strong>
        </div>
        <button class="navbar-toggler" type="button" aria-expanded="false" aria-label="Afficher le menu">☰</button>
        <div class="admin-nav-collapse">
            <nav class="admin-menu">
                <a href="<?php echo drc_h(drc_url('admin/index.php')); ?>">Acceuil</a>
                <a href="<?php echo drc_h(drc_url('admin/presence.php')); ?>">Présence</a>
                <a class="active" href="<?php echo drc_h(drc_url('admin/register.php')); ?>">Utilisateurs</a>
                <a href="<?php echo drc_h(drc_url('admin/settings.php')); ?>">Paramètres</a>
            </nav>
            <div class="admin-user">
                <span>Connecté: <strong><?php echo drc_h((string) $currentUser['name']); ?></strong></span>
                <a class="btn btn-soft" href="<?php echo drc_h(drc_url('admin/index.php?action=logout')); ?>">Déconnexion</a>
            </div>
        </div>
    </div>
</header>
<?php endif; ?>
<main class="content">
<div class="card admin-panel">
    <h1 style="margin:0 0 14px;text-align:center;">Gestion des utilisateurs admin</h1>
    <?php if (isset($errors['global'])): ?><div class="alert alert-error"><?php echo drc_h($errors['global']); ?></div><?php endif; ?>
    <?php if ($success !== ''): ?><div class="alert alert-ok"><?php echo drc_h($success); ?></div><?php endif; ?>
    <form method="post" action="register.php">
        <input type="hidden" name="form_action" value="create_user">
        <input type="hidden" name="csrf_token" value="<?php echo drc_h($csrfToken); ?>">
        <div class="grid-2">
            <div class="field">
                <label for="full_name">Nom complet</label>
                <input id="full_name" name="full_name" type="text" required value="<?php echo drc_h((string) ($_POST['full_name'] ?? '')); ?>">
                <?php if (isset($errors['full_name'])): ?><div class="small" style="color:#b42318;"><?php echo drc_h($errors['full_name']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" required value="<?php echo drc_h((string) ($_POST['email'] ?? '')); ?>">
                <?php if (isset($errors['email'])): ?><div class="small" style="color:#b42318;"><?php echo drc_h($errors['email']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label for="password">Mot de passe</label>
                <input id="password" name="password" type="password" required minlength="8">
                <?php if (isset($errors['password'])): ?><div class="small" style="color:#b42318;"><?php echo drc_h($errors['password']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label for="password_confirm">Confirmer mot de passe</label>
                <input id="password_confirm" name="password_confirm" type="password" required minlength="8">
                <?php if (isset($errors['password_confirm'])): ?><div class="small" style="color:#b42318;"><?php echo drc_h($errors['password_confirm']); ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label for="role">Rôle</label>
                <select id="role" name="role" <?php echo $userCount === 0 ? 'disabled' : ''; ?>>
                    <?php foreach (drc_roles() as $value => $label): ?>
                        <option value="<?php echo drc_h($value); ?>"><?php echo drc_h($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label><input type="checkbox" name="is_active" value="1" checked> Actif</label>
            </div>
        </div>
        <button class="btn btn-primary" type="submit"><?php echo $userCount === 0 ? 'Créer le premier super admin' : 'Créer le compte'; ?></button>
    </form>

    <?php if (is_array($editUser)): ?>
        <hr style="margin:20px 0;border:none;border-top:1px solid #DBE3EF;">
        <h2 style="margin:0 0 10px;">Modifier un utilisateur</h2>
        <form method="post" action="register.php?edit=<?php echo (int) $editUser['id']; ?>">
            <input type="hidden" name="form_action" value="update_user">
            <input type="hidden" name="csrf_token" value="<?php echo drc_h($csrfToken); ?>">
            <input type="hidden" name="user_id" value="<?php echo (int) $editUser['id']; ?>">
            <div class="grid-2">
                <div class="field">
                    <label for="edit_full_name">Nom complet</label>
                    <input id="edit_full_name" name="edit_full_name" type="text" required value="<?php echo drc_h((string) $editUser['full_name']); ?>">
                </div>
                <div class="field">
                    <label for="edit_email">E-mail</label>
                    <input id="edit_email" type="email" readonly value="<?php echo drc_h((string) $editUser['email']); ?>">
                </div>
                <div class="field">
                    <label for="edit_role">Rôle</label>
                    <select id="edit_role" name="edit_role">
                        <?php foreach (drc_roles() as $value => $label): ?>
                            <option value="<?php echo drc_h($value); ?>" <?php echo (string) $editUser['role'] === $value ? 'selected' : ''; ?>><?php echo drc_h($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label><input type="checkbox" name="edit_is_active" value="1" <?php echo (int) $editUser['is_active'] === 1 ? 'checked' : ''; ?>> Actif</label>
                </div>
                <div class="field">
                    <label for="edit_password">Nouveau mot de passe (optionnel)</label>
                    <input id="edit_password" name="edit_password" type="password" minlength="8">
                </div>
                <div class="field">
                    <label for="edit_password_confirm">Confirmer nouveau mot de passe</label>
                    <input id="edit_password_confirm" name="edit_password_confirm" type="password" minlength="8">
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>
        </form>
    <?php endif; ?>

    <?php if ($users !== []): ?>
        <hr style="margin:20px 0;border:none;border-top:1px solid #DBE3EF;">
        <h2 style="margin:0 0 10px;">Comptes existants</h2>
        <div class="table-wrap">
            <table style="min-width:0;">
                <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Actif</th><th>Dernière connexion</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo drc_h((string) $user['full_name']); ?></td>
                        <td><?php echo drc_h((string) $user['email']); ?></td>
                        <td><?php echo drc_h((string) $user['role']); ?></td>
                        <td><?php echo (int) $user['is_active'] === 1 ? 'Oui' : 'Non'; ?></td>
                        <td><?php echo drc_h((string) ($user['last_login_at'] ?? '—')); ?></td>
                        <td>
                            <?php if (drc_normalize_email((string) $user['email']) === $protectedAdminEmail): ?>
                                <span class="small">Compte protégé</span>
                            <?php else: ?>
                                <a class="btn btn-soft" href="<?php echo drc_h(drc_url('admin/register.php?edit=' . (int) $user['id'])); ?>">Éditer</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<footer class="admin-footer">
    <div class="admin-footer-logos">
        <img src="<?php echo drc_h(drc_asset($logoPaths['footer_freedom'])); ?>" alt="Freedom 250">
        <img src="<?php echo drc_h(drc_asset($logoPaths['footer_seal'])); ?>" alt="U.S. Embassy Kinshasa Seal">
    </div>
    <p>Section des Affaires publiques · Ambassade des États-Unis à Kinshasa · Designed by Invitation Speciale</p>
</footer>
</main>
<script src="<?php echo drc_h(drc_asset('sweet/sweetalert2.all.min.js')); ?>" onerror="this.onerror=null;this.src='/sweet/sweetalert2.all.min.js';"></script>
<script>if (!window.Swal) { document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\\/script>'); }</script>
<?php if ($swalPayload !== null): ?>
<script>
window.addEventListener('DOMContentLoaded', function () {
    if (!window.Swal) return;
    Swal.fire(<?php echo json_encode($swalPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
});
</script>
<?php endif; ?>
<script>
document.querySelectorAll('[data-admin-topbar]').forEach((topbar) => {
    const toggle = topbar.querySelector('.navbar-toggler');
    if (!toggle) return;
    toggle.addEventListener('click', () => {
        const opened = topbar.classList.toggle('nav-open');
        toggle.setAttribute('aria-expanded', opened ? 'true' : 'false');
    });
});
</script>
</body>
</html>
