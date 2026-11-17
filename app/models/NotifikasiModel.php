<?php
class NotifikasiModel extends Model
{
    protected string $table = 'notifications';

    public function unreadCount(int $userId): int
    {
        return (int) $this->queryScalar(
            "SELECT COUNT(*) FROM notifications
             WHERE (user_id=? OR user_id IS NULL) AND is_read=0",
            [$userId]
        );
    }

    public function forUser(int $userId, int $limit = 30): array
    {
        $limit = max(1, (int)$limit);
        return $this->query(
            "SELECT n.*, i.tanggal AS inspeksi_tanggal,
                    r.nama AS ruangan_nama, g.nama AS gedung_nama
             FROM notifications n
             LEFT JOIN inspeksi  i ON i.id = n.inspeksi_id
             LEFT JOIN ruangan   r ON r.id = i.ruangan_id
             LEFT JOIN lantai    l ON l.id = r.lantai_id
             LEFT JOIN gedung    g ON g.id = l.gedung_id
             WHERE (n.user_id=? OR n.user_id IS NULL)
             ORDER BY n.created_at DESC LIMIT {$limit}",
            [$userId]
        );
    }

    public function markRead(int $id, int $userId): void
    {
        $this->execute(
            "UPDATE notifications SET is_read=1
             WHERE id=? AND (user_id=? OR user_id IS NULL)",
            [$id, $userId]
        );
    }

    public function markAllRead(int $userId): void
    {
        $this->execute(
            "UPDATE notifications SET is_read=1
             WHERE (user_id=? OR user_id IS NULL) AND is_read=0",
            [$userId]
        );
    }

    /** Buat alert otomatis ketika skor inspeksi di bawah threshold */
    public function createAlert(int $inspeksiId, float $skor): int
    {
        $status  = $skor <= 30 ? 'kotor' : 'kurang_bersih';
        $label   = $skor <= 30 ? 'Kotor' : 'Kurang Bersih';
        $type    = $skor <= 30 ? 'alert_kotor' : 'alert_threshold';

        return $this->insert([
            'inspeksi_id' => $inspeksiId,
            'user_id'     => null,           // broadcast ke semua admin/supervisor
            'type'        => $type,
            'title'       => "Area {$label} Terdeteksi",
            'message'     => "Inspeksi #{$inspeksiId} mendapat skor " . number_format($skor, 1)
                           . ". Segera lakukan tindak lanjut.",
            'is_read'     => 0,
            'sent_email'  => 0,
        ]);
    }

    /** Buat notifikasi reminder inspeksi rutin */
    public function createReminder(int $userId, string $ruanganNama): int
    {
        return $this->insert([
            'inspeksi_id' => null,
            'user_id'     => $userId,
            'type'        => 'reminder',
            'title'       => 'Pengingat Inspeksi',
            'message'     => "Ruangan \"{$ruanganNama}\" belum diinspeksi dalam 7 hari terakhir.",
            'is_read'     => 0,
            'sent_email'  => 0,
        ]);
    }

    /** Ambil notifikasi yang belum dikirim email */
    public function getPendingEmail(int $limit = 20): array
    {
        $limit = max(1, (int)$limit);
        return $this->query(
            "SELECT n.*, u.email AS user_email, u.name AS user_name
             FROM notifications n
             LEFT JOIN users u ON u.id = n.user_id
             WHERE n.sent_email=0
               AND n.type IN ('alert_kotor','alert_threshold')
             ORDER BY n.created_at ASC LIMIT {$limit}"
        );
    }

    public function markEmailSent(int $id): void
    {
        $this->execute("UPDATE notifications SET sent_email=1 WHERE id=?", [$id]);
    }

    public function deleteOld(int $days = 90): int
    {
        $this->execute(
            "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
             AND is_read=1",
            [$days]
        );
        return 0; // rowCount not exposed via base model, acceptable
    }
}
