<?php
/**
 * Local Development Server Router for PHP Built-in Server
 * 
 * Penggunaan dari PowerShell / CMD:
 *   cd C:\Users\ariel\.gemini\antigravity\scratch\inspeksi-kebersihan-alwildan
 *   php -S localhost:8000 router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

// 1. Cek jika request aset dengan prefix /public/...
if (str_starts_with($uri, '/public/')) {
    $filePath = __DIR__ . $uri;
    if (file_exists($filePath) && !is_dir($filePath)) {
        return false;
    }
}

// 2. Cek jika request aset tanpa prefix /public (misal: /css/..., /js/..., /img/..., /sw.js, /favicon.ico)
$publicFilePath = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($publicFilePath) && !is_dir($publicFilePath)) {
    $ext = strtolower(pathinfo($publicFilePath, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    readfile($publicFilePath);
    return true;
}

// 3. Cek file fisik di root selain index dan router
$rootFilePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($rootFilePath) && !is_dir($rootFilePath) && !in_array($uri, ['/index.php', '/router.php'])) {
    return false;
}

// 4. Teruskan seluruh request dinamis (/login, /dashboard, dll.) ke public/index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require_once __DIR__ . '/public/index.php';
