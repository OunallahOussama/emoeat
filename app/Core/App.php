<?php
namespace App\Core;

class App
{
    protected Router $router;

    public function __construct()
    {
        session_start();
        $this->router = new Router();
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];
        $this->router->dispatch($uri, $method);
    }

    protected function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptDir !== '/' && $scriptDir !== '\\') {
            $uri = substr($uri, strlen($scriptDir));
        }
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');
        return $uri;
    }
}
