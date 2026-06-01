<?php
// ============================================================
// controllers/admin/AdminSiswaController.php
// Halaman data siswa admin + aksi relasi mapel
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/admin/AdminSiswaRepository.php';
require_once __DIR__ . '/AdminSiswaActionHandler.php';

class AdminSiswaController extends BaseAdminController
{
  private AdminSiswaRepository $siswaRepository;
  private AdminSiswaActionHandler $siswaActionHandler;

  public function __construct()
  {
    $this->siswaRepository = new AdminSiswaRepository();
    $this->siswaActionHandler = new AdminSiswaActionHandler($this->siswaRepository);
  }

  public function siswa(): void
  {
    $pageTitle = 'Data Siswa - Bimbel Orion';
    $activePage = 'admin-siswa';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
        header('Location: index.php?page=admin-siswa');
        exit;
      }

      $action = trim($_POST['action'] ?? '');
      match ($action) {
        'update-siswa' => $this->siswaActionHandler->handleUpdateProfile(),
        default        => $_SESSION['flash_error'] = 'Aksi data siswa tidak dikenal.',
      };

      header('Location: index.php?page=admin-siswa');
      exit;
    }

    $siswa = $this->siswaRepository->getSiswaList();
    $mapelOptions = $this->siswaRepository->getActiveMapelOptions();
    $waliOptions = $this->siswaRepository->getWaliOptions();
    $siswaMapelSelected = [];
    foreach ($siswa as $row) {
      $siswaMapelSelected[(string)$row['id']] = $this->siswaRepository->getMapelIdsBySiswa((string)$row['id']);
    }

    $totalSiswa = count($siswa);
    $siswaLocked = count(array_filter($siswa, fn($row) => (int)$row['is_locked'] === 1));
    $siswaAktif = $totalSiswa - $siswaLocked;
    $totalMapelSiswa = $this->siswaRepository->countActiveSiswaMapel();
    $mapelAktif = $totalMapelSiswa;

    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);

    $this->render('admin/siswa', compact(
      'pageTitle',
      'activePage',
      'siswa',
      'mapelOptions',
      'waliOptions',
      'siswaMapelSelected',
      'totalSiswa',
      'siswaAktif',
      'siswaLocked',
      'totalMapelSiswa',
      'mapelAktif',
      'error',
      'success'
    ));
  }
}
