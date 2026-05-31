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
    $jadwalId = trim($_POST['jadwal_id'] ?? '');
    $pertemuanKe = isset($_POST['pertemuan_ke']) ? (int)$_POST['pertemuan_ke'] : 1;
    $tipeNilai = trim($_POST['tipe_nilai'] ?? 'utama');
    $predikat = trim($_POST['predikat'] ?? '');
    $catatanGuru = trim($_POST['catatan_guru'] ?? '');

    // Validasi
    if ($jadwalId === '') {
      $_SESSION['flash_error'] = 'Jadwal harus dipilih.';
      return;
    }

    $existingNilai = $this->nilaiQueryService->findExistingNilai($jadwalId, $pertemuanKe, $tipeNilai);

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
    $nilaiId = trim($_POST['nilai_id'] ?? '');
    $pertemuanKe = isset($_POST['pertemuan_ke']) ? (int)$_POST['pertemuan_ke'] : 1;
    $tipeNilai = trim($_POST['tipe_nilai'] ?? 'utama');
    $predikat = trim($_POST['predikat'] ?? '');
    $catatanGuru = trim($_POST['catatan_guru'] ?? '');

    if ($nilaiId === '') {
      $_SESSION['flash_error'] = 'ID nilai tidak valid.';
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
    $nilaiId = trim($_POST['nilai_id'] ?? '');

    if ($nilaiId === '') {
      $_SESSION['flash_error'] = 'ID nilai tidak valid.';
      return;
    }

    $result = $this->nilaiModel->deleteNilai($nilaiId);
    $_SESSION['flash_' . ($result['success'] ? 'success' : 'error')] = $result['success']
      ? 'Nilai berhasil dihapus.'
      : ($result['message'] ?? 'Gagal menghapus nilai.');
  }
}
