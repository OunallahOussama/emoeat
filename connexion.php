<?php

/* On charge la classe Database depuis le dossier config */
require_once __DIR__ . '/config/Database.php';

/* On crée la connexion et on la stocke dans $conn
   Cette variable sera utilisée partout pour faire des requêtes SQL */
$database = new Database();
$conn = $database-> getConnection();

/* ─── Fonction pour enregistrer les actions des utilisateurs ───
   Chaque fois qu'un utilisateur se connecte, se déconnecte ou fait
   une action importante, on l'enregistre dans la table ACTIVITY_LOG */
function logActivity(PDO $conn, int $user_id, string $action): void {
    try {
        $stmt = $conn->prepare(
            "INSERT INTO ACTIVITY_LOG (ID_USER, ACTION, LOG_DATE)
             VALUES (:u, :a, SYSDATE)"
        );
        $stmt->bindParam(':u', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':a', $action,  PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
        // Log silencieux pour ne pas bloquer le flux applicatif
    }
}
?>


