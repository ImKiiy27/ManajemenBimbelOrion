<?php
// ============================================================
// controllers/guru/GuruJadwalController.php
// Halaman jadwal guru
// ============================================================

require_once __DIR__ . '/../BaseGuruController.php';
require_once __DIR__ . '/../../../models/jadwal/JadwalModel.php';

class GuruJadwalController extends BaseGuruController
{
  private JadwalModel $jadwalModel;

  public function __construct()
  {
    $this->jadwalModel = new JadwalModel();
  }

  public function jadwal(): void
  {
    $pageTitle  = 'Jadwal Mengajar - Bimbel Orion';
    $activePage = 'guru-jadwal';
    $guruId = trim((string)($_SESSION['user_id'] ?? ''));
    $jadwal = $guruId !== '' ? $this->jadwalModel->getJadwalByGuru($guruId) : [];
    $totalJadwal = count($jadwal);
    $this->render('guru/jadwal', compact('pageTitle', 'activePage', 'jadwal', 'totalJadwal'));
  }
}

