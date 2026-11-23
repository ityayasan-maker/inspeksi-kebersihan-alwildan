<?php
/**
 * JadwalModel
 * Model untuk Manajemen Jadwal Foto Kebersihan Staf Al-Wildan 8
 * Sesi Pagi (Sebelum Jam 09:00) & Sesi Siang (Sebelum Jam 14:00)
 */
class JadwalModel extends Model
{
    protected string $table = 'jadwal_kebersihan';

    /**
     * Ambil seluruh jadwal dengan relasi Petugas, Ruangan, Lantai, & Gedung
     */
    public function allWithRelasi(array $filters = []): array
    {
        $sql = "
            SELECT j.*,
                   u.name AS petugas_nama, u.email AS petugas_email, u.phone AS petugas_phone,
                   r.nama AS ruangan_nama, r.kode AS ruangan_kode, r.tipe AS ruangan_tipe,
                   l.nama AS lantai_nama,
                   g.nama AS gedung_nama, g.unit AS gedung_unit
            FROM jadwal_kebersihan j
            JOIN users u ON u.id = j.petugas_id
            JOIN ruangan r ON r.id = j.ruangan_id
            JOIN lantai l ON l.id = r.lantai_id
            JOIN gedung g ON g.id = l.gedung_id
            WHERE j.is_active = 1
        ";
        $params = [];

        if (!empty($filters['petugas_id'])) {
            $sql .= " AND j.petugas_id = ?";
            $params[] = (int)$filters['petugas_id'];
        }

        if (!empty($filters['sesi'])) {
            $sql .= " AND j.sesi = ?";
            $params[] = $filters['sesi'];
        }

        if (!empty($filters['gedung_id'])) {
            $sql .= " AND g.id = ?";
            $params[] = (int)$filters['gedung_id'];
        }

        $sql .= " ORDER BY j.sesi ASC, g.nama ASC, l.urutan ASC, r.nama ASC";

        return $this->query($sql, $params);
    }

    /**
     * Ambil status kepatuhan jadwal foto kebersihan hari ini / tanggal tertentu
     */
    public function todayStatus(string $date = '', int $petugasId = 0, string $sesi = ''): array
    {
        $date = $date ?: date('Y-m-d');
        $currentTime = date('H:i:s');

        $sql = "
            SELECT j.*,
                   u.name AS petugas_nama, u.email AS petugas_email, u.phone AS petugas_phone,
                   r.nama AS ruangan_nama, r.kode AS ruangan_kode, r.tipe AS ruangan_tipe,
                   l.nama AS lantai_nama,
                   g.nama AS gedung_nama, g.unit AS gedung_unit,
                   i.id AS inspeksi_id, i.nomor_inspeksi, i.foto_ruangan, i.skor_total, i.status AS status_kebersihan,
                   COALESCE(i.waktu_mulai::text, TO_CHAR(i.created_at, 'HH24:MI:SS')) AS waktu_inspeksi,
                   i.created_at AS inspeksi_created_at
            FROM jadwal_kebersihan j
            JOIN users u ON u.id = j.petugas_id
            JOIN ruangan r ON r.id = j.ruangan_id
            JOIN lantai l ON l.id = r.lantai_id
            JOIN gedung g ON g.id = l.gedung_id
            LEFT JOIN inspeksi i ON i.ruangan_id = j.ruangan_id
                                AND i.tanggal = ?
                                AND (i.sesi = j.sesi OR (i.sesi IS NULL AND j.sesi = 'pagi'))
            WHERE j.is_active = 1
        ";
        $params = [$date];

        if ($petugasId > 0) {
            $sql .= " AND j.petugas_id = ?";
            $params[] = $petugasId;
        }

        if ($sesi !== '') {
            $sql .= " AND j.sesi = ?";
            $params[] = $sesi;
        }

        $sql .= " ORDER BY j.sesi ASC, g.nama ASC, l.urutan ASC, r.nama ASC";

        $rows = $this->query($sql, $params);

        // Hitung status kepatuhan waktu (Tepat Waktu vs Terlambat vs Menunggu vs Belum Foto)
        foreach ($rows as &$row) {
            $jamBatas = $row['jam_batas'] ?: ($row['sesi'] === 'pagi' ? '09:00:00' : '14:00:00');

            if (!empty($row['inspeksi_id'])) {
                $waktuFoto = $row['waktu_inspeksi'] ?: '00:00:00';
                if ($waktuFoto <= $jamBatas) {
                    $row['kepatuhan'] = 'tepat_waktu';
                    $row['kepatuhan_label'] = 'Tepat Waktu (Foto Sebelum ' . substr($jamBatas, 0, 5) . ')';
                    $row['kepatuhan_badge'] = 'success';
                } else {
                    $row['kepatuhan'] = 'terlambat';
                    $row['kepatuhan_label'] = 'Selesai Terlambat (' . substr($waktuFoto, 0, 5) . ')';
                    $row['kepatuhan_badge'] = 'warning';
                }
            } else {
                if ($date < date('Y-m-d') || ($date === date('Y-m-d') && $currentTime > $jamBatas)) {
                    $row['kepatuhan'] = 'belum_foto';
                    $row['kepatuhan_label'] = 'Belum Foto (Lewat Jam ' . substr($jamBatas, 0, 5) . ')';
                    $row['kepatuhan_badge'] = 'danger';
                } else {
                    $row['kepatuhan'] = 'menunggu';
                    $row['kepatuhan_label'] = 'Menunggu Foto (Batas ' . substr($jamBatas, 0, 5) . ')';
                    $row['kepatuhan_badge'] = 'primary';
                }
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * Rekapitulasi progres foto kebersihan hari ini
     */
    public function summaryHariIni(string $date = ''): array
    {
        $rows = $this->todayStatus($date);

        $summary = [
            'total'        => count($rows),
            'done'         => 0,
            'pagi_total'   => 0,
            'pagi_done'    => 0,
            'pagi_tepat'   => 0,
            'pagi_telat'   => 0,
            'siang_total'  => 0,
            'siang_done'   => 0,
            'siang_tepat'  => 0,
            'siang_telat'  => 0,
        ];

        foreach ($rows as $r) {
            $isPagi = ($r['sesi'] === 'pagi');
            if ($isPagi) {
                $summary['pagi_total']++;
            } else {
                $summary['siang_total']++;
            }

            if (!empty($r['inspeksi_id'])) {
                $summary['done']++;
                if ($isPagi) {
                    $summary['pagi_done']++;
                    if ($r['kepatuhan'] === 'tepat_waktu') $summary['pagi_tepat']++;
                    else $summary['pagi_telat']++;
                } else {
                    $summary['siang_done']++;
                    if ($r['kepatuhan'] === 'tepat_waktu') $summary['siang_tepat']++;
                    else $summary['siang_telat']++;
                }
            }
        }

        $summary['pagi_persen']  = $summary['pagi_total'] > 0 ? round(($summary['pagi_done'] / $summary['pagi_total']) * 100) : 0;
        $summary['siang_persen'] = $summary['siang_total'] > 0 ? round(($summary['siang_done'] / $summary['siang_total']) * 100) : 0;
        $summary['total_persen'] = $summary['total'] > 0 ? round(($summary['done'] / $summary['total']) * 100) : 0;

        return $summary;
    }
}
