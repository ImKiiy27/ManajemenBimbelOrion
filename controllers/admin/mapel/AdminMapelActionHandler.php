<?php
// ============================================================
// controllers/admin/actions/AdminMapelActionHandler.php
// Fokus: proses aksi POST untuk master mapel admin
// ============================================================

require_once __DIR__ . '/../../../models/admin/AdminMapelRepository.php';

class AdminMapelActionHandler
{
  private AdminMapelRepository $mapelRepository;

  public function __construct(AdminMapelRepository $mapelRepository)
  {
    $this->mapelRepository = $mapelRepository;
  }

  public function handleCreate(): void
  {
    $result = $this->mapelRepository->createMapel([
      'nama' => trim($_POST['nama'] ?? ''),
      'deskripsi' => trim($_POST['deskripsi'] ?? ''),
      'status' => trim($_POST['status'] ?? 'aktif'),
    ]);

    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Mapel baru berhasil ditambahkan.'
      : ($result['message'] ?? 'Gagal menambahkan mapel.');
  }

  public function handleUpdate(): void
  {
    $mapelId = trim($_POST['mapel_id'] ?? '');
    if ($mapelId === '') {
      $_SESSION['flash_error'] = 'ID mapel tidak valid.';
      return;
    }

    $result = $this->mapelRepository->updateMapel($mapelId, [
      'nama' => trim($_POST['nama'] ?? ''),
      'deskripsi' => trim($_POST['deskripsi'] ?? ''),
      'status' => trim($_POST['status'] ?? 'aktif'),
    ]);

    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Mapel berhasil diperbarui.'
      : ($result['message'] ?? 'Gagal memperbarui mapel.');
  }

  public function handleToggleStatus(): void
  {
    $mapelId = trim($_POST['mapel_id'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($mapelId === '') {
      $_SESSION['flash_error'] = 'ID mapel tidak valid.';
      return;
    }

    $result = $this->mapelRepository->updateStatus($mapelId, $status);
    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Status mapel berhasil diperbarui.'
      : ($result['message'] ?? 'Gagal memperbarui status mapel.');
  }
}
