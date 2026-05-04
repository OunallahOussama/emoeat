<?php
/* ================================================
   logout.php �?" Déconnexion
   Ce fichier détruit la session de l'utilisateur
   et le redirige vers la page de connexion.
   ================================================ */
session_start();
include("connexion.php");

/* Si l'utilisateur était connecté, on enregistre sa déconnexion */
if(isset($_SESSION['user_id'])) {
    logActivity($conn, (int)$_SESSION['user_id'], 'USER_LOGOUT');
}

/* On efface toutes les données de session et on redirige */
session_destroy();
header("Location: login.php");
exit();
?>

