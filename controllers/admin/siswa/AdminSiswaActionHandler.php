<?php
// ============================================================
// controllers/admin/actions/AdminSiswaActionHandler.php
// Fokus: proses aksi POST untuk data siswa & relasi mapel siswa
// ============================================================

require_once __DIR__ . '/../../../models/admin/AdminSiswaRepository.php';

class AdminSiswaActionHandler {

  private AdminSiswaRepository $siswaRepository;

  public function __construct(AdminSiswaRepository $siswaRepository) {
    $this->siswaRepository = $siswaRepository;
  }

  public function handleUpdateProfile(): void {
    $siswaId = trim($_POST['siswa_id'] ?? '');
    $nama    = trim($_POST['nama'] ?? '');
    $kelas   = trim($_POST['kelas'] ?? '');
    $waliId  = trim($_POST['wali_id'] ?? '');
    $mapelIdsRaw = $_POST['mapel_ids'] ?? [];
    $mapelIds = is_array($mapelIdsRaw) ? $mapelIdsRaw : [];

    if ($siswaId === '') {
      $_SESSION['flash_error'] = 'ID siswa tidak valid.';
      return;
    }

    $result = $this->siswaRepository->updateSiswaProfileAndMapel($siswaId, $nama, $kelas, $waliId, $mapelIds);
    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Data siswa berhasil diperbarui.'
      : ($result['message'] ?? 'Gagal memperbarui data siswa.');
  }
}
