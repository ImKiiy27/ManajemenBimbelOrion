<?php
// ============================================================
// controllers/wali_murid/WaliMuridDashboardController.php
// Dashboard wali murid: ringkasan anak, jadwal, nilai, absensi
// ============================================================

require_once __DIR__ . '/../BaseWaliMuridController.php';
require_once __DIR__ . '/../../../models/wali_murid/WaliMuridRepository.php';

class WaliMuridDashboardController extends BaseWaliMuridController
{
  private WaliMuridRepository $repo;

  public function __construct()
  {
    $this->repo = new WaliMuridRepository();
  }

  public function dashboard(): void
  {
    $pageTitle = 'Dashboard Wali Murid - Bimbel Orion';
    $activePage = 'wali-dashboard';
    $waliId = trim((string)($_SESSION['user_id'] ?? ''));

    $wali = $waliId !== '' ? $this->repo->getWaliById($waliId) : false;
    $anak = $waliId !== '' ? $this->repo->getAnak($waliId) : [];
    $summary = $waliId !== '' ? $this->repo->getDashboardSummary($waliId) : [];

    $this->render('wali_murid/dashboard', compact(
      'pageTitle',
      'activePage',
      'wali',
      'anak',
      'summary'
    ));
  }
}


