<?php
/**
 * SecurityMiddleware
 * Dipanggil di index.php sebelum routing — satu titik keamanan global
 */
class SecurityMiddleware
{
    public static function handle(): void
    {
        // 1. Security headers
        Security::setHeaders();

        // 2. Session hardening (hanya jika sudah login)
        if (Session::isLoggedIn()) {
            Security::hardenSession();

            // 3. Session lifetime check
            $cfg          = require ROOT_PATH . '/config/app.php';
            $lastActivity = Session::get('last_activity', 0);
            if ($lastActivity && (time() - $lastActivity) > $cfg['session_lifetime']) {
                Session::destroy();
                header('Location: ' . rtrim($cfg['url'], '/') . '/login?timeout=1');
                exit;
            }
            Session::set('last_activity', time());
        }

        // 4. Blokir metode HTTP berbahaya
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, ['GET','POST','HEAD','OPTIONS'], true)) {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        // 5. Sanitasi global $_GET dan $_POST (strip null bytes)
        self::sanitizeGlobals();
    }

    private static function sanitizeGlobals(): void
    {
        // Hapus null bytes dari semua input
        array_walk_recursive($_GET,    fn(&$v) => $v = str_replace("\0", '', (string)$v));
        array_walk_recursive($_POST,   fn(&$v) => $v = str_replace("\0", '', (string)$v));
        array_walk_recursive($_COOKIE, fn(&$v) => $v = str_replace("\0", '', (string)$v));
    }
}
