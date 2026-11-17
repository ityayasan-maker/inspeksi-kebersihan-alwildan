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
        if (Session::isLoggedIn()) {
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
        $base     = rtrim($this->appConfig['url'], '/') . '/';
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
        Session::destroy();
        $this->redirect('login');
    }
}
