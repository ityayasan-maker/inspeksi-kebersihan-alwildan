<?php
/**
 * Session Helper
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                    || (!empty($_SERVER['HTTP_HOST']) && !str_contains($_SERVER['HTTP_HOST'], 'localhost'));

            ini_set('session.gc_maxlifetime', 86400);
            session_set_cookie_params([
                'lifetime' => 86400,
                'path'     => '/',
                'secure'   => $isHttps,
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

    /** CSRF token — Serverless resilient (HMAC timestamp + Double Submit Cookie) */
    public static function csrfToken(): string
    {
        $secret = getenv('SUPABASE_SECRET_KEY') ?: 'ALWILDAN_CSRF_KEY_2026';
        $time   = time();
        $token  = $time . '.' . hash_hmac('sha256', (string)$time, $secret);

        self::set('csrf_token', $token);

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (!empty($_SERVER['HTTP_HOST']) && !str_contains($_SERVER['HTTP_HOST'], 'localhost'));

        if (!headers_sent()) {
            setcookie('_csrf', $token, [
                'expires'  => time() + 86400,
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }

        return $token;
    }

    public static function verifyCsrf(string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        // 1. Verifikasi cryptographic HMAC token (stateless — tahan serverless container recycle)
        $secret = getenv('SUPABASE_SECRET_KEY') ?: 'ALWILDAN_CSRF_KEY_2026';
        $parts  = explode('.', $token);
        if (count($parts) === 2 && is_numeric($parts[0])) {
            $ts  = (int)$parts[0];
            $sig = $parts[1];
            // Valid dalam kurun waktu 24 jam (86400 detik)
            if ((time() - $ts) < 86400 && hash_equals(hash_hmac('sha256', (string)$ts, $secret), $sig)) {
                return true;
            }
        }

        // 2. Fallback: Cocokkan dengan session
        $sessToken = self::get('csrf_token');
        if ($sessToken && hash_equals($sessToken, $token)) {
            return true;
        }

        // 3. Fallback: Cocokkan dengan cookie _csrf (double submit)
        $cookieToken = $_COOKIE['_csrf'] ?? '';
        if ($cookieToken && hash_equals($cookieToken, $token)) {
            return true;
        }

        return false;
    }

    public static function regenerateCsrf(): void
    {
        self::delete('csrf_token');
    }
}
