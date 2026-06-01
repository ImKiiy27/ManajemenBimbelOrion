<?php
// ============================================================
// models/nilai/NilaiQueryService.php
// Fokus: read/query data nilai
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class NilaiQueryService {

  private PDO $db;

  public function __construct(?PDO $db = null) {
    $this->db = $db ?? getDB();
  }

  public function getNilaiByGuru(string $guruId): array {
    $stmt = $this->db->prepare("
      SELECT
        n.id,
        n.jadwal_id,
        n.pertemuan_ke,
        n.tipe_nilai,
        n.predikat,
        n.catatan_guru,
        j.id AS jadwal_id,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        s.id AS siswa_id,
        s.nama AS siswa_nama,
        s.kelas_sekolah AS siswa_kelas,
        g.nama AS guru_nama,
        mg.nama AS guru_mapel,
        m.nama AS mata_pelajaran
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN mapel mg ON mg.id = g.mapel_id
      LEFT JOIN siswa_mapel sm
        ON sm.siswa_id = s.id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      LEFT JOIN mapel m ON m.id = sm.mapel_id
      WHERE k.guru_id = ?
      ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC, n.pertemuan_ke ASC
    ");
    $stmt->execute([$guruId]);
    return $stmt->fetchAll();
  }

  public function getNilaiByJadwal(string $jadwalId): array {
    $stmt = $this->db->prepare("
      SELECT
        n.id,
        n.jadwal_id,
        n.pertemuan_ke,
        n.tipe_nilai,
        n.predikat,
        n.catatan_guru,
        j.id AS jadwal_id,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        s.id AS siswa_id,
        s.nama AS siswa_nama,
        g.nama AS guru_nama,
        mg.nama AS guru_mapel,
        m.nama AS mata_pelajaran
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN mapel mg ON mg.id = g.mapel_id
      LEFT JOIN siswa_mapel sm
        ON sm.siswa_id = s.id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      LEFT JOIN mapel m ON m.id = sm.mapel_id
      WHERE j.id = ?
      ORDER BY n.pertemuan_ke ASC
    ");
    $stmt->execute([$jadwalId]);
    return $stmt->fetchAll();
  }

  public function getNilaiById(string $nilaiId): array|false {
    $stmt = $this->db->prepare("
      SELECT
        n.id,
        n.jadwal_id,
        n.pertemuan_ke,
        n.tipe_nilai,
        n.predikat,
        n.catatan_guru,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        s.id AS siswa_id,
        s.nama AS siswa_nama,
        g.id AS guru_id,
        g.nama AS guru_nama
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      WHERE n.id = ?
      LIMIT 1
    ");
    $stmt->execute([$nilaiId]);
    return $stmt->fetch();
  }

  public function getJadwalForNilaiInput(string $guruId): array {
    $stmt = $this->db->prepare("
      SELECT
        j.id,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        s.id AS siswa_id,
        s.nama AS siswa_nama,
        s.kelas_sekolah AS siswa_kelas,
        mg.nama AS guru_mapel,
        m.nama AS mata_pelajaran
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN mapel mg ON mg.id = g.mapel_id
      LEFT JOIN siswa_mapel sm
        ON sm.siswa_id = s.id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      LEFT JOIN mapel m ON m.id = sm.mapel_id
      WHERE k.guru_id = ? AND k.status = 'aktif'
      ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC
    ");
    $stmt->execute([$guruId]);
    return $stmt->fetchAll();
  }

  public function getNilaiByAdmin(?string $guruId, ?string $siswaId): array {
    $sql = "
      SELECT
        n.id,
        n.jadwal_id,
        n.pertemuan_ke,
        n.tipe_nilai,
        n.predikat,
        n.catatan_guru,
        j.id AS jadwal_id,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        s.id AS siswa_id,
        s.nama AS siswa_nama,
        s.kelas_sekolah AS siswa_kelas,
        g.id AS guru_id,
        g.nama AS guru_nama,
        mg.nama AS guru_mapel,
        m.nama AS mata_pelajaran
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN mapel mg ON mg.id = g.mapel_id
      LEFT JOIN siswa_mapel sm
        ON sm.siswa_id = s.id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      LEFT JOIN mapel m ON m.id = sm.mapel_id
      WHERE 1=1
    ";

    $params = [];
    if (($guruId ?? '') !== '') {
      $sql .= " AND g.id = ?";
      $params[] = $guruId;
    }
    if (($siswaId ?? '') !== '') {
      $sql .= " AND s.id = ?";
      $params[] = $siswaId;
    }

    $sql .= " ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC, n.pertemuan_ke ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public function getNilaiBySiswa(string $siswaId): array {
    $stmt = $this->db->prepare("
      SELECT
        n.id,
        n.jadwal_id,
        n.pertemuan_ke,
        n.tipe_nilai,
        n.predikat,
        n.catatan_guru,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        g.nama AS guru_nama,
        mg.nama AS guru_mapel,
        COALESCE(ms.mapel_siswa, '-') AS mata_pelajaran
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN mapel mg ON mg.id = g.mapel_id
      LEFT JOIN (
        SELECT
          sm.siswa_id,
          GROUP_CONCAT(m.nama ORDER BY m.nama SEPARATOR ', ') AS mapel_siswa
        FROM siswa_mapel sm
        INNER JOIN mapel m ON m.id = sm.mapel_id
        WHERE sm.status = 'aktif'
        GROUP BY sm.siswa_id
      ) ms ON ms.siswa_id = s.id
      WHERE s.id = ?
      ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC, n.pertemuan_ke ASC
    ");
    $stmt->execute([$siswaId]);
    return $stmt->fetchAll();
  }

  public function findExistingNilai(string $jadwalId, int $pertemuanKe, string $tipeNilai): array|false {
    $stmt = $this->db->prepare("
      SELECT id FROM nilai
      WHERE jadwal_id = ? AND pertemuan_ke = ? AND tipe_nilai = ?
      LIMIT 1
    ");
    $stmt->execute([$jadwalId, $pertemuanKe, $tipeNilai]);
    return $stmt->fetch();
  }

  public function isJadwalOwnedByGuru(string $jadwalId, string $guruId): bool {
    $stmt = $this->db->prepare("
      SELECT 1
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE j.id = ? AND k.guru_id = ?
      LIMIT 1
    ");
    $stmt->execute([$jadwalId, $guruId]);
    return (bool)$stmt->fetchColumn();
  }

  public function findExistingNilaiByGuru(string $jadwalId, int $pertemuanKe, string $tipeNilai, string $guruId): array|false {
    $stmt = $this->db->prepare("
      SELECT n.id
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE n.jadwal_id = ?
        AND n.pertemuan_ke = ?
        AND n.tipe_nilai = ?
        AND k.guru_id = ?
      LIMIT 1
    ");
    $stmt->execute([$jadwalId, $pertemuanKe, $tipeNilai, $guruId]);
    return $stmt->fetch();
  }

  public function findNilaiByIdAndGuru(string $nilaiId, string $guruId): array|false {
    $stmt = $this->db->prepare("
      SELECT n.id, n.jadwal_id
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE n.id = ? AND k.guru_id = ?
      LIMIT 1
    ");
    $stmt->execute([$nilaiId, $guruId]);
    return $stmt->fetch();
  }
}
