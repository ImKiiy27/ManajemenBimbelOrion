<?php
// ============================================================
// controllers/siswa/SiswaDashboardController.php
// Halaman dashboard siswa
// ============================================================

require_once __DIR__ . '/../BaseSiswaController.php';

class SiswaDashboardController extends BaseSiswaController
{
  public function dashboard(): void
  {
    $pageTitle  = 'Dashboard Siswa - Bimbel Orion';
    $activePage = 'siswa-dashboard';
    $this->render('siswa/dashboard', compact('pageTitle', 'activePage'));
  }
}

