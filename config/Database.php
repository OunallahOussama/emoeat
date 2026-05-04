<?php
/* ================================================
   config/Database.php
   Classe qui gère la connexion à la base de données Oracle.
   On l'appelle depuis connexion.php avec new Database()
   ================================================ */
class Database {
    /* Adresse du serveur Oracle et nom de la base */
    private $db_name = "localhost:1521/XE"; 
    /* Nom d'utilisateur et mot de passe pour se connecter à Oracle */
    private $username = "ESEN_STUDENT";
    private $password = "manager";
    public $conn; /* La connexion active sera stockée ici */

    // Méthode pour obtenir la connexion à la base de données
    public function getConnection() {
        $this->conn = null;

        try {
            // Connexion avec PDO (Exigence de ton PFA)
            $this->conn = new PDO("oci:dbname=" . $this->db_name, $this->username, $this->password);
            
            // Configuration pour afficher les erreurs SQL proprement
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            // Affichage de l'erreur en cas de problème
            die("Erreur de connexion Oracle : " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>