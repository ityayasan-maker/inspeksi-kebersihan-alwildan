<?php
/**
 * Security — utilitas keamanan terpusat
 * Covers: CSRF, XSS, input sanitize, rate limiting,
 *         secure headers, session hardening, file validation
 */
class Security
{
    /* ── HTTP Security Headers ───────────────────────────── */

    public static function setHeaders(): void
    {
        // Hanya kirim header yang belum ada (hindari duplikat)
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: camera=(self), microphone=(), geolocation=()');

            // CSP: izinkan cdn yang dipakai dan supabase storage
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdn.datatables.net https://api.anthropic.com",
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.datatables.net https://fonts.googleapis.com",
                "img-src 'self' data: blob: https://*.supabase.co https://mvopjzbvhcdnnwyvucdy.supabase.co",
                "font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com",
                "connect-src 'self' https://*.supabase.co https://mvopjzbvhcdnnwyvucdy.supabase.co https://cdn.datatables.net https://cdn.jsdelivr.net",
                "frame-ancestors 'none'",
            ]);
            header("Content-Security-Policy: {$csp}");
        }
    }

    /* ── XSS Sanitization ────────────────────────────────── */

    /** Escape HTML untuk output */
    public static function e(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Strip tags + trim untuk input teks biasa */
    public static function sanitizeText(string $input, int $maxLen = 500): string
    {
        $clean = strip_tags(trim($input));
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $clean); // Hapus control chars
        return mb_substr($clean, 0, $maxLen);
    }

    /** Sanitize array rekursif */
    public static function sanitizeArray(array $arr, int $maxLen = 500): array
    {
        $result = [];
        foreach ($arr as $k => $v) {
            $cleanKey = self::sanitizeText((string)$k, 100);
            $result[$cleanKey] = is_array($v)
                ? self::sanitizeArray($v, $maxLen)
                : self::sanitizeText((string)$v, $maxLen);
        }
        return $result;
    }

    /** Validasi dan sanitasi integer */
    public static function sanitizeInt(mixed $val, int $min = 0, int $max = PHP_INT_MAX): int
    {
        $int = (int) filter_var($val, FILTER_SANITIZE_NUMBER_INT);
        return max($min, min($max, $int));
    }

    /** Sanitasi URL - hanya izinkan http/https */
    public static function sanitizeUrl(string $url): string
    {
        $url = filter_var(trim($url), FILTER_SANITIZE_URL);
        if (!filter_var($url, FILTER_VALIDATE_URL)) return '';
        $parsed = parse_url($url);
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) return '';
        return $url;
    }

    /** Sanitasi nama file — hanya alphanumeric, dash, underscore, titik */
    public static function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9._\-]/', '_', basename($name));
        // Cegah path traversal
        $name = str_replace(['..', '/', '\\'], '_', $name);
        return $name ?: 'file_' . time();
    }

    /* ── CSRF ────────────────────────────────────────────── */

    public static function csrfToken(): string
    {
        return Session::csrfToken();
    }

    public static function verifyCsrf(string $token): bool
    {
        if (!$token) return false;
        return Session::verifyCsrf($token);
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::e(self::csrfToken()) . '">';
    }

    /* ── Rate Limiting (session-based) ───────────────────── */

    /**
     * Periksa rate limit untuk suatu aksi
     * @param string $action  Identifier unik (cth: 'login', 'upload')
     * @param int    $maxHits Maksimum request per window
     * @param int    $window  Window waktu dalam detik
     */
    public static function checkRateLimit(string $action, int $maxHits = 10, int $window = 60): bool
    {
        $key      = "rl_{$action}";
        $now      = time();
        $data     = Session::get($key, ['count' => 0, 'reset_at' => $now + $window]);

        if ($now > $data['reset_at']) {
            // Reset window
            $data = ['count' => 1, 'reset_at' => $now + $window];
            Session::set($key, $data);
            return true; // Allowed
        }

        if ($data['count'] >= $maxHits) {
            return false; // Blocked
        }

        $data['count']++;
        Session::set($key, $data);
        return true;
    }

    public static function getRateLimitRemaining(string $action): int
    {
        $data = Session::get("rl_{$action}", ['reset_at' => 0]);
        return max(0, $data['reset_at'] - time());
    }

    /* ── Session Hardening ───────────────────────────────── */

    /**
     * Validasi integritas session (cegah session hijacking)
     * Dipanggil sekali setelah login
     */
    public static function hardenSession(): void
    {
        $ua  = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200);
        $ip  = self::getClientIp();
        $key = 'sess_fingerprint';

        if (!Session::has($key)) {
            // Simpan fingerprint saat pertama kali login
            Session::set($key, md5($ua . $ip));
            return;
        }

        // Validasi saat request berikutnya
        if (Session::get($key) !== md5($ua . $ip)) {
            // Fingerprint berbeda — kemungkinan session hijacking
            Session::destroy();
            // Redirect ke login
            $cfg = require ROOT_PATH . '/config/app.php';
            header('Location: ' . rtrim($cfg['url'], '/') . '/login?security=1');
            exit;
        }
    }

    /* ── Input Validation ────────────────────────────────── */

    public static function validateEmail(string $email): bool
    {
        return (bool) filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    }

    public static function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function validateEnum(string $value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    /** Password strength: min 8 char, ada huruf + angka */
    public static function validatePassword(string $pw): array
    {
        $errors = [];
        if (strlen($pw) < 8)                     $errors[] = 'Minimal 8 karakter';
        if (!preg_match('/[A-Za-z]/', $pw))       $errors[] = 'Harus ada huruf';
        if (!preg_match('/[0-9]/', $pw))          $errors[] = 'Harus ada angka';
        return $errors;
    }

    /* ── Upload Security ─────────────────────────────────── */

    /** Validasi file upload — return array error atau empty array */
    public static function validateUpload(array $file, array $options = []): array
    {
        $errors     = [];
        $maxKb      = $options['max_kb']  ?? 2048;
        $allowedExt = $options['allowed'] ?? ['jpg','jpeg','png','webp'];
        $allowedMime= $options['mimes']   ?? ['image/jpeg','image/png','image/webp','image/gif'];

        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = match($file['error'] ?? 99) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File terlalu besar.',
                UPLOAD_ERR_PARTIAL   => 'Upload tidak lengkap.',
                UPLOAD_ERR_NO_FILE   => 'Tidak ada file.',
                default              => 'Terjadi kesalahan upload.',
            };
            return $errors;
        }

        // Ukuran
        if (($file['size'] / 1024) > $maxKb) {
            $errors[] = "Ukuran file melebihi {$maxKb} KB.";
        }

        // Ekstensi
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            $errors[] = 'Ekstensi tidak diizinkan: ' . $ext;
        }

        // MIME via finfo
        if (function_exists('finfo_open')) {
            $fi   = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($fi, $file['tmp_name']);
            finfo_close($fi);
            if (!in_array($mime, $allowedMime, true)) {
                $errors[] = 'Tipe file tidak valid: ' . $mime;
            }
        }

        // Cek apakah file sungguh gambar (buka dengan GD)
        if (empty($errors) && function_exists('getimagesize')) {
            $info = @getimagesize($file['tmp_name']);
            if (!$info) {
                $errors[] = 'File bukan gambar yang valid.';
            }
        }

        return $errors;
    }

    /* ── SQL Injection Prevention ────────────────────────── */

    /**
     * Pastikan semua query pakai PDO prepared statements.
     * Method ini hanya sebagai reminder/dokumentasi.
     * PDO di base Model sudah menggunakan prepare() + execute().
     */
    public static function escapeLike(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    }

    /* ── Helpers ─────────────────────────────────────────── */

    public static function getClientIp(): string
    {
        $keys = ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    public static function generateToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }
}
