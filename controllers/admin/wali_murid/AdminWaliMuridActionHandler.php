<?php
// ============================================================
// controllers/admin/actions/AdminWaliMuridActionHandler.php
// Fokus: proses aksi POST untuk data wali murid
// ============================================================

require_once __DIR__ . '/../../../models/admin/AdminWaliMuridRepository.php';

class AdminWaliMuridActionHandler {

  private AdminWaliMuridRepository $waliRepository;

  public function __construct(AdminWaliMuridRepository $waliRepository) {
    $this->waliRepository = $waliRepository;
  }

  public function handleUpdateWali(): void {
    $waliId = trim($_POST['wali_id'] ?? '');
    $nama = trim($_POST['nama'] ?? '');

    if ($waliId === '') {
      $_SESSION['flash_error'] = 'ID wali murid tidak valid.';
      return;
    }

    if ($nama === '') {
      $_SESSION['flash_error'] = 'Nama wali murid tidak boleh kosong.';
      return;
    }

    $result = $this->waliRepository->updateWaliMurid($waliId, [
      'nama' => $nama,
      'no_telp' => trim($_POST['no_telp'] ?? ''),
      'hubungan' => trim($_POST['hubungan'] ?? ''),
      'pekerjaan' => trim($_POST['pekerjaan'] ?? ''),
      'alamat' => trim($_POST['alamat'] ?? ''),
    ]);

    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Data wali murid berhasil diperbarui.'
      : ($result['message'] ?? 'Gagal memperbarui data wali murid.');
  }

  public function handleGetSiswa(): void {
    header('Content-Type: application/json');

    $waliId = trim($_GET['wali_id'] ?? '');
    if ($waliId === '') {
      echo json_encode(['siswa' => [], 'error' => 'ID wali murid tidak valid']);
      return;
    }

    $siswa = $this->waliRepository->getWaliMuridSiswa($waliId);
    echo json_encode(['siswa' => $siswa]);
  }
}
