<?php
class StandarModel extends Model
{
    protected string $table = 'standar_kebersihan';

    public function allGrouped(): array
    {
        $rows   = $this->query(
            "SELECT * FROM standar_kebersihan WHERE is_active = 1 ORDER BY tipe_ruangan, no_urut"
        );
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['tipe_ruangan']][] = $r;
        }
        return $grouped;
    }

    public function byTipe(string $tipe): array
    {
        return $this->query(
            "SELECT * FROM standar_kebersihan WHERE tipe_ruangan = ? AND is_active = 1 ORDER BY no_urut",
            [$tipe]
        );
    }

    public function nextUrut(string $tipe): int
    {
        return (int)($this->queryScalar(
            "SELECT MAX(no_urut) FROM standar_kebersihan WHERE tipe_ruangan = ?", [$tipe]
        ) ?? 0) + 1;
    }

    public static function tipeList(): array
    {
        return [
            'kelas'        => 'Kelas',
            'toilet'       => 'Toilet',
            'lab_ipa'      => 'Lab IPA',
            'lab_komputer' => 'Lab Komputer',
            'selasar'      => 'Selasar / Koridor',
            'lapangan'     => 'Lapangan / Halaman',
            'gudang'       => 'Gudang',
            'kantor'       => 'Kantor / Ruang Kerja',
            'ruang_rapat'  => 'Ruang Rapat',
            'ruang_ibadah' => 'Ruang Ibadah (Masjid)',
            'tempat_wudhu' => 'Tempat Wudhu',
            'area_luar'    => 'Area Luar',
            'lainnya'      => 'Lainnya',
        ];
    }

    public static function frekuensiList(): array
    {
        return [
            'setiap_sesi'  => 'Setiap Sesi',
            'harian'       => 'Harian',
            'mingguan'     => 'Mingguan',
            'bulanan'      => 'Bulanan',
            'semesteran'   => 'Semesteran',
            'setiap_pakai' => 'Setiap Pakai',
        ];
    }
}
