<?php
class ActivityLogModel extends Model
{
    protected string $table = 'activity_logs';

    public function recent(int $limit = 50, ?int $userId = null): array
    {
        $limit  = max(1, (int)$limit);
        $where  = $userId ? 'WHERE al.user_id = ?' : 'WHERE 1=1';
        $params = $userId ? [$userId] : [];
        return $this->query(
            "SELECT al.*, u.name AS user_name, u.role AS user_role
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             {$where}
             ORDER BY al.created_at DESC LIMIT {$limit}",
            $params
        );
    }

    public function byModule(string $module, int $limit = 50): array
    {
        $limit = max(1, (int)$limit);
        return $this->query(
            "SELECT al.*, u.name AS user_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.module = ?
             ORDER BY al.created_at DESC LIMIT {$limit}",
            [$module]
        );
    }

    public function forTarget(string $module, int $targetId): array
    {
        return $this->query(
            "SELECT al.*, u.name AS user_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.module = ? AND al.target_id = ?
             ORDER BY al.created_at DESC",
            [$module, $targetId]
        );
    }

    public function deleteOld(int $days = 180): void
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $this->execute(
            "DELETE FROM activity_logs WHERE created_at < ?",
            [$cutoff]
        );
    }

    public function statsByModule(): array
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-30 days"));
        return $this->query(
            "SELECT module, action, COUNT(*) AS total
             FROM activity_logs
             WHERE created_at >= ?
             GROUP BY module, action
             ORDER BY total DESC",
            [$cutoff]
        );
    }
}
