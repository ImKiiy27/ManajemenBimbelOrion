<?php
// ============================================================
// models/admin/AdminGuruRepository.php
// Fokus: query data guru untuk area admin
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/AdminMapelRepository.php';

class AdminGuruRepository {

  private PDO $db;
  private AdminMapelRepository $mapelRepository;

  public function __construct(?PDO $db = null) {
    $this->db = $db ?? getDB();
    $this->mapelRepository = new AdminMapelRepository($this->db);
  }

  public function getGuruList(): array {
    $stmt = $this->db->query("
      SELECT
        u.id,
        u.email,
        u.is_locked,
        u.attempts,
        u.created_at,
        g.nama,
        g.no_telp,
        g.alamat,
        g.bio,
        g.mapel_id,
        m.nama AS mapel
      FROM users u
      LEFT JOIN guru g ON g.id = u.id
      LEFT JOIN mapel m ON m.id = g.mapel_id
      WHERE u.role = 'guru'
      ORDER BY g.nama ASC, u.created_at DESC
    ");
    return $stmt->fetchAll();
  }

  public function getGuruOptions(): array {
    $stmt = $this->db->query("
      SELECT
        g.id AS id,
        g.id AS user_id,
        g.nama,
        g.mapel_id,
        m.nama AS mapel,
        u.email
      FROM guru g
      INNER JOIN users u ON u.id = g.id
      LEFT JOIN mapel m ON m.id = g.mapel_id
      WHERE u.role = 'guru'
      ORDER BY g.nama ASC
    ");
    return $stmt->fetchAll();
  }

  public function getActiveMapelOptions(): array {
    $stmt = $this->db->query("
      SELECT id, nama, deskripsi, status
      FROM mapel
      WHERE status = 'aktif'
      ORDER BY
        CASE WHEN LOWER(nama) = 'privat' THEN 1 ELSE 0 END,
        nama ASC
    ");
    return $stmt->fetchAll();
  }

  public function updateGuruProfile(string $guruId, string $nama, string $mapelId): array {
    $nama = trim($nama);
    $mapelId = trim($mapelId);

    if ($nama === '') {
      return ['status' => 'error', 'message' => 'Nama guru wajib diisi.'];
    }

    if ($mapelId === '') {
      return ['status' => 'error', 'message' => 'Mapel ajar wajib dipilih.'];
    }

    if (!$this->roleProfileExists($guruId)) {
      return ['status' => 'error', 'message' => 'Data guru tidak ditemukan.'];
    }

    if (!$this->mapelExistsAndActive($mapelId)) {
      return ['status' => 'error', 'message' => 'Mapel yang dipilih tidak valid atau sedang nonaktif.'];
    }

    try {
      $stmt = $this->db->prepare("UPDATE guru SET nama = ?, mapel_id = ? WHERE id = ?");
      $stmt->execute([$nama, $mapelId, $guruId]);
      return ['status' => 'success'];
    } catch (Throwable $e) {
      error_log('[AdminGuruRepository::updateGuruProfile] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Terjadi kesalahan saat memperbarui data guru.'];
    }
  }

  private function roleProfileExists(string $guruId): bool {
    $stmt = $this->db->prepare("SELECT 1 FROM guru WHERE id = ? LIMIT 1");
    $stmt->execute([$guruId]);
    return (bool)$stmt->fetchColumn();
  }

  private function mapelExistsAndActive(string $mapelId): bool {
    $stmt = $this->db->prepare("SELECT 1 FROM mapel WHERE id = ? AND status = 'aktif' LIMIT 1");
    $stmt->execute([$mapelId]);
    return (bool)$stmt->fetchColumn();
  }
}
