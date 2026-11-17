<?php
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require ROOT_PATH . '/config/database.php';

            try {
                $dsn = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
                self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'], $cfg['options']);
            } catch (PDOException $e) {
                // Auto-fallback untuk development lokal (XAMPP / Laragon default root tanpa password)
                $fallbacks = [
                    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '',     'dbname' => 'inspeksi_kebersihan_alwildan'],
                    ['host' => 'localhost', 'user' => 'root', 'pass' => '',     'dbname' => 'inspeksi_kebersihan_alwildan'],
                    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '',     'dbname' => $cfg['dbname']],
                    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => 'root', 'dbname' => 'inspeksi_kebersihan_alwildan'],
                ];
                $connected = false;
                foreach ($fallbacks as $fb) {
                    try {
                        $dsn = "mysql:host={$fb['host']};dbname={$fb['dbname']};charset=utf8mb4";
                        self::$instance = new PDO($dsn, $fb['user'], $fb['pass'], $cfg['options']);
                        $connected = true;
                        break;
                    } catch (PDOException) {
                        continue;
                    }
                }
                if (!$connected) {
                    throw $e;
                }
            }

            self::$instance->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            self::$instance->exec("SET collation_connection = utf8mb4_unicode_ci");
        }
        return self::$instance;
    }
}
