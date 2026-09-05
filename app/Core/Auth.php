<?php
namespace App\Core;

class Auth
{
    private static string $sessionKey = 'nazo_admin_auth';

    /**
     * Attempt login with given password.
     */
    public static function attempt(string $password): bool
    {
        $stored = $_ENV['ADMIN_PASSWORD'] ?? '';
        if ($password === $stored) {
            session_regenerate_id(true);
            $_SESSION[self::$sessionKey] = true;
            $_SESSION['auth_time'] = time();
            return true;
        }
        return false;
    }

    /**
     * Check if admin is authenticated.
     */
    public static function check(): bool
    {
        if (!isset($_SESSION[self::$sessionKey])) return false;
        // Auto-logout after 8 hours
        if (time() - ($_SESSION['auth_time'] ?? 0) > 28800) {
            self::logout();
            return false;
        }
        return $_SESSION[self::$sessionKey] === true;
    }

    /**
     * Require authentication, redirect if not.
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            $adminRoute = $_ENV['ADMIN_ROUTE'] ?? 'adminadalahraja';
            $base = rtrim($_ENV['APP_URL'] ?? '', '/');
            header("Location: {$base}/{$adminRoute}");
            exit;
        }
    }

    /**
     * Logout and destroy session.
     */
    public static function logout(): void
    {
        unset($_SESSION[self::$sessionKey], $_SESSION['auth_time']);
        session_regenerate_id(true);
    }
}
