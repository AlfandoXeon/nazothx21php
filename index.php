<?php
/**
 * Nazo Linktree - Front Controller
 */

// Load environment variables manually (no Composer)
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');

// Load .env
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Manual PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $map = [
        'App\\Core\\'        => APP_PATH . '/Core/',
        'App\\Models\\'      => APP_PATH . '/Models/',
        'App\\Controllers\\' => APP_PATH . '/Controllers/',
    ];
    foreach ($map as $prefix => $dir) {
        if (strpos($class, $prefix) === 0) {
            $relative = substr($class, strlen($prefix));
            $file = $dir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Start session
session_start();

// Bootstrap Router
use App\Core\Router;

$router = new Router();

// Public Routes
$router->get('', 'HomeController@index');
$router->get('gallery', 'GalleryController@index');
$router->post('komentar', 'HomeController@storeComment');
$router->get('go/:id', 'HomeController@redirectCard');

// Admin Routes
$adminRoute = $_ENV['ADMIN_ROUTE'] ?? 'adminadalahraja';
$router->get($adminRoute, 'AdminController@login');
$router->post($adminRoute, 'AdminController@processLogin');
$router->get($adminRoute . '/logout', 'AdminController@logout');
$router->get($adminRoute . '/dashboard', 'AdminController@dashboard');

// Admin - Cards
$router->get($adminRoute . '/cards', 'AdminController@cards');
$router->post($adminRoute . '/cards/store', 'AdminController@storeCard');
$router->post($adminRoute . '/cards/update', 'AdminController@updateCard');
$router->post($adminRoute . '/cards/delete', 'AdminController@deleteCard');
$router->post($adminRoute . '/cards/toggle', 'AdminController@toggleCard');
$router->post($adminRoute . '/cards/reorder', 'AdminController@reorderCards');

// Admin - Gallery
$router->get($adminRoute . '/gallery', 'AdminController@gallery');
$router->post($adminRoute . '/gallery/upload', 'AdminController@uploadGallery');
$router->post($adminRoute . '/gallery/delete', 'AdminController@deleteGallery');

// Admin - Comments
$router->get($adminRoute . '/comments', 'AdminController@comments');
$router->post($adminRoute . '/comments/delete', 'AdminController@deleteComment');
$router->post($adminRoute . '/comments/toggle', 'AdminController@toggleComment');

// Admin - Social Header
$router->get($adminRoute . '/social', 'AdminController@social');
$router->post($adminRoute . '/social/store', 'AdminController@storeSocial');
$router->post($adminRoute . '/social/update', 'AdminController@updateSocial');
$router->post($adminRoute . '/social/delete', 'AdminController@deleteSocial');

// Admin - Profile
$router->get($adminRoute . '/profile', 'AdminController@profile');
$router->post($adminRoute . '/profile/update', 'AdminController@updateProfile');

// Admin - Settings
$router->get($adminRoute . '/settings', 'AdminController@settings');
$router->post($adminRoute . '/settings/update', 'AdminController@updateSettings');

// Dispatch
$router->dispatch();
