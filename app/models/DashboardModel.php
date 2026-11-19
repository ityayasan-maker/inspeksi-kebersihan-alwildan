<?php
class DashboardModel extends Model
{
    protected string $table = 'inspeksi';

    /* ── Ringkasan kartu atas ────────────────────────────── */
    public function statsKartu(): array
    {
        $today     = date('Y-m-d');
        $month     = date('Y-m');
        $date7Ago  = date('Y-m-d', strtotime('-7 days'));
        $date30Ago = date('Y-m-d', strtotime('-30 days'));

        $sql = "SELECT
            COUNT(*) AS total_inspeksi,
            COUNT(CASE WHEN tanggal = ? THEN 1 END) AS inspeksi_hari_ini,
            COUNT(CASE WHEN SUBSTRING(CAST(tanggal AS VARCHAR), 1, 7) = ? THEN 1 END) AS inspeksi_bulan,
            ROUND(AVG(skor_total), 1) AS rata_skor,
            COUNT(CASE WHEN status = 'kotor' AND tanggal >= ? THEN 1 END) AS perlu_tindak,
            COUNT(CASE WHEN status = 'bersih' THEN 1 END) AS total_bersih,
            COUNT(CASE WHEN status = 'kurang_bersih' THEN 1 END) AS total_kurang,
            COUNT(CASE WHEN status = 'kotor' THEN 1 END) AS total_kotor,
            COUNT(DISTINCT CASE WHEN tanggal >= ? THEN ruangan_id END) AS ruangan_terinspeksi
        FROM inspeksi
        WHERE is_draft = 0";

        try {
            $row = $this->queryOne($sql, [$today, $month, $date7Ago, $date30Ago]) ?: [];
        } catch (Throwable) {
            $row = [];
        }

        $totalRuangan = (int)$this->queryScalar("SELECT COUNT(*) FROM ruangan WHERE is_active=1") ?: 0;

        return [
            'total_inspeksi'     => (int)($row['total_inspeksi'] ?? 0),
            'inspeksi_hari_ini'  => (int)($row['inspeksi_hari_ini'] ?? 0),
            'inspeksi_bulan'     => (int)($row['inspeksi_bulan'] ?? 0),
            'inspeksi_bulan_ini' => (int)($row['inspeksi_bulan'] ?? 0),
            'rata_skor'          => (float)($row['rata_skor'] ?? 0),
            'skor_rata_bulan'    => (float)($row['rata_skor'] ?? 0),
            'perlu_tindak'       => (int)($row['perlu_tindak'] ?? 0),
            'total_bersih'       => (int)($row['total_bersih'] ?? 0),
            'total_kurang'       => (int)($row['total_kurang'] ?? 0),
            'total_kotor'        => (int)($row['total_kotor'] ?? 0),
            'total_ruangan'      => $totalRuangan,
            'ruangan_terInspeksi'=> (int)($row['ruangan_terinspeksi'] ?? 0),
        ];
    }

    /* ── Tren 30 hari (per hari) ─────────────────────────── */
    public function trenHarian(int $hari = 30): array
    {
        $cutoff = date('Y-m-d', strtotime("-{$hari} days"));
        return $this->query(
            "SELECT
               tanggal,
               COUNT(*) AS total,
               ROUND(AVG(skor_total),1) AS avg_skor,
               SUM(CASE WHEN status='bersih' THEN 1 ELSE 0 END) AS bersih,
               SUM(CASE WHEN status='kurang_bersih' THEN 1 ELSE 0 END) AS kurang_bersih,
               SUM(CASE WHEN status='kotor' THEN 1 ELSE 0 END) AS kotor
             FROM inspeksi
             WHERE is_draft=0
               AND tanggal >= ?
             GROUP BY tanggal
             ORDER BY tanggal ASC",
            [$cutoff]
        );
    }

    /* ── Tren 12 bulan ───────────────────────────────────── */
    public function trenBulanan(): array
    {
        $cutoff = date('Y-m-d', strtotime('-12 months'));
        return $this->query(
            "SELECT
               SUBSTRING(CAST(tanggal AS VARCHAR), 1, 7) AS bulan,
               COUNT(*) AS total,
               ROUND(AVG(skor_total),1) AS avg_skor
             FROM inspeksi
             WHERE is_draft=0
               AND tanggal >= ?
             GROUP BY SUBSTRING(CAST(tanggal AS VARCHAR), 1, 7)
             ORDER BY bulan ASC",
            [$cutoff]
        );
    }

    /* ── Distribusi status (pie chart) ───────────────────── */
    public function distribusiStatus(): array
    {
        return $this->query(
            "SELECT status, COUNT(*) AS total
             FROM inspeksi WHERE is_draft=0 AND status IS NOT NULL
             GROUP BY status"
        );
    }

    /* ── Ranking petugas (30 hari terakhir) ──────────────── */
    public function rankingPetugas(int $limit = 10): array
    {
        $limit  = max(1, (int)$limit);
        $cutoff = date('Y-m-d', strtotime('-30 days'));
        return $this->query(
            "SELECT
               u.id, u.name, u.photo, u.role,
               COUNT(i.id) AS total_inspeksi,
               ROUND(AVG(i.skor_total),1) AS avg_skor,
               SUM(CASE WHEN i.status='bersih' THEN 1 ELSE 0 END) AS bersih,
               SUM(CASE WHEN i.status='kurang_bersih' THEN 1 ELSE 0 END) AS kurang_bersih,
               SUM(CASE WHEN i.status='kotor' THEN 1 ELSE 0 END) AS kotor
             FROM users u
             JOIN inspeksi i ON i.inspected_by_id = u.id
             WHERE i.is_draft=0
               AND i.tanggal >= ?
             GROUP BY u.id, u.name, u.photo, u.role
             ORDER BY avg_skor DESC, total_inspeksi DESC
             LIMIT {$limit}",
            [$cutoff]
        );
    }

    /* ── Area / ruangan bermasalah (skor rendah) ─────────── */
    public function areaBermasalah(int $limit = 10): array
    {
        $limit = max(1, (int)$limit);
        return $this->query(
            "SELECT
               r.id AS ruangan_id, r.nama AS ruangan_nama, r.kode,
               g.nama AS gedung_nama, g.unit,
               i.id AS inspeksi_id, i.tanggal, i.skor_total, i.status,
               u.name AS petugas_nama
             FROM inspeksi i
             JOIN ruangan r ON r.id = i.ruangan_id
             JOIN lantai l ON l.id = r.lantai_id
             JOIN gedung g ON g.id = l.gedung_id
             LEFT JOIN users u ON u.id = i.assigned_petugas_id
             WHERE i.is_draft=0
               AND i.status IN ('kotor','kurang_bersih')
               AND i.id IN (
                 SELECT MAX(i2.id) FROM inspeksi i2
                 WHERE i2.is_draft=0
                 GROUP BY i2.ruangan_id
               )
             ORDER BY i.skor_total ASC
             LIMIT {$limit}"
        );
    }

    /* ── Distribusi per unit ─────────────────────────────── */
    public function distribusiUnit(): array
    {
        $cutoff = date('Y-m-d', strtotime('-30 days'));
        return $this->query(
            "SELECT
               g.unit,
               COUNT(i.id) AS total,
               ROUND(AVG(i.skor_total),1) AS avg_skor
             FROM inspeksi i
             JOIN ruangan r ON r.id = i.ruangan_id
             JOIN lantai l ON l.id = r.lantai_id
             JOIN gedung g ON g.id = l.gedung_id
             WHERE i.is_draft=0
               AND i.tanggal >= ?
             GROUP BY g.unit
             ORDER BY avg_skor DESC",
            [$cutoff]
        );
    }

    /* ── Aktivitas terbaru ───────────────────────────────── */
    public function aktivitasTerbaru(int $limit = 8): array
    {
        $limit = max(1, (int)$limit);
        return $this->query(
            "SELECT
               i.id, i.tanggal, i.skor_total, i.status, i.created_at,
               r.nama AS ruangan_nama, g.nama AS gedung_nama,
               u.name AS inspected_nama, u.photo AS inspected_photo
             FROM inspeksi i
             JOIN ruangan r ON r.id = i.ruangan_id
             JOIN lantai l ON l.id = r.lantai_id
             JOIN gedung g ON g.id = l.gedung_id
             JOIN users u ON u.id = i.inspected_by_id
             WHERE i.is_draft=0
             ORDER BY i.created_at DESC
             LIMIT {$limit}"
        );
    }

    /* ── Skor heatmap per ruangan (7 hari) ───────────────── */
    public function heatmapRuangan(): array
    {
        $cutoff = date('Y-m-d', strtotime('-7 days'));
        return $this->query(
            "SELECT
               r.id, r.nama, r.kode, g.unit,
               ROUND(AVG(i.skor_total),1) AS avg_skor,
               COUNT(i.id) AS total,
               MAX(i.tanggal) AS last_tanggal,
               MAX(i.status) AS last_status
             FROM ruangan r
             LEFT JOIN inspeksi i ON i.ruangan_id=r.id AND i.is_draft=0 AND i.tanggal >= ?
             JOIN lantai l ON l.id=r.lantai_id
             JOIN gedung g ON g.id=l.gedung_id
             WHERE r.is_active=1
             GROUP BY r.id, r.nama, r.kode, g.unit
             ORDER BY avg_skor ASC
             LIMIT 20",
            [$cutoff]
        );
    }
}
