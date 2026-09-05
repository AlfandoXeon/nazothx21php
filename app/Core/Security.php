<?php
namespace App\Core;

class Security
{
    /**
     * Generate a CSRF token and store in session.
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token from POST data.
     */
    public static function validateCsrf(): bool
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Abort if CSRF is invalid.
     */
    public static function requireCsrf(): void
    {
        if (!self::validateCsrf()) {
            http_response_code(403);
            die(json_encode(['success' => false, 'message' => 'Invalid CSRF token.']));
        }
    }

    /**
     * Sanitize string output for HTML display.
     */
    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Simple rate limiter based on session.
     * Returns true if allowed, false if rate-limited.
     */
    public static function rateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 60): bool
    {
        $sessionKey = 'rl_' . $key;
        $now = time();

        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = ['count' => 0, 'reset_at' => $now + $windowSeconds];
        }

        if ($now > $_SESSION[$sessionKey]['reset_at']) {
            $_SESSION[$sessionKey] = ['count' => 0, 'reset_at' => $now + $windowSeconds];
        }

        $_SESSION[$sessionKey]['count']++;

        return $_SESSION[$sessionKey]['count'] <= $maxAttempts;
    }

    /**
     * Validate and handle file upload.
     * Returns the new filename on success, throws on failure.
     */
    public static function handleUpload(array $file, string $destDir, array $allowedMimes): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload error: ' . $file['error']);
        }

        $maxSize = (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760);
        if ($file['size'] > $maxSize) {
            throw new \RuntimeException('File terlalu besar. Maksimal ' . ($maxSize / 1024 / 1024) . 'MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowedMimes, true)) {
            throw new \RuntimeException('Tipe file tidak diizinkan: ' . $mime);
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName  = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
        $destPath = rtrim($destDir, '/') . '/' . $newName;

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new \RuntimeException('Gagal memindahkan file upload.');
        }

        return $newName;
    }
}
