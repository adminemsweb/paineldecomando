<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var list<array{string,string,callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void { $this->add('GET', $path, $handler); }
    public function post(string $path, callable $handler): void { $this->add('POST', $path, $handler); }
    public function put(string $path, callable $handler): void { $this->add('PUT', $path, $handler); }
    public function delete(string $path, callable $handler): void { $this->add('DELETE', $path, $handler); }
    private function add(string $method, string $path, callable $handler): void { $this->routes[] = [$method, $path, $handler]; }

    public function dispatch(string $method, string $uri): never
    {
        $path = '/' . trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
        foreach ($this->routes as [$routeMethod, $route, $handler]) {
            $pattern = '#^' . preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route) . '$#';
            if ($method === $routeMethod && preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $handler(new Request(), $params);
                Response::success();
            }
        }
        Response::error('Endpoint não encontrado.', 404);
    }
}

