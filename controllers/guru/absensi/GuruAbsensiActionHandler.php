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
    header('Content-Type: application/json');

    $jadwalId = trim($_POST['jadwal_id'] ?? '');
    if ($jadwalId === '') {
      echo json_encode(['status' => 'error', 'message' => 'Jadwal ID required']);
      exit;
    }

    $jadwal = $jadwalValidator($jadwalId);
    if (!$jadwal) {
      echo json_encode(['status' => 'error', 'message' => 'Jadwal tidak ditemukan atau tidak milik Anda']);
      exit;
    }

    $siswaList = $this->queryService->getSiswaInJadwal($jadwalId);
    echo json_encode(['status' => 'success', 'data' => $siswaList]);
  }

  public function loadAbsensiData(callable $jadwalValidator): void
  {
    header('Content-Type: application/json');

    $jadwalId = trim($_POST['jadwal_id'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? '');

    if ($jadwalId === '' || $tanggal === '') {
      echo json_encode(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
      exit;
    }

    $jadwal = $jadwalValidator($jadwalId);
    if (!$jadwal) {
      echo json_encode(['status' => 'error', 'message' => 'Jadwal tidak milik Anda']);
      exit;
    }

    $absensiList = $this->queryService->getAbsensiByJadwalTanggal($jadwalId, $tanggal);
    echo json_encode(['status' => 'success', 'data' => $absensiList]);
  }

  public function saveAbsensi(callable $jadwalValidator): void
  {
    header('Content-Type: application/json');

    try {
      if (!SessionHelper::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'error', 'message' => 'CSRF token invalid']);
        exit;
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
        echo json_encode(['status' => 'error', 'message' => 'Jadwal tidak milik Anda']);
        exit;
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

      echo json_encode($result);
    } catch (Throwable $e) {
      echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
  }
}
