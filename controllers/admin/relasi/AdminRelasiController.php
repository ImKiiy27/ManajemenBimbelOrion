<?php
// ============================================================
// controllers/admin/AdminRelasiController.php
// Halaman atur pengajar / relasi siswa-guru admin
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/admin/AdminRelasiRepository.php';
require_once __DIR__ . '/AdminRelasiActionHandler.php';

class AdminRelasiController extends BaseAdminController
{
  private AdminRelasiRepository $relasiRepository;
  private AdminRelasiActionHandler $relasiActionHandler;

  public function __construct()
  {
    $this->relasiRepository = new AdminRelasiRepository();
    $this->relasiActionHandler = new AdminRelasiActionHandler($this->relasiRepository);
  }

  public function relasi(): void
  {
    $pageTitle = 'Atur Pengajar - Bimbel Orion';
    $activePage = 'admin-relasi';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
        header('Location: index.php?page=admin-relasi');
        exit;
      }

      $action = trim($_POST['action'] ?? '');
      match ($action) {
        'create-relasi' => $this->relasiActionHandler->handleCreate(),
        'update-relasi' => $this->relasiActionHandler->handleUpdate(),
        'delete-relasi' => $this->relasiActionHandler->handleDelete(),
        default => $_SESSION['flash_error'] = 'Aksi relasi tidak dikenal.',
      };

      header('Location: index.php?page=admin-relasi');
      exit;
    }

    $relasiList = $this->relasiRepository->getRelasiList();
    $siswaOptions = $this->relasiRepository->getSiswaOptions();
    $guruOptions = $this->relasiRepository->getGuruOptions();
    $siswaMapelMatrix = $this->relasiRepository->getSiswaMapelMatrix();

    $totalRelasi = count($relasiList);
    $relasiAktif = $this->relasiRepository->countRelasiAktif();
    $siswaSiapJadwal = $this->relasiRepository->countSiswaSiapJadwal();

    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);

    $this->render('admin/relasi', compact(
      'pageTitle',
      'activePage',
      'relasiList',
      'siswaOptions',
      'guruOptions',
      'siswaMapelMatrix',
      'totalRelasi',
      'relasiAktif',
      'siswaSiapJadwal',
      'error',
      'success'
    ));
  }
}
