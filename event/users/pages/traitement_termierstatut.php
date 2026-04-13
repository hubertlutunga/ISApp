<?php

include('../../../pages/bdd.php');
require_once __DIR__ . '/../../../bootstrap/app.php';

	$stmtss = $pdo->prepare("SELECT * FROM is_users WHERE phone = ?");
	$stmtss->execute([$_SESSION['user_phone']]);
	$datasession = $stmtss->fetch();
    
        $user = $datasession['cod_user'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    // Vérifiez que les données sont présentes
    if (isset($_POST['codevent']) && isset($_POST['observation'])) {
        $codevent = (int) $_POST['codevent']; 
        $observation = (string) $_POST['observation'];

        try {
            $uploadSuccess = EventPrintService::finalizeCreation(
                $pdo,
                $codevent,
                $observation,
                (int) $user,
                $_FILES['fichers'] ?? null,
                '../../pages/fichiersprint'
            );

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    }
}
?>