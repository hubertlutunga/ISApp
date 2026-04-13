<?php

try {
    EventUpdateService::updateFromRequest($pdo, (int) $cod_getevent, $_POST);

    echo '<script>
            Swal.fire({
                title: "Evénement modifié !",
                text: "Votre événement est modifié avec succès.",
                icon: "success",
                confirmButtonText: "Terminer"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "index.php?page=mb_accueil";
                }
            });
          </script>';
} catch (PDOException $e) {
    echo 'Erreur lors de la mise à jour : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    return;
}
?>
?>