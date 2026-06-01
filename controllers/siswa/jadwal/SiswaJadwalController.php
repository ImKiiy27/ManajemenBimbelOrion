<?php
// ============================================================
// controllers/siswa/SiswaJadwalController.php
// Halaman jadwal siswa
// ============================================================

require_once __DIR__ . '/../BaseSiswaController.php';
require_once __DIR__ . '/../../../models/jadwal/JadwalModel.php';

class SiswaJadwalController extends BaseSiswaController
{
  private JadwalModel $jadwalModel;

  public function __construct()
  {
    $this->jadwalModel = new JadwalModel();
  }

  public function jadwal(): void
  {
    $pageTitle  = 'Jadwal Les - Bimbel Orion';
    $activePage = 'siswa-jadwal';
    $siswaId = trim((string)($_SESSION['user_id'] ?? ''));
    $jadwal = $siswaId !== '' ? $this->jadwalModel->getJadwalBySiswa($siswaId) : [];
    $totalJadwal = count($jadwal);
    $this->render('siswa/jadwal', compact('pageTitle', 'activePage', 'jadwal', 'totalJadwal'));
  }
}

