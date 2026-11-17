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

        $totalInspeksi   = (int)$this->queryScalar("SELECT COUNT(*) FROM inspeksi WHERE is_draft=0") ?: 0;
        $inspeksiHariIni = (int)$this->queryScalar("SELECT COUNT(*) FROM inspeksi WHERE tanggal=? AND is_draft=0", [$today]) ?: 0;
        $inspeksiBulan   = (int)$this->queryScalar("SELECT COUNT(*) FROM inspeksi WHERE SUBSTRING(CAST(tanggal AS VARCHAR), 1, 7)=? AND is_draft=0", [$month]) ?: 0;
        $rataSkor        = (float)$this->queryScalar("SELECT ROUND(AVG(skor_total),1) FROM inspeksi WHERE is_draft=0") ?: 0.0;
        $perluTindak     = (int)$this->queryScalar("SELECT COUNT(*) FROM inspeksi WHERE status='kotor' AND is_draft=0 AND tanggal >= ?", [$date7Ago]) ?: 0;

        return [
            'total_inspeksi'     => $totalInspeksi,
            'inspeksi_hari_ini'  => $inspeksiHariIni,
            'inspeksi_bulan'     => $inspeksiBulan,
            'inspeksi_bulan_ini' => $inspeksiBulan,
            'rata_skor'          => $rataSkor,
            'skor_rata_bulan'    => $rataSkor,
            'perlu_tindak'       => $perluTindak,
            'total_bersih'       => (int)$this->queryScalar("SELECT COUNT(*) FROM inspeksi WHERE status='bersih' AND is_draft=0") ?: 0,
            'total_kurang'       => (int)$this->queryScalar("SELECT COUNT(*) FROM inspeksi WHERE status='kurang_bersih' AND is_draft=0") ?: 0,
            'total_kotor'        => (int)$this->queryScalar("SELECT COUNT(*) FROM inspeksi WHERE status='kotor' AND is_draft=0") ?: 0,
            'total_ruangan'      => (int)$this->queryScalar("SELECT COUNT(*) FROM ruangan WHERE is_active=1") ?: 0,
            'ruangan_terInspeksi'=> (int)$this->queryScalar("SELECT COUNT(DISTINCT ruangan_id) FROM inspeksi WHERE is_draft=0 AND tanggal >= ?", [$date30Ago]) ?: 0,
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
