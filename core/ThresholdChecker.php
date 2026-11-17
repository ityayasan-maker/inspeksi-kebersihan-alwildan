<?php
/**
 * ThresholdChecker
 * Dipanggil setelah inspeksi disimpan untuk:
 * 1. Cek apakah skor di bawah threshold → buat notifikasi
 * 2. Kirim email ke admin/supervisor
 * 3. Cek ruangan yang belum diinspeksi → buat reminder
 */
class ThresholdChecker
{
    private NotifikasiModel $notifModel;
    private SettingsModel   $settings;
    private Mailer          $mailer;

    public function __construct()
    {
        $this->notifModel = new NotifikasiModel();
        $this->settings   = new SettingsModel();
        $this->mailer     = new Mailer();
    }

    /* ── Cek skor setelah inspeksi disimpan ─────────────── */
    public function checkAfterInspeksi(int $inspeksiId): void
    {
        $inspeksi = (new InspeksiModel())->findDetail($inspeksiId);
        if (!$inspeksi || $inspeksi['is_draft']) return;

        $skor      = (float)($inspeksi['skor_total'] ?? 100);
        $threshold = (float)$this->settings->get('threshold_alert', 50);

        if ($skor <= $threshold) {
            $notifId = $this->notifModel->createAlert($inspeksiId, $skor);
            $this->sendAlertEmail($inspeksi, $skor, $notifId);
        }
    }

    /* ── Kirim email alert ke semua admin & supervisor ───── */
    private function sendAlertEmail(array $inspeksi, float $skor, int $notifId): void
    {
        try {
            $userModel = new UserModel();
            $targets   = $userModel->query(
                "SELECT id, name, email FROM users
                 WHERE role IN ('admin','supervisor') AND is_active=1 AND email != ''"
            );

            $statusLabel = Helper::statusLabel($inspeksi['status'] ?? 'kotor');
            $badgeClass  = match($inspeksi['status']??'kotor'){
                'bersih'=>'badge-bersih','kurang_bersih'=>'badge-kurang',default=>'badge-kotor'
            };

            $body = '
              <h2>⚠️ Area Bermasalah Terdeteksi</h2>
              <p>Hasil inspeksi menunjukkan kondisi yang memerlukan tindak lanjut segera.</p>
              <div class="info-box">
                <div class="info-row">
                  <span class="info-label">Ruangan</span>
                  <span class="info-val">' . htmlspecialchars($inspeksi['ruangan_nama']) . '</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Gedung</span>
                  <span class="info-val">' . htmlspecialchars($inspeksi['gedung_nama']) . '</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Tanggal Inspeksi</span>
                  <span class="info-val">' . Helper::dateId($inspeksi['tanggal']) . '</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Diinspeksi oleh</span>
                  <span class="info-val">' . htmlspecialchars($inspeksi['inspected_nama'] ?? '—') . '</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Petugas PJ</span>
                  <span class="info-val">' . htmlspecialchars($inspeksi['assigned_nama'] ?? '—') . '</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Skor</span>
                  <span class="info-val" style="font-size:20px">' . number_format($skor, 1) . '</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Status</span>
                  <span class="info-val"><span class="badge ' . $badgeClass . '">' . $statusLabel . '</span></span>
                </div>
              </div>
              <a href="' . Helper::baseUrl("inspeksi/detail/{$inspeksi['id']}") . '" class="btn">
                Lihat Detail Inspeksi
              </a>
              <p style="font-size:12px;color:#888;margin-top:16px">
                Tindak lanjut wajib dalam waktu ' .
                ($skor <= 30 ? '3 hari kerja (temuan berat)' : '7 hari kerja (temuan sedang)') .
              '.</p>';

            $html    = Mailer::template('Alert Kebersihan — ' . $inspeksi['ruangan_nama'], $body);
            $subject = '[ALERT] Area ' . $statusLabel . ': ' . $inspeksi['ruangan_nama'] . ' (Skor ' . number_format($skor,1) . ')';

            foreach ($targets as $user) {
                $sent = $this->mailer->send($user['email'], $user['name'], $subject, $html);
                if ($sent) {
                    $this->notifModel->markEmailSent($notifId);
                }
            }
        } catch (\Throwable $e) {
            // Log tapi jangan crash flow utama
            error_log('[ThresholdChecker] Email error: ' . $e->getMessage());
        }
    }

    /* ── Kirim reminder untuk ruangan yang belum diinspeksi ─ */
    public function sendReminders(): void
    {
        try {
            // Ruangan yang tidak ada inspeksi dalam 7 hari
            $db      = Database::getInstance();
            $stmt    = $db->prepare(
                "SELECT r.id, r.nama, u.id AS user_id, u.name AS user_name, u.email
                 FROM ruangan r
                 LEFT JOIN users u ON u.id = r.assigned_user_id
                 WHERE r.is_active = 1
                   AND u.email IS NOT NULL
                   AND r.id NOT IN (
                     SELECT DISTINCT ruangan_id FROM inspeksi
                     WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                       AND is_draft = 0
                   )
                 LIMIT 50"
            );
            $stmt->execute();
            $rows = $stmt->fetchAll();

            foreach ($rows as $r) {
                // Buat notifikasi in-app
                $this->notifModel->createReminder($r['user_id'], $r['nama']);

                // Kirim email reminder
                if ($r['email']) {
                    $body = '
                      <h2>📅 Pengingat Inspeksi Rutin</h2>
                      <p>Ruangan berikut belum diinspeksi dalam 7 hari terakhir:</p>
                      <div class="info-box">
                        <div class="info-row">
                          <span class="info-label">Ruangan</span>
                          <span class="info-val">' . htmlspecialchars($r['nama']) . '</span>
                        </div>
                      </div>
                      <a href="' . Helper::baseUrl('inspeksi/create?ruangan_id=' . $r['id']) . '" class="btn">
                        Mulai Inspeksi Sekarang
                      </a>';
                    $html = Mailer::template('Pengingat Inspeksi', $body);
                    $this->mailer->send($r['email'], $r['user_name'], 'Pengingat: Inspeksi Rutin', $html);
                }
            }
        } catch (\Throwable $e) {
            error_log('[ThresholdChecker] Reminder error: ' . $e->getMessage());
        }
    }
}
