<?php
class Database
{
    private static ?PDO $instance = null;
    private static bool $schemaChecked = false;

    private function __construct() {}
    private function __clone() {}

    private static function ensureSchema(PDO $pdo, string $driver): void
    {
        if (self::$schemaChecked) return;
        self::$schemaChecked = true;

        if ($driver === 'pgsql') {
            try {
                $pdo->exec("
                    ALTER TABLE inspeksi ALTER COLUMN nomor_inspeksi DROP NOT NULL;
                    ALTER TABLE inspeksi ALTER COLUMN status DROP NOT NULL;
                    ALTER TABLE inspeksi ALTER COLUMN skor_total DROP NOT NULL;
                    ALTER TABLE inspeksi ADD COLUMN IF NOT EXISTS assigned_user_id INT;
                    ALTER TABLE inspeksi ADD COLUMN IF NOT EXISTS catatan_umum TEXT;
                    ALTER TABLE inspeksi ADD COLUMN IF NOT EXISTS foto_ruangan VARCHAR(255);
                    ALTER TABLE notifications ADD COLUMN IF NOT EXISTS inspeksi_id INT;

                    INSERT INTO users (name, email, password, role, phone, is_active)
                    VALUES ('Administrator Al Wildan', 'admin@alwildan.sch.id', '$2y$12$/L2K2s3VEHvd527c19iIYuLGURVehTqdjMYrrcUp41j/lYj9nyMBe', 'admin', '081200000001', 1)
                    ON CONFLICT (email) DO UPDATE SET role = 'admin', is_active = 1, password = EXCLUDED.password;
                ");
            } catch (Throwable) {}
        }
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require ROOT_PATH . '/config/database.php';

            // 1. Cek jika ada DATABASE_URL (seperti dari Supabase connection string / Vercel)
            $dbUrl = getenv('DATABASE_URL') ?: (getenv('POSTGRES_URL') ?: '');
            if ($dbUrl) {
                $p = parse_url($dbUrl);
                $driver = (isset($p['scheme']) && ($p['scheme'] === 'postgres' || $p['scheme'] === 'postgresql')) ? 'pgsql' : 'mysql';
                $host = $p['host'] ?? 'localhost';
                $port = $p['port'] ?? ($driver === 'pgsql' ? 5432 : 3306);
                $user = $p['user'] ?? '';
                $pass = $p['pass'] ?? '';
                $dbname = ltrim($p['path'] ?? '', '/');
                $dsn = ($driver === 'pgsql')
                    ? "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require"
                    : "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

                self::$instance = new PDO($dsn, $user, $pass, $cfg['options']);
                self::ensureSchema(self::$instance, $driver);
                return self::$instance;
            }

            // 2. Cek koneksi PostgreSQL / Supabase melalui ENV terpisah
            $driver = $cfg['driver'] ?? 'mysql';
            if ($driver === 'pgsql' || str_contains($cfg['host'] ?? '', 'supabase')) {
                $port = $cfg['port'] ?? 5432;
                $dsn = "pgsql:host={$cfg['host']};port={$port};dbname={$cfg['dbname']};sslmode=require";
                self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'], $cfg['options']);
                self::ensureSchema(self::$instance, $driver);
                return self::$instance;
            }

            // 3. Fallback MySQL standar (XAMPP / Laragon / cPanel)
            try {
                $dsn = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
                self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'], $cfg['options']);
                self::$instance->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                self::$instance->exec("SET collation_connection = utf8mb4_unicode_ci");
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
                        self::$instance->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                        self::$instance->exec("SET collation_connection = utf8mb4_unicode_ci");
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
        }
        return self::$instance;
    }
}
