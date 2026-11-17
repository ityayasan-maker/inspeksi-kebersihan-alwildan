<?php
class InspeksiDetailModel extends Model
{
    protected string $table = 'inspeksi_detail';

    public function byInspeksi(int $inspeksiId): array
    {
        return $this->query(
            "SELECT d.*, sk.aspek, sk.deskripsi, sk.tipe_ruangan, sk.no_urut, sk.frekuensi
             FROM inspeksi_detail d
             JOIN standar_kebersihan sk ON sk.id = d.standar_id
             WHERE d.inspeksi_id = ?
             ORDER BY sk.tipe_ruangan, sk.no_urut",
            [$inspeksiId]
        );
    }

    public function upsert(int $inspeksiId, int $standarId, array $data): void
    {
        $existing = $this->queryOne(
            "SELECT id FROM inspeksi_detail WHERE inspeksi_id=? AND standar_id=?",
            [$inspeksiId, $standarId]
        );
        if ($existing) {
            $this->update($existing['id'], $data);
        } else {
            $this->insert(array_merge(['inspeksi_id' => $inspeksiId, 'standar_id' => $standarId], $data));
        }
    }

    public function skorFromKondisi(string $kondisi): int
    {
        return match($kondisi) {
            'bersih'        => 100,
            'kurang_bersih' => 70,
            default         => 30,
        };
    }

    public function deleteByInspeksi(int $inspeksiId): void
    {
        $this->execute("DELETE FROM inspeksi_detail WHERE inspeksi_id=?", [$inspeksiId]);
    }
}
