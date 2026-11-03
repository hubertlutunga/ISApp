<?php
if (defined('ISAPP_PROFILE_AI_WIDGET_RENDERED')) {
    return;
}
define('ISAPP_PROFILE_AI_WIDGET_RENDERED', true);
?>

<button type="button" id="client-ai-widget-fab" class="client-ai-widget-fab" aria-label="Ouvrir l assistant IA">
    <i class="fas fa-robot fs-22" aria-hidden="true"></i>
</button>

<div id="client-ai-widget-modal" class="client-ai-widget-modal" aria-hidden="true">
    <div class="client-ai-widget-backdrop" data-client-ai-close="1"></div>
    <section class="client-ai-widget-dialog" role="dialog" aria-modal="true" aria-labelledby="client-ai-widget-title">
        <div class="client-ai-widget-header">
            <div>
                <h3 id="client-ai-widget-title">Assistant IA</h3>
                <p class="mb-0">Posez vos questions sur les evenements, invitations et invites.</p>
            </div>
            <button type="button" class="btn btn-danger-light btn-sm" data-client-ai-close="1">Fermer</button>
        </div>

        <div class="client-ai-widget-body">
            <p id="client-ai-widget-status" class="client-ai-widget-status">Mode: IA connectee</p>
            <div id="client-ai-widget-messages" class="client-ai-widget-messages" aria-live="polite"></div>

            <form id="client-ai-widget-form" class="client-ai-widget-form">
                <label for="client-ai-widget-input" class="client-ai-widget-label">Votre question</label>
                <div class="client-ai-widget-input-row">
                    <input id="client-ai-widget-input" name="message" type="text" maxlength="600" placeholder="Ex: Comment ajouter un invite ?" required>
                    <button type="submit" id="client-ai-widget-submit" class="btn btn-primary">Envoyer</button>
                </div>
            </form>
        </div>
    </section>
</div>

<style>
.client-ai-widget-fab{position:fixed;right:22px;bottom:22px;z-index:2050;height:56px;width:56px;border:0;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#2563eb;color:#fff;box-shadow:0 16px 30px rgba(37,99,235,.35)}
.client-ai-widget-fab:hover{background:#1d4ed8}
.client-ai-widget-modal{position:fixed;inset:0;display:flex;align-items:flex-end;justify-content:flex-end;padding:20px;z-index:2100;opacity:0;pointer-events:none;transition:opacity .2s ease}
.client-ai-widget-modal.is-open{opacity:1;pointer-events:auto}
.client-ai-widget-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.35)}
.client-ai-widget-dialog{position:relative;width:min(460px,100%);max-height:min(88vh,720px);overflow:auto;background:#fff;border-radius:20px;padding:16px;box-shadow:0 26px 50px rgba(15,23,42,.25)}
.client-ai-widget-header{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:10px}
.client-ai-widget-header h3{margin:0;font-size:22px;font-weight:800;color:#0f172a}
.client-ai-widget-header p{color:#64748b;font-size:13px;line-height:1.5}
.client-ai-widget-body{display:grid;gap:14px}
.client-ai-widget-status{margin:0;color:#64748b;font-size:13px}
.client-ai-widget-messages{display:grid;gap:10px;max-height:320px;overflow:auto;padding:12px;border:1px solid #e2e8f0;border-radius:16px;background:#f8fafc}
.client-ai-widget-message{max-width:85%;padding:10px 12px;border-radius:14px;line-height:1.5;white-space:pre-line}
.client-ai-widget-message-user{margin-left:auto;background:#dbeafe;color:#1e3a8a}
.client-ai-widget-message-bot{margin-right:auto;background:#e2e8f0;color:#0f172a}
.client-ai-widget-form{display:grid;gap:8px}
.client-ai-widget-label{font-weight:700;color:#0f172a}
.client-ai-widget-input-row{display:flex;gap:10px}
.client-ai-widget-input-row input{flex:1;min-height:48px;border:1px solid #dbe2ef;border-radius:14px;padding:0 14px}
@media (max-width: 767px){.client-ai-widget-input-row{flex-direction:column}.client-ai-widget-input-row .btn{width:100%}.client-ai-widget-modal{padding:12px;align-items:flex-end;justify-content:center}.client-ai-widget-dialog{width:100%;max-height:84vh}.client-ai-widget-fab{right:16px;bottom:16px}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var fabButton = document.getElementById('client-ai-widget-fab');
    var modal = document.getElementById('client-ai-widget-modal');
    var form = document.getElementById('client-ai-widget-form');
    var input = document.getElementById('client-ai-widget-input');
    var submitButton = document.getElementById('client-ai-widget-submit');
    var messages = document.getElementById('client-ai-widget-messages');
    var statusLine = document.getElementById('client-ai-widget-status');

    if (!fabButton || !modal || !form || !input || !submitButton || !messages || !statusLine) {
        return;
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        window.setTimeout(function () {
            input.focus();
        }, 60);
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    function appendMessage(role, text) {
        var message = document.createElement('div');
        message.className = 'client-ai-widget-message ' + (role === 'user' ? 'client-ai-widget-message-user' : 'client-ai-widget-message-bot');
        message.textContent = text;
        messages.appendChild(message);
        messages.scrollTop = messages.scrollHeight;
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
                    statusLine.textContent = 'Mode: IA connectee';
                }

                appendMessage('bot', data.reply || 'Je n ai pas trouve de reponse precise.');
            })
            .catch(function () {
                appendMessage('bot', 'Une erreur est survenue. Reessayez dans quelques instants.');
            })
            .finally(function () {
                submitButton.disabled = false;
            });
    }

    fabButton.addEventListener('click', openModal);
    modal.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.getAttribute('data-client-ai-close') === '1') {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        sendQuestion(input.value);
    });

    appendMessage('bot', 'Bonjour ! Je suis votre assistant IA. Je peux vous aider a utiliser la plateforme (evenements, invitations, invites et confirmations).');
});
</script>
