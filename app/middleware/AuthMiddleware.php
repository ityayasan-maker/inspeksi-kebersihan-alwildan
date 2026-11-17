<?php
/**
 * Auth Middleware
 * Digunakan langsung di dalam Controller via requireAuth() / requireRole()
 * File ini juga menyediakan static helper untuk pengecekan cepat
 */
class AuthMiddleware
{
    /** Coba auto-login dari cookie PWA jika session ter-reset oleh serverless */
    public static function tryAutoLoginFromCookie(): bool
    {
        if (empty($_COOKIE['alwildan_pwa_token'])) {
            return false;
        }

        try {
            $data = json_decode(base64_decode($_COOKIE['alwildan_pwa_token']), true);
            if (!$data || empty($data['id']) || empty($data['sig']) || empty($data['exp'])) {
                return false;
            }

            if (time() > $data['exp']) {
                return false;
            }

            $userModel = new UserModel();
            $user = $userModel->find((int)$data['id']);
            if (!$user || empty($user['is_active'])) {
                return false;
            }

            $secret = getenv('SUPABASE_SECRET_KEY') ?: 'ALWILDAN_SECRET_KEY_2026';
            $expectedSig = hash_hmac('sha256', $user['id'] . '|' . $user['email'] . '|' . $user['password'], $secret);

            if (!hash_equals($expectedSig, $data['sig'])) {
                return false;
            }

            // Restore session user otomatis!
            Session::set('user_id',       $user['id']);
            Session::set('user_name',     $user['name']);
            Session::set('user_email',    $user['email']);
            Session::set('user_role',     $user['role']);
            Session::set('user_photo',    $user['photo'] ?? null);
            Session::set('last_activity', time());

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /** Pastikan user sudah login, redirect ke login jika belum */
    public static function check(): void
    {
        if (!Session::isLoggedIn()) {
            if (self::tryAutoLoginFromCookie()) {
                return; // Auto-login sukses!
            }

            Session::flash('redirect_after_login', $_SERVER['REQUEST_URI'] ?? '');
            header('Location: ' . Helper::baseUrl('login'));
            exit;
        }

        // Cek session lifetime
        $lastActivity = Session::get('last_activity', 0);
        $cfg          = require ROOT_PATH . '/config/app.php';
        if ($lastActivity && (time() - $lastActivity) > ($cfg['session_lifetime'] ?? 7200)) {
            if (self::tryAutoLoginFromCookie()) {
                Session::set('last_activity', time());
                return;
            }
            Session::destroy();
            header('Location: ' . Helper::baseUrl('login?timeout=1'));
            exit;
        }
        Session::set('last_activity', time());
    }

    /** Pastikan user memiliki role tertentu */
    public static function role(string|array $roles): void
    {
        self::check();
        if (!Session::isRole($roles)) {
            header('Location: ' . Helper::baseUrl('dashboard'));
            exit;
        }
    }

    /** Hanya admin */
    public static function adminOnly(): void
    {
        self::role('admin');
    }

    /** Admin atau supervisor */
    public static function supervisorUp(): void
    {
        self::role(['admin', 'supervisor']);
    }
}
