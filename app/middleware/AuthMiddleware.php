<?php
/**
 * Auth Middleware
 * Digunakan langsung di dalam Controller via requireAuth() / requireRole()
 * File ini juga menyediakan static helper untuk pengecekan cepat
 */
class AuthMiddleware
{
    /** Pastikan user sudah login, redirect ke login jika belum */
    public static function check(): void
    {
        if (!Session::isLoggedIn()) {
            Session::flash('redirect_after_login', $_SERVER['REQUEST_URI'] ?? '');
            $cfg = require ROOT_PATH . '/config/app.php';
            header('Location: ' . rtrim($cfg['url'], '/') . '/login');
            exit;
        }

        // Cek session lifetime
        $lastActivity = Session::get('last_activity', 0);
        $cfg          = require ROOT_PATH . '/config/app.php';
        if ($lastActivity && (time() - $lastActivity) > $cfg['session_lifetime']) {
            Session::destroy();
            header('Location: ' . rtrim($cfg['url'], '/') . '/login?timeout=1');
            exit;
        }
        Session::set('last_activity', time());
    }

    /** Pastikan user memiliki role tertentu */
    public static function role(string|array $roles): void
    {
        self::check();
        if (!Session::isRole($roles)) {
            $cfg = require ROOT_PATH . '/config/app.php';
            header('Location: ' . rtrim($cfg['url'], '/') . '/dashboard');
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
