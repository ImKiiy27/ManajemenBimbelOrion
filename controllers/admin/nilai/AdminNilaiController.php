<?php
// ============================================================
// controllers/admin/AdminNilaiController.php
// Halaman nilai admin + aksi delete nilai
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/nilai/NilaiModel.php';
require_once __DIR__ . '/../../../models/admin/AdminGuruRepository.php';
require_once __DIR__ . '/../../../models/admin/AdminSiswaRepository.php';
require_once __DIR__ . '/AdminNilaiActionHandler.php';

class AdminNilaiController extends BaseAdminController
{
  private NilaiModel $nilaiModel;
  private AdminGuruRepository $guruRepository;
  private AdminSiswaRepository $siswaRepository;
  private AdminNilaiActionHandler $nilaiActionHandler;

  public function __construct()
  {
    $this->nilaiModel = new NilaiModel();
    $this->guruRepository = new AdminGuruRepository();
    $this->siswaRepository = new AdminSiswaRepository();
    $this->nilaiActionHandler = new AdminNilaiActionHandler($this->nilaiModel);
  }

  public function nilai(): void
  {
    $pageTitle = 'Nilai - Bimbel Orion';
    $activePage = 'admin-nilai';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
        header('Location: index.php?page=admin-nilai');
        exit;
      }

      $action = trim($_POST['action'] ?? '');
      match ($action) {
        'delete-nilai' => $this->nilaiActionHandler->handleDelete(),
        default => $_SESSION['flash_error'] = 'Aksi tidak dikenal.',
      };

      header('Location: index.php?page=admin-nilai');
      exit;
    }

    $db = getDB();
    $filterGuru = isset($_GET['guru_id']) ? trim((string)$_GET['guru_id']) : '';
    $filterSiswa = isset($_GET['siswa_id']) ? trim((string)$_GET['siswa_id']) : '';

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
    if ($filterGuru !== '') {
      $sql .= " AND g.id = ?";
      $params[] = $filterGuru;
    }
    if ($filterSiswa !== '') {
      $sql .= " AND s.id = ?";
      $params[] = $filterSiswa;
    }

    $sql .= " ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC, n.pertemuan_ke ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $nilaiList = $stmt->fetchAll();

    $guruOptions = $this->guruRepository->getGuruOptions();
    $siswaOptions = $this->siswaRepository->getSiswaOptions();

    $totalNilai = count($nilaiList);
    $totalGuru = count($guruOptions);
    $totalSiswa = count($siswaOptions);

    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);

    $this->render('admin/nilai', compact(
      'pageTitle',
      'activePage',
      'nilaiList',
      'guruOptions',
      'siswaOptions',
      'totalNilai',
      'totalGuru',
      'totalSiswa',
      'filterGuru',
      'filterSiswa',
      'error',
      'success'
    ));
  }
}
