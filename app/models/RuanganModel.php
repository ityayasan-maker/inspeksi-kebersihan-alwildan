<?php
class RuanganModel extends Model
{
    protected string $table = 'ruangan';

    public function allWithRelasi(): array
    {
        return $this->query(
            "SELECT r.*, l.nama AS lantai_nama, g.id AS gedung_id, g.nama AS gedung_nama,
                    g.unit AS gedung_unit, u.name AS petugas_nama
             FROM ruangan r
             JOIN lantai l ON l.id = r.lantai_id
             JOIN gedung g ON g.id = l.gedung_id
             LEFT JOIN users u ON u.id = r.assigned_user_id
             WHERE r.is_active = 1
             ORDER BY g.unit, g.nama, l.urutan, r.nama"
        );
    }

    public function findWithRelasi(int $id): ?array
    {
        return $this->queryOne(
            "SELECT r.*, l.nama AS lantai_nama, g.id AS gedung_id,
                    g.nama AS gedung_nama, g.unit AS gedung_unit
             FROM ruangan r
             JOIN lantai l ON l.id = r.lantai_id
             JOIN gedung g ON g.id = l.gedung_id
             WHERE r.id = ? LIMIT 1",
            [$id]
        );
    }

    public function kodeExists(string $kode, int $excludeId = 0): bool
    {
        return (bool) $this->queryScalar(
            "SELECT COUNT(*) FROM ruangan WHERE kode = ? AND id != ?",
            [$kode, $excludeId]
        );
    }

    public function byTipe(string $tipe): array
    {
        return $this->query(
            "SELECT r.id, r.kode, r.nama, r.tipe, g.nama AS gedung_nama
             FROM ruangan r
             JOIN lantai l ON l.id = r.lantai_id
             JOIN gedung g ON g.id = l.gedung_id
             WHERE r.tipe = ? AND r.is_active = 1
             ORDER BY r.nama",
            [$tipe]
        );
    }
}
