<?php
/**
 * LaporanModel — query untuk halaman laporan & export
 */
class LaporanModel extends Model
{
    protected string $table = 'inspeksi';

    /**
     * Query utama laporan dengan semua filter
     */
    public function getFiltered(array $f = []): array
    {
        $where  = ['i.is_draft = 0'];
        $params = [];

        if (!empty($f['tanggal_dari']))   { $where[] = 'i.tanggal >= ?';        $params[] = $f['tanggal_dari']; }
        if (!empty($f['tanggal_sampai'])) { $where[] = 'i.tanggal <= ?';        $params[] = $f['tanggal_sampai']; }
        if (!empty($f['status']))         { $where[] = 'i.status = ?';           $params[] = $f['status']; }
        if (!empty($f['gedung_id']))       { $where[] = 'g.id = ?';              $params[] = $f['gedung_id']; }
        if (!empty($f['ruangan_id']))      { $where[] = 'i.ruangan_id = ?';      $params[] = $f['ruangan_id']; }
        if (!empty($f['petugas_id']))      { $where[] = 'i.inspected_by_id = ?'; $params[] = $f['petugas_id']; }
        if (!empty($f['unit']))            { $where[] = 'g.unit = ?';            $params[] = $f['unit']; }
        if (!empty($f['skor_min']))        { $where[] = 'i.skor_total >= ?';     $params[] = $f['skor_min']; }
        if (!empty($f['skor_max']))        { $where[] = 'i.skor_total <= ?';     $params[] = $f['skor_max']; }

        $sql = "SELECT
                  i.id, i.tanggal, i.skor_total, i.status, i.catatan_umum,
                  i.waktu_mulai, i.created_at,
                  r.nama  AS ruangan_nama, r.kode  AS ruangan_kode, r.tipe AS ruangan_tipe,
                  l.nama  AS lantai_nama,
                  g.id    AS gedung_id,
                  g.nama  AS gedung_nama, g.unit  AS gedung_unit,
                  ua.name AS assigned_nama,
                  ui.name AS inspected_nama
                FROM inspeksi i
                JOIN ruangan r  ON r.id  = i.ruangan_id
                JOIN lantai  l  ON l.id  = r.lantai_id
                JOIN gedung  g  ON g.id  = l.gedung_id
                LEFT JOIN users ua ON ua.id = i.assigned_user_id
                LEFT JOIN users ui ON ui.id = i.inspected_by_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY i.tanggal DESC, i.id DESC";

        try {
            return $this->query($sql, $params);
        } catch (Throwable) {
            $fallbackSql = str_replace(
                ['i.catatan_umum', 'i.assigned_user_id'],
                ['COALESCE(i.catatan, \'\') AS catatan_umum', 'i.id'],
                $sql
            );
            return $this->query($fallbackSql, $params);
        }
    }

    /**
     * Ringkasan statistik dari hasil filter
     */
    public function getRingkasan(array $rows): array
    {
        if (!$rows) return ['total'=>0,'avg_skor'=>0,'bersih'=>0,'kurang_bersih'=>0,'kotor'=>0];
        $total  = count($rows);
        $skor   = array_filter(array_column($rows, 'skor_total'), fn($v) => $v !== null);
        $avg    = $skor ? round(array_sum($skor)/count($skor), 1) : 0;
        return [
            'total'         => $total,
            'avg_skor'      => $avg,
            'bersih'        => count(array_filter($rows, fn($r) => $r['status']==='bersih')),
            'kurang_bersih' => count(array_filter($rows, fn($r) => $r['status']==='kurang_bersih')),
            'kotor'         => count(array_filter($rows, fn($r) => $r['status']==='kotor')),
        ];
    }

    /**
     * Detail checklist untuk 1 inspeksi (dipakai di print)
     */
    public function getDetailInspeksi(int $id): array
    {
        return $this->query(
            "SELECT d.kondisi, d.skor, d.catatan, d.foto,
                    sk.aspek, sk.deskripsi, sk.tipe_ruangan, sk.no_urut
             FROM inspeksi_detail d
             JOIN standar_kebersihan sk ON sk.id = d.standar_id
             WHERE d.inspeksi_id = ?
             ORDER BY sk.tipe_ruangan, sk.no_urut",
            [$id]
        );
    }
}
