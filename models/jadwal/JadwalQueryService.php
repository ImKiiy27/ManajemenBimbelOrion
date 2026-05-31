<?php
// ============================================================
// models/jadwal/JadwalQueryService.php
// Fokus: read/query data jadwal
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class JadwalQueryService {

  private PDO $db;

  public function __construct(?PDO $db = null) {
    $this->db = $db ?? getDB();
  }

  public function getAllJadwal(): array {
    $stmt = $this->db->query("
      SELECT
        j.id,
        j.kelas_id,
        sm.id AS siswa_mapel_id,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        k.siswa_id,
        k.guru_id,
        k.status AS kelas_status,
        s.nama AS siswa_nama,
        s.kelas_sekolah AS siswa_kelas,
        g.nama AS guru_nama,
        mg.nama AS guru_mapel,
        m.id AS mapel_id,
        m.nama AS mata_pelajaran
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN mapel mg ON mg.id = g.mapel_id
      LEFT JOIN siswa_mapel sm
        ON sm.siswa_id = k.siswa_id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      LEFT JOIN mapel m ON m.id = sm.mapel_id
      ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC
    ");
    return $stmt->fetchAll();
  }

  public function getRelasiMapelAktif(): array {
    $stmt = $this->db->query("
      SELECT
        sm.id,
        k.id AS kelas_id,
        k.siswa_id,
        k.guru_id,
        sm.mapel_id,
        sm.status,
        s.nama AS siswa_nama,
        s.kelas_sekolah AS siswa_kelas,
        g.nama AS guru_nama,
        mg.nama AS guru_mapel,
        m.nama AS mata_pelajaran
      FROM siswa_mapel sm
      INNER JOIN kelas k ON k.siswa_id = sm.siswa_id AND k.status = 'aktif'
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      INNER JOIN mapel mg ON mg.id = g.mapel_id
      INNER JOIN mapel m ON m.id = sm.mapel_id
      WHERE sm.status = 'aktif'
        AND g.mapel_id = sm.mapel_id
      ORDER BY s.nama ASC, m.nama ASC, g.nama ASC
    ");
    return $stmt->fetchAll();
  }

  public function getJadwalById(string $jadwalId): array|false {
    $stmt = $this->db->prepare("
      SELECT
        j.id,
        j.kelas_id,
        sm.id AS siswa_mapel_id,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        k.siswa_id,
        k.guru_id,
        sm.mapel_id,
        s.nama AS siswa_nama,
        g.nama AS guru_nama
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN siswa_mapel sm
        ON sm.siswa_id = k.siswa_id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      WHERE j.id = ?
      LIMIT 1
    ");
    $stmt->execute([$jadwalId]);
    return $stmt->fetch();
  }

  public function getJadwalByGuru(string $guruId): array {
    $stmt = $this->db->prepare("
      SELECT
        j.id,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
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
      WHERE k.guru_id = ?
      ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC
    ");
    $stmt->execute([$guruId]);
    return $stmt->fetchAll();
  }

  public function getJadwalBySiswa(string $siswaId): array {
    $stmt = $this->db->prepare("
      SELECT
        j.id,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        g.nama AS guru_nama,
        mg.nama AS guru_mapel,
        m.nama AS mata_pelajaran
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN mapel mg ON mg.id = g.mapel_id
      LEFT JOIN siswa_mapel sm
        ON sm.siswa_id = k.siswa_id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      LEFT JOIN mapel m ON m.id = sm.mapel_id
      WHERE k.siswa_id = ?
      ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC
    ");
    $stmt->execute([$siswaId]);
    return $stmt->fetchAll();
  }
}
