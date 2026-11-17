-- ============================================================
-- DATABASE: inspeksi_kebersihan_alwildan
-- Sistem Inspeksi & Monitoring Kebersihan
-- Al-Wildan Islamic School 8 Kemang Bogor
-- Developer: Yayasan Ambadar
-- Versi: 1.0
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

CREATE DATABASE IF NOT EXISTS `inspeksi_kebersihan_alwildan`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `inspeksi_kebersihan_alwildan`;

-- ============================================================
-- TABEL 1: users
-- ============================================================
CREATE TABLE `users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('admin','supervisor','petugas') NOT NULL DEFAULT 'petugas',
  `phone`      VARCHAR(20) DEFAULT NULL,
  `photo`      VARCHAR(255) DEFAULT NULL,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 2: gedung
-- ============================================================
CREATE TABLE `gedung` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode`       VARCHAR(20) NOT NULL,
  `nama`       VARCHAR(100) NOT NULL,
  `unit`       VARCHAR(50) NOT NULL COMMENT 'smp, smk, yayasan, masjid, al_wildan, dll',
  `keterangan` TEXT DEFAULT NULL,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_gedung_kode` (`kode`),
  KEY `idx_gedung_unit` (`unit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 3: lantai
-- ============================================================
CREATE TABLE `lantai` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `gedung_id`  INT UNSIGNED NOT NULL,
  `nama`       VARCHAR(50) NOT NULL,
  `urutan`     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lantai_gedung` (`gedung_id`),
  CONSTRAINT `fk_lantai_gedung` FOREIGN KEY (`gedung_id`)
    REFERENCES `gedung` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 4: ruangan
-- Tipe ruangan mengacu standar mutu kebersihan dokumen resmi
-- ============================================================
CREATE TABLE `ruangan` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lantai_id`        INT UNSIGNED NOT NULL,
  `kode`             VARCHAR(30) NOT NULL,
  `nama`             VARCHAR(100) NOT NULL,
  `tipe`             ENUM(
                       'kelas','toilet','lab_ipa','lab_komputer',
                       'selasar','lapangan','gudang',
                       'kantor','ruang_rapat','ruang_ibadah',
                       'tempat_wudhu','area_luar','lainnya'
                     ) NOT NULL DEFAULT 'lainnya',
  `zona`             VARCHAR(100) DEFAULT NULL,
  `assigned_user_id` INT UNSIGNED DEFAULT NULL COMMENT 'Petugas penanggung jawab area',
  `luas_m2`          DECIMAL(8,2) DEFAULT NULL,
  `keterangan`       TEXT DEFAULT NULL,
  `is_active`        TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ruangan_kode` (`kode`),
  KEY `idx_ruangan_lantai` (`lantai_id`),
  KEY `idx_ruangan_tipe` (`tipe`),
  KEY `idx_ruangan_assigned` (`assigned_user_id`),
  CONSTRAINT `fk_ruangan_lantai` FOREIGN KEY (`lantai_id`)
    REFERENCES `lantai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ruangan_assigned` FOREIGN KEY (`assigned_user_id`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 5: standar_kebersihan
-- Checklist per tipe ruangan, bersumber dari dokumen standar mutu
-- ============================================================
CREATE TABLE `standar_kebersihan` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipe_ruangan` ENUM(
                  'kelas','toilet','lab_ipa','lab_komputer',
                  'selasar','lapangan','gudang',
                  'kantor','ruang_rapat','ruang_ibadah',
                  'tempat_wudhu','area_luar','lainnya'
                ) NOT NULL,
  `no_urut`     TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `aspek`       VARCHAR(200) NOT NULL COMMENT 'Nama aspek kebersihan',
  `deskripsi`   TEXT DEFAULT NULL COMMENT 'Standar minimal',
  `frekuensi`   ENUM('setiap_sesi','harian','mingguan','bulanan','semesteran','setiap_pakai')
                NOT NULL DEFAULT 'harian',
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_standar_tipe` (`tipe_ruangan`),
  KEY `idx_standar_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 6: inspeksi
-- Header inspeksi — 1 sesi = 1 ruangan
-- assigned_user_id  : penanggung jawab area (dari ruangan)
-- inspected_by_id   : yang BENAR-BENAR melakukan inspeksi (boleh beda)
-- ============================================================
CREATE TABLE `inspeksi` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ruangan_id`       INT UNSIGNED NOT NULL,
  `assigned_user_id` INT UNSIGNED DEFAULT NULL COMMENT 'Petugas PJ area saat inspeksi',
  `inspected_by_id`  INT UNSIGNED NOT NULL    COMMENT 'Yang melakukan inspeksi',
  `tanggal`          DATE NOT NULL,
  `waktu_mulai`      TIME DEFAULT NULL,
  `waktu_selesai`    TIME DEFAULT NULL,
  `skor_total`       DECIMAL(5,2) DEFAULT NULL COMMENT 'Rata-rata skor checklist',
  `status`           ENUM('bersih','kurang_bersih','kotor') DEFAULT NULL,
  `catatan_umum`     TEXT DEFAULT NULL,
  `foto_ruangan`     VARCHAR(255) DEFAULT NULL COMMENT 'Foto kondisi umum ruangan',
  `is_draft`         TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inspeksi_ruangan` (`ruangan_id`),
  KEY `idx_inspeksi_assigned` (`assigned_user_id`),
  KEY `idx_inspeksi_inspected` (`inspected_by_id`),
  KEY `idx_inspeksi_tanggal` (`tanggal`),
  KEY `idx_inspeksi_status` (`status`),
  CONSTRAINT `fk_inspeksi_ruangan` FOREIGN KEY (`ruangan_id`)
    REFERENCES `ruangan` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_inspeksi_assigned` FOREIGN KEY (`assigned_user_id`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_inspeksi_inspected` FOREIGN KEY (`inspected_by_id`)
    REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 7: inspeksi_detail
-- Detail per checklist item dalam 1 sesi inspeksi
-- Scoring: Bersih=100, Kurang Bersih=70, Kotor=30
-- ============================================================
CREATE TABLE `inspeksi_detail` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `inspeksi_id`  INT UNSIGNED NOT NULL,
  `standar_id`   INT UNSIGNED NOT NULL,
  `kondisi`      ENUM('bersih','kurang_bersih','kotor') NOT NULL,
  `skor`         TINYINT UNSIGNED NOT NULL DEFAULT 0
                   COMMENT '100=bersih, 70=kurang_bersih, 30=kotor',
  `catatan`      TEXT DEFAULT NULL,
  `foto`         VARCHAR(255) DEFAULT NULL COMMENT 'Path file foto',
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_detail_inspeksi_standar` (`inspeksi_id`, `standar_id`),
  KEY `idx_detail_inspeksi` (`inspeksi_id`),
  KEY `idx_detail_standar` (`standar_id`),
  KEY `idx_detail_kondisi` (`kondisi`),
  CONSTRAINT `fk_detail_inspeksi` FOREIGN KEY (`inspeksi_id`)
    REFERENCES `inspeksi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_standar` FOREIGN KEY (`standar_id`)
    REFERENCES `standar_kebersihan` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 8: activity_logs
-- ============================================================
CREATE TABLE `activity_logs` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED DEFAULT NULL,
  `action`     VARCHAR(100) NOT NULL,
  `module`     VARCHAR(50) NOT NULL,
  `target_id`  INT UNSIGNED DEFAULT NULL,
  `detail`     TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_user` (`user_id`),
  KEY `idx_log_module` (`module`),
  KEY `idx_log_created` (`created_at`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 9: notifications
-- ============================================================
CREATE TABLE `notifications` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED DEFAULT NULL COMMENT 'NULL = broadcast semua',
  `inspeksi_id` INT UNSIGNED DEFAULT NULL,
  `type`       ENUM('alert_kotor','alert_threshold','info','reminder') NOT NULL DEFAULT 'info',
  `title`      VARCHAR(200) NOT NULL,
  `message`    TEXT DEFAULT NULL,
  `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
  `sent_email` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_read` (`is_read`),
  KEY `idx_notif_created` (`created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notif_inspeksi` FOREIGN KEY (`inspeksi_id`)
    REFERENCES `inspeksi` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 10: settings
-- ============================================================
CREATE TABLE `settings` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name`   VARCHAR(100) NOT NULL,
  `value`      TEXT DEFAULT NULL,
  `label`      VARCHAR(200) DEFAULT NULL,
  `group_name` VARCHAR(50) DEFAULT 'general',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATA AWAL: users
-- Akun Super Admin Al Wildan Islamic School
-- Email    : admin@alwildan.sch.id
-- Password : Admin@123
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `is_active`) VALUES
('Administrator Al Wildan', 'admin@alwildan.sch.id', '$2y$12$/L2K2s3VEHvd527c19iIYuLGURVehTqdjMYrrcUp41j/lYj9nyMBe', 'admin', '081200000001', 1);

-- ============================================================
-- CATATAN: Master Gedung, Lantai, dan Ruangan dikosongkan
-- agar Admin Al Wildan dapat menginput sesuai struktur gedung dan ruangan nyata.
-- ============================================================

-- DATA AWAL: standar_kebersihan
-- Bersumber dari Dokumen Standar Mutu Kebersihan Yayasan Am Badar
-- ============================================================

-- === KELAS ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('kelas', 1, 'Lantai',                'Bersih dari debu, noda, dan sampah; tidak licin',                        'harian'),
('kelas', 2, 'Meja dan Kursi Siswa',  'Bebas coretan, debu, dan kotoran menempel',                              'harian'),
('kelas', 3, 'Papan Tulis / Whiteboard','Bersih setelah digunakan; marker tidak kering',                        'setiap_sesi'),
('kelas', 4, 'Jendela dan Ventilasi', 'Kaca bersih, tidak berdebu; sirkulasi udara baik',                       'mingguan'),
('kelas', 5, 'Dinding dan Langit-langit','Bebas coretan, sarang laba-laba, dan noda',                           'bulanan'),
('kelas', 6, 'Tempat Sampah',         'Tersedia, bertutup, tidak meluap, dibersihkan rutin',                    'harian'),
('kelas', 7, 'Tanaman / Dekorasi',    'Terawat, tidak layu, pot bersih',                                        'mingguan');

-- === TOILET ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('toilet', 1, 'Lantai',              'Tidak licin, tidak berbau, tidak tergenang air',                          'harian'),
('toilet', 2, 'Kloset / Urinoir',    'Bebas kerak, tidak berbau, sikat tersedia',                               'harian'),
('toilet', 3, 'Wastafel',            'Bersih, tidak berkarat, sabun tangan tersedia',                           'harian'),
('toilet', 4, 'Dinding & Sekat',     'Bebas lumut, noda, dan coretan',                                          'mingguan'),
('toilet', 5, 'Cermin',              'Bersih, tidak retak',                                                     'harian'),
('toilet', 6, 'Ventilasi / Exhaust', 'Berfungsi, tidak berdebu',                                                'bulanan'),
('toilet', 7, 'Ketersediaan Air',    'Mengalir lancar, bak bersih, tidak jentik nyamuk',                        'harian'),
('toilet', 8, 'Tempat Sampah',       'Tertutup, plastik sampah diganti rutin',                                  'harian');

-- === LAB IPA ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('lab_ipa', 1, 'Meja & Peralatan Lab',  'Bersih dari zat kimia, bebas noda, tersimpan rapi',                   'setiap_pakai'),
('lab_ipa', 2, 'Lantai Lab',            'Bebas tumpahan, tidak licin, kering',                                  'harian'),
('lab_ipa', 3, 'Lemari Alat & Bahan',   'Terorganisir, berlabel, terkunci, bebas debu',                        'mingguan'),
('lab_ipa', 4, 'Tempat Cuci (Sink)',    'Tidak tersumbat, bebas noda reaktif, sabun tersedia',                  'harian'),
('lab_ipa', 5, 'Sirkulasi Udara',       'Jendela/ventilasi berfungsi baik; tidak pengap',                      'harian'),
('lab_ipa', 6, 'Pengelolaan Limbah',    'Limbah kimia dibuang sesuai SOP; tidak dibuang sembarangan',          'setiap_pakai');

-- === LAB KOMPUTER ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('lab_komputer', 1, 'Unit Komputer & Monitor','Bebas debu, layar bersih, kabel teratur',                       'mingguan'),
('lab_komputer', 2, 'Meja & Kursi Lab',       'Bersih, tidak ada coretan, kaki kursi tidak rusak',             'harian'),
('lab_komputer', 3, 'Lantai',                 'Bebas debu, tidak ada kabel berserakan',                        'harian'),
('lab_komputer', 4, 'AC / Pendingin Ruangan', 'Filter dibersihkan, suhu optimal (20–25°C)',                    'bulanan'),
('lab_komputer', 5, 'Dinding & Pintu',        'Bebas coretan, tidak lembap',                                   'bulanan'),
('lab_komputer', 6, 'Tempat Sampah',          'Tersedia, tidak ada makanan/minuman di ruang lab',              'harian');

-- === SELASAR ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('selasar', 1, 'Lantai Selasar',     'Bersih, tidak licin, tidak ada genangan air',                            'harian'),
('selasar', 2, 'Dinding Koridor',    'Bebas coretan, majalah dinding terpasang rapi',                          'mingguan'),
('selasar', 3, 'Tanaman Hias / Pot', 'Terawat, pot bersih, tidak menghalangi jalan',                           'mingguan'),
('selasar', 4, 'Papan Informasi',    'Konten terkini, tidak kusam, tidak sobek',                               'bulanan'),
('selasar', 5, 'Pencahayaan',        'Lampu berfungsi, area terang',                                           'bulanan'),
('selasar', 6, 'Aliran Drainase',    'Saluran tidak tersumbat, tidak berbau',                                  'bulanan');

-- === LAPANGAN ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('lapangan', 1, 'Area Upacara / Lapangan','Bebas sampah, rumput terpangkas, garis lapangan jelas',             'mingguan'),
('lapangan', 2, 'Taman & Tanaman',        'Dipangkas rapi, bebas gulma, dirawat',                             'mingguan'),
('lapangan', 3, 'Tempat Sampah Luar',     'Tersedia di titik strategis, tidak meluap',                        'harian'),
('lapangan', 4, 'Saluran Air / Got',      'Tidak tersumbat, tidak berbau, tidak ada sampah',                  'mingguan'),
('lapangan', 5, 'Area Parkir',            'Tertata, tidak ada genangan oli, marka jelas',                     'mingguan'),
('lapangan', 6, 'Pagar & Gerbang',        'Bersih, tidak berkarat, terkunci saat diperlukan',                 'bulanan');

-- === GUDANG ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('gudang', 1, 'Penataan Barang',     'Terorganisir, berlabel, mudah diakses, tidak menumpuk sembarangan',      'bulanan'),
('gudang', 2, 'Lantai Gudang',       'Bebas debu tebal, tidak lembap, tidak licin',                           'bulanan'),
('gudang', 3, 'Ventilasi',           'Sirkulasi udara memadai, tidak pengap',                                  'bulanan'),
('gudang', 4, 'Pengendalian Hama',   'Bebas tikus, kecoa, serangga; perangkap tersedia jika perlu',           'bulanan'),
('gudang', 5, 'Barang Tidak Terpakai','Dikelola/dieliminasi; tidak dibiarkan menumpuk',                       'semesteran');

-- === KANTOR ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('kantor', 1, 'Meja Kerja',              'Rapi, bebas tumpukan dokumen tidak perlu, bebas debu',              'harian'),
('kantor', 2, 'Perangkat Elektronik',    'Monitor, keyboard, mouse bebas debu dan kotoran',                   'mingguan'),
('kantor', 3, 'Lantai Ruang Kantor',     'Disapu dan dipel; tidak ada sampah berserakan',                     'harian'),
('kantor', 4, 'Lemari & Rak Dokumen',    'Teratur, berlabel, tidak berdebu',                                  'mingguan'),
('kantor', 5, 'AC & Sirkulasi Udara',    'Filter bersih, tidak berbau; ruangan sejuk',                       'bulanan'),
('kantor', 6, 'Tempat Sampah',           'Dikosongkan setiap hari, dibersihkan rutin',                        'harian');

-- === RUANG RAPAT ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('ruang_rapat', 1, 'Meja & Kursi Rapat', 'Ditata rapi sebelum dan setelah rapat, bebas noda',                 'setiap_pakai'),
('ruang_rapat', 2, 'Lantai',             'Bersih, tidak ada sisa makanan/minuman',                            'setiap_pakai'),
('ruang_rapat', 3, 'Papan Tulis / Proyektor','Layar bersih, marker tersedia, tidak ada sisa tulisan',         'setiap_pakai'),
('ruang_rapat', 4, 'Sirkulasi Udara',    'AC dan ventilasi berfungsi optimal',                                'bulanan');

-- === RUANG IBADAH ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('ruang_ibadah', 1, 'Karpet / Sajadah',        'Bersih dari debu, bebas noda, rapi tersusun, wangi',         'harian'),
('ruang_ibadah', 2, 'Lantai (non-karpet)',      'Disapu, dipel; tidak ada kerikil/kotoran',                   'harian'),
('ruang_ibadah', 3, 'Dinding & Mihrab',         'Bebas debu, sarang laba-laba; kaligrafi terjaga',            'mingguan'),
('ruang_ibadah', 4, 'Kipas Angin / AC',         'Bersih, berfungsi, tidak berisik',                          'bulanan'),
('ruang_ibadah', 5, 'Lemari Al-Quran & Mukena', 'Rapi, terlipat, tidak lembap, dibersihkan rutin',           'mingguan'),
('ruang_ibadah', 6, 'Pencahayaan',              'Terang, lampu berfungsi, tidak redup berlebihan',            'bulanan'),
('ruang_ibadah', 7, 'Pengeras Suara',           'Bersih dari debu, kabel teratur',                           'bulanan');

-- === TEMPAT WUDHU ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('tempat_wudhu', 1, 'Lantai Tempat Wudhu',    'Tidak licin, tidak berlumut, drainase lancar',                 'harian'),
('tempat_wudhu', 2, 'Kran & Saluran Air',     'Berfungsi, tidak bocor, tidak berkarat',                      'mingguan'),
('tempat_wudhu', 3, 'Bak Penampung',          'Tidak berjentik, tidak berlumut, dibersihkan rutin',          'mingguan'),
('tempat_wudhu', 4, 'Dinding',                'Bebas lumut, tidak kusam, bersih',                            'bulanan'),
('tempat_wudhu', 5, 'Alas Kaki / Rak Sandal', 'Tersedia, tertata, bersih',                                   'harian');

-- === AREA LUAR ===
INSERT INTO `standar_kebersihan` (`tipe_ruangan`, `no_urut`, `aspek`, `deskripsi`, `frekuensi`) VALUES
('area_luar', 1, 'Teras & Tangga',      'Disapu rutin, tidak berlumut, tidak licin',                         'harian'),
('area_luar', 2, 'Rak Sepatu / Sandal', 'Tertata, dibersihkan; tidak ada alas kaki berserakan',              'harian'),
('area_luar', 3, 'Halaman / Taman',     'Bebas sampah, tanaman terawat, tidak ada genangan',                 'mingguan'),
('area_luar', 4, 'Saluran Air',         'Tidak tersumbat, tidak berbau',                                     'mingguan');

-- ============================================================
-- DATA AWAL: settings
-- ============================================================
INSERT INTO `settings` (`key_name`, `value`, `label`, `group_name`) VALUES
('app_name',          'Inspeksi Kebersihan Al-Wildan Islamic School 8 Kemang Bogor', 'Nama Aplikasi',           'general'),
('app_logo',          '',                               'Logo Aplikasi',           'general'),
('app_url',           'http://localhost:8000',          'URL Aplikasi',            'general'),
('timezone',          'Asia/Jakarta',                   'Timezone',                'general'),
('score_bersih',      '100',                            'Skor: Bersih',            'scoring'),
('score_kurang',      '70',                             'Skor: Kurang Bersih',     'scoring'),
('score_kotor',       '30',                             'Skor: Kotor',             'scoring'),
('threshold_alert',   '50',                             'Threshold Alert (rata-rata skor)','scoring'),
('smtp_host',         'mail.hostinger.com',             'SMTP Host',               'email'),
('smtp_port',         '465',                            'SMTP Port',               'email'),
('smtp_user',         '',                               'SMTP Username',           'email'),
('smtp_pass',         '',                               'SMTP Password',           'email'),
('smtp_from',         'noreply@alwildan.sch.id',        'Email Pengirim',          'email'),
('smtp_from_name',    'Sistem Inspeksi Al-Wildan 8',    'Nama Pengirim',           'email'),
('upload_max_size',   '2048',                           'Maks Upload (KB)',         'upload'),
('upload_allowed',    'jpg,jpeg,png,webp',              'Ekstensi Diizinkan',      'upload'),
('pagination_limit',  '15',                             'Item Per Halaman',        'general'),
('session_lifetime',  '7200',                           'Session Lifetime (detik)','security');

-- ============================================================
-- END OF FILE
-- ============================================================

-- ============================================================
-- TAMBAHAN TAHAP 6: ruangan_checklist
-- Override/tambahan checklist spesifik per ruangan
-- Jika tabel ini kosong untuk ruangan X, sistem fallback ke
-- standar_kebersihan berdasarkan tipe ruangan X
-- ============================================================
CREATE TABLE IF NOT EXISTS `ruangan_checklist` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ruangan_id`   INT UNSIGNED NOT NULL,
  `standar_id`   INT UNSIGNED NOT NULL,
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ruangan_standar` (`ruangan_id`, `standar_id`),
  KEY `idx_rc_ruangan` (`ruangan_id`),
  KEY `idx_rc_standar` (`standar_id`),
  CONSTRAINT `fk_rc_ruangan` FOREIGN KEY (`ruangan_id`)
    REFERENCES `ruangan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rc_standar` FOREIGN KEY (`standar_id`)
    REFERENCES `standar_kebersihan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
