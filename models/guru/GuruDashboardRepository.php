<?php
// ============================================================
// models/guru/GuruDashboardRepository.php
// Query dashboard guru (roadmap)
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class GuruDashboardRepository
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }

  public function getMetrics(string $guruId): array
  {
    $jadwalStmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE k.guru_id = ?
    ");
    $jadwalStmt->execute([$guruId]);

    $siswaStmt = $this->db->prepare("
      SELECT COUNT(DISTINCT k.siswa_id)
      FROM kelas k
      WHERE k.guru_id = ?
        AND k.status = 'aktif'
    ");
    $siswaStmt->execute([$guruId]);

    $absensiStmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM absensi a
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE k.guru_id = ?
        AND a.tanggal = CURDATE()
    ");
    $absensiStmt->execute([$guruId]);

    return [
      'total_jadwal' => (int)$jadwalStmt->fetchColumn(),
      'total_siswa' => (int)$siswaStmt->fetchColumn(),
      'absensi_hari_ini' => (int)$absensiStmt->fetchColumn(),
    ];
  }

  public function getJadwalHariIni(string $guruId): array
  {
    $hariIni = [
      1 => 'Senin',
      2 => 'Selasa',
      3 => 'Rabu',
      4 => 'Kamis',
      5 => 'Jumat',
      6 => 'Sabtu',
      7 => 'Minggu',
    ][(int)date('N')];

    $stmt = $this->db->prepare("
      SELECT
        j.jam_mulai,
        j.jam_selesai,
        s.nama AS siswa_nama,
        COALESCE(m.nama, mg.nama, '-') AS mata_pelajaran
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
        AND j.hari = ?
      ORDER BY j.jam_mulai ASC
      LIMIT 5
    ");
    $stmt->execute([$guruId, $hariIni]);
    return $stmt->fetchAll();
  }
}

