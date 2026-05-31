<?php
declare(strict_types=1);

// ============================================================
// scripts/migrate_to_erd.php
// Migrasi schema database aktif ke struktur ERD terbaru
// ============================================================

require_once __DIR__ . '/../config/database.php';

$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

function constraintExistsByName(PDO $db, string $constraint): bool {
  $stmt = $db->prepare("
    SELECT COUNT(*)
    FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND CONSTRAINT_NAME = ?
  ");
  $stmt->execute([$constraint]);
  return (int)$stmt->fetchColumn() > 0;
}

function triggerExists(PDO $db, string $triggerName): bool {
  $stmt = $db->prepare("
    SELECT COUNT(*)
    FROM information_schema.TRIGGERS
    WHERE TRIGGER_SCHEMA = DATABASE()
      AND TRIGGER_NAME = ?
  ");
  $stmt->execute([$triggerName]);
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

function dropForeignKeysOnColumn(PDO $db, string $table, string $column): void {
  $stmt = $db->prepare("
    SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = ?
      AND COLUMN_NAME = ?
      AND REFERENCED_TABLE_NAME IS NOT NULL
    GROUP BY CONSTRAINT_NAME
  ");
  $stmt->execute([$table, $column]);
  $keys = $stmt->fetchAll(PDO::FETCH_COLUMN);

  foreach ($keys as $fkName) {
    $db->exec("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
  }
}

function execSql(PDO $db, string $sql): void {
  $db->exec($sql);
}

function ensureCounterRow(PDO $db, string $table, string $prefix): void {
  $stmt = $db->prepare("SELECT tabel FROM id_counter WHERE tabel = ? LIMIT 1");
  $stmt->execute([$table]);
  if ($stmt->fetchColumn()) {
    $up = $db->prepare("UPDATE id_counter SET prefix = ? WHERE tabel = ?");
    $up->execute([$prefix, $table]);
    return;
  }

  $ins = $db->prepare("INSERT INTO id_counter (tabel, prefix, last_id) VALUES (?, ?, 0)");
  $ins->execute([$table, $prefix]);
}

function nextId(PDO $db, string $table, string $prefix): string {
  ensureCounterRow($db, $table, $prefix);

  $stmt = $db->prepare("SELECT last_id, prefix FROM id_counter WHERE tabel = ? LIMIT 1");
  $stmt->execute([$table]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  $current = (int)($row['last_id'] ?? 0);
  $actualPrefix = (string)($row['prefix'] ?? $prefix);
  if ($actualPrefix === '') {
    $actualPrefix = $prefix;
  }

  $next = $current + 1;
  $up = $db->prepare("UPDATE id_counter SET last_id = ?, prefix = ? WHERE tabel = ?");
  $up->execute([$next, $actualPrefix, $table]);

  return $actualPrefix . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
}

function ensureMapelByName(PDO $db, string $name, string $description = ''): string {
  $normalized = trim($name);
  if ($normalized === '') {
    $normalized = 'Privat';
  }

  $stmt = $db->prepare("SELECT id FROM mapel WHERE LOWER(nama) = LOWER(?) LIMIT 1");
  $stmt->execute([$normalized]);
  $existing = $stmt->fetchColumn();
  if ($existing) {
    return (string)$existing;
  }

  $mapelId = nextId($db, 'mapel', 'MPL');
  $ins = $db->prepare("INSERT INTO mapel (id, nama, deskripsi) VALUES (?, ?, ?)");
  $ins->execute([$mapelId, $normalized, $description]);
  return $mapelId;
}

function ensureForeignKey(
  PDO $db,
  string $table,
  string $column,
  string $constraintName,
  string $refTable,
  string $refColumn,
  string $onDelete = 'CASCADE'
): void {
  if (constraintExistsByName($db, $constraintName) || hasForeignKeyOnColumn($db, $table, $column)) {
    return;
  }

  $sql = "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraintName}` FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`) ON DELETE {$onDelete}";
  $db->exec($sql);
}

function dropTriggerIfExists(PDO $db, string $triggerName): void {
  if (!triggerExists($db, $triggerName)) {
    return;
  }
  $db->exec("DROP TRIGGER `{$triggerName}`");
}

try {
  out('[1/9] Menyiapkan tabel inti ERD...');

  execSql($db, "
    CREATE TABLE IF NOT EXISTS `mapel` (
      `id` char(6) NOT NULL,
      `nama` varchar(100) NOT NULL,
      `deskripsi` varchar(255) DEFAULT NULL,
      `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
      PRIMARY KEY (`id`),
      UNIQUE KEY `uq_mapel_nama` (`nama`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  ");

  if (!columnExists($db, 'mapel', 'status')) {
    execSql($db, "ALTER TABLE `mapel` ADD COLUMN `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif' AFTER `deskripsi`");
  }
  execSql($db, "UPDATE `mapel` SET `status` = 'aktif' WHERE `status` IS NULL OR `status` = ''");

  execSql($db, "
    CREATE TABLE IF NOT EXISTS `wali_murid` (
      `id` char(6) NOT NULL,
      `nama` varchar(100) NOT NULL,
      `no_telp` varchar(20) DEFAULT NULL,
      `hubungan` varchar(30) DEFAULT NULL,
      `pekerjaan` varchar(100) DEFAULT NULL,
      `alamat` varchar(255) DEFAULT NULL,
      `foto_path` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  ");

  execSql($db, "
    CREATE TABLE IF NOT EXISTS `pendaftaran_mapel` (
      `id` char(6) NOT NULL,
      `pendaftaran_id` char(6) NOT NULL,
      `mapel_id` char(6) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uq_pendaftaran_mapel` (`pendaftaran_id`,`mapel_id`),
      KEY `idx_pm_mapel` (`mapel_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  ");

  execSql($db, "
    CREATE TABLE IF NOT EXISTS `kelas` (
      `id` char(6) NOT NULL,
      `siswa_id` char(6) NOT NULL,
      `guru_id` char(6) NOT NULL,
      `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `uq_kelas_siswa_guru` (`siswa_id`,`guru_id`),
      KEY `idx_kelas_guru` (`guru_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  ");

  ensureCounterRow($db, 'mapel', 'MPL');
  ensureCounterRow($db, 'wali_murid', 'WLM');
  ensureCounterRow($db, 'pendaftaran_mapel', 'PMA');
  ensureCounterRow($db, 'kelas', 'KLS');

  $privatMapelId = ensureMapelByName($db, 'Privat', 'Mapel default');

  out('[2/9] Migrasi tabel guru...');
  if (tableExists($db, 'guru')) {
    if (columnExists($db, 'guru', 'user_id') && !columnExists($db, 'guru', 'id')) {
      dropForeignKeysOnColumn($db, 'guru', 'user_id');
      execSql($db, "ALTER TABLE `guru` CHANGE COLUMN `user_id` `id` char(6) NOT NULL");
    }

    if (!columnExists($db, 'guru', 'mapel_id')) {
      execSql($db, "ALTER TABLE `guru` ADD COLUMN `mapel_id` char(6) DEFAULT NULL AFTER `id`");
    }
    if (!columnExists($db, 'guru', 'alamat')) {
      execSql($db, "ALTER TABLE `guru` ADD COLUMN `alamat` varchar(255) DEFAULT NULL AFTER `nama`");
    }
    if (!columnExists($db, 'guru', 'no_telp')) {
      execSql($db, "ALTER TABLE `guru` ADD COLUMN `no_telp` varchar(20) DEFAULT NULL AFTER `alamat`");
    }
    if (!columnExists($db, 'guru', 'foto_path')) {
      execSql($db, "ALTER TABLE `guru` ADD COLUMN `foto_path` varchar(255) DEFAULT NULL AFTER `no_telp`");
    }
    if (!columnExists($db, 'guru', 'bio')) {
      execSql($db, "ALTER TABLE `guru` ADD COLUMN `bio` text DEFAULT NULL AFTER `foto_path`");
    }

    if (columnExists($db, 'guru', 'mapel')) {
      $rows = $db->query("SELECT DISTINCT TRIM(mapel) AS mapel_nama FROM guru WHERE mapel IS NOT NULL AND TRIM(mapel) <> ''")->fetchAll(PDO::FETCH_ASSOC);
      foreach ($rows as $r) {
        ensureMapelByName($db, (string)$r['mapel_nama']);
      }

      execSql($db, "
        UPDATE guru g
        JOIN mapel m ON LOWER(m.nama) = LOWER(g.mapel)
        SET g.mapel_id = m.id
        WHERE (g.mapel_id IS NULL OR g.mapel_id = '')
          AND g.mapel IS NOT NULL
          AND TRIM(g.mapel) <> ''
      ");
    }

    $stmtGuruNull = $db->prepare("UPDATE guru SET mapel_id = ? WHERE mapel_id IS NULL OR mapel_id = ''");
    $stmtGuruNull->execute([$privatMapelId]);
    execSql($db, "ALTER TABLE `guru` MODIFY COLUMN `mapel_id` char(6) NOT NULL");

    if (columnExists($db, 'guru', 'mapel')) {
      execSql($db, "ALTER TABLE `guru` DROP COLUMN `mapel`");
    }

    ensureForeignKey($db, 'guru', 'id', 'fk_guru_users', 'users', 'id', 'CASCADE');
    ensureForeignKey($db, 'guru', 'mapel_id', 'fk_guru_mapel', 'mapel', 'id', 'RESTRICT');
  }

  out('[3/9] Migrasi tabel siswa...');
  if (tableExists($db, 'siswa')) {
    if (columnExists($db, 'siswa', 'user_id') && !columnExists($db, 'siswa', 'id')) {
      dropForeignKeysOnColumn($db, 'siswa', 'user_id');
      execSql($db, "ALTER TABLE `siswa` CHANGE COLUMN `user_id` `id` char(6) NOT NULL");
    }

    if (!columnExists($db, 'siswa', 'wali_id')) {
      execSql($db, "ALTER TABLE `siswa` ADD COLUMN `wali_id` char(6) DEFAULT NULL AFTER `id`");
    }
    if (!columnExists($db, 'siswa', 'kelas_sekolah')) {
      execSql($db, "ALTER TABLE `siswa` ADD COLUMN `kelas_sekolah` varchar(50) DEFAULT NULL AFTER `nama`");
    }
    if (!columnExists($db, 'siswa', 'asal_sekolah')) {
      execSql($db, "ALTER TABLE `siswa` ADD COLUMN `asal_sekolah` varchar(100) DEFAULT NULL AFTER `kelas_sekolah`");
    }
    if (!columnExists($db, 'siswa', 'alamat')) {
      execSql($db, "ALTER TABLE `siswa` ADD COLUMN `alamat` varchar(255) DEFAULT NULL AFTER `asal_sekolah`");
    }
    if (!columnExists($db, 'siswa', 'no_telp')) {
      execSql($db, "ALTER TABLE `siswa` ADD COLUMN `no_telp` varchar(20) DEFAULT NULL AFTER `alamat`");
    }
    if (!columnExists($db, 'siswa', 'foto_path')) {
      execSql($db, "ALTER TABLE `siswa` ADD COLUMN `foto_path` varchar(255) DEFAULT NULL AFTER `no_telp`");
    }

    if (columnExists($db, 'siswa', 'kelas')) {
      execSql($db, "UPDATE siswa SET kelas_sekolah = COALESCE(NULLIF(kelas_sekolah, ''), kelas)");
    }

    if (tableExists($db, 'profil')) {
      execSql($db, "
        UPDATE siswa s
        JOIN profil p ON p.user_id = s.id
        SET
          s.alamat = COALESCE(NULLIF(s.alamat, ''), p.alamat),
          s.no_telp = COALESCE(NULLIF(s.no_telp, ''), p.no_telp),
          s.foto_path = COALESCE(NULLIF(s.foto_path, ''), p.foto_path)
      ");
      execSql($db, "
        UPDATE guru g
        JOIN profil p ON p.user_id = g.id
        SET
          g.alamat = COALESCE(NULLIF(g.alamat, ''), p.alamat),
          g.no_telp = COALESCE(NULLIF(g.no_telp, ''), p.no_telp),
          g.foto_path = COALESCE(NULLIF(g.foto_path, ''), p.foto_path)
      ");
    }

    execSql($db, "UPDATE siswa SET kelas_sekolah = 'Privat' WHERE kelas_sekolah IS NULL OR kelas_sekolah = ''");
    execSql($db, "ALTER TABLE `siswa` MODIFY COLUMN `kelas_sekolah` varchar(50) NOT NULL");

    if (columnExists($db, 'siswa', 'kelas')) {
      execSql($db, "ALTER TABLE `siswa` DROP COLUMN `kelas`");
    }

    ensureForeignKey($db, 'siswa', 'id', 'fk_siswa_users', 'users', 'id', 'CASCADE');
    ensureForeignKey($db, 'siswa', 'wali_id', 'fk_siswa_wali', 'wali_murid', 'id', 'SET NULL');
  }

  out('[4/9] Migrasi tabel siswa_mapel + kelas...');
  if (tableExists($db, 'siswa_mapel')) {
    // Jika schema lama punya guru_id, buat relasi kelas dari data lama dulu
    if (columnExists($db, 'siswa_mapel', 'guru_id')) {
      $rows = $db->query("SELECT DISTINCT siswa_id, guru_id, status FROM siswa_mapel WHERE siswa_id IS NOT NULL AND guru_id IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
      foreach ($rows as $r) {
        $cek = $db->prepare("SELECT id FROM kelas WHERE siswa_id = ? AND guru_id = ? LIMIT 1");
        $cek->execute([(string)$r['siswa_id'], (string)$r['guru_id']]);
        if ($cek->fetchColumn()) {
          continue;
        }
        $kelasId = nextId($db, 'kelas', 'KLS');
        $insKelas = $db->prepare("
          INSERT INTO kelas (id, siswa_id, guru_id, status)
          VALUES (?, ?, ?, ?)
        ");
        $insKelas->execute([
          $kelasId,
          (string)$r['siswa_id'],
          (string)$r['guru_id'],
          in_array((string)$r['status'], ['aktif', 'nonaktif'], true) ? (string)$r['status'] : 'aktif',
        ]);
      }
    }

    if (!columnExists($db, 'siswa_mapel', 'mapel_id')) {
      execSql($db, "ALTER TABLE `siswa_mapel` ADD COLUMN `mapel_id` char(6) DEFAULT NULL AFTER `siswa_id`");
    }

    if (columnExists($db, 'siswa_mapel', 'mata_pelajaran')) {
      $rows = $db->query("SELECT DISTINCT TRIM(mata_pelajaran) AS mapel_nama FROM siswa_mapel WHERE mata_pelajaran IS NOT NULL AND TRIM(mata_pelajaran) <> ''")->fetchAll(PDO::FETCH_ASSOC);
      foreach ($rows as $r) {
        ensureMapelByName($db, (string)$r['mapel_nama']);
      }
      execSql($db, "
        UPDATE siswa_mapel sm
        JOIN mapel m ON LOWER(m.nama) = LOWER(sm.mata_pelajaran)
        SET sm.mapel_id = m.id
        WHERE (sm.mapel_id IS NULL OR sm.mapel_id = '')
          AND sm.mata_pelajaran IS NOT NULL
          AND TRIM(sm.mata_pelajaran) <> ''
      ");
    }

    if (columnExists($db, 'siswa_mapel', 'guru_id')) {
      execSql($db, "
        UPDATE siswa_mapel sm
        JOIN guru g ON g.id = sm.guru_id
        SET sm.mapel_id = g.mapel_id
        WHERE sm.mapel_id IS NULL OR sm.mapel_id = ''
      ");
    }

    $stmtMapelNull = $db->prepare("UPDATE siswa_mapel SET mapel_id = ? WHERE mapel_id IS NULL OR mapel_id = ''");
    $stmtMapelNull->execute([$privatMapelId]);
    execSql($db, "ALTER TABLE `siswa_mapel` MODIFY COLUMN `mapel_id` char(6) NOT NULL");

    // Ubah FK siswa_mapel.siswa_id -> siswa.id
    dropForeignKeysOnColumn($db, 'siswa_mapel', 'siswa_id');
    ensureForeignKey($db, 'siswa_mapel', 'siswa_id', 'fk_siswa_mapel_siswa', 'siswa', 'id', 'CASCADE');
    ensureForeignKey($db, 'siswa_mapel', 'mapel_id', 'fk_siswa_mapel_mapel', 'mapel', 'id', 'CASCADE');

    if (!indexExists($db, 'siswa_mapel', 'idx_sm_siswa')) {
      execSql($db, "ALTER TABLE `siswa_mapel` ADD KEY `idx_sm_siswa` (`siswa_id`)");
    }
    if (!indexExists($db, 'siswa_mapel', 'idx_sm_mapel')) {
      execSql($db, "ALTER TABLE `siswa_mapel` ADD KEY `idx_sm_mapel` (`mapel_id`)");
    }
    if (indexExists($db, 'siswa_mapel', 'uq_siswa_mapel')) {
      execSql($db, "ALTER TABLE `siswa_mapel` DROP INDEX `uq_siswa_mapel`");
    }
    if (!indexExists($db, 'siswa_mapel', 'uq_siswa_mapel')) {
      execSql($db, "ALTER TABLE `siswa_mapel` ADD UNIQUE KEY `uq_siswa_mapel` (`siswa_id`,`mapel_id`)");
    }

    if (columnExists($db, 'siswa_mapel', 'guru_id')) {
      dropForeignKeysOnColumn($db, 'siswa_mapel', 'guru_id');
      execSql($db, "ALTER TABLE `siswa_mapel` DROP COLUMN `guru_id`");
    }
    if (columnExists($db, 'siswa_mapel', 'mata_pelajaran')) {
      execSql($db, "ALTER TABLE `siswa_mapel` DROP COLUMN `mata_pelajaran`");
    }
  }

  out('[5/9] Migrasi tabel jadwal...');
  if (tableExists($db, 'jadwal')) {
    if (!columnExists($db, 'jadwal', 'kelas_id')) {
      execSql($db, "ALTER TABLE `jadwal` ADD COLUMN `kelas_id` char(6) DEFAULT NULL AFTER `id`");
    }

    if (columnExists($db, 'jadwal', 'siswa_mapel_id') && tableExists($db, 'kelas')) {
      // Mapping jadwal lama (berbasis siswa_mapel) -> kelas_id
      if (columnExists($db, 'siswa_mapel', 'id')) {
        // Jika kolom lama masih ada saat mapping, gunakan data kelas yang sudah dibentuk
        if (columnExists($db, 'siswa_mapel', 'siswa_id') && columnExists($db, 'kelas', 'siswa_id') && columnExists($db, 'kelas', 'guru_id')) {
          // Untuk mapping, kita pakai backup dari tabel sementara bila guru_id sudah di-drop
          // kalau guru_id sudah tidak ada, mapping lewat data kelas aktif pertama per siswa
          if (columnExists($db, 'siswa_mapel', 'guru_id')) {
            execSql($db, "
              UPDATE jadwal j
              JOIN siswa_mapel sm ON sm.id = j.siswa_mapel_id
              JOIN kelas k ON k.siswa_id = sm.siswa_id AND k.guru_id = sm.guru_id
              SET j.kelas_id = k.id
              WHERE j.kelas_id IS NULL OR j.kelas_id = ''
            ");
          } else {
            execSql($db, "
              UPDATE jadwal j
              JOIN siswa_mapel sm ON sm.id = j.siswa_mapel_id
              JOIN kelas k ON k.siswa_id = sm.siswa_id
              SET j.kelas_id = k.id
              WHERE (j.kelas_id IS NULL OR j.kelas_id = '')
                AND k.status = 'aktif'
            ");
          }
        }
      }
    }

    // Pastikan kelas_id terisi
    $remaining = (int)$db->query("SELECT COUNT(*) FROM jadwal WHERE kelas_id IS NULL OR kelas_id = ''")->fetchColumn();
    if ($remaining > 0) {
      // fallback: isi dengan kelas pertama aktif (jika ada)
      $fallback = $db->query("SELECT id FROM kelas ORDER BY id ASC LIMIT 1")->fetchColumn();
      if ($fallback) {
        $updFallback = $db->prepare("UPDATE jadwal SET kelas_id = ? WHERE kelas_id IS NULL OR kelas_id = ''");
        $updFallback->execute([(string)$fallback]);
      }
    }

    if (columnExists($db, 'jadwal', 'siswa_mapel_id')) {
      dropForeignKeysOnColumn($db, 'jadwal', 'siswa_mapel_id');
      if (indexExists($db, 'jadwal', 'uq_siswa_waktu')) {
        execSql($db, "ALTER TABLE `jadwal` DROP INDEX `uq_siswa_waktu`");
      }
      execSql($db, "ALTER TABLE `jadwal` DROP COLUMN `siswa_mapel_id`");
    }

    execSql($db, "ALTER TABLE `jadwal` MODIFY COLUMN `kelas_id` char(6) NOT NULL");
    if (!indexExists($db, 'jadwal', 'uq_kelas_waktu')) {
      execSql($db, "ALTER TABLE `jadwal` ADD UNIQUE KEY `uq_kelas_waktu` (`kelas_id`,`hari`,`jam_mulai`)");
    }
    ensureForeignKey($db, 'jadwal', 'kelas_id', 'fk_jadwal_kelas', 'kelas', 'id', 'CASCADE');
  }

  out('[6/9] Migrasi tabel nilai...');
  if (tableExists($db, 'nilai')) {
    if (!columnExists($db, 'nilai', 'jadwal_id')) {
      execSql($db, "ALTER TABLE `nilai` ADD COLUMN `jadwal_id` char(6) DEFAULT NULL AFTER `id`");
    }
    if (!columnExists($db, 'nilai', 'pertemuan_ke')) {
      execSql($db, "ALTER TABLE `nilai` ADD COLUMN `pertemuan_ke` int(11) NOT NULL DEFAULT 1 AFTER `tipe_nilai`");
    }
    if (!columnExists($db, 'nilai', 'catatan_guru')) {
      execSql($db, "ALTER TABLE `nilai` ADD COLUMN `catatan_guru` text DEFAULT NULL AFTER `predikat`");
    }

    if (columnExists($db, 'nilai', 'siswa_mapel_id') && tableExists($db, 'jadwal')) {
      execSql($db, "
        UPDATE nilai n
        JOIN (
          SELECT kelas_id, MIN(id) AS jadwal_id
          FROM jadwal
          GROUP BY kelas_id
        ) jg ON 1 = 1
        SET n.jadwal_id = COALESCE(n.jadwal_id, jg.jadwal_id)
        WHERE n.jadwal_id IS NULL
      ");
    }

    if (columnExists($db, 'nilai', 'siswa_mapel_id')) {
      dropForeignKeysOnColumn($db, 'nilai', 'siswa_mapel_id');
      if (indexExists($db, 'nilai', 'siswa_mapel_id')) {
        execSql($db, "ALTER TABLE `nilai` DROP INDEX `siswa_mapel_id`");
      }
      execSql($db, "ALTER TABLE `nilai` DROP COLUMN `siswa_mapel_id`");
    }

    // Jika masih ada nilai tanpa jadwal, isi fallback dengan jadwal pertama
    $nullNilai = (int)$db->query("SELECT COUNT(*) FROM nilai WHERE jadwal_id IS NULL OR jadwal_id = ''")->fetchColumn();
    if ($nullNilai > 0) {
      $fallbackJadwal = $db->query("SELECT id FROM jadwal ORDER BY id ASC LIMIT 1")->fetchColumn();
      if ($fallbackJadwal) {
        $updNilai = $db->prepare("UPDATE nilai SET jadwal_id = ? WHERE jadwal_id IS NULL OR jadwal_id = ''");
        $updNilai->execute([(string)$fallbackJadwal]);
      }
    }

    // Hanya ubah NOT NULL jika data sudah valid
    $remainingNilaiNull = (int)$db->query("SELECT COUNT(*) FROM nilai WHERE jadwal_id IS NULL OR jadwal_id = ''")->fetchColumn();
    if ($remainingNilaiNull === 0) {
      execSql($db, "ALTER TABLE `nilai` MODIFY COLUMN `jadwal_id` char(6) NOT NULL");
    }

    ensureForeignKey($db, 'nilai', 'jadwal_id', 'fk_nilai_jadwal', 'jadwal', 'id', 'CASCADE');
  }

  out('[7/9] Migrasi tabel absensi...');
  if (tableExists($db, 'absensi')) {
    if (columnExists($db, 'absensi', 'nis_siswa')) {
      if (!indexExists($db, 'absensi', 'idx_absensi_jadwal')) {
        execSql($db, "ALTER TABLE `absensi` ADD KEY `idx_absensi_jadwal` (`jadwal_id`)");
      }
      if (indexExists($db, 'absensi', 'uq_absensi')) {
        execSql($db, "ALTER TABLE `absensi` DROP INDEX `uq_absensi`");
      }
      execSql($db, "ALTER TABLE `absensi` DROP COLUMN `nis_siswa`");
    }
    if (!indexExists($db, 'absensi', 'uq_absensi')) {
      execSql($db, "ALTER TABLE `absensi` ADD UNIQUE KEY `uq_absensi` (`jadwal_id`,`tanggal`)");
    }
    ensureForeignKey($db, 'absensi', 'jadwal_id', 'fk_absensi_jadwal', 'jadwal', 'id', 'CASCADE');
  }

  out('[8/9] Sinkronisasi FK tambahan dan kolom pendaftaran...');
  if (tableExists($db, 'pendaftaran')) {
    if (!columnExists($db, 'pendaftaran', 'kelas_sekolah')) {
      execSql($db, "ALTER TABLE `pendaftaran` ADD COLUMN `kelas_sekolah` varchar(50) NOT NULL DEFAULT 'Privat' AFTER `telepon`");
    }
    if (!columnExists($db, 'pendaftaran', 'program')) {
      execSql($db, "ALTER TABLE `pendaftaran` ADD COLUMN `program` varchar(50) NOT NULL DEFAULT 'Privat' AFTER `kelas_sekolah`");
    }
  }

  ensureForeignKey($db, 'kelas', 'siswa_id', 'fk_kelas_siswa', 'siswa', 'id', 'CASCADE');
  ensureForeignKey($db, 'kelas', 'guru_id', 'fk_kelas_guru', 'guru', 'id', 'CASCADE');
  ensureForeignKey($db, 'pendaftaran_mapel', 'pendaftaran_id', 'fk_pm_pendaftaran', 'pendaftaran', 'id', 'CASCADE');
  ensureForeignKey($db, 'pendaftaran_mapel', 'mapel_id', 'fk_pm_mapel', 'mapel', 'id', 'CASCADE');

  out('[9/9] Cleanup akhir...');
  if (tableExists($db, 'profil')) {
    execSql($db, "DROP TABLE `profil`");
  }

  // Hapus trigger legacy yang masih mengacu schema lama
  dropTriggerIfExists($db, 'before_insert_siswa_mapel');
  dropTriggerIfExists($db, 'before_insert_jadwal');

  // rapikan counter untuk tabel yang kini tidak dipakai
  $db->prepare("DELETE FROM id_counter WHERE tabel = ?")->execute(['profil']);
  ensureCounterRow($db, 'jadwal', 'JDL');
  ensureCounterRow($db, 'nilai', 'NLI');
  ensureCounterRow($db, 'absensi', 'ABS');
  ensureCounterRow($db, 'siswa_mapel', 'SMP');
  ensureCounterRow($db, 'kelas', 'KLS');
  ensureCounterRow($db, 'mapel', 'MPL');
  ensureCounterRow($db, 'wali_murid', 'WLM');
  ensureCounterRow($db, 'pendaftaran_mapel', 'PMA');

  out('Migrasi ERD selesai tanpa exception.');
} catch (Throwable $e) {
  out('Migrasi gagal: ' . $e->getMessage());
  exit(1);
}
