<?php  

// Vérifiez si le numéro de téléphone de l'utilisateur est défini dans la session
if (isset($_SESSION['user_phone'])) {
    // Préparez la requête pour récupérer les informations de l'utilisateur
    $stmtss = $pdo->prepare("SELECT * FROM is_users WHERE phone = ?");
    $stmtss->execute([$_SESSION['user_phone']]);
    $datasession = $stmtss->fetch();

    // Vérifiez si l'utilisateur a été trouvé
    if ($datasession) {
        $coduser = $datasession['cod_user'];

        // Vérifiez si 'cod' est présent dans les paramètres GET
        if (isset($_GET['cod'])) {
            $cod = $_GET['cod'];
            $creaValue = 4; // Utilisez un entier directement
            $DeleteT = $pdo->prepare("UPDATE events SET crea = :crea WHERE cod_event = :codevent");
            $DeleteT->bindParam(':crea', $creaValue, PDO::PARAM_INT);
            $DeleteT->bindParam(':codevent', $cod);
            $DeleteT->execute(); 

            // Insérer dans la table amorcage_dossier
            $sqlficher = "INSERT INTO amorcage_dossier (cod_event, cod_user, date_enreg) VALUES (?, ?, NOW())";
            $stmtfichier = $pdo->prepare($sqlficher); 
            $stmtfichier->execute([$cod, $coduser]);

            // Redirection en fonction du type d'utilisateur
            if ($datasession['type_user'] !== '3') {
                echo '<script>window.location="index.php?page=admin_accueil";</script>';
            } elseif ($datasession['type_user'] === '3') { 
                echo '<script>window.location="index.php?page=crea_accueil";</script>';
            }
        } else {
            // Gestion de l'erreur si 'cod' n'est pas défini
            echo 'Erreur : le paramètre "cod" est manquant.';
        }
    } else {
        // Gestion de l'erreur si l'utilisateur n'est pas trouvé
        echo 'Erreur : utilisateur non trouvé.';
    }
} else {
    // Gestion de l'erreur si l'utilisateur n'est pas connecté
    echo 'Erreur : vous devez être connecté.';
}
?>