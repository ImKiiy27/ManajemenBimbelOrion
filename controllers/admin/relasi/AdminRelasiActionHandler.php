<?php
// ============================================================
// controllers/admin/actions/AdminRelasiActionHandler.php
// Fokus: proses aksi POST relasi siswa-guru admin
// ============================================================

require_once __DIR__ . '/../../../models/admin/AdminRelasiRepository.php';

class AdminRelasiActionHandler {

  private AdminRelasiRepository $relasiRepository;

  public function __construct(AdminRelasiRepository $relasiRepository) {
    $this->relasiRepository = $relasiRepository;
  }

  public function handleCreate(): void {
    $result = $this->relasiRepository->createRelasi(
      trim($_POST['siswa_id'] ?? ''),
      trim($_POST['mapel_id'] ?? ''),
      trim($_POST['guru_id'] ?? ''),
      trim($_POST['status'] ?? 'aktif')
    );

    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? ($result['message'] ?? 'Relasi belajar berhasil ditambahkan.')
      : ($result['message'] ?? 'Gagal menambahkan relasi belajar.');
  }

  public function handleUpdate(): void {
    $result = $this->relasiRepository->updateRelasi(
      trim($_POST['relasi_id'] ?? ''),
      trim($_POST['siswa_id'] ?? ''),
      trim($_POST['mapel_id'] ?? ''),
      trim($_POST['guru_id'] ?? ''),
      trim($_POST['status'] ?? 'aktif')
    );

    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Relasi belajar berhasil diperbarui.'
      : ($result['message'] ?? 'Gagal memperbarui relasi belajar.');
  }

  public function handleDelete(): void {
    $result = $this->relasiRepository->deleteRelasi(trim($_POST['relasi_id'] ?? ''));
    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Relasi belajar berhasil dihapus.'
      : ($result['message'] ?? 'Gagal menghapus relasi belajar.');
  }
}
