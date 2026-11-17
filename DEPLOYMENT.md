# PANDUAN DEPLOYMENT — Sistem Inspeksi Kebersihan
## Al-Wildan Islamic School 8 Kemang Bogor
### Developer: Yayasan Ambadar | Target: Hostinger / cPanel / Vercel / Local

---

## DAFTAR ISI
1. Persiapan
2. Upload ke Hostinger
3. Import Database
4. Konfigurasi Environment
5. Setup SMTP Email
6. Verifikasi Sistem
7. Production Checklist
8. Backup Strategy
9. Maintenance & Update
10. Troubleshooting

---

## 1. PERSIAPAN

### Yang dibutuhkan:
- Akun Hostinger aktif dengan paket yang mendukung PHP 8+
- Akses ke hPanel Hostinger
- File Manager atau FTP client (FileZilla, WinSCP)
- Database MySQL sudah dibuat di hPanel
- Nama domain / subdomain yang sudah diarahkan ke hosting

### Struktur file yang akan diupload:
```
inspeksi-kebersihan/
├── .htaccess                    ← Root redirect ke public/
├── app/                         ← Controllers, Models, Views
├── config/
│   ├── app.php                  ← ⚠️ EDIT INI
│   └── database.php             ← ⚠️ EDIT INI
├── core/                        ← Engine MVC
├── database/
│   └── database.sql             ← Import ke MySQL
├── public/                      ← Document Root Hostinger
│   ├── .htaccess
│   ├── index.php
│   ├── css/, js/
│   └── uploads/                 ← Folder foto inspeksi
└── SECURITY.md
```

---

## 2. UPLOAD KE HOSTINGER

### Opsi A — File Manager hPanel (Recommended untuk pemula)

1. Login ke **hPanel** → pilih **Files** → **File Manager**
2. Navigasi ke folder `public_html` (atau subdomain folder)
3. Klik **Upload Files** → pilih semua file project → Upload
4. Pastikan struktur folder sesuai di atas

### Opsi B — FTP dengan FileZilla

```
Host:     ftp.yourdomain.com  (atau IP server Hostinger)
Username: u123456789         (lihat di hPanel > FTP Accounts)
Password: [password FTP Anda]
Port:     21
```

Langkah:
1. Connect ke server
2. Navigasi ke `/public_html/` di panel kanan (server)
3. Drag & drop seluruh isi folder `inspeksi-kebersihan/` ke sana
4. Pastikan `.htaccess` ikut terupload (file hidden)

### Opsi C — ZIP upload (Paling cepat)

1. Buat ZIP seluruh folder `inspeksi-kebersihan/`
2. Upload ZIP ke `public_html/` via File Manager
3. Klik kanan ZIP → **Extract** → Extract di sini
4. Hapus file ZIP setelah selesai

### ⚠️ PENTING: Document Root Hostinger

Hostinger menyajikan `public_html` sebagai web root. Pastikan:
- File `.htaccess` di root folder terupload
- Folder `public/` berada SATU LEVEL di dalam `public_html/`
- Contoh path: `public_html/inspeksi-kebersihan/public/index.php`

Jika menggunakan subdomain (cth: `inspeksi.alwildan.sch.id`):
- Set document root subdomain ke: `public_html/inspeksi-kebersihan/public`
- Di hPanel → **Domains** → subdomain → **Manage** → ubah Document Root

---

## 3. IMPORT DATABASE

### Langkah di hPanel:

1. **hPanel** → **Databases** → **MySQL Databases**
2. Buat database baru:
   - Database name: `u123456_inspeksi` (sesuaikan prefix)
   - Username: `u123456_user`
   - Password: [buat password kuat]
   - Klik **Create**
3. Klik **phpMyAdmin** untuk buka interface DB
4. Pilih database yang baru dibuat di panel kiri
5. Klik tab **Import** (atas)
6. Klik **Choose File** → pilih `database/database.sql`
7. Format: SQL, Character set: utf8mb4
8. Klik **Import** (bawah)
9. Tunggu hingga muncul pesan **Import has been successfully finished**

### Verifikasi import:
Setelah import, pastikan tabel berikut ada:
```
✓ users            ✓ gedung          ✓ lantai
✓ ruangan          ✓ standar_kebersihan
✓ ruangan_checklist ✓ inspeksi       ✓ inspeksi_detail
✓ activity_logs    ✓ notifications   ✓ settings
```

### Jika database.sql sudah diimport sebelumnya (update):
Jalankan `database/patch_tahap6.sql` untuk menambah tabel baru.

---

## 4. KONFIGURASI ENVIRONMENT

### Edit config/database.php

```php
return [
    'host'    => 'localhost',           // Biasanya localhost di Hostinger
    'dbname'  => 'u123456_inspeksi',    // ← Nama database Anda
    'user'    => 'u123456_user',        // ← Username database Anda
    'pass'    => 'YourStrongPass123!',  // ← Password database Anda
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
```

### Edit config/app.php

```php
return [
    'name'     => 'Inspeksi Kebersihan Al Wildan',
    'url'      => 'https://inspeksi.alwildan.sch.id', // ← URL domain Anda
    'debug'    => false,    // ← WAJIB false di production!
    'timezone' => 'Asia/Jakarta',
    // ... (sisa tidak perlu diubah)
];
```

### Permissions folder uploads

Via SSH (jika tersedia) atau File Manager:
```bash
chmod 755 public/uploads/
chmod 755 public/uploads/inspeksi/
chmod 755 public/uploads/profile/
chmod 755 public/uploads/temp/
```

Di File Manager Hostinger:
- Klik kanan folder `uploads/` → **Change Permissions**
- Set ke `755`
- Centang **Apply to subdirectories**

---

## 5. SETUP SMTP EMAIL

### Konfigurasi di UI (Recommended):

1. Login ke sistem sebagai **Admin**
2. Buka menu **Pengaturan** → tab **Email / SMTP**
3. Isi:

| Field | Nilai |
|-------|-------|
| SMTP Host | `mail.hostinger.com` |
| SMTP Port | `465` |
| SMTP Username | `noreply@alwildan.sch.id` |
| SMTP Password | [password email Hostinger] |
| Email Pengirim | `noreply@alwildan.sch.id` |
| Nama Pengirim | `Sistem Inspeksi Al Wildan` |

4. Klik **Simpan Pengaturan**
5. Klik **Kirim Test Email** untuk verifikasi

### Buat email di Hostinger terlebih dahulu:

1. **hPanel** → **Emails** → **Email Accounts**
2. Buat email: `noreply@alwildan.sch.id`
3. Catat password → gunakan di settings sistem

---

## 6. VERIFIKASI SISTEM

### Checklist setelah deployment:

```
□ 1. Buka URL sistem → tampil halaman login
□ 2. Login dengan: admin@alwildan.sch.id / Admin@123
□ 3. Ganti password admin segera setelah login pertama
□ 4. Dashboard tampil (meski data kosong)
□ 5. Sidebar semua menu bisa diklik tanpa error
□ 6. Buka menu Gedung → data seed muncul (7 gedung)
□ 7. Buka menu Ruangan → data 39 ruangan muncul
□ 8. Buka menu Standar → 70+ checklist item muncul
□ 9. Coba buat Inspeksi baru → form muncul normal
□ 10. Upload foto → berhasil tersimpan di uploads/
□ 11. Laporan → filter dan tabel bekerja
□ 12. Settings → test email berhasil terkirim
□ 13. Cek halaman /admin/activity-log → log login muncul
```

### Test URL yang harus bisa diakses:
```
https://inspeksi.alwildan.sch.id/login
https://inspeksi.alwildan.sch.id/dashboard
https://inspeksi.alwildan.sch.id/gedung
https://inspeksi.alwildan.sch.id/inspeksi/create
https://inspeksi.alwildan.sch.id/laporan
```

---

## 7. PRODUCTION CHECKLIST

### Keamanan — WAJIB dilakukan:

```
□ config/app.php  → 'debug' => false
□ Ganti password semua user default (Admin@123)
□ Ganti email semua user ke email nyata
□ Pastikan .htaccess berjalan (coba akses /config/database.php → harus 403)
□ Pastikan folder uploads tidak bisa diakses langsung
□ Test CSRF: coba submit form tanpa token → harus ditolak
```

### Performa:

```
□ Aktifkan Hostinger LiteSpeed Cache (jika tersedia)
□ Pastikan PHP version 8.1+ (hPanel → Advanced → PHP Configuration)
□ Enable OPcache (hPanel → PHP Configuration → opcache.enable=1)
□ Aktifkan GZIP di Hostinger
```

### Data awal:

```
□ Update nama user sesuai nama nyata
□ Update email user ke email nyata
□ Verifikasi 39 ruangan sudah sesuai kondisi aktual
□ Sesuaikan assigned petugas per ruangan
□ Update kode gedung jika berbeda
□ Set threshold_alert sesuai standar mutu (default: 50)
```

---

## 8. BACKUP STRATEGY

### A. Backup Database Otomatis (Hostinger)

1. **hPanel** → **Backups** → **Schedule Backup**
2. Set frekuensi: **Weekly** (minimum)
3. Aktifkan email notifikasi backup

### B. Backup Manual via phpMyAdmin

1. phpMyAdmin → pilih database `u123456_inspeksi`
2. Tab **Export** → Method: **Quick** → Format: **SQL**
3. Klik **Export** → simpan file .sql
4. Lakukan setiap: Mingguan (data), Bulanan (full)

### C. Backup File Upload

Via File Manager:
1. Pilih folder `public/uploads/`
2. Klik kanan → **Compress** → ZIP
3. Download ke komputer lokal
4. Lakukan setiap: Bulanan

### D. Script backup otomatis (via cron Hostinger)

Di **hPanel** → **Advanced** → **Cron Jobs**, tambahkan:
```
# Backup database setiap malam jam 02:00
0 2 * * * mysqldump -u u123456_user -pYourPass u123456_inspeksi > /home/u123456/backups/db_$(date +\%Y\%m\%d).sql

# Hapus backup lebih dari 30 hari
0 3 * * * find /home/u123456/backups/ -name "*.sql" -mtime +30 -delete
```

### Retensi backup yang direkomendasikan:
```
Database harian  → simpan 7 hari
Database mingguan → simpan 4 minggu
Database bulanan  → simpan 12 bulan
File uploads     → simpan 3 bulan
```

---

## 9. MAINTENANCE & UPDATE

### Cara update sistem:

1. Backup database dan file terlebih dahulu
2. Upload file baru via File Manager / FTP
3. Jika ada perubahan database → jalankan SQL patch via phpMyAdmin
4. Clear cache browser
5. Test semua fungsi utama

### Log file:

PHP error log: `/home/u123456/logs/error_log` (tanyakan ke support Hostinger)

Activity log sistem: Buka **Admin** → **Activity Log** di menu sidebar

### Bersihkan data lama (via phpMyAdmin):

```sql
-- Hapus activity log lebih dari 6 bulan
DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 180 DAY);

-- Hapus notifikasi yang sudah dibaca lebih dari 3 bulan
DELETE FROM notifications WHERE is_read=1 AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Cek ukuran database
SELECT table_name, ROUND((data_length + index_length)/1024/1024, 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = 'u123456_inspeksi'
ORDER BY size_mb DESC;
```

---

## 10. TROUBLESHOOTING

### ❌ Error 500 Internal Server Error

**Penyebab umum:**
- `config/database.php` salah (host/dbname/user/pass)
- `.htaccess` tidak berjalan (mod_rewrite belum aktif)
- PHP version < 8.0

**Solusi:**
1. Cek PHP version di hPanel → Advanced → PHP Configuration → ubah ke 8.1
2. Pastikan `AllowOverride All` aktif (hubungi support Hostinger)
3. Sementara set `debug => true` di config/app.php untuk lihat error detail
4. **Ingat set kembali ke false setelah selesai debug!**

---

### ❌ Halaman 404 semua URL

**Penyebab:** mod_rewrite tidak aktif atau `.htaccess` tidak terbaca

**Solusi:**
1. Pastikan `.htaccess` di root folder terupload (file hidden!)
2. Di hPanel → Files → File Manager → aktifkan "Show Hidden Files"
3. Tambahkan di `.htaccess` root jika perlu: `Options +FollowSymLinks`

---

### ❌ Upload foto gagal

**Penyebab:**
- Permission folder `uploads/` kurang
- `upload_max_filesize` PHP terlalu kecil
- GD library tidak aktif

**Solusi:**
1. Pastikan permission folder `uploads/` = 755
2. hPanel → PHP Configuration → `upload_max_filesize` = 10M
3. hPanel → PHP Configuration → aktifkan `gd` extension

---

### ❌ Email tidak terkirim

**Penyebab:**
- Konfigurasi SMTP salah
- Email Hostinger belum dibuat
- Port 465 diblokir

**Solusi:**
1. Pastikan email `noreply@domain.com` sudah dibuat di Hostinger
2. Coba port 587 (TLS) jika 465 gagal
3. Pastikan username = alamat email lengkap (bukan hanya user)
4. Cek password email di hPanel → Email Accounts

---

### ❌ Session logout sendiri

**Penyebab:** Session lifetime habis (default 2 jam)

**Solusi:**
1. Settings → Keamanan → `session_lifetime` → ubah ke 14400 (4 jam)
2. Atau set di PHP: `session.gc_maxlifetime = 14400`

---

### ❌ Database connection failed

**Penyebab:** Hostinger DB host bukan `localhost`

**Solusi:**
- Di hPanel → MySQL Databases → lihat **MySQL Host**
- Biasanya `localhost` tapi beberapa server: `127.0.0.1` atau `mysql.hostinger.com`
- Update `config/database.php` → `'host'`

---

## INFORMASI AKUN DEFAULT

> ⚠️ **GANTI SEMUA PASSWORD INI SEGERA SETELAH DEPLOYMENT!**

| Role | Email | Password Default |
|------|-------|-----------------|
| Admin | admin@alwildan.sch.id | Admin@123 |
| Supervisor (PLT) | nadia@alwildan.sch.id | Admin@123 |
| Supervisor (GA) | ga@alwildan.sch.id | Admin@123 |
| Petugas | sigit@alwildan.sch.id | Admin@123 |
| Petugas | wira@alwildan.sch.id | Admin@123 |
| Petugas | hendi@alwildan.sch.id | Admin@123 |
| Petugas | hendra@alwildan.sch.id | Admin@123 |
| Petugas | yudi@alwildan.sch.id | Admin@123 |

---

## RINGKASAN CEPAT (TL;DR)

```
1. Upload semua file ke public_html/
2. Set Document Root subdomain ke .../public/
3. Import database/database.sql via phpMyAdmin
4. Edit config/database.php → isi kredensial DB Hostinger
5. Edit config/app.php → isi URL domain, set debug=false
6. chmod 755 public/uploads/ (semua subfolder)
7. Buka URL → login → ganti semua password default
8. Settings → SMTP → isi email Hostinger → test email
9. Buat backup pertama
10. 🚀 Sistem siap digunakan!
```

---

*Dokumen ini dibuat untuk Yayasan Am Badar — SMP & SMK Al Wildan Islamic School.*
*Versi: 1.0 | Dibuat: 2025 | Confidential*
