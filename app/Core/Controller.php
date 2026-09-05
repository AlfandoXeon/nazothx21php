<?php
namespace App\Core;

class Controller
{
    /**
     * Render a view file with given data.
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        // Make data available as variables
        extract($data);

        // Capture view content
        ob_start();
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            die("View not found: {$view}");
        }
        require $viewFile;
        $content = ob_get_clean();

        // Render layout
        $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Redirect to a URL.
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Return JSON response.
     */
    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get base URL.
     */
    protected function baseUrl(string $path = ''): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * Flash a message to session.
     */
    protected function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Get and clear a flash message.
     */
    protected function getFlash(string $key): ?string
    {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}
