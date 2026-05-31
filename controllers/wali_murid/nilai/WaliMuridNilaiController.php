<?php
// ============================================================
// controllers/wali_murid/WaliMuridNilaiController.php
// Halaman nilai (perkembangan) anak-anak wali murid
// ============================================================

require_once __DIR__ . '/../BaseWaliMuridController.php';
require_once __DIR__ . '/../../../models/wali_murid/WaliMuridRepository.php';

class WaliMuridNilaiController extends BaseWaliMuridController
{
  private WaliMuridRepository $repo;

  public function __construct()
  {
    $this->repo = new WaliMuridRepository();
  }

  public function nilai(): void
  {
    $pageTitle = 'Nilai Anak - Bimbel Orion';
    $activePage = 'wali-nilai';
    $waliId = trim((string)($_SESSION['user_id'] ?? ''));

    $anak = $waliId !== '' ? $this->repo->getAnak($waliId) : [];

    // Kumpulkan nilai per anak
    $nilaiPerAnak = [];
    foreach ($anak as $a) {
      $nilaiPerAnak[$a['id']] = $this->repo->getNilaiAnak($a['id']);
    }

    $this->render('wali_murid/nilai', compact(
      'pageTitle',
      'activePage',
      'anak',
      'nilaiPerAnak'
    ));
  }
}


