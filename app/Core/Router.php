<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes[] = ['method' => 'GET', 'path' => $path, 'handler' => $handler];
    }

    public function post(string $path, string $handler): void
    {
        $this->routes[] = ['method' => 'POST', 'path' => $path, 'handler' => $handler];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $_GET['url'] ?? '';
        $uri    = trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = '#^' . preg_replace('/:([a-zA-Z]+)/', '(?P<$1>[^/]+)', $route['path']) . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                // Extract named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$controllerName, $action] = explode('@', $route['handler']);
                $controllerClass = "App\\Controllers\\{$controllerName}";

                if (!class_exists($controllerClass)) {
                    $this->abort(404);
                    return;
                }

                $controller = new $controllerClass();

                if (!method_exists($controller, $action)) {
                    $this->abort(404);
                    return;
                }

                $controller->$action($params);
                return;
            }
        }

        $this->abort(404);
    }

    private function abort(int $code): void
    {
        http_response_code($code);
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        echo "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>404 — Not Found</title>
            <link rel='preconnect' href='https://fonts.googleapis.com'>
            <link href='https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap' rel='stylesheet'>
            <style>
                *{margin:0;padding:0;box-sizing:border-box}
                body{background:#0a0a0a;color:#fff;font-family:'Inter',sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;text-align:center}
                h1{font-size:6rem;font-weight:600;color:#f59e0b;line-height:1}
                p{color:#666;margin-top:1rem;font-size:1rem}
                a{color:#f59e0b;text-decoration:none;margin-top:2rem;display:inline-block;border:1px solid #f59e0b;padding:.5rem 1.5rem;border-radius:4px;font-size:.875rem;transition:all .2s}
                a:hover{background:#f59e0b;color:#000}
            </style>
        </head>
        <body>
            <div>
                <h1>{$code}</h1>
                <p>Halaman tidak ditemukan.</p>
                <a href='{$base}/'>Kembali ke Beranda</a>
            </div>
        </body>
        </html>
        ";
    }
}
