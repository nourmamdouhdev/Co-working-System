<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable|array}> */
    private array $routes = [];

    public function get(string $pattern, callable|array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable|array $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $regex = $this->toRegex($route['pattern']);
            if (!preg_match($regex, $uri, $matches)) {
                continue;
            }

            if ($method === 'POST' && !verify_csrf_token($_POST['_csrf'] ?? null)) {
                http_response_code(419);
                echo 'Invalid CSRF token.';
                return;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }

            $handler = $route['handler'];
            if (is_array($handler) && count($handler) === 2 && is_string($handler[0])) {
                $controller = new $handler[0]();
                $methodName = $handler[1];
                $controller->{$methodName}($params);
                return;
            }

            $handler($params);
            return;
        }

        http_response_code(404);
        echo 'Not Found';
    }

    private function toRegex(string $pattern): string
    {
        $pattern = rtrim($pattern, '/');
        if ($pattern === '') {
            $pattern = '/';
        }

        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '/?$#';
    }
}
