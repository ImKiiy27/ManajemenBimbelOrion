<?php
// ============================================================
// controllers/admin/AdminAbsensiController.php
// Admin dashboard, filter, koreksi absensi
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/absensi/AbsensiCommandService.php';
require_once __DIR__ . '/../../../models/absensi/AbsensiQueryService.php';
require_once __DIR__ . '/../../../helpers/SessionHelper.php';

class AdminAbsensiController extends BaseAdminController
{
  private AbsensiCommandService $commandService;
  private AbsensiQueryService $queryService;

  public function __construct()
  {
    $this->commandService = new AbsensiCommandService();
    $this->queryService = new AbsensiQueryService();
  }

  /**
   * Dashboard absensi dengan rekap & filter
   */
  public function absensi(): void
  {
    // Get filter parameters
    $guruId = trim($_GET['guru_id'] ?? '');
    $siswaId = trim($_GET['siswa_id'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $tanggalStart = trim($_GET['tgl_dari'] ?? date('Y-m-01'));
    $tanggalEnd = trim($_GET['tgl_sampai'] ?? date('Y-m-t'));
    $page = max(1, (int)($_GET['p'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Get data
    $absensiList = $this->queryService->getAbsensiByAdmin(
      $guruId ?: null,
      $siswaId ?: null,
      $status ?: null,
      $tanggalStart,
      $tanggalEnd,
      $perPage,
      $offset
    );

    $totalCount = $this->queryService->countAbsensiByAdmin(
      $guruId ?: null,
      $siswaId ?: null,
      $status ?: null,
      $tanggalStart,
      $tanggalEnd
    );

    $totalPages = max(1, (int)ceil($totalCount / $perPage));

    // Get filter options
    $guruList = $this->getGuruList();
    $siswaList = $this->getSiswaList();

    $pageTitle = 'Absensi - Bimbel Orion';
    $activePage = 'admin-absensi';

    $this->render('admin/absensi', compact(
      'pageTitle',
      'activePage',
      'absensiList',
      'guruList',
      'siswaList',
      'guruId',
      'siswaId',
      'status',
      'tanggalStart',
      'tanggalEnd',
      'page',
      'totalPages',
      'totalCount'
    ));
  }

  /**
   * Save correction (POST with audit reason)
   */
  public function saveCorrection(): void
  {
    header('Content-Type: application/json');

    try {
      // Validate CSRF
      if (!SessionHelper::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'error', 'message' => 'CSRF token invalid']);
        exit;
      }

      $absensiId = trim($_POST['absensi_id'] ?? '');
      $newStatus = trim($_POST['status'] ?? '');
      $newAlasan = trim($_POST['alasan'] ?? '') ?: null;
      $correctionReason = trim($_POST['reason'] ?? '');
      $adminId = SessionHelper::getUserId();
      if (!$adminId) {
        echo json_encode(['status' => 'error', 'message' => 'Session admin tidak valid']);
        exit;
      }

      if (!$absensiId || !$newStatus) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit;
      }

      // Get existing absensi
      $existing = $this->queryService->getAbsensiById($absensiId);
      if (!$existing) {
        echo json_encode(['status' => 'error', 'message' => 'Absensi tidak ditemukan']);
        exit;
      }

      // Validate new status
      if (!in_array($newStatus, ['Hadir', 'Izin', 'Sakit', 'Alpa'], true)) {
        echo json_encode(['status' => 'error', 'message' => 'Status tidak valid']);
        exit;
      }

      // Validate mandatory alasan
      if (in_array($newStatus, ['Izin', 'Sakit', 'Alpa'], true) && empty($newAlasan)) {
        echo json_encode(['status' => 'error', 'message' => 'Alasan wajib diisi untuk status ini']);
        exit;
      }

      // Save correction dengan audit trail
      $result = $this->commandService->createOrUpdateAbsensi(
        $existing['jadwal_id'],
        $existing['tanggal'],
        $existing['siswa_id'],
        $newStatus,
        $newAlasan,
        $existing['catatan_guru'], // keep existing catatan
        $adminId
      );

      if ($result['status'] === 'success') {
        // Log correction reason ke audit table
        $this->logCorrectionReason($absensiId, $adminId, $correctionReason);
      }

      echo json_encode($result);

    } catch (Throwable $e) {
      echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
  }

  /**
   * Get detail absensi untuk modal correction
   */
  public function getAbsensiDetail(): void
  {
    header('Content-Type: application/json');

    $absensiId = trim($_GET['id'] ?? '');
    if (!$absensiId) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
      exit;
    }

    $detail = $this->queryService->getAbsensiById($absensiId);
    if (!$detail) {
      echo json_encode(['status' => 'error', 'message' => 'Absensi tidak ditemukan']);
      exit;
    }

    // Get audit trail
    $trail = $this->queryService->getAbsensiAuditTrail($absensiId);

    echo json_encode([
      'status' => 'success',
      'data' => $detail,
      'trail' => $trail
    ]);
  }

  // ========== PRIVATE HELPERS ==========

  private function getGuruList(): array {
    $stmt = getDB()->prepare("
      SELECT DISTINCT g.id, g.nama
      FROM guru g
      ORDER BY g.nama ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
  }

  private function getSiswaList(): array {
    $stmt = getDB()->prepare("
      SELECT id, nama FROM siswa ORDER BY nama ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
  }

  private function logCorrectionReason(string $absensiId, string $adminId, string $reason): void {
    $stmt = getDB()->prepare("
      INSERT INTO absensi_audit (
        absensi_id, changed_by, action_type, reason
      ) VALUES (?, ?, 'CORRECTION_REASON', ?)
    ");
    $stmt->execute([$absensiId, $adminId, $reason ?: null]);
  }
}
