<?php
// ============================================================
// controllers/guru/GuruDashboardController.php
// Halaman dashboard guru
// ============================================================

require_once __DIR__ . '/../BaseGuruController.php';
require_once __DIR__ . '/../../../models/guru/GuruDashboardRepository.php';

class GuruDashboardController extends BaseGuruController
{
  private GuruDashboardRepository $dashboardRepository;

  public function __construct()
  {
    $this->dashboardRepository = new GuruDashboardRepository();
  }

  public function dashboard(): void
  {
    $pageTitle  = 'Dashboard Guru - Bimbel Orion';
    $activePage = 'guru-dashboard';
    $guruId = trim((string)($_SESSION['user_id'] ?? ''));
    $metrics = $guruId !== '' ? $this->dashboardRepository->getMetrics($guruId) : [];
    $jadwalHariIni = $guruId !== '' ? $this->dashboardRepository->getJadwalHariIni($guruId) : [];

    $this->render('guru/dashboard', compact('pageTitle', 'activePage', 'metrics', 'jadwalHariIni'));
  }
}

