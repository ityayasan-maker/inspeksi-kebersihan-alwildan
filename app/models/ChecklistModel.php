<?php
/**
 * ChecklistModel
 * Mengelola checklist per ruangan (override atau fallback ke tipe)
 */
class ChecklistModel extends Model
{
    protected string $table = 'ruangan_checklist';

    /**
     * Ambil checklist efektif untuk 1 ruangan.
     * Logika:
     *   1. Jika ruangan punya custom override di ruangan_checklist → pakai itu
     *   2. Jika tidak ada override → fallback ke standar_kebersihan.tipe_ruangan
     */
    public function getEffectiveChecklist(int $ruanganId): array
    {
        // Cek apakah ada override
        $hasOverride = (bool) $this->queryScalar(
            "SELECT COUNT(*) FROM ruangan_checklist WHERE ruangan_id = ? AND is_active = 1",
            [$ruanganId]
        );

        if ($hasOverride) {
            return $this->query(
                "SELECT sk.*, rc.id AS rc_id
                 FROM ruangan_checklist rc
                 JOIN standar_kebersihan sk ON sk.id = rc.standar_id
                 WHERE rc.ruangan_id = ? AND rc.is_active = 1
                 ORDER BY sk.tipe_ruangan, sk.no_urut",
                [$ruanganId]
            );
        }

        // Fallback: ambil berdasarkan tipe ruangan
        return $this->query(
            "SELECT sk.*, NULL AS rc_id
             FROM standar_kebersihan sk
             JOIN ruangan r ON r.id = ?
             WHERE sk.tipe_ruangan = r.tipe AND sk.is_active = 1
             ORDER BY sk.no_urut",
            [$ruanganId]
        );
    }

    /** Ambil override yang sudah di-assign ke ruangan */
    public function getByRuangan(int $ruanganId): array
    {
        return $this->query(
            "SELECT rc.id, rc.standar_id, rc.is_active,
                    sk.aspek, sk.deskripsi, sk.tipe_ruangan, sk.frekuensi, sk.no_urut
             FROM ruangan_checklist rc
             JOIN standar_kebersihan sk ON sk.id = rc.standar_id
             WHERE rc.ruangan_id = ?
             ORDER BY sk.tipe_ruangan, sk.no_urut",
            [$ruanganId]
        );
    }

    /** Standar yang BELUM di-assign ke ruangan ini (untuk modal tambah) */
    public function getAvailable(int $ruanganId, string $tipeRuangan = ''): array
    {
        $sql = "SELECT sk.*
                FROM standar_kebersihan sk
                WHERE sk.is_active = 1
                AND sk.id NOT IN (
                    SELECT standar_id FROM ruangan_checklist WHERE ruangan_id = ?
                )";
        $params = [$ruanganId];
        if ($tipeRuangan) {
            $sql    .= " AND sk.tipe_ruangan = ?";
            $params[] = $tipeRuangan;
        }
        $sql .= " ORDER BY sk.tipe_ruangan, sk.no_urut";
        return $this->query($sql, $params);
    }

    /** Bulk assign: hapus semua override lama, insert baru */
    public function bulkAssign(int $ruanganId, array $standarIds): void
    {
        $this->execute(
            "DELETE FROM ruangan_checklist WHERE ruangan_id = ?",
            [$ruanganId]
        );
        if (!$standarIds) return;
        $vals = implode(',', array_fill(0, count($standarIds), "(?,?,1)"));
        $params = [];
        foreach ($standarIds as $sid) {
            $params[] = $ruanganId;
            $params[] = (int)$sid;
        }
        $this->execute(
            "INSERT INTO ruangan_checklist (ruangan_id, standar_id, is_active) VALUES {$vals}",
            $params
        );
    }

    /** Toggle aktif/nonaktif 1 item override */
    public function toggle(int $id, int $ruanganId): bool
    {
        return $this->execute(
            "UPDATE ruangan_checklist SET is_active = 1 - is_active
             WHERE id = ? AND ruangan_id = ?",
            [$id, $ruanganId]
        );
    }

    /** Reset override → hapus semua, sistem fallback ke tipe */
    public function resetOverride(int $ruanganId): bool
    {
        return $this->execute(
            "DELETE FROM ruangan_checklist WHERE ruangan_id = ?",
            [$ruanganId]
        );
    }

    /** Apakah ruangan menggunakan custom override? */
    public function hasOverride(int $ruanganId): bool
    {
        return (bool) $this->queryScalar(
            "SELECT COUNT(*) FROM ruangan_checklist WHERE ruangan_id = ? AND is_active = 1",
            [$ruanganId]
        );
    }

    /** Copy checklist dari satu ruangan ke ruangan lain */
    public function copyFrom(int $sourceRuanganId, int $targetRuanganId): int
    {
        $source = $this->query(
            "SELECT standar_id FROM ruangan_checklist WHERE ruangan_id = ? AND is_active = 1",
            [$sourceRuanganId]
        );
        if (!$source) return 0;
        $ids = array_column($source, 'standar_id');
        $this->bulkAssign($targetRuanganId, $ids);
        return count($ids);
    }
}
