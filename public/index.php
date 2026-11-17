<?php
/**
 * Entry Point
 * Sistem Inspeksi Kebersihan — Al Wildan Islamic School
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('PUBLIC_PATH', __DIR__);

// Error handling (matikan di production)
$config = require ROOT_PATH . '/config/app.php';
if ($config['debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Autoload
spl_autoload_register(function (string $class): void {
    $paths = [
        APP_PATH  . '/controllers/' . $class . '.php',
        APP_PATH  . '/models/'      . $class . '.php',
        APP_PATH  . '/middleware/'  . $class . '.php',
        CORE_PATH . '/'             . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Bootstrap
require_once CORE_PATH . '/Session.php';
Session::start();

require_once CORE_PATH . '/Helper.php';
require_once CORE_PATH . '/Security.php';
require_once APP_PATH  . '/middleware/SecurityMiddleware.php';

// Jalankan security checks sebelum routing
SecurityMiddleware::handle();

require_once CORE_PATH . '/Router.php';

$router = new Router();
$router->dispatch();
