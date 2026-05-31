<?php
// ============================================================
// controllers/admin/actions/AdminGuruActionHandler.php
// Fokus: proses aksi POST untuk data guru
// ============================================================

require_once __DIR__ . '/../../../models/admin/AdminGuruRepository.php';

class AdminGuruActionHandler
{
  private AdminGuruRepository $guruRepository;

  public function __construct(AdminGuruRepository $guruRepository)
  {
    $this->guruRepository = $guruRepository;
  }

  public function handleUpdateProfile(): void
  {
    $guruId = trim($_POST['guru_id'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $mapelId = trim($_POST['mapel_id'] ?? '');

    if ($guruId === '') {
      $_SESSION['flash_error'] = 'ID guru tidak valid.';
      return;
    }

    $result = $this->guruRepository->updateGuruProfile($guruId, $nama, $mapelId);
    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Data guru berhasil diperbarui.'
      : ($result['message'] ?? 'Gagal memperbarui data guru.');
  }
}
