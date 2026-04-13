<?php 

if (isset($_GET['idinv']) && isset($_GET['presence'])) {
    $codevent = $_GET['cod'];
    $inviteId = (int) $_GET['idinv'];
    $datainvite = RsvpService::findInviteById($pdo, $inviteId);
    $nominv = $datainvite ? RsvpService::buildInviteDisplayName($datainvite) : '';
    $confirmationName = RsvpService::normalizeConfirmationName($nominv);
    $confirmation = $confirmationName !== '' ? RsvpService::findConfirmation($pdo, (string) $_GET['cod'], $confirmationName) : null;

    $confirmationMessage = match ((string) ($confirmation['presence'] ?? '')) {
        'oui' => 'votre présence avait déjà été confirmée.',
        'non' => 'votre non participation avait déjà été enregistrée',
        default => 'statut de présence inconnu',
    };

    $nometmsg = $nominv . ', ' . $confirmationMessage;

  
    if ($confirmation) {

        showAlert($nometmsg, "", "accueil");

    } else {

        handlePresence($nominv, $_GET['presence'], $_GET['cod'], $_GET['idinv']);

    }

} else {
    $datainvite = isset($_GET['idinv']) ? RsvpService::findInviteById($pdo, (int) $_GET['idinv']) : null;
    $nominv = $datainvite ? RsvpService::buildInviteDisplayName($datainvite) : '';
    $confirmationName = RsvpService::normalizeConfirmationName($nominv);
    $dataconfinv = $confirmationName !== '' ? RsvpService::findConfirmation($pdo, (string) ($_GET['cod'] ?? ''), $confirmationName) : null;



}

// Fonction pour afficher une alerte
function showAlert($nominv, $message, $icon, $page = null) {
    echo '<script src="sweet/sweetalert2.all.min.js"></script>';
    echo '<script>
            Swal.fire({
                title: "' . html_entity_decode($nominv, ENT_QUOTES, 'UTF-8') . '",
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
function handlePresence($nominv, $presence, $codevent, $idinv) {
    global $pdo; // Accéder à la variable $pdo

    if ($presence === 'oui') {
    
    /* 

        $sql = 'INSERT INTO confirmation (cod_mar, noms, presence, date_enreg) VALUES (:cod_mar, :noms, :presence, NOW())';
        $q = $pdo->prepare($sql);
        $q->bindValue(':cod_mar', $codevent);
        $q->bindValue(':noms', $nominv); 
        $q->bindValue(':presence', $presence);  
        $q->execute();
        $q->closeCursor();

        showAlert($nominv, "Votre confirmation est enregistrée avec succès.", "success", "accueil");
    
    */

    ?>














<?php 

include('modalreponse.php');
 
$nominv = html_entity_decode($nominv, ENT_QUOTES, 'UTF-8');

?>



 


<!-- Scripts -->
<script>
function openModal(inviteName, inviteId) {
    document.getElementById('modalTitle').innerText = inviteName;
    document.getElementById('shareModal').style.display = 'flex';
   // const linkpdf = "../pages/invitation_elect.php?cod=" + inviteId + "&event=<?php echo $codevent; ?>";
    document.getElementById('inviteName').value = inviteName;
}

function closeModal() {
    document.getElementById('shareModal').style.display = 'none';
}
</script>

<!-- Script pour ouverture automatique -->
<?php if (!empty($nominv) && !empty($codevent)) : ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    openModal("<?php echo html_entity_decode($nominv, ENT_QUOTES, 'UTF-8'); ?>", "<?php echo $codevent; ?>");
});
</script>
<?php endif; ?>













    <?php
    
    } elseif ($presence === 'non') {

        echo '<script src="sweet/sweetalert2.all.min.js"></script>';
        echo '<script>
            Swal.fire({
                title: "' . html_entity_decode($nominv, ENT_QUOTES, 'UTF-8') . '",
                text: "Vous êtes sur le point de répondre NON à cette invitation. Confirmez-vous ?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Oui, confirmer",
                cancelButtonText: "Annuler"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "index.php?page=reponsenon&cod=' . $_GET['cod'] . '&idinv=' . $_GET['idinv'] . '";
                }
            });
        </script>';





    } elseif ($presence === 'plustard') {
        showAlert($nominv, "Pour des raisons de logistique, nous vous prions de bien vouloir confirmer votre participation, ou pas, quelques jours avant la cérémonie.", "warning");
    }
}

?>