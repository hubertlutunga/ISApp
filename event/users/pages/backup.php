<?php 
 
// Requête pour récupérer les données de la table mariages
$sql_select = "SELECT * FROM mariages";
$stmt_select = $pdo->prepare($sql_select);
$stmt_select->execute();

if ($stmt_select->rowCount() > 0) {
    // Préparer la requête d'insertion
    $sql_insert = "INSERT INTO events (cod_event, 
                                        cod_user, 
                                        type_event, 
                                        type_mar, 
                                        modele_inv, 
                                        modele_chev, 
                                        date_event, 
                                        lieu, 
                                        adresse, 
                                        prenom_epoux, 
                                        nom_epoux, 
                                        prenom_epouse, 
                                        nom_epouse, 
                                        nom_familleepoux, 
                                        nom_familleepouse, 
                                        nomfetard, 
                                        themeconf, 
                                        autres_precisions, 
                                        initiale_mar, 
                                        photostory, 
                                        logo, 
                                        icone, 
                                        photo, 
                                        phone, 
                                        email, 
                                        gestioninvite, 
                                        invit_religieux, 
                                        ajustenom, 
                                        invit_coutumier, 
                                        invit_civil, 
                                        date_enreg) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $pdo->prepare($sql_insert);

    // Boucle pour chaque ligne de mariages
    while ($row = $stmt_select->fetch(PDO::FETCH_ASSOC)) {
        // Remplacer les valeurs par des variables appropriées
        $cod_mar = $row['cod_mar'];
        $cod_user = null; // À définir selon votre logique
        $type_event = $row['type_event'];
        $type_mar = $row['type_mar'];
        $modele_inv = null; // Laisser vide
        $modele_chev = null; // Laisser vide
        $date_event = $row['date_mar'];
        $lieu = $row['lieu'];
        $adresse = null; // Laisser vide
        $prenom_epoux = $row['prenom_epoux'];
        $nom_epoux = $row['nom_epoux'];
        $prenom_epouse = $row['prenom_epouse'];
        $nom_epouse = $row['nom_epouse'];
        $nom_familleepoux = null; // Laisser vide
        $nom_familleepouse = null; // Laisser vide
        $nomfetard = null; // Laisser vide
        $themeconf = null; // Laisser vide
        $autres_precisions = null; // Laisser vide
        $initiale_mar = $row['initiale_mar'];
        $photostory = $row['photostory'];
        $logo = $row['logo'];
        $icone = $row['icone'];
        $photo = $row['photo'];
        $phone = $row['phone'];
        $email = $row['email'];
        $gestioninvite = $row['gestioninvite'];
        $invit_religieux = null; // Laisser vide
        $ajustenom = null; // Laisser vide
        $invit_coutumier = null; // Laisser vide
        $invit_civil = null; // Laisser vide
        $date_enreg = date('Y-m-d H:i:s'); // Date d'enregistrement actuelle

        // Exécuter la requête préparée
        $stmt_insert->execute([$cod_mar, $cod_user, $type_event, $type_mar, $modele_inv, $modele_chev, $date_event, $lieu, $adresse, $prenom_epoux, $nom_epoux, $prenom_epouse, $nom_epouse, $nom_familleepoux, $nom_familleepouse, $nomfetard, $themeconf, $autres_precisions, $initiale_mar, $photostory, $logo, $icone, $photo, $phone, $email, $gestioninvite, $invit_religieux, $ajustenom, $invit_coutumier, $invit_civil, $date_enreg]);
    }

    echo "Données insérées avec succès.";
} else {
    echo "Aucune donnée trouvée dans la table mariages.";
}
 
?>