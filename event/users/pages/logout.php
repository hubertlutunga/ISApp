<?php
//session_start(); // Démarrer la session
UserAccountService::clearImpersonation();
session_unset(); // Détruire toutes les variables de session
session_destroy(); // Détruire la session
header("Location: ../index.php?page=login"); // Rediriger vers la page d'accueil
exit();
?>