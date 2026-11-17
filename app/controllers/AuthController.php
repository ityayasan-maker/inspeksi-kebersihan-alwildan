<?php
class AuthController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        parent::__construct();
        $this->users = new UserModel();
    }

    /* ── GET /login ─────────────────────────────────────── */
    public function showLogin(): void
    {
        if (Session::isLoggedIn() || AuthMiddleware::tryAutoLoginFromCookie()) {
            $this->redirect('dashboard');
        }
        $timeout = isset($_GET['timeout']);
        $this->view('auth.login', compact('timeout'));
    }

    /* ── POST /login ─────────────────────────────────────── */
    public function login(): void
    {
        $this->verifyCsrf();

        // Rate limiting sederhana via session
        $attempts = Session::get('login_attempts', 0);
        $lastFail  = Session::get('login_last_fail', 0);

        if ($attempts >= 5 && (time() - $lastFail) < 300) {
            Session::flash('error', 'Terlalu banyak percobaan. Tunggu 5 menit.');
            $this->redirect('login');
        }

        $email    = trim($this->post('email', ''));
        $password = $this->post('password', '');

        // Validasi input
        if (!$email || !$password) {
            Session::flash('error', 'Email dan password wajib diisi.');
            $this->redirect('login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Format email tidak valid.');
            $this->redirect('login');
        }

        // Cari user
        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::set('login_attempts', $attempts + 1);
            Session::set('login_last_fail', time());
            Session::flash('error', 'Email atau password salah.');
            $this->redirect('login');
        }

        // Login berhasil — reset attempts
        Session::set('login_attempts', 0);
        Session::delete('login_last_fail');

        // Isi session
        Session::set('user_id',    $user['id']);
        Session::set('user_name',  $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role',  $user['role']);
        Session::set('user_photo', $user['photo']);
        Session::set('last_activity', time());

        // Buat persistent auth token (90 hari) agar di PWA/Mobile tidak perlu login lagi
        $secret = getenv('SUPABASE_SECRET_KEY') ?: 'ALWILDAN_SECRET_KEY_2026';
        $exp = time() + (86400 * 90);
        $sig = hash_hmac('sha256', $user['id'] . '|' . $user['email'] . '|' . $user['password'], $secret);
        $token = base64_encode(json_encode([
            'id'    => $user['id'],
            'email' => $user['email'],
            'exp'   => $exp,
            'sig'   => $sig,
        ]));

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (!empty($_SERVER['HTTP_HOST']) && !str_contains($_SERVER['HTTP_HOST'], 'localhost'));

        setcookie('alwildan_pwa_token', $token, [
            'expires'  => $exp,
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        // Regenerate session ID (fixation prevention)
        session_regenerate_id(true);
        Session::regenerateCsrf();

        // Refresh password hash jika perlu
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $this->users->update($user['id'], [
                'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            ]);
        }

        $this->logActivity('login', 'auth', $user['id'], 'Login berhasil');

        // Redirect ke halaman semula atau dashboard
        $redirect = Session::flash('redirect_after_login') ?: '';
        $base     = rtrim(Helper::baseUrl(), '/') . '/';
        if ($redirect && str_starts_with($redirect, $base)) {
            header("Location: {$redirect}");
            exit;
        }
        $this->redirect('dashboard');
    }

    /* ── GET /logout ─────────────────────────────────────── */
    public function logout(): void
    {
        $userId = Session::get('user_id');
        if ($userId) {
            $this->logActivity('logout', 'auth', $userId, 'Logout');
        }

        // Hapus persistent cookie token
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (!empty($_SERVER['HTTP_HOST']) && !str_contains($_SERVER['HTTP_HOST'], 'localhost'));

        setcookie('alwildan_pwa_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        Session::destroy();
        $this->redirect('login');
    }
}
