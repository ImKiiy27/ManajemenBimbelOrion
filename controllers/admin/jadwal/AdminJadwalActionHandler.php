<?php
// ============================================================
// controllers/admin/actions/AdminJadwalActionHandler.php
// Fokus: proses aksi POST jadwal + helper hari Indonesia
// ============================================================

require_once __DIR__ . '/../../../models/jadwal/JadwalModel.php';

class AdminJadwalActionHandler {

  private JadwalModel $jadwalModel;

  public function __construct(JadwalModel $jadwalModel) {
    $this->jadwalModel = $jadwalModel;
  }

  public function handleCreate(): void {
    $result = $this->jadwalModel->createJadwal([
      'siswa_mapel_id' => trim($_POST['siswa_mapel_id'] ?? ''),
      'hari'           => trim($_POST['hari'] ?? ''),
      'jam_mulai'      => trim($_POST['jam_mulai'] ?? ''),
      'jam_selesai'    => trim($_POST['jam_selesai'] ?? ''),
    ]);

    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Jadwal berhasil ditambahkan.'
      : ($result['message'] ?? 'Gagal menambahkan jadwal.');
  }

  public function handleUpdate(): void {
    $jadwalId = trim($_POST['jadwal_id'] ?? '');
    if ($jadwalId === '') {
      $_SESSION['flash_error'] = 'ID jadwal tidak valid.';
      return;
    }

    $result = $this->jadwalModel->updateJadwal($jadwalId, [
      'siswa_mapel_id' => trim($_POST['siswa_mapel_id'] ?? ''),
      'hari'           => trim($_POST['hari'] ?? ''),
      'jam_mulai'      => trim($_POST['jam_mulai'] ?? ''),
      'jam_selesai'    => trim($_POST['jam_selesai'] ?? ''),
    ]);

    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Jadwal berhasil diperbarui.'
      : ($result['message'] ?? 'Gagal memperbarui jadwal.');
  }

  public function handleDelete(): void {
    $jadwalId = trim($_POST['jadwal_id'] ?? '');
    if ($jadwalId === '') {
      $_SESSION['flash_error'] = 'ID jadwal tidak valid.';
      return;
    }

    $result = $this->jadwalModel->deleteJadwal($jadwalId);
    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'Jadwal berhasil dihapus.'
      : ($result['message'] ?? 'Gagal menghapus jadwal.');
  }

  public function hariIndonesia(int $dayNumber): string {
    return match ($dayNumber) {
      1 => 'Senin',
      2 => 'Selasa',
      3 => 'Rabu',
      4 => 'Kamis',
      5 => 'Jumat',
      6 => 'Sabtu',
      7 => 'Minggu',
      default => '',
    };
  }
}


