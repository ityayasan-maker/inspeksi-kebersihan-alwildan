# Security Checklist — Sistem Inspeksi Kebersihan Al Wildan

## Proteksi yang Diimplementasikan

### 1. Authentication & Session
- [x] Password bcrypt cost=12 dengan auto-rehash
- [x] Session regenerate ID setelah login (`session_regenerate_id(true)`)
- [x] Session lifetime check (default 2 jam)
- [x] Session fingerprint (User-Agent + IP) untuk cegah session hijacking
- [x] CSRF token per-session, regenerate setelah login
- [x] Rate limiting login: 5 kali gagal → kunci 5 menit
- [x] HttpOnly + SameSite=Lax cookie untuk session

### 2. Input & Output
- [x] Semua output via `Helper::e()` → htmlspecialchars
- [x] Semua query via PDO prepared statements → cegah SQL injection
- [x] `strip_tags()` + `preg_replace` null bytes pada input teks
- [x] Validasi MIME type upload via `finfo_open()` (bukan hanya ekstensi)
- [x] `basename()` pada semua filename untuk cegah path traversal
- [x] `sanitizeFilename()` — hapus karakter berbahaya dari nama file

### 3. HTTP Headers
- [x] `X-Content-Type-Options: nosniff`
- [x] `X-Frame-Options: SAMEORIGIN`
- [x] `X-XSS-Protection: 1; mode=block`
- [x] `Referrer-Policy: strict-origin-when-cross-origin`
- [x] `Content-Security-Policy` — whitelist CDN yang dipakai
- [x] `Permissions-Policy` — disable camera, mic, geolocation

### 4. File Upload
- [x] Whitelist ekstensi: jpg, jpeg, png, webp
- [x] Validasi MIME type via finfo
- [x] Validasi via getimagesize() — file harus gambar sungguh
- [x] Nama file di-random (date + random_bytes) — tidak bisa ditebak
- [x] Eksekusi PHP diblokir di folder uploads via .htaccess
- [x] Direktori uploads tidak bisa di-browse (Options -Indexes)

### 5. Role & Authorization
- [x] Role-based access control: admin / supervisor / petugas
- [x] `AuthMiddleware::check()` di setiap protected route
- [x] `AuthMiddleware::adminOnly()` untuk aksi admin
- [x] `AuthMiddleware::supervisorUp()` untuk supervisor + admin
- [x] Petugas hanya bisa lihat & edit inspeksi miliknya sendiri

### 6. Logging
- [x] Activity log semua aksi: create, update, delete, login, logout
- [x] Log menyimpan: user_id, action, module, target_id, detail, IP, user_agent
- [x] Viewer activity log tersedia di /admin/activity-log (admin only)

### 7. Hosting-level (Hostinger)
- [x] Root .htaccess redirect ke public/ (file sensitif tidak exposed)
- [x] public/.htaccess blokir akses .sql, .env, .log, .sh
- [x] Folder uploads dilindungi dari eksekusi PHP
- [x] PHP 8+ dengan error display off di production

## Konfigurasi Production Checklist

```
config/app.php:
  'debug' => false   ← WAJIB false di production

config/database.php:
  Ganti host/dbname/user/pass sesuai Hostinger

public/.htaccess:
  URL rewrite aktif (pastikan mod_rewrite ON)

Settings (via UI):
  smtp_user, smtp_pass → isi email Hostinger
  threshold_alert → sesuaikan kebutuhan
```
