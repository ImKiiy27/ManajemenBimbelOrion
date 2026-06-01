<?php
// ============================================================
// controllers/guru/GuruNilaiController.php
// Halaman input nilai guru + aksi POST nilai
// ============================================================

require_once __DIR__ . '/../BaseGuruController.php';
require_once __DIR__ . '/../../../models/nilai/NilaiModel.php';
require_once __DIR__ . '/GuruNilaiActionHandler.php';

class GuruNilaiController extends BaseGuruController
{
  private NilaiModel $nilaiModel;
  private GuruNilaiActionHandler $nilaiActionHandler;

  public function __construct()
  {
    $this->nilaiModel = new NilaiModel();
    $this->nilaiActionHandler = new GuruNilaiActionHandler($this->nilaiModel);
  }

  public function nilai(): void
  {
    $pageTitle  = 'Input Nilai - Bimbel Orion';
    $activePage = 'guru-nilai';
    $guruId = trim((string)($_SESSION['user_id'] ?? ''));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
        header('Location: index.php?page=guru-nilai');
        exit;
      }

      $action = trim($_POST['action'] ?? '');
      match ($action) {
        'save-nilai' => $this->nilaiActionHandler->handleSave(),
        'update-nilai' => $this->nilaiActionHandler->handleUpdate(),
        'delete-nilai' => $this->nilaiActionHandler->handleDelete(),
        default => $_SESSION['flash_error'] = 'Aksi tidak dikenal.',
      };

      header('Location: index.php?page=guru-nilai');
      exit;
    }

    $jadwalList = $guruId !== '' ? $this->nilaiModel->getJadwalForNilaiInput($guruId) : [];
    $nilaiList = $guruId !== '' ? $this->nilaiModel->getNilaiByGuru($guruId) : [];

    $totalJadwal = count($jadwalList);
    $totalNilai = count($nilaiList);

    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);

    $this->render('guru/nilai', compact('pageTitle', 'activePage', 'jadwalList', 'nilaiList', 'totalJadwal', 'totalNilai', 'error', 'success'));
  }
}

