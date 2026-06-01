<?php
// ============================================================
// models/admin/AdminWaliMuridRepository.php
// Fokus: query data wali murid untuk area admin
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';

class AdminWaliMuridRepository {

  private PDO $db;
  private IdCounterModel $idCounterModel;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null) {
    $this->db = $db ?? getDB();
    $this->idCounterModel = $idCounterModel ?? new IdCounterModel($this->db);
  }

  public function getWaliMuridList(): array {
    $stmt = $this->db->query("
      SELECT
        wm.id,
        wm.nama,
        wm.no_telp,
        wm.hubungan,
        wm.pekerjaan,
        wm.alamat,
        wm.foto_path,
        u.id AS user_id,
        u.email AS user_email,
        u.is_locked,
        u.attempts,
        u.created_at,
        COUNT(s.id) AS total_siswa
      FROM wali_murid wm
      LEFT JOIN users u ON u.id = wm.id
      LEFT JOIN siswa s ON s.wali_id = wm.id
      GROUP BY
        wm.id,
        wm.nama,
        wm.no_telp,
        wm.hubungan,
        wm.pekerjaan,
        wm.alamat,
        wm.foto_path,
        u.id,
        u.email,
        u.is_locked,
        u.attempts,
        u.created_at
      ORDER BY wm.nama ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getWaliMuridById(string $id): ?array {
    $stmt = $this->db->prepare("
      SELECT *
      FROM wali_murid
      WHERE id = ?
    ");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getWaliMuridSiswa(string $waliId): array {
    $stmt = $this->db->prepare("
      SELECT
        s.id,
        s.nama,
        s.kelas_sekolah AS kelas,
        u.email
      FROM siswa s
      INNER JOIN users u ON u.id = s.id
      WHERE s.wali_id = ?
      ORDER BY s.nama ASC
    ");
    $stmt->execute([$waliId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function createWaliMurid(array $data): array {
    $nama = trim((string)($data['nama'] ?? ''));
    $noTelp = trim((string)($data['no_telp'] ?? ''));
    $hubungan = trim((string)($data['hubungan'] ?? ''));
    $pekerjaan = trim((string)($data['pekerjaan'] ?? ''));
    $alamat = trim((string)($data['alamat'] ?? ''));

    if ($nama === '') {
      return ['status' => 'error', 'message' => 'Nama wali murid wajib diisi.'];
    }

    try {
      $id = $this->idCounterModel->generateId('wali_murid', 'WLM');
      $stmt = $this->db->prepare("
        INSERT INTO wali_murid (id, nama, no_telp, hubungan, pekerjaan, alamat)
        VALUES (?, ?, ?, ?, ?, ?)
      ");
      $stmt->execute([
        $id,
        $nama,
        $noTelp !== '' ? $noTelp : null,
        $hubungan !== '' ? $hubungan : null,
        $pekerjaan !== '' ? $pekerjaan : null,
        $alamat !== '' ? $alamat : null,
      ]);

      return ['status' => 'success', 'message' => 'Wali murid berhasil ditambahkan.'];
    } catch (Exception $e) {
      error_log('[AdminWaliMuridRepository::createWaliMurid] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Gagal menambahkan wali murid. Silakan coba lagi.'];
    }
  }

  public function updateWaliMurid(string $id, array $data): array {
    $nama = trim((string)($data['nama'] ?? ''));
    if ($nama === '') {
      return ['status' => 'error', 'message' => 'Nama wali murid wajib diisi.'];
    }

    try {
      $stmt = $this->db->prepare("
        UPDATE wali_murid
        SET
          nama = ?,
          no_telp = ?,
          hubungan = ?,
          pekerjaan = ?,
          alamat = ?
        WHERE id = ?
      ");
      $stmt->execute([
        $nama,
        trim((string)($data['no_telp'] ?? '')) ?: null,
        trim((string)($data['hubungan'] ?? '')) ?: null,
        trim((string)($data['pekerjaan'] ?? '')) ?: null,
        trim((string)($data['alamat'] ?? '')) ?: null,
        $id
      ]);

      if ($stmt->rowCount() === 0 && $this->getWaliMuridById($id) === null) {
        return ['status' => 'error', 'message' => 'Data wali murid tidak ditemukan.'];
      }

      return ['status' => 'success', 'message' => 'Wali murid berhasil diperbarui'];
    } catch (Exception $e) {
      error_log('[AdminWaliMuridRepository::updateWaliMurid] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Gagal memperbarui wali murid. Silakan coba lagi.'];
    }
  }

  public function deleteWaliMurid(string $id): array {
    try {
      if ($this->hasLinkedUserAccount($id)) {
        return ['status' => 'error', 'message' => 'Wali ini memiliki akun user. Nonaktifkan atau hapus melalui menu User agar data tetap konsisten.'];
      }

      // Check if has siswa
      $stmt = $this->db->prepare("SELECT COUNT(*) FROM siswa WHERE wali_id = ?");
      $stmt->execute([$id]);
      $hasSiswa = (int)$stmt->fetchColumn() > 0;

      if ($hasSiswa) {
        return ['status' => 'error', 'message' => 'Wali murid masih memiliki siswa yang terkait'];
      }

      $stmt = $this->db->prepare("DELETE FROM wali_murid WHERE id = ?");
      $stmt->execute([$id]);

      return ['status' => 'success', 'message' => 'Wali murid berhasil dihapus'];
    } catch (Exception $e) {
      error_log('[AdminWaliMuridRepository::deleteWaliMurid] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Gagal menghapus wali murid. Silakan coba lagi.'];
    }
  }

  public function forceDeleteWaliMurid(string $id): array {
    $id = trim($id);
    if ($id === '') {
      return ['status' => 'error', 'message' => 'ID wali murid tidak valid.'];
    }

    try {
      if ($this->hasLinkedUserAccount($id)) {
        return ['status' => 'error', 'message' => 'Wali ini memiliki akun user. Nonaktifkan atau hapus melalui menu User agar data tetap konsisten.'];
      }

      $this->db->beginTransaction();
      $stmt = $this->db->prepare("UPDATE siswa SET wali_id = NULL WHERE wali_id = ?");
      $stmt->execute([$id]);

      $deleteStmt = $this->db->prepare("DELETE FROM wali_murid WHERE id = ?");
      $deleteStmt->execute([$id]);

      $this->db->commit();
      return ['status' => 'success', 'message' => 'Wali murid berhasil dihapus dan relasi siswa telah diputuskan.'];
    } catch (Exception $e) {
      if ($this->db->inTransaction()) {
        $this->db->rollBack();
      }
      error_log('[AdminWaliMuridRepository::forceDeleteWaliMurid] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Gagal menghapus wali murid. Silakan coba lagi.'];
    }
  }

  private function hasLinkedUserAccount(string $waliId): bool {
    $stmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM users
      WHERE id = ? AND role = 'wali_murid'
    ");
    $stmt->execute([$waliId]);
    return (int)$stmt->fetchColumn() > 0;
  }
}
