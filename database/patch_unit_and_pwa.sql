-- Patch: Modifikasi kolom unit dan tambah foto ruangan
-- Jalankan query ini di phpMyAdmin pada database u491012215_inspeksi:

ALTER TABLE `gedung` MODIFY COLUMN `unit` VARCHAR(50) NOT NULL COMMENT 'smp, smk, yayasan, masjid, al_wildan, dll';

-- Tambahkan kolom foto ruangan (opsional/rekomendasi jika belum ada)
ALTER TABLE `inspeksi` ADD COLUMN IF NOT EXISTS `foto_ruangan` VARCHAR(255) DEFAULT NULL COMMENT 'Foto kondisi umum ruangan' AFTER `catatan_umum`;
