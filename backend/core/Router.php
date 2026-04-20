<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, $handler, array $middleware = []): void    { $this->add('GET',    $path, $handler, $middleware); }
    public function post(string $path, $handler, array $middleware = []): void   { $this->add('POST',   $path, $handler, $middleware); }
    public function put(string $path, $handler, array $middleware = []): void    { $this->add('PUT',    $path, $handler, $middleware); }
    public function delete(string $path, $handler, array $middleware = []): void { $this->add('DELETE', $path, $handler, $middleware); }

    private function add(string $method, string $path, $handler, array $middleware): void
    {
        // Convert {param} to regex capture groups.
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . rtrim($pattern, '/') . '/?$#';
        $this->routes[] = compact('method', 'pattern', 'handler', 'middleware');
    }

    public function dispatch(string $method, string $uri): void
    {
        // Strip query string and normalize.
        $uri  = parse_url($uri, PHP_URL_PATH) ?? '/';
        $uri  = rtrim($uri, '/');
        if ($uri === '') $uri = '/';

        // Respond to CORS preflight.
        if ($method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        foreach ($this->routes as $r) {
            if ($r['method'] !== $method) continue;
            if (!preg_match($r['pattern'], $uri, $matches)) continue;

            $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);

            // Run middleware; any of them can short-circuit with Response::error.
            $context = [];
            foreach ($r['middleware'] as $mw) {
                $context = array_merge($context, (new $mw())->handle() ?? []);
            }

            [$class, $action] = $r['handler'];
            $controller = new $class();
            $controller->$action($params, $context);
            return;
        }

        Response::error('Route not found: ' . $method . ' ' . $uri, 404);
    }
}
