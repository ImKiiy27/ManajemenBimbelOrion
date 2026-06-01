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
    $guruList = $this->queryService->getGuruList();
    $siswaList = $this->queryService->getSiswaList();

    $pageTitle = 'Absensi - Bimbel Orion';
    $activePage = 'admin-absensi';
    $csrf_token = SessionHelper::getCsrfToken();

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
      'totalCount',
      'csrf_token'
    ));
  }

  /**
   * Save correction (POST with audit reason)
   */
  public function saveCorrection(): void
  {
    try {
      // Validate CSRF
      if (!SessionHelper::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $this->jsonResponse(['status' => 'error', 'message' => 'CSRF token invalid'], 400);
      }

      $absensiId = trim($_POST['absensi_id'] ?? '');
      $newStatus = trim($_POST['status'] ?? '');
      $newAlasan = trim($_POST['alasan'] ?? '') ?: null;
      $correctionReason = trim($_POST['reason'] ?? '');
      $adminId = SessionHelper::getUserId();
      if (!$adminId) {
        $this->jsonResponse(['status' => 'error', 'message' => 'Session admin tidak valid'], 401);
      }

      if (!$absensiId || !$newStatus) {
        $this->jsonResponse(['status' => 'error', 'message' => 'Data tidak lengkap'], 422);
      }

      // Get existing absensi
      $existing = $this->queryService->getAbsensiById($absensiId);
      if (!$existing) {
        $this->jsonResponse(['status' => 'error', 'message' => 'Absensi tidak ditemukan'], 404);
      }

      // Validate new status
      if (!in_array($newStatus, ['Hadir', 'Izin', 'Sakit', 'Alpa'], true)) {
        $this->jsonResponse(['status' => 'error', 'message' => 'Status tidak valid'], 422);
      }

      // Validate mandatory alasan
      if (in_array($newStatus, ['Izin', 'Sakit', 'Alpa'], true) && empty($newAlasan)) {
        $this->jsonResponse(['status' => 'error', 'message' => 'Alasan wajib diisi untuk status ini'], 422);
      }

      if ($correctionReason === '') {
        $this->jsonResponse(['status' => 'error', 'message' => 'Alasan koreksi wajib diisi'], 422);
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
        $this->commandService->logCorrectionReason($absensiId, $adminId, $correctionReason);
      }

      $this->jsonResponse($result, ($result['status'] ?? 'error') === 'success' ? 200 : 422);

    } catch (Throwable $e) {
      error_log('[AdminAbsensiController::saveCorrection] ' . $e->getMessage());
      $this->jsonResponse(['status' => 'error', 'message' => 'Terjadi kesalahan server. Silakan coba lagi.'], 500);
    }
  }

  /**
   * Get detail absensi untuk modal correction
   */
  public function getAbsensiDetail(): void
  {
    $absensiId = trim($_GET['id'] ?? '');
    if (!$absensiId) {
      $this->jsonResponse(['status' => 'error', 'message' => 'ID tidak valid'], 422);
    }

    $detail = $this->queryService->getAbsensiById($absensiId);
    if (!$detail) {
      $this->jsonResponse(['status' => 'error', 'message' => 'Absensi tidak ditemukan'], 404);
    }

    // Get audit trail
    $trail = $this->queryService->getAbsensiAuditTrail($absensiId);

    $this->jsonResponse([
      'status' => 'success',
      'data' => $detail,
      'trail' => $trail
    ]);
  }

  private function jsonResponse(array $payload, int $statusCode = 200): void
  {
    while (ob_get_level() > 0) {
      ob_end_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    if (!array_key_exists('data', $payload)) {
      $payload['data'] = new stdClass();
    }
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
      http_response_code(500);
      echo '{"status":"error","message":"Gagal memproses data JSON"}';
      exit;
    }
    echo $json;
    exit;
  }

}
