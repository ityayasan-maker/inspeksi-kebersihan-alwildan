<?php
/**
 * Konfigurasi Database PDO
 * Mendukung environment variables (Vercel, Railway, Docker) maupun nilai lokal (XAMPP / Laragon).
 */
return [
    'host'    => getenv('DB_HOST') ?: '127.0.0.1',
    'dbname'  => getenv('DB_NAME') ?: 'inspeksi_kebersihan_alwildan',
    'user'    => getenv('DB_USER') ?: 'root',
    'pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
