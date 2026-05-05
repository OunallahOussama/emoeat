<?php
namespace App\Core;

class Router
{
    protected array $routes = [];

    public function get(string $path, string $action): void
    {
        $this->addRoute('GET', $path, $action);
    }

    public function post(string $path, string $action): void
    {
        $this->addRoute('POST', $path, $action);
    }

    protected function addRoute(string $method, string $path, string $action): void
    {
        [$controller, $method_name] = explode('@', $action);
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => 'App\\Controllers\\' . $controller,
            'action' => $method_name,
        ];
    }

    public function dispatch(string $uri, string $method): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $uri, $params)) {
                $controllerClass = $route['controller'];
                $actionMethod = $route['action'];
                $controller = new $controllerClass();
                call_user_func_array([$controller, $actionMethod], $params);
                return;
            }
        }

        http_response_code(404);
        echo '<h1>404 - Page non trouvée</h1>';
    }

    protected function matchPath(string $routePath, string $uri, &$params = []): bool
    {
        $params = [];
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[] = $value;
                }
            }
            return true;
        }
        return false;
    }
}
