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
}
