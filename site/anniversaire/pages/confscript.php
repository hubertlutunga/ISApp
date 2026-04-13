<?php 

if (isset($_GET['idinv']) && isset($_GET['presence'])) {
    // Préparer la requête pour récupérer l'invité
    $stmt = $pdo->prepare("SELECT * FROM invite WHERE id_inv = :id_inv");
    $stmt->execute([':id_inv' => $_GET['idinv']]);
    $datainvite = $stmt->fetch(PDO::FETCH_ASSOC);
    $nominv = $datainvite ? $datainvite['nom'] : '';

    // Vérifier si l'utilisateur a déjà confirmé
    $checkSql = 'SELECT COUNT(*) FROM confirmation WHERE noms = :noms';
    $checkQ = $pdo->prepare($checkSql);
    $checkQ->bindValue(':noms', $nominv); 
    $checkQ->execute();
    $count = $checkQ->fetchColumn();
    $checkQ->closeCursor();

    if ($count > 0) {
        showAlert($nominv, "Votre réponse avait déjà été enregistrée.", "warning", "accueil");
    } else {
        handlePresence($nominv, $_GET['presence'], $_GET['cod'], $_GET['idinv']);
    }
} else {
    // Préparer la requête pour récupérer l'invité
    $stmt2x = $pdo->prepare("SELECT * FROM invite WHERE id_inv = :id_inv");
    $stmt2x->execute([':id_inv' => $_GET['idinv']]);
    $datainvite = $stmt2x->fetch(PDO::FETCH_ASSOC);

    $sing = match ($datainvite['sing']) {
        'C' => 'Couple',
        'Mr' => 'Monsieur',
        'Mme' => 'Madame',
        default => '',
    };

    $nominv = $datainvite ? $sing . ' ' . ucfirst($datainvite['nom']) : '';

    // Vérifier si l'utilisateur a déjà confirmé
    $stmt3x = $pdo->prepare("SELECT presence, date_enreg FROM confirmation WHERE noms = :noms");
    $stmt3x->bindValue(':noms', $datainvite['nom']);
    $stmt3x->execute();
    $dataconfinv = $stmt3x->fetch(PDO::FETCH_ASSOC);

    $confirmationMessage = match ($dataconfinv['presence']) {
        'oui' => 'votre présence est confirmée pour la fête de',
        'non' => 'votre non participation a été enregistrée',
        default => 'statut de présence inconnu',
    };

    // Vérifiez si 'date_enreg' est défini avant de l'utiliser
    $dateEnreg = isset($dataconfinv['date_enreg']) ? date('d/m/Y', strtotime($dataconfinv['date_enreg'])) : 'date inconnue';

    echo  '<h5 class="dynamic-color"><b>' . htmlspecialchars($nominv, ENT_QUOTES) . '</b>, ' . $confirmationMessage .'</h5>';
}

// Fonction pour afficher une alerte
function showAlert($nominv, $message, $icon, $page = null) {
    echo '<script src="sweet/sweetalert2.all.min.js"></script>';
    echo '<script>
            Swal.fire({
                title: "' . htmlspecialchars($nominv, ENT_QUOTES) . '",
                text: "' . $message . '",
                icon: "' . $icon . '",
                confirmButtonText: "OK"
            }).then((result) => {
                if (result.isConfirmed) {';
    if ($page) {
        echo 'window.location.href = "index.php?page=' . $page . '&cod=' . htmlspecialchars($_GET['cod']) . '&idinv=' . htmlspecialchars($_GET['idinv']) . '";';
    }
    echo '}
            });
          </script>';
}

// Fonction pour gérer la présence
function handlePresence($nominv, $presence, $cod, $idinv) {
    global $pdo; // Accéder à la variable $pdo

    if ($presence === 'oui') {
        $sql = 'INSERT INTO confirmation (cod_mar, noms, presence, date_enreg) VALUES (:cod_mar, :noms, :presence, NOW())';
        $q = $pdo->prepare($sql);
        $q->bindValue(':cod_mar', $cod);
        $q->bindValue(':noms', $nominv); 
        $q->bindValue(':presence', $presence);  
        $q->execute();
        $q->closeCursor();

        showAlert($nominv, "Votre confirmation est enregistrée avec succès.", "success", "accueil");
    } elseif ($presence === 'non') {
        showAlert($nominv, "Vous êtes sur le point de répondre non à cette invitation.", "warning", "reponsenon");
    } elseif ($presence === 'plustard') {
        showAlert($nominv, "Pour des raisons de logistique, nous vous prions de bien vouloir confirmer votre participation, ou pas, avant le 5 août 2025.", "warning");
    }
}

?>