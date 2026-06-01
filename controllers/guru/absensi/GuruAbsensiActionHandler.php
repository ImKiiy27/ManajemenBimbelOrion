<?php
// ============================================================
// controllers/guru/absensi/GuruAbsensiActionHandler.php
// Handler aksi absensi guru (AJAX)
// ============================================================

class GuruAbsensiActionHandler
{
  private AbsensiCommandService $commandService;
  private AbsensiQueryService $queryService;

  public function __construct(AbsensiCommandService $commandService, AbsensiQueryService $queryService)
  {
    $this->commandService = $commandService;
    $this->queryService = $queryService;
  }

  public function loadSiswaInJadwal(callable $jadwalValidator): void
  {
    $jadwalId = trim($_POST['jadwal_id'] ?? '');
    if ($jadwalId === '') {
      $this->jsonResponse(['status' => 'error', 'message' => 'Jadwal ID required'], 422);
    }

    $jadwal = $jadwalValidator($jadwalId);
    if (!$jadwal) {
      $this->jsonResponse(['status' => 'error', 'message' => 'Jadwal tidak ditemukan atau tidak milik Anda'], 404);
    }

    $siswaList = $this->queryService->getSiswaInJadwal($jadwalId);
    $this->jsonResponse(['status' => 'success', 'data' => $siswaList]);
  }

  public function loadAbsensiData(callable $jadwalValidator): void
  {
    $jadwalId = trim($_POST['jadwal_id'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');

    if ($jadwalId === '' || $tanggal === '') {
      $this->jsonResponse(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 422);
    }

    $jadwal = $jadwalValidator($jadwalId);
    if (!$jadwal) {
      $this->jsonResponse(['status' => 'error', 'message' => 'Jadwal tidak milik Anda'], 403);
    }

    $absensiList = $this->queryService->getAbsensiByJadwalTanggal($jadwalId, $tanggal);
    $this->jsonResponse(['status' => 'success', 'data' => $absensiList]);
  }

  public function saveAbsensi(callable $jadwalValidator): void
  {
    try {
      if (!SessionHelper::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $this->jsonResponse(['status' => 'error', 'message' => 'CSRF token invalid'], 400);
      }

      $guruId = SessionHelper::getUserId();
      $jadwalId = trim($_POST['jadwal_id'] ?? '');
      $tanggal = trim($_POST['tanggal'] ?? '');
      $siswaId = trim($_POST['siswa_id'] ?? '');
      $status = trim($_POST['status'] ?? '');
      $alasan = trim($_POST['alasan'] ?? '') ?: null;
      $catatanGuru = trim($_POST['catatan_guru'] ?? '') ?: null;

      $jadwal = $jadwalValidator($jadwalId);
      if (!$jadwal) {
        $this->jsonResponse(['status' => 'error', 'message' => 'Jadwal tidak milik Anda'], 403);
      }

      $result = $this->commandService->createOrUpdateAbsensi(
        $jadwalId,
        $tanggal,
        $siswaId,
        $status,
        $alasan,
        $catatanGuru,
        $guruId
      );

      $this->jsonResponse($result);
    } catch (Throwable $e) {
      error_log('[GuruAbsensiActionHandler::saveAbsensi] ' . $e->getMessage());
      $this->jsonResponse(['status' => 'error', 'message' => 'Terjadi kesalahan saat menyimpan absensi.'], 500);
    }
  }

  private function jsonResponse(array $payload, int $statusCode = 200): void
  {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    if (!array_key_exists('data', $payload)) {
      $payload['data'] = new stdClass();
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
  }
}
