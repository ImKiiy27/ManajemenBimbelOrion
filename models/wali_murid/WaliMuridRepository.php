<?php
// ============================================================
// models/wali_murid/WaliMuridRepository.php
// Repository data wali murid: anak, jadwal, nilai, absensi
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';

class WaliMuridRepository
{
  private PDO $db;
  private IdCounterModel $idCounterModel;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null)
  {
    $this->db = $db ?? getDB();
    $this->idCounterModel = $idCounterModel ?? new IdCounterModel($this->db);
  }

  /**
   * Ambil data wali murid beserta nama
   */
  public function getWaliById(string $waliId): array|false
  {
    $stmt = $this->db->prepare("
      SELECT id, nama, no_telp AS no_telepon, alamat, pekerjaan, hubungan
      FROM wali_murid
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->execute([$waliId]);
    return $stmt->fetch();
  }

  /**
   * Ambil semua anak (siswa) yang terhubung dengan wali ini
   */
  public function getAnak(string $waliId): array
  {
    $stmt = $this->db->prepare("
      SELECT
        s.id,
        s.nama,
        s.kelas_sekolah,
        CASE
          WHEN EXISTS (SELECT 1 FROM kelas kx WHERE kx.siswa_id = s.id AND kx.status = 'aktif')
            OR EXISTS (
              SELECT 1 FROM siswa_mapel smx
              WHERE smx.siswa_id = s.id AND smx.status = 'aktif'
            )
          THEN 'aktif'
          ELSE 'nonaktif'
        END AS status,
        COALESCE(ms.mapel_siswa, '-') AS mapel_aktif,
        (SELECT COUNT(*) FROM kelas WHERE siswa_id = s.id AND status = 'aktif') AS total_kelas
      FROM siswa s
      LEFT JOIN (
        SELECT
          sm.siswa_id,
          GROUP_CONCAT(m.nama ORDER BY m.nama SEPARATOR ', ') AS mapel_siswa
        FROM siswa_mapel sm
        INNER JOIN mapel m ON m.id = sm.mapel_id
        WHERE sm.status = 'aktif'
        GROUP BY sm.siswa_id
      ) ms ON ms.siswa_id = s.id
      WHERE s.wali_id = ?
      ORDER BY s.nama ASC
    ");
    $stmt->execute([$waliId]);
    return $stmt->fetchAll();
  }

  /**
   * Ambil jadwal untuk seorang anak
   */
  public function getJadwalAnak(string $siswaId): array
  {
    $stmt = $this->db->prepare("
      SELECT
        j.id,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        g.nama AS guru_nama,
        mg.nama AS guru_mapel,
        COALESCE(ms.mapel_siswa, '-') AS mata_pelajaran
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
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
      ) ms ON ms.siswa_id = k.siswa_id
      WHERE k.siswa_id = ?
      ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC
    ");
    $stmt->execute([$siswaId]);
    return $stmt->fetchAll();
  }

  /**
   * Ambil nilai untuk seorang anak, grouped by jadwal
   */
  public function getNilaiAnak(string $siswaId): array
  {
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
        s.nama AS siswa_nama,
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
      ORDER BY n.jadwal_id ASC, n.pertemuan_ke ASC
    ");
    $stmt->execute([$siswaId]);
    return $stmt->fetchAll();
  }

  /**
   * Ambil absensi untuk seorang anak
   */
  public function getAbsensiAnak(string $siswaId): array
  {
    $stmt = $this->db->prepare("
      SELECT
        a.id,
        a.jadwal_id,
        a.tanggal,
        a.status,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        g.nama AS guru_nama,
        mg.nama AS guru_mapel,
        COALESCE(ms.mapel_siswa, '-') AS mata_pelajaran
      FROM absensi a
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
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
      ) ms ON ms.siswa_id = a.siswa_id
      WHERE a.siswa_id = ?
      ORDER BY a.tanggal DESC, j.jam_mulai DESC
    ");
    $stmt->execute([$siswaId]);
    return $stmt->fetchAll();
  }

  /**
   * Hitung summary untuk dashboard: total anak, total jadwal aktif, rata-rata nilai, kehadiran
   */
  public function getDashboardSummary(string $waliId): array
  {
    // Total anak
    $stmt1 = $this->db->prepare("SELECT COUNT(*) as total FROM siswa WHERE wali_id = ?");
    $stmt1->execute([$waliId]);
    $totalAnak = (int)$stmt1->fetch()['total'];

    // Total jadwal aktif
    $stmt2 = $this->db->prepare("
      SELECT COUNT(DISTINCT j.id) as total
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      WHERE s.wali_id = ? AND k.status = 'aktif'
    ");
    $stmt2->execute([$waliId]);
    $totalJadwal = (int)$stmt2->fetch()['total'];

    // Rata-rata nilai (predikat)
    $stmt3 = $this->db->prepare("
      SELECT
        ROUND(COUNT(n.id) / NULLIF(COUNT(DISTINCT s.id), 0), 1) as avg_nilai_count,
        SUM(CASE WHEN n.predikat = 'A' THEN 1 ELSE 0 END) as predikat_a,
        SUM(CASE WHEN n.predikat = 'B' THEN 1 ELSE 0 END) as predikat_b,
        SUM(CASE WHEN n.predikat = 'C' THEN 1 ELSE 0 END) as predikat_c
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      WHERE s.wali_id = ?
    ");
    $stmt3->execute([$waliId]);
    $nilaiSummary = $stmt3->fetch();

    // Kehadiran (rasio hadir vs total)
    $stmt4 = $this->db->prepare("
      SELECT
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
        COUNT(a.id) as total_absensi
      FROM absensi a
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      WHERE s.wali_id = ?
    ");
    $stmt4->execute([$waliId]);
    $absensiSummary = $stmt4->fetch();

    return [
      'total_anak' => $totalAnak,
      'total_jadwal' => $totalJadwal,
      'nilai_summary' => $nilaiSummary,
      'absensi_summary' => $absensiSummary,
    ];
  }
}
