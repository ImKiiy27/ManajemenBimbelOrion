<?php
// ============================================================
// models/absensi/AbsensiQueryService.php
// Query absensi lintas role
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class AbsensiQueryService
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }

  /**
   * Data absensi guru (hanya jadwal milik guru).
   */
  public function getAbsensiByGuru(string $guruId, ?string $tanggalStart = null, ?string $tanggalEnd = null): array {
    $sql = '
      SELECT
        a.id,
        a.jadwal_id,
        a.tanggal,
        a.siswa_id,
        a.status,
        a.alasan,
        a.catatan_guru,
        a.updated_by,
        a.updated_at,
        a.created_at,
        s.nama AS siswa_nama,
        j.hari,
        j.jam_mulai,
        j.jam_selesai
      FROM absensi a
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = a.siswa_id
      WHERE k.guru_id = ?
    ';

    $params = [$guruId];

    if ($tanggalStart && $tanggalEnd) {
      $sql .= ' AND a.tanggal BETWEEN ? AND ?';
      $params[] = $tanggalStart;
      $params[] = $tanggalEnd;
    }

    $sql .= " ORDER BY a.tanggal DESC, FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC, s.nama ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  /**
   * Jadwal milik guru untuk input absensi.
   */
  public function getJadwalByGuru(string $guruId): array {
    $stmt = $this->db->prepare('
      SELECT
        j.id,
        j.kelas_id,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        COUNT(DISTINCT k.siswa_id) AS jumlah_siswa,
        GROUP_CONCAT(DISTINCT s.nama ORDER BY s.nama SEPARATOR ", ") AS siswa_names
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      WHERE k.guru_id = ?
      GROUP BY j.id, j.kelas_id, j.hari, j.jam_mulai, j.jam_selesai
      ORDER BY FIELD(j.hari, \'Senin\',\'Selasa\',\'Rabu\',\'Kamis\',\'Jumat\',\'Sabtu\',\'Minggu\'), j.jam_mulai ASC
    ');
    $stmt->execute([$guruId]);
    return $stmt->fetchAll();
  }

  /**
   * Siswa pada jadwal tertentu.
   */
  public function getSiswaInJadwal(string $jadwalId): array {
    $stmt = $this->db->prepare('
      SELECT s.id, s.nama
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      WHERE j.id = ?
      ORDER BY s.nama ASC
    ');
    $stmt->execute([$jadwalId]);
    return $stmt->fetchAll();
  }

  /**
   * Ambil absensi untuk satu jadwal dan tanggal.
   */
  public function getAbsensiByJadwalTanggal(string $jadwalId, string $tanggal): array {
    $stmt = $this->db->prepare('
      SELECT
        a.id,
        a.jadwal_id,
        a.tanggal,
        a.siswa_id,
        a.status,
        a.alasan,
        a.catatan_guru,
        a.updated_by,
        a.updated_at
      FROM absensi a
      WHERE a.jadwal_id = ?
        AND a.tanggal = ?
      ORDER BY a.siswa_id ASC
    ');
    $stmt->execute([$jadwalId, $tanggal]);
    return $stmt->fetchAll();
  }

  /**
   * Rekap absensi admin dengan filter + paging.
   */
  public function getAbsensiByAdmin(
    ?string $guruId = null,
    ?string $siswaId = null,
    ?string $status = null,
    ?string $tanggalStart = null,
    ?string $tanggalEnd = null,
    int $limit = 100,
    int $offset = 0
  ): array {
    $sql = '
      SELECT
        a.id,
        a.jadwal_id,
        a.tanggal,
        a.siswa_id,
        a.status,
        a.alasan,
        a.catatan_guru,
        a.updated_by,
        a.updated_at,
        a.created_at,
        s.nama AS siswa_nama,
        g.nama AS guru_nama,
        j.hari,
        j.jam_mulai,
        j.jam_selesai
      FROM absensi a
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = a.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      WHERE 1 = 1
    ';

    $params = [];

    if ($guruId) {
      $sql .= ' AND k.guru_id = ?';
      $params[] = $guruId;
    }
    if ($siswaId) {
      $sql .= ' AND a.siswa_id = ?';
      $params[] = $siswaId;
    }
    if ($status) {
      $sql .= ' AND a.status = ?';
      $params[] = $status;
    }
    if ($tanggalStart && $tanggalEnd) {
      $sql .= ' AND a.tanggal BETWEEN ? AND ?';
      $params[] = $tanggalStart;
      $params[] = $tanggalEnd;
    }

    $sql .= ' ORDER BY a.tanggal DESC, s.nama ASC LIMIT ? OFFSET ?';
    $params[] = max(1, $limit);
    $params[] = max(0, $offset);

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public function countAbsensiByAdmin(
    ?string $guruId = null,
    ?string $siswaId = null,
    ?string $status = null,
    ?string $tanggalStart = null,
    ?string $tanggalEnd = null
  ): int {
    $sql = '
      SELECT COUNT(*) AS total
      FROM absensi a
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE 1 = 1
    ';

    $params = [];

    if ($guruId) {
      $sql .= ' AND k.guru_id = ?';
      $params[] = $guruId;
    }
    if ($siswaId) {
      $sql .= ' AND a.siswa_id = ?';
      $params[] = $siswaId;
    }
    if ($status) {
      $sql .= ' AND a.status = ?';
      $params[] = $status;
    }
    if ($tanggalStart && $tanggalEnd) {
      $sql .= ' AND a.tanggal BETWEEN ? AND ?';
      $params[] = $tanggalStart;
      $params[] = $tanggalEnd;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
  }

  /**
   * Riwayat absensi siswa (read-only).
   */
  public function getAbsensiHistorySiswa(string $siswaId, ?string $tanggalStart = null, ?string $tanggalEnd = null): array {
    $sql = '
      SELECT
        a.id,
        a.jadwal_id,
        a.tanggal,
        a.siswa_id,
        a.status,
        a.alasan,
        a.catatan_guru,
        a.updated_at,
        g.nama AS guru_nama,
        j.hari,
        j.jam_mulai,
        j.jam_selesai
      FROM absensi a
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN guru g ON g.id = k.guru_id
      WHERE a.siswa_id = ?
    ';

    $params = [$siswaId];

    if ($tanggalStart && $tanggalEnd) {
      $sql .= ' AND a.tanggal BETWEEN ? AND ?';
      $params[] = $tanggalStart;
      $params[] = $tanggalEnd;
    }

    $sql .= ' ORDER BY a.tanggal DESC, j.jam_mulai DESC';

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  /**
   * Riwayat absensi untuk wali murid (read-only anak sendiri).
   */
  public function getAbsensiHistoryWaliMurid(string $waliMuridId): array {
    $stmt = $this->db->prepare('
      SELECT
        a.id,
        a.jadwal_id,
        a.tanggal,
        a.siswa_id,
        a.status,
        a.alasan,
        a.catatan_guru,
        a.updated_at,
        s.nama AS siswa_nama,
        g.nama AS guru_nama,
        j.hari,
        j.jam_mulai,
        j.jam_selesai
      FROM absensi a
      INNER JOIN siswa s ON s.id = a.siswa_id
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN guru g ON g.id = k.guru_id
      WHERE s.wali_id = ?
      ORDER BY s.nama ASC, a.tanggal DESC, j.jam_mulai DESC
    ');
    $stmt->execute([$waliMuridId]);
    return $stmt->fetchAll();
  }

  public function getGuruDashboardMetrics(string $guruId, ?string $bulan = null): array {
    $bulan = $bulan ?? date('Y-m');

    $stmt = $this->db->prepare("
      SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) AS izin,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) AS sakit,
        SUM(CASE WHEN a.status = 'Alpa' THEN 1 ELSE 0 END) AS alpa
      FROM absensi a
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE k.guru_id = ?
        AND DATE_FORMAT(a.tanggal, '%Y-%m') = ?
    ");
    $stmt->execute([$guruId, $bulan]);

    $row = $stmt->fetch() ?: [];
    return [
      'total' => (int)($row['total'] ?? 0),
      'hadir' => (int)($row['hadir'] ?? 0),
      'izin' => (int)($row['izin'] ?? 0),
      'sakit' => (int)($row['sakit'] ?? 0),
      'alpa' => (int)($row['alpa'] ?? 0),
    ];
  }

  public function getAbsensiAuditTrail(string $absensiId): array {
    $stmt = $this->db->prepare('
      SELECT
        aa.id,
        aa.absensi_id,
        aa.old_status,
        aa.new_status,
        aa.old_alasan,
        aa.new_alasan,
        aa.changed_by,
        aa.changed_at,
        aa.action_type,
        aa.reason
      FROM absensi_audit aa
      WHERE aa.absensi_id = ?
      ORDER BY aa.changed_at DESC, aa.id DESC
    ');
    $stmt->execute([$absensiId]);
    return $stmt->fetchAll();
  }

  public function getAbsensiById(string $absensiId): array|false {
    $stmt = $this->db->prepare('
      SELECT
        a.id,
        a.jadwal_id,
        a.tanggal,
        a.siswa_id,
        a.status,
        a.alasan,
        a.catatan_guru,
        a.updated_by,
        a.updated_at,
        a.created_at,
        s.nama AS siswa_nama,
        g.nama AS guru_nama,
        j.hari,
        j.jam_mulai,
        j.jam_selesai
      FROM absensi a
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = a.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      WHERE a.id = ?
      LIMIT 1
    ');
    $stmt->execute([$absensiId]);
    return $stmt->fetch();
  }

  public function getGuruList(): array {
    $stmt = $this->db->prepare("
      SELECT DISTINCT g.id, g.nama
      FROM guru g
      ORDER BY g.nama ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function getSiswaList(): array {
    $stmt = $this->db->prepare("
      SELECT id, nama FROM siswa ORDER BY nama ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function getJadwalWithValidation(string $jadwalId, string $guruId): array|false {
    $stmt = $this->db->prepare("
      SELECT j.id, j.kelas_id, j.hari, j.jam_mulai, j.jam_selesai, k.guru_id
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE j.id = ? AND k.guru_id = ?
      LIMIT 1
    ");
    $stmt->execute([$jadwalId, $guruId]);
    return $stmt->fetch();
  }
}
