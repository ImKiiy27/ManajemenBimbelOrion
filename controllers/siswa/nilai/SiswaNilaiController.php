<?php
// ============================================================
// controllers/siswa/SiswaNilaiController.php
// Halaman nilai siswa
// ============================================================

require_once __DIR__ . '/../BaseSiswaController.php';
require_once __DIR__ . '/../../../models/nilai/NilaiQueryService.php';

class SiswaNilaiController extends BaseSiswaController
{
  private NilaiQueryService $nilaiQueryService;

  public function __construct()
  {
    $this->nilaiQueryService = new NilaiQueryService();
  }

  public function nilai(): void
  {
    $pageTitle  = 'Nilai Saya - Bimbel Orion';
    $activePage = 'siswa-nilai';
    $siswaId = trim((string)($_SESSION['user_id'] ?? ''));

    $nilaiSiswa = [];
    if ($siswaId !== '') {
      $nilaiSiswa = $this->nilaiQueryService->getNilaiBySiswa($siswaId);
    }

    $totalNilai = count($nilaiSiswa);
    $this->render('siswa/nilai', compact('pageTitle', 'activePage', 'nilaiSiswa', 'totalNilai'));
  }
}

