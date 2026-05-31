<?php
// ============================================================
// controllers/admin/AdminGuruController.php
// Halaman data guru admin
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/admin/AdminGuruRepository.php';
require_once __DIR__ . '/AdminGuruActionHandler.php';

class AdminGuruController extends BaseAdminController
{
  private AdminGuruRepository $guruRepository;
  private AdminGuruActionHandler $guruActionHandler;

  public function __construct()
  {
    $this->guruRepository = new AdminGuruRepository();
    $this->guruActionHandler = new AdminGuruActionHandler($this->guruRepository);
  }

  public function guru(): void
  {
    $pageTitle = 'Data Guru - Bimbel Orion';
    $activePage = 'admin-guru';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
        header('Location: index.php?page=admin-guru');
        exit;
      }

      $action = trim($_POST['action'] ?? '');
      match ($action) {
        'update-guru' => $this->guruActionHandler->handleUpdateProfile(),
        default => $_SESSION['flash_error'] = 'Aksi data guru tidak dikenal.',
      };

      header('Location: index.php?page=admin-guru');
      exit;
    }

    $guru = $this->guruRepository->getGuruList();
    $mapelOptions = $this->guruRepository->getActiveMapelOptions();

    $totalGuru = count($guru);
    $guruLocked = count(array_filter($guru, fn($row) => (int)$row['is_locked'] === 1));
    $guruAktif = $totalGuru - $guruLocked;
    $guruSudahSetMapel = count(array_filter($guru, fn($row) => trim((string)($row['mapel_id'] ?? '')) !== ''));
    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);

    $this->render('admin/guru', compact(
      'pageTitle',
      'activePage',
      'guru',
      'mapelOptions',
      'totalGuru',
      'guruAktif',
      'guruLocked',
      'guruSudahSetMapel',
      'error',
      'success'
    ));
  }
}
