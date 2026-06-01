<?php
// ============================================================
// controllers/admin/AdminMapelController.php
// Halaman master mapel admin
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/admin/AdminMapelRepository.php';
require_once __DIR__ . '/AdminMapelActionHandler.php';

class AdminMapelController extends BaseAdminController
{
  private AdminMapelRepository $mapelRepository;
  private AdminMapelActionHandler $mapelActionHandler;

  public function __construct()
  {
    $this->mapelRepository = new AdminMapelRepository();
    $this->mapelActionHandler = new AdminMapelActionHandler($this->mapelRepository);
  }

  public function mapel(): void
  {
    $pageTitle = 'Kelola Mapel - Bimbel Orion';
    $activePage = 'admin-mapel';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
        header('Location: index.php?page=admin-mapel');
        exit;
      }

      $action = trim($_POST['action'] ?? '');
      match ($action) {
        'create' => $this->mapelActionHandler->handleCreate(),
        'update' => $this->mapelActionHandler->handleUpdate(),
        'toggle-status' => $this->mapelActionHandler->handleToggleStatus(),
        default => $_SESSION['flash_error'] = 'Aksi mapel tidak dikenal.',
      };

      header('Location: index.php?page=admin-mapel');
      exit;
    }

    $mapel = $this->mapelRepository->getMapelList();
    $summary = $this->mapelRepository->getSummary($mapel);
    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);

    $this->render('admin/mapel', compact(
      'pageTitle',
      'activePage',
      'mapel',
      'summary',
      'error',
      'success'
    ));
  }
}
