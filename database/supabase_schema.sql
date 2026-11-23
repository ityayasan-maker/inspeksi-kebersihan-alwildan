-- ============================================================
-- SUPABASE POSTGRESQL SCHEMA
-- Sistem Inspeksi Kebersihan Al-Wildan Islamic School 8 Kemang Bogor
-- Developer: Yayasan Ambadar
-- ============================================================

-- 1. users
CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'petugas',
  phone VARCHAR(20) DEFAULT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_active ON users(is_active);

-- 2. gedung
CREATE TABLE IF NOT EXISTS gedung (
  id SERIAL PRIMARY KEY,
  kode VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  unit VARCHAR(50) NOT NULL,
  keterangan TEXT DEFAULT NULL,
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_gedung_unit ON gedung(unit);

-- 3. lantai
CREATE TABLE IF NOT EXISTS lantai (
  id SERIAL PRIMARY KEY,
  gedung_id INT NOT NULL REFERENCES gedung(id) ON DELETE CASCADE ON UPDATE CASCADE,
  nama VARCHAR(50) NOT NULL,
  urutan SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_lantai_gedung ON lantai(gedung_id);

-- 4. ruangan
CREATE TABLE IF NOT EXISTS ruangan (
  id SERIAL PRIMARY KEY,
  lantai_id INT NOT NULL REFERENCES lantai(id) ON DELETE CASCADE ON UPDATE CASCADE,
  kode VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  tipe VARCHAR(50) NOT NULL DEFAULT 'lainnya',
  zona VARCHAR(100) DEFAULT NULL,
  assigned_user_id INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  luas_m2 NUMERIC(8,2) DEFAULT NULL,
  keterangan TEXT DEFAULT NULL,
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_ruangan_lantai ON ruangan(lantai_id);
CREATE INDEX IF NOT EXISTS idx_ruangan_tipe ON ruangan(tipe);
CREATE INDEX IF NOT EXISTS idx_ruangan_assigned ON ruangan(assigned_user_id);

-- 5. standar_kebersihan
CREATE TABLE IF NOT EXISTS standar_kebersihan (
  id SERIAL PRIMARY KEY,
  tipe_ruangan VARCHAR(50) NOT NULL,
  no_urut SMALLINT NOT NULL DEFAULT 1,
  aspek VARCHAR(100) NOT NULL,
  deskripsi TEXT NOT NULL,
  frekuensi VARCHAR(20) NOT NULL DEFAULT 'harian',
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_standar_tipe ON standar_kebersihan(tipe_ruangan);

-- 6. ruangan_checklist
CREATE TABLE IF NOT EXISTS ruangan_checklist (
  id SERIAL PRIMARY KEY,
  ruangan_id INT NOT NULL REFERENCES ruangan(id) ON DELETE CASCADE ON UPDATE CASCADE,
  standar_id INT NOT NULL REFERENCES standar_kebersihan(id) ON DELETE CASCADE ON UPDATE CASCADE,
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (ruangan_id, standar_id)
);
CREATE INDEX IF NOT EXISTS idx_rc_ruangan ON ruangan_checklist(ruangan_id);
CREATE INDEX IF NOT EXISTS idx_rc_standar ON ruangan_checklist(standar_id);

-- 7. inspeksi
CREATE TABLE IF NOT EXISTS inspeksi (
  id SERIAL PRIMARY KEY,
  nomor_inspeksi VARCHAR(50) NOT NULL UNIQUE,
  ruangan_id INT NOT NULL REFERENCES ruangan(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  inspected_by_id INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  assigned_petugas_id INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  tanggal DATE NOT NULL,
  waktu_mulai TIME DEFAULT NULL,
  waktu_selesai TIME DEFAULT NULL,
  sesi VARCHAR(20) NOT NULL DEFAULT 'pagi',
  skor_total NUMERIC(5,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(20) NOT NULL DEFAULT 'kotor',
  catatan TEXT DEFAULT NULL,
  catatan_umum TEXT DEFAULT NULL,
  foto_ruangan VARCHAR(255) DEFAULT NULL,
  is_draft SMALLINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_inspeksi_ruangan ON inspeksi(ruangan_id);
CREATE INDEX IF NOT EXISTS idx_inspeksi_petugas ON inspeksi(inspected_by_id);
CREATE INDEX IF NOT EXISTS idx_inspeksi_tanggal ON inspeksi(tanggal);
CREATE INDEX IF NOT EXISTS idx_inspeksi_sesi ON inspeksi(sesi);
CREATE INDEX IF NOT EXISTS idx_inspeksi_status ON inspeksi(status);
CREATE INDEX IF NOT EXISTS idx_inspeksi_tanggal ON inspeksi(tanggal);
CREATE INDEX IF NOT EXISTS idx_inspeksi_status ON inspeksi(status);

-- 8. inspeksi_detail
CREATE TABLE IF NOT EXISTS inspeksi_detail (
  id SERIAL PRIMARY KEY,
  inspeksi_id INT NOT NULL REFERENCES inspeksi(id) ON DELETE CASCADE ON UPDATE CASCADE,
  standar_id INT NOT NULL REFERENCES standar_kebersihan(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  skor SMALLINT NOT NULL,
  kondisi VARCHAR(20) NOT NULL,
  catatan TEXT DEFAULT NULL,
  foto VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (inspeksi_id, standar_id)
);
CREATE INDEX IF NOT EXISTS idx_id_inspeksi ON inspeksi_detail(inspeksi_id);
CREATE INDEX IF NOT EXISTS idx_id_standar ON inspeksi_detail(standar_id);

-- 9. notifications
CREATE TABLE IF NOT EXISTS notifications (
  id SERIAL PRIMARY KEY,
  user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(20) NOT NULL DEFAULT 'info',
  is_read SMALLINT NOT NULL DEFAULT 0,
  sent_email SMALLINT NOT NULL DEFAULT 0,
  data JSONB DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_notif_user ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notif_read ON notifications(is_read);

-- 10. activity_logs
CREATE TABLE IF NOT EXISTS activity_logs (
  id SERIAL PRIMARY KEY,
  user_id INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
  action VARCHAR(50) NOT NULL,
  module VARCHAR(50) NOT NULL,
  target_id INT DEFAULT NULL,
  description TEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_al_user ON activity_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_al_module ON activity_logs(module);
CREATE INDEX IF NOT EXISTS idx_al_created ON activity_logs(created_at);

-- 11. settings
CREATE TABLE IF NOT EXISTS settings (
  key_name VARCHAR(50) PRIMARY KEY,
  value TEXT DEFAULT NULL,
  label VARCHAR(100) NOT NULL,
  group_name VARCHAR(30) NOT NULL DEFAULT 'general',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 12. jadwal_kebersihan (Target foto: Sebelum Jam 09:00 Pagi & Sebelum Jam 14:00 Siang)
CREATE TABLE IF NOT EXISTS jadwal_kebersihan (
  id SERIAL PRIMARY KEY,
  petugas_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  ruangan_id INT NOT NULL REFERENCES ruangan(id) ON DELETE CASCADE,
  sesi VARCHAR(20) NOT NULL DEFAULT 'pagi',
  hari VARCHAR(20) DEFAULT 'setiap_hari',
  jam_mulai TIME DEFAULT '06:30',
  jam_batas TIME NOT NULL DEFAULT '09:00',
  catatan TEXT DEFAULT NULL,
  is_active SMALLINT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_jadwal_petugas ON jadwal_kebersihan(petugas_id);
CREATE INDEX IF NOT EXISTS idx_jadwal_ruangan ON jadwal_kebersihan(ruangan_id);
CREATE INDEX IF NOT EXISTS idx_jadwal_sesi ON jadwal_kebersihan(sesi);

-- DATA AWAL: Akun Super Admin Al-Wildan
INSERT INTO users (name, email, password, role, phone, is_active)
VALUES ('Administrator Al Wildan', 'admin@alwildan.sch.id', '$2y$12$/L2K2s3VEHvd527c19iIYuLGURVehTqdjMYrrcUp41j/lYj9nyMBe', 'admin', '081200000001', 1)
ON CONFLICT (email) DO NOTHING;

-- DATA AWAL: settings
INSERT INTO settings (key_name, value, label, group_name) VALUES
('app_name',          'Inspeksi Kebersihan Al-Wildan Islamic School 8 Kemang Bogor', 'Nama Aplikasi', 'general'),
('app_logo',          '', 'Logo Aplikasi', 'general'),
('app_url',           'http://localhost:8000', 'URL Aplikasi', 'general'),
('timezone',          'Asia/Jakarta', 'Timezone', 'general'),
('score_bersih',      '100', 'Skor: Bersih', 'scoring'),
('score_kurang',      '70', 'Skor: Kurang Bersih', 'scoring'),
('score_kotor',       '30', 'Skor: Kotor', 'scoring'),
('threshold_alert',   '50', 'Threshold Alert', 'scoring'),
('upload_max_size',   '2048', 'Maks Upload (KB)', 'upload'),
('upload_allowed',    'jpg,jpeg,png,webp', 'Ekstensi Diizinkan', 'upload'),
('pagination_limit',  '15', 'Item Per Halaman', 'general'),
('session_lifetime',  '7200', 'Session Lifetime (detik)', 'security')
ON CONFLICT (key_name) DO NOTHING;

-- DATA AWAL: Standar Kebersihan (70+ Mutu Kebersihan)
INSERT INTO standar_kebersihan (tipe_ruangan, no_urut, aspek, deskripsi, frekuensi) VALUES
('kelas', 1, 'Lantai', 'Disapu, dipel, tidak berdebu, bebas sampah/noda', 'harian'),
('kelas', 2, 'Meja & Kursi Siswa', 'Bersih dari coretan, debu, tersusun rapi', 'harian'),
('kelas', 3, 'Meja & Kursi Guru', 'Rapi, bersih dari debu, dokumen tertata', 'harian'),
('kelas', 4, 'Papan Tulis / Whiteboard', 'Dibersihkan setelah KBM, spidol/penghapus tersusun', 'harian'),
('kelas', 5, 'Jendela, Kaca & Ventilasi', 'Bebas debu, sarang laba-laba, kaca bening', 'mingguan'),
('kelas', 6, 'Pintu, Kusen & Handle', 'Bebas debu, noda sidik jari, berfungsi baik', 'mingguan'),
('kelas', 7, 'Tempat Sampah', 'Ada plastik sampah, tidak penuh, dibersihkan rutin', 'harian'),
('kelas', 8, 'Dinding & Sudut Ruangan', 'Bebas noda, sarang laba-laba, coretan', 'mingguan'),
('kelas', 9, 'Kipas Angin / AC', 'Bersih dari debu tebal, filter dicuci rutin', 'bulanan'),
('kelas', 10, 'Gorden / Hordeng', 'Bersih, rapi, tidak sobek/kusam', 'bulanan'),

('toilet', 1, 'Lantai Toilet', 'Disikat, dipel, tidak licin, tidak ada genangan air', 'harian'),
('toilet', 2, 'Kloset (Duduk/Jongkok)', 'Disikat, bebas kerak/kuning, disiram bersih, wangi', 'harian'),
('toilet', 3, 'Wastafel & Cermin', 'Bersih, kran berfungsi, cermin tidak buram', 'harian'),
('toilet', 4, 'Dinding & Pintu', 'Bebas lumut, kerak, noda sabun, handle bersih', 'mingguan'),
('toilet', 5, 'Tempat Sampah Toilet', 'Tertutup, ada plastik, dikosongkan 2x sehari', 'harian'),
('toilet', 6, 'Saluran Air / Floor Drain', 'Lancar, tidak tersumbat rambut/kotoran, tidak berbau', 'harian'),
('toilet', 7, 'Sabun Cuci Tangan', 'Tersedia dan terisi', 'harian'),
('toilet', 8, 'Aroma / Bau', 'Tidak pesing/bau tidak sedap, pewangi ruangan aktif', 'harian'),

('lab_ipa', 1, 'Meja Praktikum', 'Bebas residu zat kimia/air, bersih dari debu', 'harian'),
('lab_ipa', 2, 'Lantai Lab', 'Disapu, dipel, bebas pecahan kaca/residu', 'harian'),
('lab_ipa', 3, 'Wastafel Lab & Keran', 'Bebas noda kimia, saluran lancar, tidak mampet', 'harian'),
('lab_ipa', 4, 'Lemari Alat & Bahan Kimia', 'Tertata rapi, berlabel jelas, kaca bersih', 'mingguan'),
('lab_ipa', 5, 'Ventilasi / Exhaust Fan', 'Berfungsi baik, tidak berdebu tebal', 'bulanan'),
('lab_ipa', 6, 'Tempat Sampah Khusus', 'Terpisah sampah organik/anorganik/pecah belah', 'harian'),

('lab_komputer', 1, 'Meja & Kursi Komputer', 'Bersih dari debu, keyboard & mouse tertata rapi', 'harian'),
('lab_komputer', 2, 'Lantai Lab', 'Disapu, dipel (tidak terlalu basah/lembab)', 'harian'),
('lab_komputer', 3, 'Monitor & Unit PC', 'Bebas debu, kabel teratur, tidak berserakan', 'mingguan'),
('lab_komputer', 4, 'AC & Ruangan', 'Suhu terjaga, filter bersih, tidak berbau apek', 'bulanan'),
('lab_komputer', 5, 'Tempat Sampah', 'Tersedia, tidak ada sisa makanan/minuman', 'harian'),

('selasar', 1, 'Lantai Selasar/Koridor', 'Disapu, dipel rutin, bebas sampah, tidak licin', 'harian'),
('selasar', 2, 'Railing Tangga / Pembatas', 'Bebas debu, dilap rutin, aman dan kokoh', 'harian'),
('selasar', 3, 'Dinding & Pilar', 'Bebas coretan, noda tapak kaki, sarang laba-laba', 'mingguan'),
('selasar', 4, 'Tempat Sampah Selasar', 'Dikosongkan berkala, bersih, tidak tumpah', 'harian'),
('selasar', 5, 'Lampu Koridor', 'Berfungsi, kap lampu bersih dari sarang laba-laba', 'bulanan'),

('kantor', 1, 'Meja Kerja & Komputer', 'Tertata rapi, bersih dari debu dan tumpukan dokumen', 'harian'),
('kantor', 2, 'Lantai Kantor', 'Disapu, dipel, bebas sampah, karpet divacuum', 'harian'),
('kantor', 3, 'Lemari Arsip & Rak Dokumen', 'Rapi berlabel, bebas debu di bagian atas', 'mingguan'),
('kantor', 4, 'Jendela & Kaca', 'Bebas noda/debu, gorden rapi', 'mingguan'),
('kantor', 5, 'Tempat Sampah', 'Dikosongkan setiap sore, ada plastik pelapis', 'harian'),
('kantor', 6, 'Dispenser / Pantry Kecil', 'Area bersih, tidak ada genangan air, gelas tertata', 'harian'),

('ruang_rapat', 1, 'Meja Rapat Utama', 'Permukaan bersih mengkilap, kursi tersusun rapi', 'harian'),
('ruang_rapat', 2, 'Lantai / Karpet', 'Bersih, divacuum, wangi, tidak ada noda tumpahan', 'harian'),
('ruang_rapat', 3, 'Proyektor / TV Screen', 'Bebas debu, kabel HDMI/power rapi tergulung', 'mingguan'),
('ruang_rapat', 4, 'Whiteboard / Smartboard', 'Dibersihkan setelah rapat, marker tersusun', 'harian'),
('ruang_rapat', 5, 'Aroma Ruangan', 'Harum/segar, AC dingin optimal', 'harian'),

('ruang_ibadah', 1, 'Karpet / Sajadah', 'Bersih dari debu, bebas noda, rapi tersusun, wangi', 'harian'),
('ruang_ibadah', 2, 'Lantai (non-karpet)', 'Disapu, dipel; tidak ada kerikil/kotoran', 'harian'),
('ruang_ibadah', 3, 'Dinding & Mihrab', 'Bebas debu, sarang laba-laba; kaligrafi terjaga', 'mingguan'),
('ruang_ibadah', 4, 'Kipas Angin / AC', 'Bersih, berfungsi, tidak berisik', 'bulanan'),
('ruang_ibadah', 5, 'Lemari Al-Quran & Mukena', 'Rapi, terlipat, tidak lembap, dibersihkan rutin', 'mingguan'),
('ruang_ibadah', 6, 'Pencahayaan', 'Terang, lampu berfungsi, tidak redup berlebihan', 'bulanan'),
('ruang_ibadah', 7, 'Pengeras Suara', 'Bersih dari debu, kabel teratur', 'bulanan'),

('tempat_wudhu', 1, 'Lantai Tempat Wudhu', 'Tidak licin, tidak berlumut, drainase lancar', 'harian'),
('tempat_wudhu', 2, 'Kran & Saluran Air', 'Berfungsi, tidak bocor, tidak berkarat', 'mingguan'),
('tempat_wudhu', 3, 'Bak Penampung', 'Tidak berjentik, tidak berlumut, dibersihkan rutin', 'mingguan'),
('tempat_wudhu', 4, 'Dinding', 'Bebas lumut, tidak kusam, bersih', 'bulanan'),
('tempat_wudhu', 5, 'Alas Kaki / Rak Sandal', 'Tersedia, tertata, bersih', 'harian'),

('area_luar', 1, 'Teras & Tangga', 'Disapu rutin, tidak berlumut, tidak licin', 'harian'),
('area_luar', 2, 'Rak Sepatu / Sandal', 'Tertata, dibersihkan; tidak ada alas kaki berserakan', 'harian'),
('area_luar', 3, 'Halaman / Taman', 'Bebas sampah, tanaman terawat, tidak ada genangan', 'mingguan'),
('area_luar', 4, 'Saluran Air', 'Tidak tersumbat, tidak berbau', 'mingguan'),

('gudang', 1, 'Tata Letak Barang', 'Barang tersusun rapi di rak, ada jalur jalan', 'mingguan'),
('gudang', 2, 'Lantai Gudang', 'Disapu berkala, bebas sampah basah/makanan', 'mingguan'),
('gudang', 3, 'Ventilasi & Bebas Hama', 'Tidak lembap, tidak ada sarang tikus/serangga', 'bulanan');
