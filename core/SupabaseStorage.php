<?php
/**
 * SupabaseStorage
 * Helper untuk upload, delete, dan generate public URL Supabase Storage
 */
class SupabaseStorage
{
    private string $url;
    private string $key;
    private string $bucket;

    public function __construct()
    {
        $cfg = require ROOT_PATH . '/config/app.php';
        $sb  = $cfg['supabase'] ?? [];

        $rawUrl       = rtrim($sb['url'] ?? '', '/');
        // Bersihkan jika user menyertakan /rest/v1 di belakang URL
        $this->url    = preg_replace('#/rest/v1/?$#', '', $rawUrl);
        // Prioritaskan secret_key untuk operasi server-side storage, fallback ke publishable_key
        $this->key    = $sb['secret_key'] ?: ($sb['publishable_key'] ?: '');
        $this->bucket = $sb['bucket'] ?? 'inspeksi';
    }

    public function isConfigured(): bool
    {
        return !empty($this->url) && !empty($this->key);
    }

    /**
     * Upload file ke Supabase Storage via REST API
     * @param string $storagePath Path di dalam bucket (cth: "inspeksi/12/foto_123.jpg")
     * @param string $localFilePath Path fisik file sementara di server
     * @param string $mimeType MIME type file
     * @return array ['success' => bool, 'url' => string, 'error' => string]
     */
    public function upload(string $storagePath, string $localFilePath, string $mimeType = 'image/jpeg'): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Supabase Storage belum dikonfigurasi (SUPABASE_URL kosong).'];
        }

        $endpoint = "{$this->url}/storage/v1/object/{$this->bucket}/" . ltrim($storagePath, '/');
        $fileData = file_get_contents($localFilePath);

        if ($fileData === false) {
            return ['success' => false, 'error' => 'Gagal membaca file lokal untuk upload.'];
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $fileData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "apikey: {$this->key}",
                "Authorization: Bearer {$this->key}",
                "Content-Type: {$mimeType}",
                "x-upsert: true"
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['success' => false, 'error' => "cURL Error: {$curlErr}"];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $publicUrl = $this->getPublicUrl($storagePath);
            return [
                'success' => true,
                'url'     => $publicUrl,
                'path'    => $storagePath,
            ];
        }

        $resJson = json_decode($response, true);
        $msg = $resJson['message'] ?? $resJson['error'] ?? "Upload gagal dengan HTTP status {$httpCode}";
        return ['success' => false, 'error' => $msg];
    }

    /**
     * Dapatkan Public URL dari object di bucket publik
     */
    public function getPublicUrl(string $storagePath): string
    {
        return "{$this->url}/storage/v1/object/public/{$this->bucket}/" . ltrim($storagePath, '/');
    }

    /**
     * Hapus file dari Supabase Storage
     */
    public function delete(string $storagePath): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $endpoint = "{$this->url}/storage/v1/object/{$this->bucket}";
        $payload  = json_encode(['prefixes' => [ltrim($storagePath, '/')]]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "apikey: {$this->key}",
                "Authorization: Bearer {$this->key}",
                "Content-Type: application/json",
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode >= 200 && $httpCode < 300);
    }
}
