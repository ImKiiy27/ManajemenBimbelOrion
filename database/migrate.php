<?php

// ============================================================
// database/migrate.php
// Migration runner + schema upgrader (absensi)
// ============================================================

require_once __DIR__ . '/../config/database.php';

function out(string $message): void {
  echo $message . PHP_EOL;
}

function tableExists(PDO $db, string $table): bool {
  $stmt = $db->prepare("
    SELECT COUNT(*)
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = ?
  ");
  $stmt->execute([$table]);
  return (int)$stmt->fetchColumn() > 0;
}

function columnExists(PDO $db, string $table, string $column): bool {
  $stmt = $db->prepare("
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = ?
      AND COLUMN_NAME = ?
  ");
  $stmt->execute([$table, $column]);
  return (int)$stmt->fetchColumn() > 0;
}

function indexExists(PDO $db, string $table, string $index): bool {
  $stmt = $db->prepare("
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = ?
      AND INDEX_NAME = ?
  ");
  $stmt->execute([$table, $index]);
  return (int)$stmt->fetchColumn() > 0;
}

function constraintExists(PDO $db, string $constraint): bool {
  $stmt = $db->prepare("
    SELECT COUNT(*)
    FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND CONSTRAINT_NAME = ?
  ");
  $stmt->execute([$constraint]);
  return (int)$stmt->fetchColumn() > 0;
}

function hasForeignKeyOnColumn(PDO $db, string $table, string $column): bool {
  $stmt = $db->prepare("
    SELECT COUNT(*)
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = ?
      AND COLUMN_NAME = ?
      AND REFERENCED_TABLE_NAME IS NOT NULL
  ");
  $stmt->execute([$table, $column]);
  return (int)$stmt->fetchColumn() > 0;
}

function execSql(PDO $db, string $sql): void {
  $db->exec($sql);
}

function ensureCounterAbsensi(PDO $db): void {
  $stmt = $db->prepare("SELECT tabel FROM id_counter WHERE tabel = ? LIMIT 1");
  $stmt->execute(['absensi']);
  if ($stmt->fetchColumn()) {
    $upd = $db->prepare("UPDATE id_counter SET prefix = ? WHERE tabel = ?");
    $upd->execute(['ABS', 'absensi']);
    return;
  }

  $ins = $db->prepare("INSERT INTO id_counter (tabel, prefix, last_id) VALUES (?, ?, 0)");
  $ins->execute(['absensi', 'ABS']);
}

function ensureAbsensiAuditTable(PDO $db): void {
  if (!tableExists($db, 'absensi_audit')) {
    execSql($db, "
      CREATE TABLE `absensi_audit` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `absensi_id` char(6) NOT NULL,
        `old_status` enum('Hadir','Izin','Sakit','Alpa') DEFAULT NULL,
        `new_status` enum('Hadir','Izin','Sakit','Alpa') DEFAULT NULL,
        `old_alasan` text DEFAULT NULL,
        `new_alasan` text DEFAULT NULL,
        `changed_by` char(6) NOT NULL,
        `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `action_type` enum('INSERT','UPDATE','CORRECTION','DELETE','CORRECTION_REASON') NOT NULL,
        `reason` text DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_absensi_id` (`absensi_id`),
        KEY `idx_changed_at` (`changed_at`),
        KEY `idx_changed_by` (`changed_by`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
  } else {
    if (!columnExists($db, 'absensi_audit', 'reason')) {
      execSql($db, "ALTER TABLE `absensi_audit` ADD COLUMN `reason` text DEFAULT NULL AFTER `action_type`");
    }

    if (columnExists($db, 'absensi_audit', 'action_type')) {
      execSql($db, "
        ALTER TABLE `absensi_audit`
        MODIFY COLUMN `action_type` enum('INSERT','UPDATE','CORRECTION','DELETE','CORRECTION_REASON') NOT NULL
      ");
    }

    if (!indexExists($db, 'absensi_audit', 'idx_absensi_id')) {
      execSql($db, "ALTER TABLE `absensi_audit` ADD KEY `idx_absensi_id` (`absensi_id`)");
    }
    if (!indexExists($db, 'absensi_audit', 'idx_changed_at')) {
      execSql($db, "ALTER TABLE `absensi_audit` ADD KEY `idx_changed_at` (`changed_at`)");
    }
    if (!indexExists($db, 'absensi_audit', 'idx_changed_by')) {
      execSql($db, "ALTER TABLE `absensi_audit` ADD KEY `idx_changed_by` (`changed_by`)");
    }
  }

  if (!hasForeignKeyOnColumn($db, 'absensi_audit', 'absensi_id')
      && !constraintExists($db, 'fk_absensi_audit_absensi')) {
    execSql($db, "
      ALTER TABLE `absensi_audit`
      ADD CONSTRAINT `fk_absensi_audit_absensi`
      FOREIGN KEY (`absensi_id`) REFERENCES `absensi` (`id`) ON DELETE CASCADE
    ");
  }

  if (!hasForeignKeyOnColumn($db, 'absensi_audit', 'changed_by')
      && !constraintExists($db, 'fk_absensi_audit_changed_by')) {
    execSql($db, "
      ALTER TABLE `absensi_audit`
      ADD CONSTRAINT `fk_absensi_audit_changed_by`
      FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
    ");
  }
}

function ensureAbsensiSchema(PDO $db): void {
  if (!tableExists($db, 'absensi')) {
    execSql($db, "
      CREATE TABLE `absensi` (
        `id` char(6) NOT NULL,
        `jadwal_id` char(6) NOT NULL,
        `tanggal` date NOT NULL,
        `siswa_id` char(6) NOT NULL,
        `status` enum('Hadir','Izin','Sakit','Alpa') NOT NULL DEFAULT 'Hadir',
        `alasan` text DEFAULT NULL,
        `catatan_guru` text DEFAULT NULL,
        `updated_by` char(6) NOT NULL,
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_jadwal_tanggal_siswa` (`jadwal_id`,`tanggal`,`siswa_id`),
        KEY `idx_jadwal_tanggal` (`jadwal_id`,`tanggal`),
        KEY `idx_siswa_tanggal` (`siswa_id`,`tanggal`),
        KEY `idx_updated_at` (`updated_at`),
        KEY `idx_status` (`status`),
        CONSTRAINT `fk_absensi_jadwal` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_absensi_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_absensi_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    return;
  }

  if (!columnExists($db, 'absensi', 'siswa_id')) {
    execSql($db, "ALTER TABLE `absensi` ADD COLUMN `siswa_id` char(6) NULL AFTER `tanggal`");
  }
  if (!columnExists($db, 'absensi', 'status')) {
    execSql($db, "ALTER TABLE `absensi` ADD COLUMN `status` enum('Hadir','Izin','Sakit','Alpa') NULL AFTER `siswa_id`");
  }
  if (!columnExists($db, 'absensi', 'alasan')) {
    execSql($db, "ALTER TABLE `absensi` ADD COLUMN `alasan` text DEFAULT NULL AFTER `status`");
  }
  if (!columnExists($db, 'absensi', 'catatan_guru')) {
    execSql($db, "ALTER TABLE `absensi` ADD COLUMN `catatan_guru` text DEFAULT NULL AFTER `alasan`");
  }
  if (!columnExists($db, 'absensi', 'updated_by')) {
    execSql($db, "ALTER TABLE `absensi` ADD COLUMN `updated_by` char(6) NULL AFTER `catatan_guru`");
  }
  if (!columnExists($db, 'absensi', 'created_at')) {
    execSql($db, "ALTER TABLE `absensi` ADD COLUMN `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `updated_at`");
  }

  if (columnExists($db, 'absensi', 'status_kehadiran')) {
    execSql($db, "
      UPDATE absensi
      SET status = status_kehadiran
      WHERE status IS NULL OR status = ''
    ");
  }

  if (columnExists($db, 'absensi', 'keterangan')) {
    execSql($db, "
      UPDATE absensi
      SET alasan = keterangan
      WHERE (alasan IS NULL OR alasan = '')
        AND keterangan IS NOT NULL
        AND keterangan <> ''
    ");
  }

  execSql($db, "
    UPDATE absensi a
    INNER JOIN jadwal j ON j.id = a.jadwal_id
    INNER JOIN kelas k ON k.id = j.kelas_id
    SET a.siswa_id = k.siswa_id
    WHERE a.siswa_id IS NULL OR a.siswa_id = ''
  ");

  execSql($db, "
    UPDATE absensi a
    INNER JOIN jadwal j ON j.id = a.jadwal_id
    INNER JOIN kelas k ON k.id = j.kelas_id
    SET a.updated_by = k.guru_id
    WHERE a.updated_by IS NULL OR a.updated_by = ''
  ");

  execSql($db, "UPDATE absensi SET status = 'Hadir' WHERE status IS NULL OR status = ''");

  $fallbackUser = $db->query("
    SELECT id
    FROM users
    ORDER BY (role = 'admin') DESC, id ASC
    LIMIT 1
  ")->fetchColumn();
  if ($fallbackUser) {
    $stmt = $db->prepare("UPDATE absensi SET updated_by = ? WHERE updated_by IS NULL OR updated_by = ''");
    $stmt->execute([(string)$fallbackUser]);
  }

  execSql($db, "
    ALTER TABLE `absensi`
    MODIFY COLUMN `siswa_id` char(6) NOT NULL,
    MODIFY COLUMN `status` enum('Hadir','Izin','Sakit','Alpa') NOT NULL DEFAULT 'Hadir',
    MODIFY COLUMN `updated_by` char(6) NOT NULL
  ");

  if (indexExists($db, 'absensi', 'uq_absensi')) {
    execSql($db, "ALTER TABLE `absensi` DROP INDEX `uq_absensi`");
  }
  if (!indexExists($db, 'absensi', 'uk_jadwal_tanggal_siswa')) {
    execSql($db, "ALTER TABLE `absensi` ADD UNIQUE KEY `uk_jadwal_tanggal_siswa` (`jadwal_id`,`tanggal`,`siswa_id`)");
  }
  if (!indexExists($db, 'absensi', 'idx_jadwal_tanggal')) {
    execSql($db, "ALTER TABLE `absensi` ADD KEY `idx_jadwal_tanggal` (`jadwal_id`,`tanggal`)");
  }
  if (!indexExists($db, 'absensi', 'idx_siswa_tanggal')) {
    execSql($db, "ALTER TABLE `absensi` ADD KEY `idx_siswa_tanggal` (`siswa_id`,`tanggal`)");
  }
  if (!indexExists($db, 'absensi', 'idx_updated_at')) {
    execSql($db, "ALTER TABLE `absensi` ADD KEY `idx_updated_at` (`updated_at`)");
  }
  if (!indexExists($db, 'absensi', 'idx_status')) {
    execSql($db, "ALTER TABLE `absensi` ADD KEY `idx_status` (`status`)");
  }

  if (columnExists($db, 'absensi', 'status_kehadiran')) {
    execSql($db, "ALTER TABLE `absensi` DROP COLUMN `status_kehadiran`");
  }
  if (columnExists($db, 'absensi', 'keterangan')) {
    execSql($db, "ALTER TABLE `absensi` DROP COLUMN `keterangan`");
  }

  if (!hasForeignKeyOnColumn($db, 'absensi', 'siswa_id')
      && !constraintExists($db, 'fk_absensi_siswa')) {
    execSql($db, "
      ALTER TABLE `absensi`
      ADD CONSTRAINT `fk_absensi_siswa`
      FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
    ");
  }

  if (!hasForeignKeyOnColumn($db, 'absensi', 'updated_by')
      && !constraintExists($db, 'fk_absensi_updated_by')) {
    execSql($db, "
      ALTER TABLE `absensi`
      ADD CONSTRAINT `fk_absensi_updated_by`
      FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
    ");
  }
}

function ensurePendaftaranSchema(PDO $db): void {
  if (!tableExists($db, 'pendaftaran')) {
    return;
  }

  if (columnExists($db, 'pendaftaran', 'telepon')) {
    execSql($db, "ALTER TABLE `pendaftaran` MODIFY COLUMN `telepon` VARCHAR(30) NOT NULL");
  }

  if (!columnExists($db, 'pendaftaran', 'alamat')) {
    execSql($db, "ALTER TABLE `pendaftaran` ADD COLUMN `alamat` TEXT NULL AFTER `telepon`");
  }
  if (!columnExists($db, 'pendaftaran', 'jenjang')) {
    execSql($db, "ALTER TABLE `pendaftaran` ADD COLUMN `jenjang` VARCHAR(30) NULL AFTER `alamat`");
  }
  if (!columnExists($db, 'pendaftaran', 'asal_sekolah')) {
    execSql($db, "ALTER TABLE `pendaftaran` ADD COLUMN `asal_sekolah` VARCHAR(150) NULL AFTER `kelas_sekolah`");
  }
  if (!columnExists($db, 'pendaftaran', 'nama_wali')) {
    execSql($db, "ALTER TABLE `pendaftaran` ADD COLUMN `nama_wali` VARCHAR(150) NULL AFTER `asal_sekolah`");
  }
  if (!columnExists($db, 'pendaftaran', 'no_hp_wali')) {
    execSql($db, "ALTER TABLE `pendaftaran` ADD COLUMN `no_hp_wali` VARCHAR(30) NULL AFTER `nama_wali`");
  }
  if (!columnExists($db, 'pendaftaran', 'catatan')) {
    execSql($db, "ALTER TABLE `pendaftaran` ADD COLUMN `catatan` TEXT NULL AFTER `no_hp_wali`");
  }

  if (!indexExists($db, 'pendaftaran', 'idx_pendaftaran_telepon')) {
    execSql($db, "ALTER TABLE `pendaftaran` ADD KEY `idx_pendaftaran_telepon` (`telepon`)");
  }
  if (!indexExists($db, 'pendaftaran', 'idx_pendaftaran_no_hp_wali')) {
    execSql($db, "ALTER TABLE `pendaftaran` ADD KEY `idx_pendaftaran_no_hp_wali` (`no_hp_wali`)");
  }
}

try {
  $migrationsDir = __DIR__ . '/migrations';
  $db = getDB();
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  if (!is_dir($migrationsDir)) {
    mkdir($migrationsDir, 0755, true);
    out('Created migrations directory.');
  }

  $sqlFiles = glob($migrationsDir . '/*.sql') ?: [];
  sort($sqlFiles);

  foreach ($sqlFiles as $file) {
    $filename = basename($file);
    out("Running migration: {$filename}");
    $sql = file_get_contents($file);
    if ($sql === false) {
      throw new RuntimeException("Cannot read migration file: {$filename}");
    }
    $db->exec($sql);
  }

  out('Ensuring absensi schema...');
  ensureAbsensiSchema($db);
  ensureAbsensiAuditTable($db);
  ensureCounterAbsensi($db);
  ensurePendaftaranSchema($db);

  out('Migration completed successfully.');
} catch (Throwable $e) {
  out('Migration failed: ' . $e->getMessage());
  exit(1);
}


