<?php
class GedungModel extends Model
{
    protected string $table = 'gedung';

    public function allWithCount(): array
    {
        return $this->query(
            "SELECT g.*, COUNT(DISTINCT r.id) AS total_ruangan
             FROM gedung g
             LEFT JOIN lantai l ON l.gedung_id = g.id
             LEFT JOIN ruangan r ON r.lantai_id = l.id
             WHERE g.is_active = 1
             GROUP BY g.id
             ORDER BY g.unit, g.nama"
        );
    }

    public function kodeExists(string $kode, int $excludeId = 0): bool
    {
        return (bool) $this->queryScalar(
            "SELECT COUNT(*) FROM gedung WHERE kode = ? AND id != ?",
            [$kode, $excludeId]
        );
    }
}
