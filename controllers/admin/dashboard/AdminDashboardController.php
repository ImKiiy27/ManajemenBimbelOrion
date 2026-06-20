<?php
// ============================================================
// controllers/admin/AdminDashboardController.php
// Halaman dashboard admin
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/pendaftaran/PendaftaranModel.php';
require_once __DIR__ . '/../../../models/jadwal/JadwalModel.php';
require_once __DIR__ . '/../../../models/admin/AdminGuruRepository.php';
require_once __DIR__ . '/../../../models/admin/AdminSiswaRepository.php';

class AdminDashboardController extends BaseAdminController
{
  private PendaftaranModel $pendaftaranModel;
  private JadwalModel $jadwalModel;
  private AdminGuruRepository $guruRepository;
  private AdminSiswaRepository $siswaRepository;

  public function __construct()
  {
    $this->pendaftaranModel = new PendaftaranModel();
    $this->jadwalModel = new JadwalModel();
    $this->guruRepository = new AdminGuruRepository();
    $this->siswaRepository = new AdminSiswaRepository();
  }

  public function dashboard(): void
  {
    $pageTitle = 'Dashboard Admin - Bimbel Orion';
    $activePage = 'admin-dashboard';

    $pendaftaran = $this->pendaftaranModel->getPendaftaranTerbaru(10);
    $totalPendaftar = $this->pendaftaranModel->countPendaftaran();
    $totalJadwal = count($this->jadwalModel->getAllJadwal());
    $totalGuru = count($this->guruRepository->getGuruOptions());
    $totalSiswa = count($this->siswaRepository->getSiswaOptions());

    $this->render('admin/dashboard', compact(
      'pageTitle',
      'activePage',
      'pendaftaran',
      'totalPendaftar',
      'totalJadwal',
      'totalGuru',
      'totalSiswa'
    ));
  }
}

