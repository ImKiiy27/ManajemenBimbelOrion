<?php
// ============================================================
// controllers/guru/actions/GuruNilaiActionHandler.php
// Fokus: proses aksi POST nilai dari guru
// ============================================================

require_once __DIR__ . '/../../../models/nilai/NilaiModel.php';

class GuruNilaiActionHandler {

  private NilaiModel $nilaiModel;

  public function __construct(NilaiModel $nilaiModel) {
    $this->nilaiModel = $nilaiModel;
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

    $db = getDB();
    $stmt = $db->prepare("
      SELECT id FROM nilai
      WHERE jadwal_id = ? AND pertemuan_ke = ? AND tipe_nilai = ?
      LIMIT 1
    ");
    $stmt->execute([$jadwalId, $pertemuanKe, $tipeNilai]);
    $existingNilai = $stmt->fetch();

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
