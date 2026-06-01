<?php
// ============================================================
// controllers/guru/actions/GuruNilaiActionHandler.php
// Fokus: proses aksi POST nilai dari guru
// ============================================================

require_once __DIR__ . '/../../../models/nilai/NilaiModel.php';
require_once __DIR__ . '/../../../models/nilai/NilaiQueryService.php';

class GuruNilaiActionHandler {

  private NilaiModel $nilaiModel;
  private NilaiQueryService $nilaiQueryService;

  public function __construct(NilaiModel $nilaiModel, ?NilaiQueryService $nilaiQueryService = null) {
    $this->nilaiModel = $nilaiModel;
    $this->nilaiQueryService = $nilaiQueryService ?? new NilaiQueryService();
  }

  public function handleSave(): void {
    $guruId = trim((string)($_SESSION['user_id'] ?? ''));
    $jadwalId = trim($_POST['jadwal_id'] ?? '');
    $pertemuanKe = isset($_POST['pertemuan_ke']) ? (int)$_POST['pertemuan_ke'] : 1;
    $tipeNilai = trim($_POST['tipe_nilai'] ?? 'utama');
    $predikat = trim($_POST['predikat'] ?? '');
    $catatanGuru = trim($_POST['catatan_guru'] ?? '');

    // Validasi
    if ($guruId === '') {
      $this->denyOwnership('Sesi guru tidak valid.');
      return;
    }

    if ($jadwalId === '') {
      $_SESSION['flash_error'] = 'Jadwal harus dipilih.';
      return;
    }

    if (!$this->nilaiModel->isJadwalOwnedByGuru($jadwalId, $guruId)) {
      $this->denyOwnership('Akses ditolak. Jadwal bukan milik Anda.');
      return;
    }

    $existingNilai = $this->nilaiModel->findExistingNilaiByGuru($jadwalId, $pertemuanKe, $tipeNilai, $guruId);

    if ($existingNilai) {
      $result = $this->nilaiModel->updateNilai($existingNilai['id'], [
        'pertemuan_ke' => $pertemuanKe,
        'tipe_nilai' => $tipeNilai,
        'predikat' => $predikat,
        'catatan_guru' => $catatanGuru
      ]);
      $_SESSION['flash_' . ($result['success'] ? 'success' : 'error')] = $result['success']
        ? 'Nilai berhasil diperbarui.'
        : ($result['message'] ?? 'Gagal memperbarui nilai.');
      return;
    }

    $result = $this->nilaiModel->createNilai([
      'jadwal_id' => $jadwalId,
      'pertemuan_ke' => $pertemuanKe,
      'tipe_nilai' => $tipeNilai,
      'predikat' => $predikat,
      'catatan_guru' => $catatanGuru
    ]);
    $_SESSION['flash_' . ($result['success'] ? 'success' : 'error')] = $result['success']
      ? 'Nilai berhasil ditambahkan.'
      : ($result['message'] ?? 'Gagal menambahkan nilai.');
  }

  public function handleUpdate(): void {
    $guruId = trim((string)($_SESSION['user_id'] ?? ''));
    $nilaiId = trim($_POST['nilai_id'] ?? '');
    $pertemuanKe = isset($_POST['pertemuan_ke']) ? (int)$_POST['pertemuan_ke'] : 1;
    $tipeNilai = trim($_POST['tipe_nilai'] ?? 'utama');
    $predikat = trim($_POST['predikat'] ?? '');
    $catatanGuru = trim($_POST['catatan_guru'] ?? '');

    if ($guruId === '') {
      $this->denyOwnership('Sesi guru tidak valid.');
      return;
    }

    if ($nilaiId === '') {
      $_SESSION['flash_error'] = 'ID nilai tidak valid.';
      return;
    }

    if (!$this->nilaiModel->findNilaiByIdAndGuru($nilaiId, $guruId)) {
      $this->denyOwnership('Akses ditolak. Nilai ini bukan milik Anda.');
      return;
    }

    $result = $this->nilaiModel->updateNilai($nilaiId, [
      'pertemuan_ke' => $pertemuanKe,
      'tipe_nilai' => $tipeNilai,
      'predikat' => $predikat,
      'catatan_guru' => $catatanGuru
    ]);

    $_SESSION['flash_' . ($result['success'] ? 'success' : 'error')] = $result['success']
      ? 'Nilai berhasil diperbarui.'
      : ($result['message'] ?? 'Gagal memperbarui nilai.');
  }

  public function handleDelete(): void {
    $guruId = trim((string)($_SESSION['user_id'] ?? ''));
    $nilaiId = trim($_POST['nilai_id'] ?? '');

    if ($guruId === '') {
      $this->denyOwnership('Sesi guru tidak valid.');
      return;
    }

    if ($nilaiId === '') {
      $_SESSION['flash_error'] = 'ID nilai tidak valid.';
      return;
    }

    if (!$this->nilaiModel->findNilaiByIdAndGuru($nilaiId, $guruId)) {
      $this->denyOwnership('Akses ditolak. Nilai ini bukan milik Anda.');
      return;
    }

    $result = $this->nilaiModel->deleteNilai($nilaiId);
    $_SESSION['flash_' . ($result['success'] ? 'success' : 'error')] = $result['success']
      ? 'Nilai berhasil dihapus.'
      : ($result['message'] ?? 'Gagal menghapus nilai.');
  }

  private function denyOwnership(string $message): void {
    $isAjax = isset($_GET['action'])
      || stripos((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false
      || strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

    if ($isAjax) {
      http_response_code(403);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
        'status' => 'error',
        'message' => $message,
        'data' => new stdClass()
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    $_SESSION['flash_error'] = $message;
  }
}
