<?php
/**
 * Base Controller
 */
abstract class Controller
{
    protected array $appConfig;

    public function __construct()
    {
        $this->appConfig = require ROOT_PATH . '/config/app.php';
        date_default_timezone_set($this->appConfig['timezone']);
    }

    /* ── View renderer ───────────────────────────────────── */

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $appConfig = $this->appConfig;
        $user      = Session::user();

        $file = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View tidak ditemukan: {$file}");
        }
        require $file;
    }

    /** Render view di dalam layout */
    protected function layout(string $view, array $data = [], string $layout = 'main'): void
    {
        $content = $this->capture($view, $data);
        $data['content'] = $content;
        $this->view("partials.layout_{$layout}", $data);
    }

    /** Capture view output ke string */
    protected function capture(string $view, array $data = []): string
    {
        ob_start();
        $this->view($view, $data);
        return ob_get_clean();
    }

    /* ── Response helpers ────────────────────────────────── */

    protected function redirect(string $path): void
    {
        $url = Helper::baseUrl($path);
        header("Location: {$url}");
        exit;
    }

    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function jsonSuccess(mixed $data = null, string $message = 'OK'): void
    {
        $this->json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    protected function jsonError(string $message, int $code = 400): void
    {
        $this->json(['success' => false, 'message' => $message], $code);
    }

    /* ── Input helpers ───────────────────────────────────── */

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    /** Validasi CSRF, abort jika gagal */
    protected function verifyCsrf(): void
    {
        $token = $this->post('_csrf') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_COOKIE['_csrf'] ?? ''));
        if (!Session::verifyCsrf($token)) {
            if ($this->isAjax()) {
                $this->jsonError('CSRF token tidak valid.', 403);
            } else {
                Session::flash('error', 'Sesi kedaluwarsa. Silakan coba masuk kembali.');
                $this->redirect('login');
            }
        }
    }

    /* ── Auth shortcuts ──────────────────────────────────── */

    protected function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            if (class_exists('AuthMiddleware') && AuthMiddleware::tryAutoLoginFromCookie()) {
                return;
            }
            Session::flash('redirect_after_login', $_SERVER['REQUEST_URI'] ?? '');
            $this->redirect('login');
        }
    }

    protected function requireRole(string|array $roles): void
    {
        $this->requireAuth();
        if (!Session::isRole($roles)) {
            $this->redirect('dashboard');
        }
    }

    /* ── Activity log ────────────────────────────────────── */

    protected function logActivity(string $action, string $module, ?int $targetId = null, string $detail = ''): void
    {
        try {
            $log = new ActivityLogModel();
            $log->insert([
                'user_id'    => Session::get('user_id'),
                'action'     => $action,
                'module'     => $module,
                'target_id'  => $targetId,
                'detail'     => $detail,
                'ip_address' => Helper::clientIp(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (\Throwable) {
            // Jangan biarkan gagal log merusak flow utama
        }
    }
}
