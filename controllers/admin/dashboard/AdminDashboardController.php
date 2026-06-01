<?php
// ============================================================
// controllers/admin/AdminDashboardController.php
// Halaman dashboard admin
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/pendaftaran/PendaftaranModel.php';
require_once __DIR__ . '/../../../models/jadwal/JadwalModel.php';

class AdminDashboardController extends BaseAdminController
{
  private PendaftaranModel $pendaftaranModel;
  private JadwalModel $jadwalModel;

  public function __construct()
  {
    $this->pendaftaranModel = new PendaftaranModel();
    $this->jadwalModel = new JadwalModel();
  }

  public function dashboard(): void
  {
    $pageTitle = 'Dashboard Admin - Bimbel Orion';
    $activePage = 'admin-dashboard';

    $pendaftaran = $this->pendaftaranModel->getPendaftaranTerbaru(10);
    $totalPendaftar = $this->pendaftaranModel->countPendaftaran();
    $totalJadwal = count($this->jadwalModel->getAllJadwal());

    $this->render('admin/dashboard', compact(
      'pageTitle',
      'activePage',
      'pendaftaran',
      'totalPendaftar',
      'totalJadwal'
    ));
  }
}

