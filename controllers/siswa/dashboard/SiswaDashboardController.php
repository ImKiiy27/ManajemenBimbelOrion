<?php
// ============================================================
// controllers/siswa/SiswaDashboardController.php
// Halaman dashboard siswa
// ============================================================

require_once __DIR__ . '/../BaseSiswaController.php';
require_once __DIR__ . '/../../../models/siswa/SiswaDashboardRepository.php';

class SiswaDashboardController extends BaseSiswaController
{
  private SiswaDashboardRepository $dashboardRepository;

  public function __construct()
  {
    $this->dashboardRepository = new SiswaDashboardRepository();
  }

  public function dashboard(): void
  {
    $pageTitle  = 'Dashboard Siswa - Bimbel Orion';
    $activePage = 'siswa-dashboard';
    $siswaId = trim((string)($_SESSION['user_id'] ?? ''));
    $metrics = $siswaId !== '' ? $this->dashboardRepository->getMetrics($siswaId) : [];
    $jadwalRingkas = $siswaId !== '' ? $this->dashboardRepository->getJadwalRingkas($siswaId) : [];
    $nilaiTerbaru = $siswaId !== '' ? $this->dashboardRepository->getNilaiTerbaru($siswaId) : [];

    $this->render('siswa/dashboard', compact(
      'pageTitle',
      'activePage',
      'metrics',
      'jadwalRingkas',
      'nilaiTerbaru'
    ));
  }
}

