<?php

$profileUser = UserAccountService::currentSessionUser($pdo) ?? [];
if ($profileUser === []) {
    PageRouter::redirect('index.php?page=logout');
}

$profileFlash = null;
$profileFlashType = 'success';
$profileAlertScript = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_profile'])) {
        $profileResult = UserAccountService::updateProfile($pdo, (int) ($profileUser['cod_user'] ?? 0), $_POST);
        $profileFlash = (string) ($profileResult['message'] ?? '');
        $profileFlashType = !empty($profileResult['success']) ? 'success' : 'error';
        $profileAlertScript = '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({title:' . json_encode(!empty($profileResult['success']) ? 'Profil mis a jour' : 'Mise a jour impossible') . ',text:' . json_encode($profileFlash) . ',icon:' . json_encode(!empty($profileResult['success']) ? 'success' : 'error') . ',confirmButtonText:"Fermer"});});</script>';
        $profileUser = UserAccountService::currentSessionUser($pdo) ?? $profileUser;
    }

    if (isset($_POST['change_password'])) {
        $passwordResult = UserAccountService::changePassword(
            $pdo,
            (int) ($profileUser['cod_user'] ?? 0),
            $_POST['current_password'] ?? null,
            $_POST['new_password'] ?? null,
            $_POST['confirm_new_password'] ?? null
        );
        $profileFlash = (string) ($passwordResult['message'] ?? '');
        $profileFlashType = !empty($passwordResult['success']) ? 'success' : 'error';
        $profileAlertScript = '<script>document.addEventListener("DOMContentLoaded",function(){Swal.fire({title:' . json_encode(!empty($passwordResult['success']) ? 'Mot de passe modifie' : 'Modification impossible') . ',text:' . json_encode($profileFlash) . ',icon:' . json_encode(!empty($passwordResult['success']) ? 'success' : 'error') . ',confirmButtonText:"Fermer"});});</script>';
    }
}
?>

<div class="wrapper">
    <?php include('header.php'); ?>

    <div class="content-wrapper">
        <div class="container-full">
            <div class="container py-30">
                <section class="client-profile-hero">
                    <div>
                        <span class="client-profile-kicker">Mon compte</span>
                        <h1>Profil client</h1>
                        <p>Modifiez vos informations de contact, puis mettez a jour votre mot de passe depuis un espace unique.</p>
                    </div>
                    <div class="client-profile-summary">
                        <span><strong><?php echo htmlspecialchars((string) ($profileUser['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                        <span><?php echo htmlspecialchars((string) ($profileUser['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </section>

                <?php if ($profileFlash !== null && $profileFlash !== '') { ?>
                <div class="client-profile-flash client-profile-flash-<?php echo htmlspecialchars($profileFlashType, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($profileFlash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php } ?>

                <div class="row g-4">
                    <div class="col-xl-7 col-12">
                        <section class="client-profile-card">
                            <div class="client-profile-head">
                                <h2>Informations personnelles</h2>
                                <p>Gardez vos coordonnees a jour pour vos commandes, confirmations et echanges.</p>
                            </div>

                            <form method="post" class="client-profile-form-grid">
                                <input type="hidden" name="save_profile" value="1">

                                <label>
                                    <span>Noms</span>
                                    <input type="text" name="noms" value="<?php echo htmlspecialchars((string) ($profileUser['noms'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                </label>

                                <label>
                                    <span>Telephone</span>
                                    <input type="text" name="phone" value="<?php echo htmlspecialchars((string) ($profileUser['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                </label>

                                <label class="client-profile-field-full">
                                    <span>Email</span>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars((string) ($profileUser['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                </label>

                                <div class="client-profile-actions client-profile-field-full">
                                    <button type="submit" class="btn btn-primary">Enregistrer mes informations</button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <div class="col-xl-5 col-12">
                        <section class="client-profile-card">
                            <div class="client-profile-head">
                                <h2>Securite</h2>
                                <p>Changez votre mot de passe actuel. En cas d oubli, utilisez le lien depuis l ecran de connexion.</p>
                            </div>

                            <form method="post" class="client-profile-form-grid">
                                <input type="hidden" name="change_password" value="1">

                                <label class="client-profile-field-full">
                                    <span>Mot de passe actuel</span>
                                    <input type="password" name="current_password" required>
                                </label>

                                <label>
                                    <span>Nouveau mot de passe</span>
                                    <input type="password" name="new_password" minlength="8" required>
                                </label>

                                <label>
                                    <span>Confirmer le mot de passe</span>
                                    <input type="password" name="confirm_new_password" minlength="8" required>
                                </label>

                                <div class="client-profile-actions client-profile-field-full">
                                    <button type="submit" class="btn btn-dark">Changer mon mot de passe</button>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                <section class="client-profile-card client-profile-ai-card mt-24">
                    <div class="p-15 d-flex justify-content-between align-items-center client-profile-ai-head">
                        <div class="d-flex align-items-center gap-10">
                            <i class="fas fa-robot fs-20" aria-hidden="true"></i>
                            <div>
                                <h2>Assistant IA</h2>
                                <p>Discutez avec l IA a tout moment via l icone flottante en bas a droite.</p>
                            </div>
                        </div>
                        <span class="badge badge-primary">En ligne</span>
                    </div>

                    <p class="client-profile-ai-note mb-0">Cliquez sur l icone Assistant IA pour ouvrir le modal et poser vos questions (evenements, invitations, invites, confirmations).</p>
                </section>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</div>

<style>
html, body{height:auto !important;overflow-y:auto !important}
body.layout-top-nav.fixed{overflow-y:auto !important}
.client-profile-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:30px;border-radius:28px;background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 100%);color:#fff;margin-bottom:24px;box-shadow:0 24px 50px rgba(15,23,42,.16)}
.client-profile-kicker{display:inline-flex;padding:7px 12px;border-radius:999px;background:rgba(255,255,255,.14);font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
.client-profile-hero h1{margin:14px 0 8px;font-size:34px;font-weight:800;color:#fff}
.client-profile-hero p{margin:0;max-width:720px;color:rgba(255,255,255,.82);line-height:1.7}
.client-profile-summary{display:grid;gap:8px;padding:16px 18px;border-radius:20px;background:rgba(255,255,255,.12)}
.client-profile-summary span{color:#f8fafc}
.client-profile-flash{margin-bottom:18px;padding:14px 16px;border-radius:16px;font-weight:700}
.client-profile-flash-success{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
.client-profile-flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.client-profile-card{background:#fff;border-radius:24px;padding:24px;box-shadow:0 18px 40px rgba(15,23,42,.08);height:100%}
.client-profile-head h2{margin:0 0 8px;font-size:24px;font-weight:800;color:#0f172a}
.client-profile-head p{margin:0 0 20px;color:#64748b;line-height:1.6}
.client-profile-ai-head{position:relative;border-radius:16px;margin-bottom:18px;background:#f8fafc;border:1px solid #e2e8f0}
.client-profile-ai-head h2{margin:0;font-size:20px;font-weight:800;color:#0f172a}
.client-profile-ai-head p{margin:2px 0 0;color:#64748b;font-size:13px;line-height:1.5}
.client-profile-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.client-profile-form-grid label{display:flex;flex-direction:column;gap:8px;font-weight:700;color:#0f172a}
.client-profile-form-grid input{min-height:52px;border:1px solid #dbe2ef;border-radius:16px;padding:0 14px;background:#fff;box-shadow:none}
.client-profile-field-full{grid-column:1/-1}
.client-profile-actions{display:flex;justify-content:flex-start}
.mt-24{margin-top:24px}
.client-profile-ai-note{color:#475569;line-height:1.7}
@media (max-width: 767px){.client-profile-form-grid{grid-template-columns:1fr}.client-profile-hero{padding:24px}.client-profile-hero h1{font-size:28px}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function unlockProfilePageScroll() {
        var html = document.documentElement;
        var body = document.body;
        if (!body) {
            return;
        }

        if (body.classList.contains('modal-open') && !document.querySelector('.modal.show')) {
            body.classList.remove('modal-open');
        }

        if (body.style.overflow === 'hidden') {
            body.style.overflow = 'auto';
        }

        if (body.style.overflowY === 'hidden') {
            body.style.overflowY = 'auto';
        }

        html.style.overflowY = 'auto';
        body.style.overflowY = 'auto';
    }

    unlockProfilePageScroll();
    window.setTimeout(unlockProfilePageScroll, 120);
});
</script>

<?php if ($profileAlertScript !== null) {
    echo $profileAlertScript;
} ?>