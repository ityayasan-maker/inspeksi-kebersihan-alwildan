<?php
/**
 * Helper — Global Utility Functions
 */
class Helper
{
    /* ── URL ─────────────────────────────────────────────── */

    public static function baseUrl(string $path = ''): string
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                     (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                     ? 'https' : 'http';
            $base = $proto . '://' . $_SERVER['HTTP_HOST'];
        } else {
            $cfg  = require ROOT_PATH . '/config/app.php';
            $base = rtrim($cfg['url'], '/');
        }
        return $path ? "{$base}/" . ltrim($path, '/') : $base;
    }

    public static function asset(string $path): string
    {
        return self::baseUrl('public/' . ltrim($path, '/'));
    }

    /* ── String ──────────────────────────────────────────── */

    public static function e(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function slug(string $str): string
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        return preg_replace('/[\s-]+/', '-', $str);
    }

    public static function truncate(string $str, int $len = 100, string $end = '…'): string
    {
        return mb_strlen($str) > $len ? mb_substr($str, 0, $len) . $end : $str;
    }

    /* ── Tanggal ─────────────────────────────────────────── */

    public static function formatDate(string $date, string $format = 'd M Y'): string
    {
        if (!$date) return '-';
        return date($format, strtotime($date));
    }

    public static function formatDatetime(string $dt): string
    {
        return self::formatDate($dt, 'd M Y H:i');
    }

    public static function dateId(string $date): string
    {
        $months = [
            1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
            7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'
        ];
        if (!$date) return '-';
        $ts = strtotime($date);
        return date('d', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    }

    /* ── Scoring ─────────────────────────────────────────── */

    public static function skorLabel(float $skor): string
    {
        if ($skor >= 85) return 'Bersih';
        if ($skor >= 55) return 'Kurang Bersih';
        return 'Kotor';
    }

    public static function skorBadgeClass(float $skor): string
    {
        if ($skor >= 85) return 'success';
        if ($skor >= 55) return 'warning';
        return 'danger';
    }

    public static function kondisiFromSkor(int $skor): string
    {
        return match($skor) {
            100     => 'bersih',
            70      => 'kurang_bersih',
            default => 'kotor',
        };
    }

    public static function statusLabel(string $status): string
    {
        return match($status) {
            'bersih'        => 'Bersih',
            'kurang_bersih' => 'Kurang Bersih',
            'kotor'         => 'Kotor',
            default         => ucfirst($status),
        };
    }

    public static function statusBadge(string $status): string
    {
        $class = match($status) {
            'bersih'        => 'success',
            'kurang_bersih' => 'warning',
            'kotor'         => 'danger',
            default         => 'secondary',
        };
        return "<span class=\"badge bg-{$class}\">" . self::e(self::statusLabel($status)) . "</span>";
    }

    /* ── Unit Organisasi ─────────────────────────────────── */

    public static function unitList(): array
    {
        return [
            'al_wildan_1' => 'Al Wildan 1',
            'al_wildan_2' => 'Al Wildan 2',
            'al_wildan_3' => 'Al Wildan 3',
            'tk'          => 'TK Al Wildan',
            'sd'          => 'SD Al Wildan',
            'smp'         => 'SMP Al Wildan',
            'sma'         => 'SMA Al Wildan',
            'yayasan'     => 'Kantor Yayasan Am Badar',
            'masjid'      => 'Masjid',
            'lainnya'     => 'Lainnya',
        ];
    }

    public static function unitLabel(string $unit): string
    {
        $list = self::unitList();
        $key  = strtolower(trim($unit));
        return $list[$key] ?? strtoupper(str_replace('_', ' ', $unit));
    }

    public static function unitBadge(string $unit): string
    {
        $key = strtolower(trim($unit));
        $class = match($key) {
            'smp'       => 'success',
            'smk'       => 'primary',
            'yayasan'   => 'warning',
            'masjid'    => 'info',
            'al_wildan' => 'danger',
            'sd'        => 'secondary',
            'tk'        => 'info',
            default     => 'dark',
        };
        $label = self::unitLabel($unit);
        return "<span class=\"badge bg-{$class}\">" . self::e($label) . "</span>";
    }

    /* ── Upload ──────────────────────────────────────────── */

    public static function uploadUrl(string $path): string
    {
        if (empty($path)) return '';
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cfg = require ROOT_PATH . '/config/app.php';
        if (!empty($cfg['supabase']['url'])) {
            $bucket = $cfg['supabase']['bucket'] ?? 'inspeksi';
            return rtrim($cfg['supabase']['url'], '/') . "/storage/v1/object/public/{$bucket}/" . ltrim($path, '/');
        }

        return self::baseUrl('public/uploads/' . ltrim($path, '/'));
    }

    public static function generateFilename(string $ext): string
    {
        return date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
    }

    /* ── Security ────────────────────────────────────────── */

    public static function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function clientIp(): string
    {
        $keys = ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key])[0];
                return filter_var(trim($ip), FILTER_VALIDATE_IP) ? trim($ip) : '0.0.0.0';
            }
        }
        return '0.0.0.0';
    }

    /* ── Pagination ──────────────────────────────────────── */

    public static function paginationLinks(array $paginator, string $baseUrl): string
    {
        $current  = $paginator['current_page'];
        $last     = $paginator['last_page'];
        if ($last <= 1) return '';

        $html = '<nav><ul class="pagination pagination-sm mb-0">';

        // Prev
        $prevDisabled = $current <= 1 ? ' disabled' : '';
        $prevHref     = $current > 1  ? "{$baseUrl}?page=" . ($current - 1) : '#';
        $html .= "<li class=\"page-item{$prevDisabled}\"><a class=\"page-link\" href=\"{$prevHref}\">&laquo;</a></li>";

        // Pages
        $range = range(max(1, $current - 2), min($last, $current + 2));
        foreach ($range as $p) {
            $active = $p === $current ? ' active' : '';
            $html  .= "<li class=\"page-item{$active}\"><a class=\"page-link\" href=\"{$baseUrl}?page={$p}\">{$p}</a></li>";
        }

        // Next
        $nextDisabled = $current >= $last ? ' disabled' : '';
        $nextHref     = $current < $last  ? "{$baseUrl}?page=" . ($current + 1) : '#';
        $html .= "<li class=\"page-item{$nextDisabled}\"><a class=\"page-link\" href=\"{$nextHref}\">&raquo;</a></li>";

        $html .= '</ul></nav>';
        return $html;
    }

    /* ── Flash message HTML ──────────────────────────────── */

    public static function flashAlert(): string
    {
        $success = Session::flash('success');
        $error   = Session::flash('error');
        $warning = Session::flash('warning');
        $html    = '';
        if ($success) $html .= "<div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">" . self::e($success) . "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>";
        if ($error)   $html .= "<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">"  . self::e($error)   . "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>";
        if ($warning) $html .= "<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">" . self::e($warning) . "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>";
        return $html;
    }

    /* ── CSRF input field ────────────────────────────────── */

    public static function csrfField(): string
    {
        $token = Session::csrfToken();
        return "<input type=\"hidden\" name=\"_csrf\" value=\"{$token}\">";
    }
}
