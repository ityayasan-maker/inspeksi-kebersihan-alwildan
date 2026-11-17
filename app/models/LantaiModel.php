<?php
class LantaiModel extends Model
{
    protected string $table = 'lantai';

    public function allWithGedung(): array
    {
        return $this->query(
            "SELECT l.*, g.nama AS gedung_nama, g.unit AS gedung_unit,
                    (SELECT COUNT(*) FROM ruangan r WHERE r.lantai_id = l.id AND r.is_active = 1) AS total_ruangan
             FROM lantai l
             JOIN gedung g ON g.id = l.gedung_id
             ORDER BY g.unit, g.nama, l.urutan"
        );
    }

    public function byGedung(int $gedungId): array
    {
        return $this->query(
            "SELECT * FROM lantai WHERE gedung_id = ? ORDER BY urutan",
            [$gedungId]
        );
    }
}
