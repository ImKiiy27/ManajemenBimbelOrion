-- ============================================================
-- Bimbel Orion - Seed Data (Testing Lokal)
-- Gunakan setelah menjalankan database/schema.sql
-- ============================================================

START TRANSACTION;

-- ------------------------------------------------------------
-- Catatan keamanan password:
-- Password di bawah ini sudah berbentuk hash bcrypt (password_hash PHP).
-- Untuk produksi, ganti dengan hash baru dari:
-- php -r "echo password_hash('PasswordBaruAnda', PASSWORD_DEFAULT), PHP_EOL;"
-- ------------------------------------------------------------

-- ------------------------------------------------------------
-- 1) Akun user dasar (admin + contoh role lain)
-- ------------------------------------------------------------
INSERT INTO users (id, email, password, role, is_locked, attempts) VALUES
('ADM001', 'admin@orion.local', '$2y$10$KVyT4rrFCIhdMVCs9Qs1zOq1blL/GIYItG5EzHxIbj.XhoY874u9W', 'admin', 0, 0),
('GRU001', 'guru1@orion.local', '$2y$10$PoAQB.Yn3DZy62jyiEieTeZeuoNQWJSaPNBkiiETHzpNwy07R/Dyu', 'guru', 0, 0),
('SSW001', 'siswa1@orion.local', '$2y$10$VGzLKBYpUiD3y5OUdTB6ReszGLqqRc/t5F2RQxj.ITEd5nYVyxxWW', 'siswa', 0, 0),
('WLM001', 'wali1@orion.local', '$2y$10$QpyQPtYUNr4oZmQuLzgcRuoKn92fy1FxHgretgmxXsGNuQnm4GbUa', 'wali_murid', 0, 0);

-- ------------------------------------------------------------
-- 2) Mapel contoh
-- ------------------------------------------------------------
INSERT INTO mapel (id, nama, deskripsi, status) VALUES
('MPL001', 'Matematika', 'Mapel Matematika dasar dan lanjutan', 'aktif'),
('MPL002', 'Bahasa Inggris', 'Mapel bahasa Inggris untuk siswa', 'aktif'),
('MPL003', 'Privat', 'Program belajar privat', 'aktif');

-- ------------------------------------------------------------
-- 3) Data wali murid contoh
-- ------------------------------------------------------------
INSERT INTO wali_murid (id, nama, no_telp, hubungan, pekerjaan, alamat) VALUES
('WLM001', 'Budi Santoso', '081234567890', 'Ayah', 'Karyawan Swasta', 'Jember');

-- ------------------------------------------------------------
-- 4) Data guru contoh
-- ------------------------------------------------------------
INSERT INTO guru (id, mapel_id, nama, no_telp, alamat, bio) VALUES
('GRU001', 'MPL001', 'Andi Pratama', '081200001111', 'Jember', 'Guru Matematika');

-- ------------------------------------------------------------
-- 5) Data siswa contoh
-- ------------------------------------------------------------
INSERT INTO siswa (id, nama, kelas_sekolah, asal_sekolah, alamat, no_telp, wali_id) VALUES
('SSW001', 'Citra Lestari', '10 SMA', 'SMA Negeri 1 Jember', 'Jember', '081300002222', 'WLM001');

-- ------------------------------------------------------------
-- Relasi pembelajaran contoh (agar fitur jadwal/nilai/absensi bisa dites)
-- ------------------------------------------------------------
INSERT INTO siswa_mapel (id, siswa_id, mapel_id, status) VALUES
('SMP001', 'SSW001', 'MPL001', 'aktif'),
('SMP002', 'SSW001', 'MPL002', 'aktif');

INSERT INTO kelas (id, siswa_id, guru_id, status) VALUES
('KLS001', 'SSW001', 'GRU001', 'aktif');

INSERT INTO jadwal (id, kelas_id, hari, jam_mulai, jam_selesai) VALUES
('JDL001', 'KLS001', 'Senin', '15:00:00', '16:30:00');

INSERT INTO nilai (id, jadwal_id, pertemuan_ke, tipe_nilai, predikat, catatan_guru) VALUES
('NLI001', 'JDL001', 1, 'utama', 88.50, 'Nilai awal sangat baik');

-- Counter awal agar generator ID berikutnya tidak bentrok
INSERT INTO id_counter (tabel, prefix, last_id) VALUES
('users', 'ADM', 1),
('guru', 'GRU', 1),
('siswa', 'SSW', 1),
('wali_murid', 'WLM', 1),
('mapel', 'MPL', 3),
('siswa_mapel', 'SMP', 2),
('kelas', 'KLS', 1),
('jadwal', 'JDL', 1),
('nilai', 'NLI', 1),
('pendaftaran', 'PDT', 0),
('pendaftaran_mapel', 'PMA', 0),
('absensi', 'ABS', 0)
ON DUPLICATE KEY UPDATE
  prefix = VALUES(prefix),
  last_id = VALUES(last_id);

COMMIT;

