<?php
// ============================================================
// controllers/admin/AdminWaliMuridController.php
// Halaman data wali murid admin
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/admin/AdminWaliMuridRepository.php';

class AdminWaliMuridController extends BaseAdminController
{
  private AdminWaliMuridRepository $waliRepository;

  public function __construct()
  {
    $this->waliRepository = new AdminWaliMuridRepository();
  }

  public function waliMurid(): void
  {
    $pageTitle = 'Data Wali Murid - Bimbel Orion';
    $activePage = 'admin-wali-murid';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
        header('Location: index.php?page=admin-wali-murid');
        exit;
      }

      $action = trim($_POST['action'] ?? '');
      $result = match ($action) {
        'create-wali' => $this->waliRepository->createWaliMurid($_POST),
        'update-wali' => $this->waliRepository->updateWaliMurid(trim((string)($_POST['wali_id'] ?? '')), $_POST),
        'delete-wali' => $this->waliRepository->deleteWaliMurid(trim((string)($_POST['wali_id'] ?? ''))),
        'delete-wali-force' => $this->waliRepository->forceDeleteWaliMurid(trim((string)($_POST['wali_id'] ?? ''))),
        default => ['status' => 'error', 'message' => 'Aksi wali murid tidak dikenal.'],
      };

      $_SESSION['flash_' . $result['status']] = $result['message'] ?? (
        $result['status'] === 'success' ? 'Aksi berhasil diproses.' : 'Aksi gagal diproses.'
      );
      header('Location: index.php?page=admin-wali-murid');
      exit;
    }

    $waliMurid = $this->waliRepository->getWaliMuridList();
    $totalWali = count($waliMurid);
    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);

    $this->render('admin/wali_murid', compact(
      'pageTitle',
      'activePage',
      'waliMurid',
      'totalWali',
      'error',
      'success'
    ));
  }

  public function getSiswa(): void
  {
    header('Content-Type: application/json');

    $waliId = trim((string)($_GET['wali_id'] ?? ''));
    if ($waliId === '') {
      http_response_code(400);
      echo json_encode(['status' => 'error', 'message' => 'ID wali tidak valid.', 'siswa' => []]);
      return;
    }

    echo json_encode([
      'status' => 'success',
      'siswa' => $this->waliRepository->getWaliMuridSiswa($waliId),
    ]);
  }
}
