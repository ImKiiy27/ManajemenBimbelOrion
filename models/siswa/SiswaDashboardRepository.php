<?php
// ============================================================
// models/siswa/SiswaDashboardRepository.php
// Query dashboard siswa (roadmap)
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class SiswaDashboardRepository
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }

  public function getMetrics(string $siswaId): array
  {
    $mapelStmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM siswa_mapel
      WHERE siswa_id = ?
        AND status = 'aktif'
    ");
    $mapelStmt->execute([$siswaId]);

    $jadwalStmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE k.siswa_id = ?
    ");
    $jadwalStmt->execute([$siswaId]);

    $hadirStmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM absensi
      WHERE siswa_id = ?
        AND status = 'Hadir'
    ");
    $hadirStmt->execute([$siswaId]);

    return [
      'total_mapel' => (int)$mapelStmt->fetchColumn(),
      'total_jadwal' => (int)$jadwalStmt->fetchColumn(),
      'total_hadir' => (int)$hadirStmt->fetchColumn(),
    ];
  }

  public function getJadwalRingkas(string $siswaId): array
  {
    $hariIni = $this->getHariIniIndonesia();

    $stmt = $this->db->prepare("
      SELECT
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        g.nama AS guru_nama,
        COALESCE(m.nama, mg.nama, '-') AS mata_pelajaran
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
        AND j.hari = ?
      ORDER BY j.jam_mulai ASC
    ");
    $stmt->execute([$siswaId, $hariIni]);
    return $stmt->fetchAll();
  }

  public function getNilaiTerbaru(string $siswaId): array
  {
    $stmt = $this->db->prepare("
      SELECT
        n.tipe_nilai,
        n.predikat,
        n.catatan_guru,
        COALESCE(m.nama, mg.nama, '-') AS mata_pelajaran
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN mapel mg ON mg.id = g.mapel_id
      LEFT JOIN siswa_mapel sm
        ON sm.siswa_id = k.siswa_id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      LEFT JOIN mapel m ON m.id = sm.mapel_id
      WHERE k.siswa_id = ?
      ORDER BY n.pertemuan_ke DESC, n.id DESC
      LIMIT 5
    ");
    $stmt->execute([$siswaId]);
    return $stmt->fetchAll();
  }

  private function getHariIniIndonesia(): string
  {
    $dayName = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format('l');

    return [
      'Monday' => 'Senin',
      'Tuesday' => 'Selasa',
      'Wednesday' => 'Rabu',
      'Thursday' => 'Kamis',
      'Friday' => 'Jumat',
      'Saturday' => 'Sabtu',
      'Sunday' => 'Minggu',
    ][$dayName] ?? '';
  }
}

