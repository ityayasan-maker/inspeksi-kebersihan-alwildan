<?php
/**
 * Router — dispatch URL ke Controller@method
 */
class Router
{
    private array $routes = [];

    public function __construct()
    {
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        // ── Auth ─────────────────────────────────────────
        $this->add('GET',  'login',   'AuthController@showLogin');
        $this->add('POST', 'login',   'AuthController@login');
        $this->add('GET',  'logout',  'AuthController@logout');

        // ── Dashboard ─────────────────────────────────────
        $this->add('GET',  'dashboard',              'DashboardController@index');
        $this->add('GET',  'dashboard/stats',        'DashboardController@stats');
        $this->add('GET',  'dashboard/ranking',      'DashboardController@ranking');
        $this->add('GET',  'dashboard/area-masalah', 'DashboardController@areaMasalah');

        // ── Jadwal Foto Kebersihan ────────────────────────
        $this->add('GET',  'jadwal',         'JadwalController@index');
        $this->add('POST', 'jadwal/create',  'JadwalController@store');
        $this->add('POST', 'jadwal/delete',  'JadwalController@delete');

        // ── Master: Gedung ────────────────────────────────
        $this->add('GET',  'gedung',          'GedungController@index');
        $this->add('GET',  'gedung/create',   'GedungController@create');
        $this->add('POST', 'gedung/create',   'GedungController@store');
        $this->add('GET',  'gedung/edit/:id', 'GedungController@edit');
        $this->add('POST', 'gedung/edit/:id', 'GedungController@update');
        $this->add('POST', 'gedung/delete',   'GedungController@delete');

        // ── Master: Lantai ────────────────────────────────
        $this->add('GET',  'lantai',          'LantaiController@index');
        $this->add('POST', 'lantai/create',   'LantaiController@store');
        $this->add('POST', 'lantai/edit/:id', 'LantaiController@update');
        $this->add('POST', 'lantai/delete',   'LantaiController@delete');
        $this->add('GET',  'lantai/by-gedung', 'LantaiController@byGedungAjax');

        // ── Master: Ruangan ───────────────────────────────
        $this->add('GET',  'ruangan',          'RuanganController@index');
        $this->add('GET',  'ruangan/create',   'RuanganController@create');
        $this->add('POST', 'ruangan/create',   'RuanganController@store');
        $this->add('GET',  'ruangan/edit/:id', 'RuanganController@edit');
        $this->add('POST', 'ruangan/edit/:id', 'RuanganController@update');
        $this->add('POST', 'ruangan/delete',   'RuanganController@delete');
        // ── Checklist per Ruangan ─────────────────────────────
        $this->add('GET',  'checklist/:id',          'ChecklistController@index');
        $this->add('POST', 'checklist/:id/assign',   'ChecklistController@assign');
        $this->add('POST', 'checklist/:id/add',      'ChecklistController@add');
        $this->add('POST', 'checklist/:id/toggle',   'ChecklistController@toggle');
        $this->add('POST', 'checklist/:id/reset',    'ChecklistController@reset');
        $this->add('GET',  'checklist/:id/preview',  'ChecklistController@preview');
        $this->add('POST', 'checklist/copy',         'ChecklistController@copy');

        // ── Master: Standar Kebersihan ────────────────────
        $this->add('GET',  'standar',          'StandarController@index');
        $this->add('GET',  'standar/create',   'StandarController@create');
        $this->add('POST', 'standar/create',   'StandarController@store');
        $this->add('GET',  'standar/edit/:id', 'StandarController@edit');
        $this->add('POST', 'standar/edit/:id', 'StandarController@update');
        $this->add('POST', 'standar/delete',   'StandarController@delete');
        $this->add('GET',  'standar/by-tipe',  'StandarController@byTipe'); // AJAX

        // ── Master: Users ─────────────────────────────────
        $this->add('GET',  'users',          'UserController@index');
        $this->add('GET',  'users/create',   'UserController@create');
        $this->add('POST', 'users/create',   'UserController@store');
        $this->add('GET',  'users/edit/:id', 'UserController@edit');
        $this->add('POST', 'users/edit/:id', 'UserController@update');
        $this->add('POST', 'users/delete',   'UserController@delete');
        $this->add('GET',  'users/profile',  'UserController@profile');
        $this->add('POST', 'users/profile',  'UserController@updateProfile');

        // ── Inspeksi ─────────────────────────────────────
        $this->add('GET',  'inspeksi',              'InspeksiController@index');
        $this->add('GET',  'inspeksi/create',       'InspeksiController@create');
        $this->add('POST', 'inspeksi/create',       'InspeksiController@store');
        $this->add('GET',  'inspeksi/detail/:id',   'InspeksiController@detail');
        $this->add('GET',  'inspeksi/edit/:id',     'InspeksiController@edit');
        $this->add('POST', 'inspeksi/edit/:id',     'InspeksiController@update');
        $this->add('POST', 'inspeksi/delete',       'InspeksiController@delete');
        $this->add('POST', 'inspeksi/upload-foto',  'InspeksiController@uploadFoto');
        $this->add('GET',  'inspeksi/checklist',    'InspeksiController@getChecklist'); // AJAX

        // ── Laporan ───────────────────────────────────────
        $this->add('GET',  'laporan',             'LaporanController@index');
        $this->add('GET',  'laporan/export-excel','LaporanController@exportExcel');
        $this->add('GET',  'laporan/export-pdf',  'LaporanController@exportPdf');
        $this->add('GET',  'laporan/print',       'LaporanController@print');

        // ── Notifikasi ────────────────────────────────────
        $this->add('GET',  'notifikasi',          'NotifikasiController@index');
        $this->add('POST', 'notifikasi/read',     'NotifikasiController@markRead');
        $this->add('GET',  'notifikasi/unread',   'NotifikasiController@unreadCount'); // AJAX


        // ── Upload ───────────────────────────────────────────
        $this->add('POST', 'upload/inspeksi-foto',  'UploadController@inspeksiFoto');
        $this->add('POST', 'upload/profile-photo',  'UploadController@profilePhoto');
        $this->add('POST', 'upload/delete',         'UploadController@deleteFile');
        $this->add('GET',  'upload/gallery/:id',    'UploadController@gallery');

        // ── Admin ────────────────────────────────────────
        $this->add('GET', 'admin/activity-log', 'AdminController@activityLog');

                // ── Settings (admin only) ─────────────────────────
        $this->add('GET',  'settings',  'SettingsController@index');
        $this->add('POST', 'settings',  'SettingsController@update');

        // ── Default: redirect ke dashboard ───────────────
        $this->add('GET', '', 'DashboardController@index');
    }

    private function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => trim($path, '/'),
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        if (isset($_GET['url'])) {
            $uri = trim(parse_url($_GET['url'], PHP_URL_PATH) ?? '', '/');
        } else {
            $rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            if ($scriptDir !== '/' && $scriptDir !== '\\' && $scriptDir !== '.') {
                $scriptDirNormalized = str_replace('\\', '/', $scriptDir);
                if (str_starts_with($rawUri, $scriptDirNormalized)) {
                    $rawUri = substr($rawUri, strlen($scriptDirNormalized));
                }
            }
            $uri = trim($rawUri, '/');
        }

        foreach ($this->routes as $route) {
            $params = $this->match($route['path'], $uri);
            if ($params !== false && $route['method'] === $method) {
                [$class, $action] = explode('@', $route['handler']);

                // Autoload sudah terdaftar di index.php
                $controller = new $class();
                $controller->$action(...array_values($params));
                return;
            }
        }

        // 404
        http_response_code(404);
        require APP_PATH . '/views/errors/404.php';
    }

    /** Cocokkan path route (dengan :param) ke URI aktual */
    private function match(string $routePath, string $uri): array|false
    {
        $routeParts = explode('/', $routePath);
        $uriParts   = explode('/', $uri);

        if (count($routeParts) !== count($uriParts)) return false;

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, ':')) {
                $params[ltrim($part, ':')] = $uriParts[$i];
            } elseif ($part !== $uriParts[$i]) {
                return false;
            }
        }
        return $params;
    }
}
