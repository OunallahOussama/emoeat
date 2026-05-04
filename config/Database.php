<?php
/* ================================================
   config/Database.php
   Classe qui gère la connexion à la base de données Oracle.
   On l'appelle depuis connexion.php avec new Database()
   ================================================ */
class Database {
    private $host;
    private $port;
    private $service;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host     = getenv('DB_HOST') ?: 'localhost';
        $this->port     = getenv('DB_PORT') ?: '1521';
        $this->service  = getenv('DB_SERVICE') ?: 'XEPDB1';
        $this->username = getenv('DB_USER') ?: 'emoeat_user';
        $this->password = getenv('DB_PASSWORD') ?: 'emoeat_pass';
    }

    // Méthode pour obtenir la connexion à la base de données
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "oci:dbname=//" . $this->host . ":" . $this->port . "/" . $this->service . ";charset=AL32UTF8";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Configuration pour afficher les erreurs SQL proprement
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            die("Erreur de connexion Oracle : " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>