<?php
// ============================================================
// controllers/admin/AdminNilaiController.php
// Halaman nilai admin + aksi delete nilai
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/nilai/NilaiModel.php';
require_once __DIR__ . '/../../../models/nilai/NilaiQueryService.php';
require_once __DIR__ . '/../../../models/admin/AdminGuruRepository.php';
require_once __DIR__ . '/../../../models/admin/AdminSiswaRepository.php';
require_once __DIR__ . '/AdminNilaiActionHandler.php';

class AdminNilaiController extends BaseAdminController
{
  private NilaiModel $nilaiModel;
  private NilaiQueryService $nilaiQueryService;
  private AdminGuruRepository $guruRepository;
  private AdminSiswaRepository $siswaRepository;
  private AdminNilaiActionHandler $nilaiActionHandler;

  public function __construct()
  {
    $this->nilaiModel = new NilaiModel();
    $this->nilaiQueryService = new NilaiQueryService();
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

    $filterGuru = isset($_GET['guru_id']) ? trim((string)$_GET['guru_id']) : '';
    $filterSiswa = isset($_GET['siswa_id']) ? trim((string)$_GET['siswa_id']) : '';

    $nilaiList = $this->nilaiQueryService->getNilaiByAdmin($filterGuru, $filterSiswa);

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
