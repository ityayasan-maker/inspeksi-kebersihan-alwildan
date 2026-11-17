<?php
/**
 * Session Helper
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', 7200);
            session_set_cookie_params([
                'lifetime' => 7200,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** Flash message — tulis sekali, baca sekali */
    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    public static function isLoggedIn(): bool
    {
        return self::has('user_id');
    }

    public static function user(): ?array
    {
        if (!self::isLoggedIn()) return null;
        return [
            'id'    => self::get('user_id'),
            'name'  => self::get('user_name'),
            'email' => self::get('user_email'),
            'role'  => self::get('user_role'),
            'photo' => self::get('user_photo'),
        ];
    }

    public static function role(): ?string
    {
        return self::get('user_role');
    }

    public static function isRole(string|array $roles): bool
    {
        $current = self::role();
        if (!$current) return false;
        return in_array($current, (array) $roles, true);
    }

    /** CSRF token */
    public static function csrfToken(): string
    {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }

    public static function verifyCsrf(string $token): bool
    {
        return hash_equals(self::csrfToken(), $token);
    }

    public static function regenerateCsrf(): void
    {
        self::set('csrf_token', bin2hex(random_bytes(32)));
    }
}
