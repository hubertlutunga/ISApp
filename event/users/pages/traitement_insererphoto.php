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
        $codevent = $_POST['codevent']; 
        $legendephoto = $_POST['observation'];

        try { 


            $uploadSuccess = true; // Variable pour suivre le succès du téléchargement
       if (!empty($_FILES['fichers']['name'])) {
            try {
                $newFileName = EventMediaService::storeCompressedJpeg($_FILES['fichers'], '../../../couple/images');
                EventMediaService::updateEventFields($pdo, (int) $codevent, [
                    'photostory' => $newFileName,
                    'icone' => $newFileName,
                    'photo' => $newFileName,
                ]);
            } catch (RuntimeException $e) {
                $uploadSuccess = false;
            }
        }

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    }
}
?>