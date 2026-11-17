-- Patch Tahap 6: Tambah tabel ruangan_checklist
-- Jalankan ini jika database.sql sudah diimport sebelumnya
-- (Jika pakai database.sql terbaru, tabel ini sudah termasuk)

USE `inspeksi_kebersihan`;

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
