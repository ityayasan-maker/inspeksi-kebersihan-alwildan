<?php
/**
 * Uploader — handles image upload, validation, compress, resize
 */
class Uploader
{
    private array  $allowed   = ['jpg','jpeg','png','webp'];
    private int    $maxSizeKb = 2048;          // 2 MB
    private int    $maxWidth  = 1280;
    private int    $maxHeight = 1280;
    private int    $quality   = 82;            // JPEG quality

    private string $baseDir;

    public function __construct()
    {
        $cfg           = require ROOT_PATH . '/config/app.php';
        $this->baseDir = $cfg['upload']['path'];
        if (!empty($cfg['upload']['allowed'])) {
            $this->allowed = $cfg['upload']['allowed'];
        }
        if (!empty($cfg['upload']['max_size'])) {
            $this->maxSizeKb = (int) $cfg['upload']['max_size'];
        }
    }

    /* ── Public API ─────────────────────────────────────── */

    /**
     * Upload & simpan foto inspeksi
     * @param  array  $_FILES entry
     * @param  int    $inspeksiId   → disimpan di uploads/inspeksi/{id}/ atau Supabase Storage
     * @param  string $prefixName   → nama prefix file (misal 'ruangan', 'item_1')
     * @return array  ['success'=>bool, 'filename'=>string, 'url'=>string, 'error'=>string]
     */
    public function uploadInspeksiFoto(array $file, int $inspeksiId, string $prefixName = 'foto'): array
    {
        $targetDir = $this->baseDir . "/inspeksi/{$inspeksiId}";
        $subPath   = "inspeksi/{$inspeksiId}";
        return $this->process($file, $targetDir, $prefixName, $subPath);
    }

    /**
     * Upload foto profil user
     */
    public function uploadProfilePhoto(array $file, int $userId): array
    {
        $targetDir = $this->baseDir . '/profile';
        $subPath   = "profile";
        return $this->process($file, $targetDir, "user_{$userId}", $subPath);
    }

    /**
     * Hapus file upload
     */
    public function delete(string $relativePath): bool
    {
        $cleanPath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $supabase = new SupabaseStorage();
        if ($supabase->isConfigured()) {
            $supabase->delete($cleanPath);
        }

        $full = $this->baseDir . '/' . $cleanPath;
        if (file_exists($full)) {
            return @unlink($full);
        }
        return false;
    }

    /* ── Core process ────────────────────────────────────── */

    private function process(array $file, string $targetDir, string $prefixName = '', string $subPath = ''): array
    {
        // 1. Cek upload error
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->err($this->uploadErrorMessage($file['error'] ?? 99));
        }

        // 2. Validasi ukuran (hingga 10 MB)
        $sizeKb = ($file['size'] ?? 0) / 1024;
        if ($sizeKb > $this->maxSizeKb) {
            return $this->err("Ukuran file melebihi batas " . round($this->maxSizeKb / 1024) . " MB.");
        }

        // 3. Validasi ekstensi
        $origExt = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($origExt && !in_array($origExt, $this->allowed, true)) {
            return $this->err('Format file tidak diizinkan. Gunakan: ' . implode(', ', $this->allowed));
        }

        // 4. Validasi MIME type via finfo
        $mimeType = 'image/jpeg';
        if (function_exists('finfo_open')) {
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']) ?: 'image/jpeg';
            finfo_close($finfo);
        }

        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mimeType, $allowedMime, true)) {
            return $this->err('File bukan format gambar yang didukung.');
        }

        // 5. Generate nama file unik
        $ext      = 'jpg';
        $prefix   = $prefixName ?: 'foto';
        $filename = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        // 6. Buat file sementara di sys_get_temp_dir() (aman di Serverless Vercel /tmp)
        $tempDir  = sys_get_temp_dir();
        $tempPath = rtrim($tempDir, '/\\') . DIRECTORY_SEPARATOR . 'tmp_' . uniqid('alw_', true) . '.jpg';

        // 7. Proses gambar (resize + compress)
        $processed = $this->processImage($file['tmp_name'], $mimeType, $tempPath);
        if (!$processed) {
            @unlink($tempPath);
            return $this->err('Gagal memproses dan mengompresi gambar.');
        }

        // 8. Integrasi Supabase Storage jika dikonfigurasi (Utama di Vercel / Cloud)
        $supabase = new SupabaseStorage();
        if ($supabase->isConfigured()) {
            $storageFolder = $subPath ? trim(str_replace('\\', '/', $subPath), '/') : 'inspeksi';
            $storagePath   = $storageFolder . '/' . $filename;

            $sbResult = $supabase->upload($storagePath, $tempPath, 'image/jpeg');
            @unlink($tempPath);

            if ($sbResult['success']) {
                return [
                    'success'  => true,
                    'filename' => $filename,
                    'url'      => $sbResult['url'],
                    'path'     => $storagePath,
                    'error'    => '',
                ];
            }
            return $this->err('Supabase Upload: ' . ($sbResult['error'] ?? 'Gagal upload ke cloud storage.'));
        }

        // 9. Fallback Local Disk (untuk offline development XAMPP/Laragon)
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        $destPath = $targetDir . '/' . $filename;
        if (is_dir($targetDir) && @copy($tempPath, $destPath)) {
            @unlink($tempPath);
            return ['success' => true, 'filename' => $filename, 'error' => ''];
        }

        @unlink($tempPath);
        return $this->err('Gagal menyimpan file ke penyimpanan server.');
    }

    /* ── Image processing ────────────────────────────────── */

    private function processImage(string $srcPath, string $mime, string $destPath): bool
    {
        // Buat resource dari sumber
        $src = match($mime) {
            'image/jpeg'    => @imagecreatefromjpeg($srcPath),
            'image/png'     => @imagecreatefrompng($srcPath),
            'image/webp'    => @imagecreatefromwebp($srcPath),
            'image/gif'     => @imagecreatefromgif($srcPath),
            default         => false,
        };

        if (!$src) {
            // Fallback: copy langsung jika GD gagal
            return copy($srcPath, $destPath);
        }

        [$origW, $origH] = [imagesx($src), imagesy($src)];

        // Hitung dimensi baru (proporsional)
        [$newW, $newH] = $this->calcDimensions($origW, $origH);

        // Buat canvas baru
        $dst = imagecreatetruecolor($newW, $newH);

        // Preserve transparency untuk PNG/WebP
        if (in_array($mime, ['image/png', 'image/webp', 'image/gif'])) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $transparent);
        } else {
            // Background putih untuk JPEG
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);
        }

        // Resize dengan resampling halus
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // Simpan sebagai JPEG
        $result = imagejpeg($dst, $destPath, $this->quality);

        imagedestroy($src);
        imagedestroy($dst);

        return $result;
    }

    private function calcDimensions(int $origW, int $origH): array
    {
        if ($origW <= $this->maxWidth && $origH <= $this->maxHeight) {
            return [$origW, $origH];
        }
        $ratio  = min($this->maxWidth / $origW, $this->maxHeight / $origH);
        return [(int)round($origW * $ratio), (int)round($origH * $ratio)];
    }

    /* ── Helpers ─────────────────────────────────────────── */

    private function err(string $msg): array
    {
        return ['success' => false, 'filename' => '', 'error' => $msg];
    }

    private function uploadErrorMessage(int $code): string
    {
        return match($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file terlalu besar.',
            UPLOAD_ERR_PARTIAL  => 'File hanya terupload sebagian.',
            UPLOAD_ERR_NO_FILE  => 'Tidak ada file yang dipilih.',
            UPLOAD_ERR_NO_TMP_DIR => 'Direktori sementara tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            default             => 'Terjadi kesalahan saat upload.',
        };
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
