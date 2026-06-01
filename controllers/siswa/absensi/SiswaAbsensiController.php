<?php
// ============================================================
// controllers/siswa/SiswaAbsensiController.php
// Riwayat absensi siswa (read-only)
// ============================================================

require_once __DIR__ . '/../BaseSiswaController.php';
require_once __DIR__ . '/../../../models/absensi/AbsensiQueryService.php';
require_once __DIR__ . '/../../../helpers/SessionHelper.php';

class SiswaAbsensiController extends BaseSiswaController
{
  private AbsensiQueryService $queryService;

  public function __construct()
  {
    $this->queryService = new AbsensiQueryService();
  }

  /**
   * Tampilkan riwayat absensi siswa
   */
  public function absensi(): void
  {
    $siswaId = SessionHelper::getUserId();
    if (!$siswaId) {
      header('Location: index.php?page=login');
      exit;
    }

    // Get filter parameters
    $tanggalStart = trim($_GET['tgl_dari'] ?? date('Y-m-01'));
    $tanggalEnd = trim($_GET['tgl_sampai'] ?? date('Y-m-t'));

    // Get absensi history
    $absensiList = $this->queryService->getAbsensiHistorySiswa($siswaId, $tanggalStart, $tanggalEnd);

    // Calculate metrics
    $metrics = $this->calculateMetrics($absensiList);

    $pageTitle = 'Riwayat Absensi - Bimbel Orion';
    $activePage = 'siswa-absensi';

    $this->render('siswa/absensi', compact(
      'pageTitle',
      'activePage',
      'absensiList',
      'metrics',
      'tanggalStart',
      'tanggalEnd'
    ));
  }

  // ========== PRIVATE HELPERS ==========

  private function calculateMetrics(array $absensiList): array {
    $metrics = [
      'total' => count($absensiList),
      'hadir' => 0,
      'izin' => 0,
      'sakit' => 0,
      'alpa' => 0
    ];

    foreach ($absensiList as $item) {
      $status = strtolower((string)($item['status'] ?? ''));
      if (isset($metrics[$status])) {
        $metrics[$status]++;
      }
    }

    if ($metrics['total'] > 0) {
      $metrics['persentase_hadir'] = round(($metrics['hadir'] / $metrics['total']) * 100, 1);
    } else {
      $metrics['persentase_hadir'] = 0;
    }

    return $metrics;
  }
}
