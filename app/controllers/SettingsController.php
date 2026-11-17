<?php
class SettingsController extends Controller
{
    private SettingsModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new SettingsModel();
        AuthMiddleware::adminOnly();
    }

    public function index(): void
    {
        $groups = [
            'general'  => $this->model->getByGroup('general'),
            'scoring'  => $this->model->getByGroup('scoring'),
            'email'    => $this->model->getByGroup('email'),
            'upload'   => $this->model->getByGroup('upload'),
            'security' => $this->model->getByGroup('security'),
        ];
        $pageTitle = 'Pengaturan Sistem';
        $this->layout('settings.index', compact('groups', 'pageTitle'));
    }

    public function update(): void
    {
        $this->verifyCsrf();
        $data = $_POST['settings'] ?? [];
        if (!is_array($data)) { Session::flash('error','Data tidak valid.'); $this->redirect('settings'); }

        // Jangan simpan password SMTP jika kosong (biarkan nilai lama)
        if (empty($data['smtp_pass'])) unset($data['smtp_pass']);

        $this->model->setMultiple($data);
        $this->logActivity('update', 'settings', null, implode(',', array_keys($data)));

        // Test email jika diminta
        if (!empty($_POST['test_email'])) {
            $this->sendTestEmail();
            return;
        }

        Session::flash('success', 'Pengaturan berhasil disimpan.');
        $this->redirect('settings');
    }

    private function sendTestEmail(): void
    {
        $to = Session::get('user_email');
        if (!$to) { Session::flash('error','Email admin tidak ditemukan.'); $this->redirect('settings'); }

        $body = '
          <h2>✅ Test Email Berhasil</h2>
          <p>Ini adalah email percobaan dari Sistem Inspeksi Kebersihan Al Wildan.</p>
          <p>Jika Anda menerima email ini, konfigurasi SMTP Anda sudah benar.</p>
          <div class="info-box">
            <div class="info-row"><span class="info-label">Waktu kirim</span>
              <span class="info-val">' . date('d/m/Y H:i:s') . '</span></div>
            <div class="info-row"><span class="info-label">Server</span>
              <span class="info-val">' . htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost') . '</span></div>
          </div>';

        $mailer = new Mailer();
        $html   = Mailer::template('Test Email', $body);
        $result = $mailer->send($to, Session::get('user_name'), '[TEST] Konfigurasi SMTP Berhasil', $html);

        if ($result) {
            Session::flash('success', "Test email berhasil dikirim ke {$to}.");
        } else {
            $errors = implode('; ', $mailer->getErrors());
            Session::flash('error', "Gagal mengirim test email: {$errors}");
        }
        $this->redirect('settings');
    }
}
