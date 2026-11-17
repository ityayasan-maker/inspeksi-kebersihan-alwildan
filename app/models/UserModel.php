<?php
class UserModel extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            "SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1",
            [$email]
        );
    }

    public function findActive(int $id): ?array
    {
        return $this->queryOne(
            "SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1",
            [$id]
        );
    }

    public function allActive(string $role = ''): array
    {
        $sql    = "SELECT id, name, email, role, phone, photo FROM users WHERE is_active = 1";
        $params = [];
        if ($role) { $sql .= " AND role = ?"; $params[] = $role; }
        return $this->query($sql . " ORDER BY name", $params);
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        return (bool) $this->queryScalar(
            "SELECT COUNT(*) FROM users WHERE email = ? AND id != ?",
            [$email, $excludeId]
        );
    }
}
