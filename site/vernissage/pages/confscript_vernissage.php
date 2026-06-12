<?php

require_once __DIR__ . '/../../../bootstrap/app.php';

if (!isset($_GET['idinv'], $_GET['presence'])) {
    return;
}

if (isset($_GET['ok']) || isset($_GET['err'])) {
    return;
}

$inviteId = (int) $_GET['idinv'];
$presence = (string) $_GET['presence'];
$eventCode = (string) ($_GET['cod'] ?? '');
$datainvite = $inviteId > 0 ? RsvpService::findInviteById($pdo, $inviteId) : null;
$nominv = $datainvite ? RsvpService::buildInviteDisplayName($datainvite) : '';
$confirmationName = RsvpService::normalizeConfirmationName($nominv);
$confirmation = ($eventCode !== '' && $confirmationName !== '') ? RsvpService::findConfirmation($pdo, $eventCode, $confirmationName) : null;

if ($nominv === '' || $presence !== 'oui') {
    return;
}

$modalMode = $confirmation ? 'already' : 'form';
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof openVernissageConfirmModal === 'function') {
        openVernissageConfirmModal(
            <?php echo json_encode(html_entity_decode($nominv, ENT_QUOTES, 'UTF-8'), JSON_UNESCAPED_UNICODE); ?>,
            <?php echo json_encode($eventCode, JSON_UNESCAPED_UNICODE); ?>,
            <?php echo json_encode((string) $inviteId, JSON_UNESCAPED_UNICODE); ?>,
            <?php echo json_encode($modalMode, JSON_UNESCAPED_UNICODE); ?>
        );
    }
});
</script>
