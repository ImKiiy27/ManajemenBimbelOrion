-- ============================================================
-- Bimbel Orion - Database Schema
-- Dibuat dari analisis query pada model/repository/service.
-- Target: MySQL / MariaDB (utf8mb4)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS absensi_audit;
DROP TABLE IF EXISTS absensi;
DROP TABLE IF EXISTS pendaftaran_mapel;
DROP TABLE IF EXISTS pendaftaran;
DROP TABLE IF EXISTS nilai;
DROP TABLE IF EXISTS jadwal;
DROP TABLE IF EXISTS kelas;
DROP TABLE IF EXISTS siswa_mapel;
DROP TABLE IF EXISTS siswa;
DROP TABLE IF EXISTS guru;
DROP TABLE IF EXISTS wali_murid;
DROP TABLE IF EXISTS mapel;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS id_counter;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- 1) Counter ID custom (dipakai IdCounterModel)
-- ------------------------------------------------------------
CREATE TABLE id_counter (
  tabel VARCHAR(64) NOT NULL,
  prefix VARCHAR(10) NOT NULL,
  last_id INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (tabel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 2) Users (auth utama)
-- ------------------------------------------------------------
CREATE TABLE users (
  id CHAR(6) NOT NULL,
  email VARCHAR(191) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','guru','siswa','wali_murid') NOT NULL,
  is_locked TINYINT(1) NOT NULL DEFAULT 0,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_users_email (email),
  KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 3) Master mapel
-- ------------------------------------------------------------
CREATE TABLE mapel (
  id CHAR(6) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  deskripsi TEXT DEFAULT NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_mapel_nama (nama),
  KEY idx_mapel_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 4) Profil wali murid
-- Catatan:
-- Kolom id TIDAK dipasang FK ke users.id karena di kode saat ini
-- admin dapat membuat data wali_murid tanpa akun user.
-- ------------------------------------------------------------
CREATE TABLE wali_murid (
  id CHAR(6) NOT NULL,
  nama VARCHAR(150) NOT NULL,
  no_telp VARCHAR(20) DEFAULT NULL,
  hubungan VARCHAR(50) DEFAULT NULL,
  pekerjaan VARCHAR(100) DEFAULT NULL,
  alamat TEXT DEFAULT NULL,
  foto_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_wali_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 5) Profil guru
-- ------------------------------------------------------------
CREATE TABLE guru (
  id CHAR(6) NOT NULL,
  mapel_id CHAR(6) DEFAULT NULL,
  nama VARCHAR(150) NOT NULL,
  no_telp VARCHAR(20) DEFAULT NULL,
  alamat TEXT DEFAULT NULL,
  foto_path VARCHAR(255) DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_guru_mapel (mapel_id),
  KEY idx_guru_nama (nama),
  CONSTRAINT fk_guru_user
    FOREIGN KEY (id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_guru_mapel
    FOREIGN KEY (mapel_id) REFERENCES mapel(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 6) Profil siswa
-- ------------------------------------------------------------
CREATE TABLE siswa (
  id CHAR(6) NOT NULL,
  nama VARCHAR(150) NOT NULL,
  kelas_sekolah VARCHAR(50) DEFAULT 'Privat',
  asal_sekolah VARCHAR(150) DEFAULT NULL,
  alamat TEXT DEFAULT NULL,
  no_telp VARCHAR(20) DEFAULT NULL,
  foto_path VARCHAR(255) DEFAULT NULL,
  wali_id CHAR(6) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_siswa_wali (wali_id),
  KEY idx_siswa_nama (nama),
  CONSTRAINT fk_siswa_user
    FOREIGN KEY (id) REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_siswa_wali
    FOREIGN KEY (wali_id) REFERENCES wali_murid(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 7) Relasi siswa dengan mapel yang diambil
-- ------------------------------------------------------------
CREATE TABLE siswa_mapel (
  id CHAR(6) NOT NULL,
  siswa_id CHAR(6) NOT NULL,
  mapel_id CHAR(6) NOT NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_siswa_mapel_pair (siswa_id, mapel_id),
  KEY idx_siswa_mapel_status (status),
  KEY idx_siswa_mapel_mapel (mapel_id),
  CONSTRAINT fk_siswa_mapel_siswa
    FOREIGN KEY (siswa_id) REFERENCES siswa(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_siswa_mapel_mapel
    FOREIGN KEY (mapel_id) REFERENCES mapel(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 8) Relasi pengajar siswa (kelas)
-- ------------------------------------------------------------
CREATE TABLE kelas (
  id CHAR(6) NOT NULL,
  siswa_id CHAR(6) NOT NULL,
  guru_id CHAR(6) NOT NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_kelas_siswa_guru (siswa_id, guru_id),
  KEY idx_kelas_status (status),
  KEY idx_kelas_guru (guru_id),
  CONSTRAINT fk_kelas_siswa
    FOREIGN KEY (siswa_id) REFERENCES siswa(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_kelas_guru
    FOREIGN KEY (guru_id) REFERENCES guru(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 9) Jadwal pembelajaran
-- ------------------------------------------------------------
CREATE TABLE jadwal (
  id CHAR(6) NOT NULL,
  kelas_id CHAR(6) NOT NULL,
  hari ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  jam_mulai TIME NOT NULL,
  jam_selesai TIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_jadwal_kelas_hari_jam (kelas_id, hari, jam_mulai),
  KEY idx_jadwal_hari_jam (hari, jam_mulai),
  CONSTRAINT fk_jadwal_kelas
    FOREIGN KEY (kelas_id) REFERENCES kelas(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 10) Nilai siswa
-- Catatan:
-- Kolom "predikat" dipakai sebagai skor numerik 0-100 di form guru.
-- ------------------------------------------------------------
CREATE TABLE nilai (
  id CHAR(6) NOT NULL,
  jadwal_id CHAR(6) NOT NULL,
  pertemuan_ke INT UNSIGNED NOT NULL DEFAULT 1,
  tipe_nilai ENUM('utama','susulan','remedial') NOT NULL DEFAULT 'utama',
  predikat DECIMAL(5,2) NOT NULL,
  catatan_guru TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_nilai_jadwal_pertemuan_tipe (jadwal_id, pertemuan_ke, tipe_nilai),
  KEY idx_nilai_jadwal (jadwal_id),
  CONSTRAINT fk_nilai_jadwal
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 11) Pendaftaran calon siswa (public)
-- ------------------------------------------------------------
CREATE TABLE pendaftaran (
  id CHAR(6) NOT NULL,
  nama VARCHAR(150) NOT NULL,
  email VARCHAR(191) NOT NULL,
  telepon VARCHAR(20) NOT NULL,
  kelas_sekolah VARCHAR(50) DEFAULT 'Privat',
  program VARCHAR(50) DEFAULT NULL,
  status ENUM('pending','diproses','diterima','ditolak') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pendaftaran_email (email),
  KEY idx_pendaftaran_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 12) Mapel yang dipilih pada pendaftaran
-- ------------------------------------------------------------
CREATE TABLE pendaftaran_mapel (
  id CHAR(6) NOT NULL,
  pendaftaran_id CHAR(6) NOT NULL,
  mapel_id CHAR(6) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_pendaftaran_mapel_pair (pendaftaran_id, mapel_id),
  KEY idx_pendaftaran_mapel_mapel (mapel_id),
  CONSTRAINT fk_pendaftaran_mapel_pendaftaran
    FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_pendaftaran_mapel_mapel
    FOREIGN KEY (mapel_id) REFERENCES mapel(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 13) Absensi
-- ------------------------------------------------------------
CREATE TABLE absensi (
  id CHAR(6) NOT NULL,
  jadwal_id CHAR(6) NOT NULL,
  tanggal DATE NOT NULL,
  siswa_id CHAR(6) NOT NULL,
  status ENUM('Hadir','Izin','Sakit','Alpa') NOT NULL DEFAULT 'Hadir',
  alasan TEXT DEFAULT NULL,
  catatan_guru TEXT DEFAULT NULL,
  updated_by CHAR(6) NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_jadwal_tanggal_siswa (jadwal_id, tanggal, siswa_id),
  KEY idx_absensi_jadwal_tanggal (jadwal_id, tanggal),
  KEY idx_absensi_siswa_tanggal (siswa_id, tanggal),
  KEY idx_absensi_updated_by (updated_by),
  KEY idx_absensi_status (status),
  CONSTRAINT fk_absensi_jadwal
    FOREIGN KEY (jadwal_id) REFERENCES jadwal(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_absensi_siswa
    FOREIGN KEY (siswa_id) REFERENCES siswa(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_absensi_updated_by
    FOREIGN KEY (updated_by) REFERENCES users(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- 14) Audit perubahan absensi
-- ------------------------------------------------------------
CREATE TABLE absensi_audit (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  absensi_id CHAR(6) NOT NULL,
  old_status ENUM('Hadir','Izin','Sakit','Alpa') DEFAULT NULL,
  new_status ENUM('Hadir','Izin','Sakit','Alpa') DEFAULT NULL,
  old_alasan TEXT DEFAULT NULL,
  new_alasan TEXT DEFAULT NULL,
  changed_by CHAR(6) NOT NULL,
  changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  action_type ENUM('INSERT','UPDATE','CORRECTION','DELETE','CORRECTION_REASON') NOT NULL,
  reason TEXT DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_absensi_audit_absensi (absensi_id),
  KEY idx_absensi_audit_changed_by (changed_by),
  KEY idx_absensi_audit_changed_at (changed_at),
  CONSTRAINT fk_absensi_audit_absensi
    FOREIGN KEY (absensi_id) REFERENCES absensi(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_absensi_audit_changed_by
    FOREIGN KEY (changed_by) REFERENCES users(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

