<?php
class SettingsModel extends Model
{
    protected string $table = 'settings';

    private static ?array $cache = null;

    public function getAllAsArray(): array
    {
        if (self::$cache !== null) return self::$cache;
        $rows = $this->query("SELECT key_name, value FROM settings");
        self::$cache = array_column($rows, 'value', 'key_name');
        return self::$cache;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->getAllAsArray();
        return $all[$key] ?? $default;
    }

    public function set(string $key, string $value): void
    {
        self::$cache = null; // Invalidate cache
        $exists = $this->queryScalar(
            "SELECT COUNT(*) FROM settings WHERE key_name=?", [$key]
        );
        if ($exists) {
            $this->execute("UPDATE settings SET value=? WHERE key_name=?", [$value, $key]);
        } else {
            $this->insert(['key_name'=>$key, 'value'=>$value]);
        }
    }

    public function setMultiple(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function getByGroup(string $group): array
    {
        return $this->query(
            "SELECT * FROM settings WHERE group_name=? ORDER BY key_name", [$group]
        );
    }
}
