<?php
/* ================================================
   config/Database.php
   Classe qui gère la connexion à la base de données MySQL.
   On l'appelle depuis connexion.php avec new Database()
   ================================================ */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host     = getenv('DB_HOST') ?: 'localhost';
        $this->db_name  = getenv('DB_NAME') ?: 'emoeat';
        $this->username = getenv('DB_USER') ?: 'emoeat_user';
        $this->password = getenv('DB_PASSWORD') ?: 'emoeat_pass';
    }

    // Méthode pour obtenir la connexion à la base de données
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Configuration pour afficher les erreurs SQL proprement
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            die("Erreur de connexion MySQL : " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>