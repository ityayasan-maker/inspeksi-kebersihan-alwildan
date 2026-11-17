<?php
class InspeksiModel extends Model
{
    protected string $table = 'inspeksi';

    public function allWithRelasi(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['ruangan_id'])) {
            $where[] = 'i.ruangan_id = ?'; $params[] = $filters['ruangan_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'i.status = ?'; $params[] = $filters['status'];
        }
        if (!empty($filters['inspected_by_id'])) {
            $where[] = 'i.inspected_by_id = ?'; $params[] = $filters['inspected_by_id'];
        }
        if (!empty($filters['tanggal_dari'])) {
            $where[] = 'i.tanggal >= ?'; $params[] = $filters['tanggal_dari'];
        }
        if (!empty($filters['tanggal_sampai'])) {
            $where[] = 'i.tanggal <= ?'; $params[] = $filters['tanggal_sampai'];
        }
        // Petugas hanya bisa lihat yang terkait dirinya
        if (!empty($filters['user_id_filter'])) {
            $where[] = '(i.assigned_user_id = ? OR i.inspected_by_id = ?)';
            $params[] = $filters['user_id_filter'];
            $params[] = $filters['user_id_filter'];
        }

        $sql = "SELECT i.*,
                       r.nama AS ruangan_nama, r.kode AS ruangan_kode, r.tipe AS ruangan_tipe,
                       l.nama AS lantai_nama,
                       g.nama AS gedung_nama, g.unit AS gedung_unit,
                       ua.name AS assigned_nama,
                       ui.name AS inspected_nama
                FROM inspeksi i
                JOIN ruangan r ON r.id = i.ruangan_id
                JOIN lantai  l ON l.id = r.lantai_id
                JOIN gedung  g ON g.id = l.gedung_id
                LEFT JOIN users ua ON ua.id = i.assigned_user_id
                LEFT JOIN users ui ON ui.id = i.inspected_by_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY i.tanggal DESC, i.created_at DESC";

        try {
            return $this->paginate($sql, $params, $page, $perPage);
        } catch (Throwable) {
            $fallbackSql = str_replace('i.assigned_user_id', 'i.id', $sql);
            return $this->paginate($fallbackSql, $params, $page, $perPage);
        }
    }

    public function findDetail(int $id): ?array
    {
        $sql = "SELECT i.*,
                    r.nama AS ruangan_nama, r.kode AS ruangan_kode, r.tipe AS ruangan_tipe,
                    l.nama AS lantai_nama,
                    g.nama AS gedung_nama, g.unit AS gedung_unit,
                    ua.name AS assigned_nama, ua.phone AS assigned_phone,
                    ui.name AS inspected_nama
             FROM inspeksi i
             JOIN ruangan r ON r.id = i.ruangan_id
             JOIN lantai  l ON l.id = r.lantai_id
             JOIN gedung  g ON g.id = l.gedung_id
             LEFT JOIN users ua ON ua.id = i.assigned_user_id
             LEFT JOIN users ui ON ui.id = i.inspected_by_id
             WHERE i.id = ? LIMIT 1";

        try {
            return $this->queryOne($sql, [$id]);
        } catch (Throwable) {
            $fallbackSql = str_replace('i.assigned_user_id', 'i.id', $sql);
            return $this->queryOne($fallbackSql, [$id]);
        }
    }

    /** Hitung & update skor_total + status dari detail yang sudah tersimpan */
    public function recalcScore(int $inspeksiId): void
    {
        $avg = $this->queryScalar(
            "SELECT AVG(skor) FROM inspeksi_detail WHERE inspeksi_id = ?",
            [$inspeksiId]
        );
        $avg    = round((float)$avg, 2);
        $status = $avg >= 85 ? 'bersih' : ($avg >= 55 ? 'kurang_bersih' : 'kotor');
        $this->update($inspeksiId, ['skor_total' => $avg, 'status' => $status]);
    }

    public function recentByRuangan(int $ruanganId, int $limit = 5): array
    {
        $limit = max(1, (int)$limit);
        return $this->query(
            "SELECT i.*, ui.name AS inspected_nama
             FROM inspeksi i
             LEFT JOIN users ui ON ui.id = i.inspected_by_id
             WHERE i.ruangan_id = ? AND i.is_draft = 0
             ORDER BY i.tanggal DESC LIMIT {$limit}",
            [$ruanganId]
        );
    }

    public function statsRingkasan(): array
    {
        return $this->queryOne(
            "SELECT
               COUNT(*) AS total,
               SUM(status='bersih') AS bersih,
               SUM(status='kurang_bersih') AS kurang_bersih,
               SUM(status='kotor') AS kotor,
               ROUND(AVG(skor_total),1) AS rata_skor
             FROM inspeksi WHERE is_draft=0"
        );
    }
}
