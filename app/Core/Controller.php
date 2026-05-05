<?php
namespace App\Core;

use Database;

class Controller
{
    protected \PDO $db;

    public function __construct()
    {
        $database = new \Database();
        $this->db = $database->getConnection();
    }

    protected function view(string $viewName, array $data = []): void
    {
        extract($data);
        $viewPath = dirname(__DIR__) . '/Views/' . $viewName . '.php';
        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "View not found: $viewName";
            return;
        }
        require $viewPath;
    }

    protected function redirect(string $url): void
    {
        header("Location: " . $url);
        exit();
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    protected function isAdmin(): bool
    {
        return $this->isLoggedIn() && strtoupper(trim($_SESSION['role'] ?? '')) === 'ADMIN';
    }

    protected function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }
    }

    protected function getUserId(): int
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    protected function getUserName(): string
    {
        return $_SESSION['user_name'] ?? 'Utilisateur';
    }

    protected function getUserRole(): string
    {
        return strtoupper(trim($_SESSION['role'] ?? 'CLIENT'));
    }
}
