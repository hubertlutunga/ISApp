<?php

include('../../../pages/bdd.php');
require_once __DIR__ . '/../../../bootstrap/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    if (isset($_POST['codevent'])) {
        $codevent = (int) $_POST['codevent']; 

        try {
            $uploadTargetDir = realpath(__DIR__ . '/../../pages/fichiers');
            if ($uploadTargetDir === false) {
                throw new RuntimeException('Le dossier de destination des invitations est introuvable.');
            }

            $storedFiles = EventDocumentService::storeUploadedFiles($_FILES['fichers'] ?? null, $uploadTargetDir);
            $currentEvent = EventUpdateService::findEventById($pdo, $codevent);

            EventUpdateService::updateInvitationTemplate($pdo, $codevent, [
                'invit_religieux' => $storedFiles !== [] ? end($storedFiles) : ($currentEvent['invit_religieux'] ?? null),
                'ajustenom' => $_POST['ajustenom'] ?? null,
                'taillenominv' => $_POST['taillenominv'] ?? null,
                'alignnominv' => $_POST['alignnominv'] ?? null,
                'pagenom' => $_POST['pagenom'] ?? null,
                'pagebouton' => $_POST['pagebouton'] ?? null,
                'colornom' => $_POST['colornom'] ?? null,
                'bordgauchenominv' => $_POST['bordgauchenominv'] ?? null,
                'qrcode' => $_POST['qrcode'] ?? 'non',
                'pageqr' => $_POST['pageqr'] ?? ($currentEvent['pageqr'] ?? null),
                'hautqr' => $_POST['hautqr'] ?? ($currentEvent['hautqr'] ?? null),
                'gaucheqr' => $_POST['gaucheqr'] ?? ($currentEvent['gaucheqr'] ?? null),
                'tailleqr' => $_POST['tailleqr'] ?? ($currentEvent['tailleqr'] ?? null),
                'lang' => $_POST['lang'] ?? null,
            ]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    }
}
?>