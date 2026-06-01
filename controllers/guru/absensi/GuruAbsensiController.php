<?php
// ============================================================
// controllers/guru/GuruAbsensiController.php
// Input absensi untuk guru
// ============================================================

require_once __DIR__ . '/../BaseGuruController.php';
require_once __DIR__ . '/../../../models/absensi/AbsensiCommandService.php';
require_once __DIR__ . '/../../../models/absensi/AbsensiQueryService.php';
require_once __DIR__ . '/../../../helpers/SessionHelper.php';
require_once __DIR__ . '/GuruAbsensiActionHandler.php';

class GuruAbsensiController extends BaseGuruController
{
  private AbsensiCommandService $commandService;
  private AbsensiQueryService $queryService;
  private GuruAbsensiActionHandler $actionHandler;

  public function __construct()
  {
    $this->commandService = new AbsensiCommandService();
    $this->queryService = new AbsensiQueryService();
    $this->actionHandler = new GuruAbsensiActionHandler($this->commandService, $this->queryService);
  }

  /**
   * Tampilkan halaman input absensi
   */
  public function absensi(): void
  {
    $guruId = SessionHelper::getUserId();
    if (!$guruId) {
      header('Location: index.php?page=login');
      exit;
    }

    // Get jadwal milik guru
    $jadwalList = $this->queryService->getJadwalByGuru($guruId);

    $pageTitle = 'Input Absensi - Bimbel Orion';
    $activePage = 'guru-absensi';

    $this->render('guru/absensi', compact(
      'pageTitle',
      'activePage',
      'jadwalList'
    ));
  }

  /**
   * Load siswa dalam jadwal (AJAX)
   */
  public function loadSiswaInJadwal(): void
  {
    $this->actionHandler->loadSiswaInJadwal(fn(string $jadwalId) => $this->getJadwalWithValidation($jadwalId));
  }

  /**
   * Load data absensi untuk siswa & jadwal (untuk edit/show)
   */
  public function loadAbsensiData(): void
  {
    $this->actionHandler->loadAbsensiData(fn(string $jadwalId) => $this->getJadwalWithValidation($jadwalId));
  }

  /**
   * Save absensi (create/update)
   */
  public function saveAbsensi(): void
  {
    $this->actionHandler->saveAbsensi(fn(string $jadwalId) => $this->getJadwalWithValidation($jadwalId));
  }

  /**
   * Get riwayat absensi (untuk list view)
   */
  public function riwayat(): void
  {
    $guruId = SessionHelper::getUserId();

    $tanggalStart = $_GET['tgl_dari'] ?? date('Y-m-01');
    $tanggalEnd = $_GET['tgl_sampai'] ?? date('Y-m-t');

    $absensiList = $this->queryService->getAbsensiByGuru($guruId, $tanggalStart, $tanggalEnd);
    $metrics = $this->queryService->getGuruDashboardMetrics($guruId);

    $pageTitle = 'Riwayat Absensi - Bimbel Orion';
    $activePage = 'guru-absensi';

    $this->render('guru/absensi-riwayat', compact(
      'pageTitle',
      'activePage',
      'absensiList',
      'metrics',
      'tanggalStart',
      'tanggalEnd'
    ));
  }

  // ========== PRIVATE HELPERS ==========

  /**
   * Validate jadwal ownership (security critical)
   */
  private function getJadwalWithValidation(string $jadwalId): array|false {
    $guruId = SessionHelper::getUserId();
    if (!$guruId) {
      return false;
    }
    return $this->queryService->getJadwalWithValidation($jadwalId, $guruId);
  }
}
