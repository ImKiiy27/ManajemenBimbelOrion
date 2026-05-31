<?php
// ============================================================
// controllers/admin/actions/AdminNilaiActionHandler.php
// Fokus: proses aksi POST nilai dari admin (delete)
// ============================================================

require_once __DIR__ . '/../../../models/nilai/NilaiModel.php';

class AdminNilaiActionHandler {

  private NilaiModel $nilaiModel;

  public function __construct(NilaiModel $nilaiModel) {
    $this->nilaiModel = $nilaiModel;
  }

  public function handleDelete(): void {
    $nilaiId = trim($_POST['nilai_id'] ?? '');

    // Validasi
    if ($nilaiId === '') {
      $_SESSION['flash_error'] = 'ID nilai tidak valid.';
      return;
    }

    $result = $this->nilaiModel->deleteNilai($nilaiId);
    $_SESSION['flash_success'] = 'Nilai berhasil dihapus.';
  }
}

