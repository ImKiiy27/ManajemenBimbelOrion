<?php
// ============================================================
// controllers/guru/GuruDashboardController.php
// Halaman dashboard guru
// ============================================================

require_once __DIR__ . '/../BaseGuruController.php';

class GuruDashboardController extends BaseGuruController
{
  public function dashboard(): void
  {
    $pageTitle  = 'Dashboard Guru - Bimbel Orion';
    $activePage = 'guru-dashboard';
    $this->render('guru/dashboard', compact('pageTitle', 'activePage'));
  }
}

