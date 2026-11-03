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
                    <div class="client-profile-head">
                        <h2>Assistant IA</h2>
                        <p>Posez vos questions sur la plateforme: ajout d evenement, invitations, invites, confirmations et plus.</p>
                    </div>

                    <div class="client-profile-ai-chat">
                        <p id="profile-ai-status" class="client-profile-ai-status">Mode: Assistant guide</p>
                        <div id="profile-ai-messages" class="client-profile-ai-messages" aria-live="polite"></div>

                        <div id="profile-ai-suggestions" class="client-profile-ai-suggestions">
                            <button type="button" class="client-profile-ai-chip" data-question="Comment ajouter un evenement ?">Ajouter un evenement</button>
                            <button type="button" class="client-profile-ai-chip" data-question="Comment ajouter une invitation ?">Ajouter une invitation</button>
                            <button type="button" class="client-profile-ai-chip" data-question="Comment ajouter un invite ?">Ajouter un invite</button>
                            <button type="button" class="client-profile-ai-chip" data-question="Comment suivre les confirmations ?">Suivre les confirmations</button>
                        </div>

                        <form id="profile-ai-form" class="client-profile-ai-form">
                            <label for="profile-ai-input" class="client-profile-ai-label">Votre question</label>
                            <div class="client-profile-ai-input-row">
                                <input id="profile-ai-input" name="message" type="text" maxlength="600" placeholder="Ex: Comment ajouter un invite ?" required>
                                <button type="submit" id="profile-ai-submit" class="btn btn-primary">Envoyer</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</div>

<style>
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
.client-profile-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.client-profile-form-grid label{display:flex;flex-direction:column;gap:8px;font-weight:700;color:#0f172a}
.client-profile-form-grid input{min-height:52px;border:1px solid #dbe2ef;border-radius:16px;padding:0 14px;background:#fff;box-shadow:none}
.client-profile-field-full{grid-column:1/-1}
.client-profile-actions{display:flex;justify-content:flex-start}
.mt-24{margin-top:24px}
.client-profile-ai-chat{display:grid;gap:14px}
.client-profile-ai-status{margin:0;color:#64748b;font-size:13px}
.client-profile-ai-messages{display:grid;gap:10px;max-height:320px;overflow:auto;padding:12px;border:1px solid #e2e8f0;border-radius:16px;background:#f8fafc}
.client-profile-ai-message{max-width:85%;padding:10px 12px;border-radius:14px;line-height:1.5;white-space:pre-line}
.client-profile-ai-message-user{margin-left:auto;background:#dbeafe;color:#1e3a8a}
.client-profile-ai-message-bot{margin-right:auto;background:#e2e8f0;color:#0f172a}
.client-profile-ai-suggestions{display:flex;flex-wrap:wrap;gap:8px}
.client-profile-ai-chip{border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:8px 12px;font-size:13px}
.client-profile-ai-chip:hover{background:#dbeafe}
.client-profile-ai-form{display:grid;gap:8px}
.client-profile-ai-label{font-weight:700;color:#0f172a}
.client-profile-ai-input-row{display:flex;gap:10px}
.client-profile-ai-input-row input{flex:1;min-height:48px;border:1px solid #dbe2ef;border-radius:14px;padding:0 14px}
@media (max-width: 767px){.client-profile-ai-input-row{flex-direction:column}.client-profile-ai-input-row .btn{width:100%}}
@media (max-width: 767px){.client-profile-form-grid{grid-template-columns:1fr}.client-profile-hero{padding:24px}.client-profile-hero h1{font-size:28px}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('profile-ai-form');
    var input = document.getElementById('profile-ai-input');
    var submitButton = document.getElementById('profile-ai-submit');
    var messages = document.getElementById('profile-ai-messages');
    var suggestionsContainer = document.getElementById('profile-ai-suggestions');
    var statusLine = document.getElementById('profile-ai-status');

    if (!form || !input || !submitButton || !messages || !suggestionsContainer || !statusLine) {
        return;
    }

    function appendMessage(role, text) {
        var message = document.createElement('div');
        message.className = 'client-profile-ai-message ' + (role === 'user' ? 'client-profile-ai-message-user' : 'client-profile-ai-message-bot');
        message.textContent = text;
        messages.appendChild(message);
        messages.scrollTop = messages.scrollHeight;
    }

    function renderSuggestions(items) {
        if (!Array.isArray(items) || items.length === 0) {
            return;
        }

        suggestionsContainer.innerHTML = '';
        items.forEach(function (question) {
            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'client-profile-ai-chip';
            chip.setAttribute('data-question', question);
            chip.textContent = question;
            suggestionsContainer.appendChild(chip);
        });
    }

    function sendQuestion(question) {
        var normalizedQuestion = String(question || '').trim();
        if (normalizedQuestion === '') {
            return;
        }

        appendMessage('user', normalizedQuestion);
        input.value = '';
        submitButton.disabled = true;

        var formData = new URLSearchParams();
        formData.append('message', normalizedQuestion);

        fetch('pages/profile_ai_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (!data.success) {
                    appendMessage('bot', data.message || 'Je n ai pas pu traiter votre demande pour le moment.');
                    return;
                }

                if (data.source === 'openai' || data.source === 'anthropic') {
                    statusLine.textContent = 'Mode: IA connectee (' + data.source + ')';
                } else {
                    statusLine.textContent = 'Mode: Assistant guide (secours local)';
                }

                appendMessage('bot', data.reply || 'Je n ai pas trouve de reponse precise.');
                renderSuggestions(data.suggestions || []);
            })
            .catch(function () {
                appendMessage('bot', 'Une erreur est survenue. Reessayez dans quelques instants.');
            })
            .finally(function () {
                submitButton.disabled = false;
            });
    }

    appendMessage('bot', 'Bonjour ! Je suis votre assistant IA. Je peux vous aider a utiliser la plateforme (evenements, invitations, invites et confirmations).');

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        sendQuestion(input.value);
    });

    suggestionsContainer.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (!target.classList.contains('client-profile-ai-chip')) {
            return;
        }

        sendQuestion(target.getAttribute('data-question') || '');
    });
});
</script>

<?php if ($profileAlertScript !== null) {
    echo $profileAlertScript;
} ?>