<?php
// ============================================================
// controllers/wali_murid/WaliMuridAbsensiController.php
// Riwayat absensi anak-anak wali murid (read-only)
// ============================================================

require_once __DIR__ . '/../BaseWaliMuridController.php';
require_once __DIR__ . '/../../../models/absensi/AbsensiQueryService.php';
require_once __DIR__ . '/../../../helpers/SessionHelper.php';

class WaliMuridAbsensiController extends BaseWaliMuridController
{
  private AbsensiQueryService $queryService;

  public function __construct()
  {
    $this->queryService = new AbsensiQueryService();
  }

  /**
   * Tampilkan riwayat absensi anak-anak wali murid
   */
  public function absensi(): void
  {
    $waliId = SessionHelper::getUserId();
    if (!$waliId) {
      header('Location: index.php?page=login');
      exit;
    }

    // Get absensi history untuk anak-anak wali
    $absensiList = $this->queryService->getAbsensiHistoryWaliMurid($waliId);

    // Group by siswa
    $groupedByStudent = $this->groupAbsensiByStudent($absensiList);

    // Calculate overall metrics
    $metrics = $this->calculateMetrics($absensiList);

    $pageTitle = 'Riwayat Absensi Anak - Bimbel Orion';
    $activePage = 'wali-absensi';

    $this->render('wali_murid/absensi', compact(
      'pageTitle',
      'activePage',
      'groupedByStudent',
      'metrics',
      'absensiList'
    ));
  }

  // ========== PRIVATE HELPERS ==========

  private function groupAbsensiByStudent(array $absensiList): array {
    $grouped = [];

    foreach ($absensiList as $item) {
      $siswaId = $item['siswa_id'];
      if (!isset($grouped[$siswaId])) {
        $grouped[$siswaId] = [
          'siswa_id' => $siswaId,
          'siswa_nama' => $item['siswa_nama'],
          'data' => []
        ];
      }
      $grouped[$siswaId]['data'][] = $item;
    }

    return $grouped;
  }

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

