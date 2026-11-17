<?php
class GedungModel extends Model
{
    protected string $table = 'gedung';

    public function allWithCount(): array
    {
        return $this->query(
            "SELECT g.*,
                    (SELECT COUNT(r.id) FROM ruangan r JOIN lantai l ON l.id = r.lantai_id WHERE l.gedung_id = g.id) AS total_ruangan
             FROM gedung g
             WHERE g.is_active = 1
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
