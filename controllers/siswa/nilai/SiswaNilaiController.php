<?php
// ============================================================
// controllers/siswa/SiswaNilaiController.php
// Halaman nilai siswa
// ============================================================

require_once __DIR__ . '/../BaseSiswaController.php';

class SiswaNilaiController extends BaseSiswaController
{
  public function nilai(): void
  {
    $pageTitle  = 'Nilai Saya - Bimbel Orion';
    $activePage = 'siswa-nilai';
    $siswaId = trim((string)($_SESSION['user_id'] ?? ''));

    $nilaiSiswa = [];
    if ($siswaId !== '') {
      $db = getDB();
      $stmt = $db->prepare("
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
      $nilaiSiswa = $stmt->fetchAll();
    }

    $totalNilai = count($nilaiSiswa);
    $this->render('siswa/nilai', compact('pageTitle', 'activePage', 'nilaiSiswa', 'totalNilai'));
  }
}

