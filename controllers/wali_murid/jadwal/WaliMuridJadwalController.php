<?php
// ============================================================
// controllers/wali_murid/WaliMuridJadwalController.php
// Jadwal pembelajaran anak-anak wali murid
// ============================================================

require_once __DIR__ . '/../BaseWaliMuridController.php';
require_once __DIR__ . '/../../../models/wali_murid/WaliMuridRepository.php';

class WaliMuridJadwalController extends BaseWaliMuridController
{
  private WaliMuridRepository $repo;

  public function __construct()
  {
    $this->repo = new WaliMuridRepository();
  }

  public function jadwal(): void
  {
    $pageTitle = 'Jadwal Anak - Bimbel Orion';
    $activePage = 'wali-jadwal';
    $waliId = trim((string)($_SESSION['user_id'] ?? ''));

    $anak = $waliId !== '' ? $this->repo->getAnak($waliId) : [];

    // Kumpulkan jadwal per anak
    $jadwalPerAnak = [];
    foreach ($anak as $a) {
      $jadwalPerAnak[$a['id']] = $this->repo->getJadwalAnak($a['id']);
    }

    $this->render('wali_murid/jadwal', compact(
      'pageTitle',
      'activePage',
      'anak',
      'jadwalPerAnak'
    ));
  }
}
