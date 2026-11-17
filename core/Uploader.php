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
     * @param  int    $inspeksiId   → disimpan di uploads/inspeksi/{id}/
     * @return array  ['success'=>bool, 'filename'=>string, 'error'=>string]
     */
    public function uploadInspeksiFoto(array $file, int $inspeksiId): array
    {
        $targetDir = $this->baseDir . "/inspeksi/{$inspeksiId}";
        return $this->process($file, $targetDir);
    }

    /**
     * Upload foto profil user
     */
    public function uploadProfilePhoto(array $file, int $userId): array
    {
        $targetDir = $this->baseDir . '/profile';
        $result    = $this->process($file, $targetDir, "user_{$userId}");
        return $result;
    }

    /**
     * Hapus file upload
     */
    public function delete(string $relativePath): bool
    {
        $supabase = new SupabaseStorage();
        if ($supabase->isConfigured()) {
            $supabase->delete($relativePath);
        }

        $full = $this->baseDir . '/' . ltrim($relativePath, '/');
        if (file_exists($full)) {
            return unlink($full);
        }
        return false;
    }

    /* ── Core process ────────────────────────────────────── */

    private function process(array $file, string $targetDir, string $prefixName = ''): array
    {
        // 1. Cek upload error
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->err($this->uploadErrorMessage($file['error'] ?? 99));
        }

        // 2. Validasi ukuran
        $sizeKb = $file['size'] / 1024;
        if ($sizeKb > $this->maxSizeKb) {
            return $this->err("Ukuran file melebihi batas {$this->maxSizeKb} KB.");
        }

        // 3. Validasi ekstensi (dari nama file)
        $origExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($origExt, $this->allowed, true)) {
            return $this->err('Format file tidak diizinkan. Gunakan: ' . implode(', ', $this->allowed));
        }

        // 4. Validasi MIME type via finfo (lebih aman dari ekstensi)
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mimeType, $allowedMime, true)) {
            return $this->err('File bukan gambar yang valid.');
        }

        // 5. Buat direktori target
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                return $this->err('Gagal membuat direktori upload.');
            }
            // Copy .htaccess keamanan
            $htaccessSrc = $this->baseDir . '/inspeksi/.htaccess';
            if (file_exists($htaccessSrc)) {
                copy($htaccessSrc, $targetDir . '/.htaccess');
            }
        }

        // 6. Generate nama file unik
        $ext      = 'jpg'; // selalu simpan sebagai JPG/WebP setelah compress
        $prefix   = $prefixName ?: 'foto';
        $filename = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $targetDir . '/' . $filename;

        // 7. Proses gambar (resize + compress)
        $processed = $this->processImage($file['tmp_name'], $mimeType, $destPath);
        if (!$processed) {
            return $this->err('Gagal memproses gambar.');
        }

        // 8. Integrasi Supabase Storage jika dikonfigurasi
        $supabase = new SupabaseStorage();
        if ($supabase->isConfigured()) {
            $subPath = str_replace(rtrim($this->baseDir, '/\\'), '', $targetDir);
            $storagePath = trim(str_replace('\\', '/', $subPath), '/') . '/' . $filename;
            $sbResult = $supabase->upload($storagePath, $destPath, 'image/jpeg');
            if ($sbResult['success']) {
                return [
                    'success'  => true,
                    'filename' => $storagePath,
                    'url'      => $sbResult['url'],
                    'error'    => '',
                ];
            }
            return $this->err('Supabase Upload: ' . $sbResult['error']);
        }

        return ['success' => true, 'filename' => $filename, 'error' => ''];
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
