<?php
/**
 * Konfigurasi Database PDO
 * Mendukung Supabase PostgreSQL maupun MySQL (XAMPP / cPanel / TiDB)
 */
$isPg = (getenv('DB_DRIVER') === 'pgsql' ||
         getenv('DB_PORT') == '5432' ||
         getenv('DB_PORT') == '6543' ||
         str_contains(getenv('DB_HOST') ?: '', 'supabase') ||
         getenv('POSTGRES_URL') !== false);

return [
    'driver'  => $isPg ? 'pgsql' : 'mysql',
    'host'    => getenv('DB_HOST') ?: '127.0.0.1',
    'port'    => getenv('DB_PORT') ?: ($isPg ? 5432 : 3306),
    'dbname'  => getenv('DB_NAME') ?: ($isPg ? 'postgres' : 'inspeksi_kebersihan_alwildan'),
    'user'    => getenv('DB_USER') ?: 'root',
    'pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => true,
    ],
];
