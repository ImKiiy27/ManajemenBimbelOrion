<?php
// ============================================================
// controllers/admin/AdminJadwalController.php
// Halaman jadwal admin + aksi CRUD jadwal
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/jadwal/JadwalModel.php';
require_once __DIR__ . '/AdminJadwalActionHandler.php';

class AdminJadwalController extends BaseAdminController
{
  private JadwalModel $jadwalModel;
  private AdminJadwalActionHandler $jadwalActionHandler;

  public function __construct()
  {
    $this->jadwalModel = new JadwalModel();
    $this->jadwalActionHandler = new AdminJadwalActionHandler($this->jadwalModel);
  }

  public function jadwal(): void
  {
    $pageTitle = 'Jadwal - Bimbel Orion';
    $activePage = 'admin-jadwal';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
        header('Location: index.php?page=admin-jadwal');
        exit;
      }

      $action = trim($_POST['action'] ?? '');
      match ($action) {
        'create-jadwal' => $this->jadwalActionHandler->handleCreate(),
        'update-jadwal' => $this->jadwalActionHandler->handleUpdate(),
        'delete-jadwal' => $this->jadwalActionHandler->handleDelete(),
        default         => $_SESSION['flash_error'] = 'Aksi jadwal tidak dikenal.',
      };

      header('Location: index.php?page=admin-jadwal');
      exit;
    }

    $jadwal = $this->jadwalModel->getAllJadwal();
    $relasiMapelAktif = $this->jadwalModel->getRelasiMapelAktif();
    $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    $hariIni = $this->jadwalActionHandler->hariIndonesia((int)date('N'));
    $totalJadwal = count($jadwal);
    $jadwalHariIni = count(array_filter($jadwal, fn($row) => ($row['hari'] ?? '') === $hariIni));
    $totalGuruTerjadwal = count(array_unique(array_filter(array_map(
      fn($row) => $row['guru_id'] ?? null,
      $jadwal
    ))));
    $totalSiswaTerjadwal = count(array_unique(array_filter(array_map(
      fn($row) => $row['siswa_id'] ?? null,
      $jadwal
    ))));

    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);

    $this->render('admin/jadwal', compact(
      'pageTitle',
      'activePage',
      'jadwal',
      'relasiMapelAktif',
      'totalJadwal',
      'jadwalHariIni',
      'totalGuruTerjadwal',
      'totalSiswaTerjadwal',
      'error',
      'success',
      'hariIni',
      'hariList'
    ));
  }
}
