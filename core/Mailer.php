<?php
/**
 * Mailer — SMTP email sender via native PHP / Hostinger SMTP
 * Tidak memerlukan library eksternal. Menggunakan PHP's mail() sebagai
 * fallback, tapi utamanya pakai SMTP socket langsung.
 *
 * Untuk Hostinger: SMTP host=mail.hostinger.com, port=465 (SSL)
 */
class Mailer
{
    private string $host;
    private int    $port;
    private string $user;
    private string $pass;
    private string $fromEmail;
    private string $fromName;
    private bool   $debug;

    private array  $errors = [];

    public function __construct()
    {
        $cfg = (new SettingsModel())->getAllAsArray();
        $this->host      = $cfg['smtp_host']      ?? 'mail.hostinger.com';
        $this->port      = (int)($cfg['smtp_port'] ?? 465);
        $this->user      = $cfg['smtp_user']      ?? '';
        $this->pass      = $cfg['smtp_pass']      ?? '';
        $this->fromEmail = $cfg['smtp_from']      ?? '';
        $this->fromName  = $cfg['smtp_from_name'] ?? 'Inspeksi Kebersihan';
        $this->debug     = false;
    }

    /* ── Public API ─────────────────────────────────────── */

    public function send(string $to, string $toName, string $subject, string $htmlBody): bool
    {
        $this->errors = [];

        // Validasi konfigurasi
        if (!$this->user || !$this->pass || !$this->fromEmail) {
            $this->errors[] = 'SMTP belum dikonfigurasi. Isi pengaturan email di menu Settings.';
            return false;
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Alamat email tidak valid: {$to}";
            return false;
        }

        // Coba kirim via SMTP socket
        try {
            return $this->sendSmtp($to, $toName, $subject, $htmlBody);
        } catch (\Throwable $e) {
            $this->errors[] = 'SMTP error: ' . $e->getMessage();

            // Fallback ke PHP mail() jika SMTP gagal
            return $this->sendFallback($to, $subject, $htmlBody);
        }
    }

    public function getErrors(): array { return $this->errors; }

    /* ── SMTP socket ─────────────────────────────────────── */

    private function sendSmtp(string $to, string $toName, string $subject, string $htmlBody): bool
    {
        $ssl    = ($this->port === 465);
        $prefix = $ssl ? 'ssl://' : '';

        $sock = @fsockopen($prefix . $this->host, $this->port, $errno, $errstr, 15);
        if (!$sock) {
            throw new \RuntimeException("Tidak bisa terhubung ke SMTP: {$errstr} ({$errno})");
        }

        stream_set_timeout($sock, 15);

        // Helper baca/tulis
        $read  = fn() => fgets($sock, 4096);
        $write = function(string $cmd) use ($sock): string {
            fputs($sock, $cmd . "\r\n");
            return fgets($sock, 4096);
        };

        $read(); // Greeting

        // EHLO
        $write('EHLO ' . gethostname());

        // STARTTLS jika port 587
        if ($this->port === 587) {
            $write('STARTTLS');
            stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $write('EHLO ' . gethostname());
        }

        // Auth
        $write('AUTH LOGIN');
        $write(base64_encode($this->user));
        $authResp = $write(base64_encode($this->pass));
        if (!str_starts_with(trim($authResp), '235')) {
            fclose($sock);
            throw new \RuntimeException('Autentikasi SMTP gagal. Periksa username/password.');
        }

        // Envelope
        $write("MAIL FROM:<{$this->fromEmail}>");
        $rcptResp = $write("RCPT TO:<{$to}>");
        if (!str_starts_with(trim($rcptResp), '2')) {
            fclose($sock);
            throw new \RuntimeException("Alamat penerima ditolak: {$to}");
        }

        // Body
        $write('DATA');
        $boundary = '----=_Part_' . md5(uniqid());
        $headers  = $this->buildHeaders($to, $toName, $subject, $boundary);
        $body     = $this->buildBody($htmlBody, $boundary);
        fputs($sock, $headers . $body . "\r\n.\r\n");
        $dataResp = fgets($sock, 4096);

        $write('QUIT');
        fclose($sock);

        if (!str_starts_with(trim($dataResp), '2')) {
            throw new \RuntimeException("Gagal mengirim pesan: {$dataResp}");
        }

        return true;
    }

    /* ── Build headers & body ────────────────────────────── */

    private function buildHeaders(string $to, string $toName, string $subject, string $boundary): string
    {
        $date    = date('r');
        $msgId   = '<' . time() . '.' . md5(uniqid()) . '@' . gethostname() . '>';
        $fromEnc = '=?UTF-8?B?' . base64_encode($this->fromName) . '?=';
        $toEnc   = $toName ? ('=?UTF-8?B?' . base64_encode($toName) . '?= <' . $to . '>') : $to;
        $subjEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        return "Date: {$date}\r\n"
             . "From: {$fromEnc} <{$this->fromEmail}>\r\n"
             . "To: {$toEnc}\r\n"
             . "Subject: {$subjEnc}\r\n"
             . "Message-ID: {$msgId}\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n"
             . "X-Mailer: PHP/" . phpversion() . "\r\n"
             . "\r\n";
    }

    private function buildBody(string $html, string $boundary): string
    {
        // Plain text fallback
        $plain = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $html));
        $plain = html_entity_decode($plain, ENT_QUOTES, 'UTF-8');

        return "--{$boundary}\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "Content-Transfer-Encoding: base64\r\n\r\n"
             . chunk_split(base64_encode(wordwrap($plain, 76))) . "\r\n"
             . "--{$boundary}\r\n"
             . "Content-Type: text/html; charset=UTF-8\r\n"
             . "Content-Transfer-Encoding: base64\r\n\r\n"
             . chunk_split(base64_encode($html)) . "\r\n"
             . "--{$boundary}--";
    }

    /* ── PHP mail() fallback ─────────────────────────────── */

    private function sendFallback(string $to, string $subject, string $htmlBody): bool
    {
        $headers = "MIME-Version: 1.0\r\n"
                 . "Content-Type: text/html; charset=UTF-8\r\n"
                 . "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $result  = @mail($to, $subject, $htmlBody, $headers);
        if (!$result) {
            $this->errors[] = 'PHP mail() juga gagal. Pastikan server mendukung fungsi mail().';
        }
        return $result;
    }

    /* ── HTML template ───────────────────────────────────── */

    public static function template(string $title, string $body, string $footer = ''): string
    {
        $footer = $footer ?: 'Email ini dikirim otomatis oleh Sistem Inspeksi Kebersihan Al Wildan. Jangan balas email ini.';
        return '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
        <style>
        body{margin:0;padding:0;background:#f4f6f9;font-family:"Segoe UI",Arial,sans-serif}
        .wrap{max-width:560px;margin:24px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.08)}
        .header{background:linear-gradient(135deg,#1a5c38,#2d8653);padding:28px 32px;text-align:center}
        .header h1{color:#fff;font-size:18px;margin:0 0 4px}
        .header p{color:rgba(255,255,255,.8);font-size:13px;margin:0}
        .body{padding:28px 32px}
        .body h2{font-size:16px;color:#1a2035;margin:0 0 12px}
        .body p{font-size:14px;color:#444;line-height:1.7;margin:0 0 12px}
        .info-box{background:#f8fafb;border:1px solid #e5e7eb;border-radius:8px;padding:14px 16px;margin:16px 0}
        .info-row{display:flex;justify-content:space-between;padding:4px 0;font-size:13px}
        .info-label{color:#888}
        .info-val{font-weight:600;color:#1a2035}
        .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600}
        .badge-bersih{background:#dcfce7;color:#166534}
        .badge-kurang{background:#fef9c3;color:#854d0e}
        .badge-kotor{background:#fee2e2;color:#991b1b}
        .btn{display:inline-block;padding:10px 24px;background:#2d8653;color:#fff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;margin:16px 0}
        .footer{background:#f8fafb;padding:16px 32px;text-align:center;font-size:11px;color:#aaa;border-top:1px solid #e5e7eb}
        </style></head><body>
        <div class="wrap">
          <div class="header">
            <h1>📋 Inspeksi Kebersihan</h1>
            <p>Yayasan Am Badar — Al Wildan Islamic School</p>
          </div>
          <div class="body">' . $body . '</div>
          <div class="footer">' . htmlspecialchars($footer) . '</div>
        </div></body></html>';
    }
}
