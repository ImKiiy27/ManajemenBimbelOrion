<?php
// ============================================================
// models/admin/AdminMapelRepository.php
// Fokus: master data mapel untuk area admin
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';

class AdminMapelRepository
{
  private PDO $db;
  private IdCounterModel $idCounterModel;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null)
  {
    $this->db = $db ?? getDB();
    $this->idCounterModel = $idCounterModel ?? new IdCounterModel($this->db);
    $this->ensureSchema();
  }

  public function getMapelList(): array
  {
    $stmt = $this->db->query("
      SELECT
        m.id,
        m.nama,
        m.deskripsi,
        m.status,
        COALESCE(g.cnt, 0) AS total_guru,
        COALESCE(sm.cnt, 0) AS total_siswa,
        COALESCE(pm.cnt, 0) AS total_pendaftar
      FROM mapel m
      LEFT JOIN (
        SELECT mapel_id, COUNT(*) AS cnt
        FROM guru
        GROUP BY mapel_id
      ) g ON g.mapel_id = m.id
      LEFT JOIN (
        SELECT mapel_id, COUNT(*) AS cnt
        FROM siswa_mapel
        GROUP BY mapel_id
      ) sm ON sm.mapel_id = m.id
      LEFT JOIN (
        SELECT mapel_id, COUNT(*) AS cnt
        FROM pendaftaran_mapel
        GROUP BY mapel_id
      ) pm ON pm.mapel_id = m.id
      ORDER BY
        CASE WHEN m.status = 'aktif' THEN 0 ELSE 1 END,
        CASE WHEN LOWER(m.nama) = 'privat' THEN 1 ELSE 0 END,
        m.nama ASC
    ");

    return $stmt->fetchAll();
  }

  public function findById(string $mapelId): array|false
  {
    $stmt = $this->db->prepare("
      SELECT id, nama, deskripsi, status
      FROM mapel
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->execute([$mapelId]);
    return $stmt->fetch();
  }

  public function createMapel(array $data): array
  {
    $nama = trim((string)($data['nama'] ?? ''));
    $deskripsi = trim((string)($data['deskripsi'] ?? ''));
    $status = $this->normalizeStatus((string)($data['status'] ?? 'aktif'));

    if ($nama === '') {
      return ['status' => 'error', 'message' => 'Nama mapel wajib diisi.'];
    }

    if ($this->existsByName($nama)) {
      return ['status' => 'error', 'message' => 'Nama mapel sudah ada.'];
    }

    try {
      $mapelId = $this->idCounterModel->generateId('mapel', 'MPL');
      $stmt = $this->db->prepare("
        INSERT INTO mapel (id, nama, deskripsi, status)
        VALUES (?, ?, ?, ?)
      ");
      $stmt->execute([$mapelId, $nama, $deskripsi, $status]);

      return ['status' => 'success', 'id' => $mapelId];
    } catch (PDOException $e) {
      return ['status' => 'error', 'message' => $this->friendlyDuplicateMessage($e)];
    } catch (Throwable $e) {
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  public function updateMapel(string $mapelId, array $data): array
  {
    $current = $this->findById($mapelId);
    if (!$current) {
      return ['status' => 'error', 'message' => 'Data mapel tidak ditemukan.'];
    }

    $nama = trim((string)($data['nama'] ?? ''));
    $deskripsi = trim((string)($data['deskripsi'] ?? ''));
    $status = $this->normalizeStatus((string)($data['status'] ?? ($current['status'] ?? 'aktif')));

    if ($nama === '') {
      return ['status' => 'error', 'message' => 'Nama mapel wajib diisi.'];
    }

    if ($this->existsByName($nama, $mapelId)) {
      return ['status' => 'error', 'message' => 'Nama mapel sudah ada.'];
    }

    try {
      $stmt = $this->db->prepare("
        UPDATE mapel
        SET nama = ?, deskripsi = ?, status = ?
        WHERE id = ?
      ");
      $stmt->execute([$nama, $deskripsi, $status, $mapelId]);
      return ['status' => 'success'];
    } catch (PDOException $e) {
      return ['status' => 'error', 'message' => $this->friendlyDuplicateMessage($e)];
    } catch (Throwable $e) {
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  public function updateStatus(string $mapelId, string $status): array
  {
    $current = $this->findById($mapelId);
    if (!$current) {
      return ['status' => 'error', 'message' => 'Data mapel tidak ditemukan.'];
    }

    $status = $this->normalizeStatus($status);

    try {
      $stmt = $this->db->prepare("UPDATE mapel SET status = ? WHERE id = ?");
      $stmt->execute([$status, $mapelId]);
      return ['status' => 'success'];
    } catch (Throwable $e) {
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  public function getSummary(array $mapelList): array
  {
    $total = count($mapelList);
    $aktif = count(array_filter($mapelList, fn($row) => ($row['status'] ?? 'aktif') === 'aktif'));
    $nonaktif = $total - $aktif;
    $dipakaiGuru = count(array_filter($mapelList, fn($row) => (int)($row['total_guru'] ?? 0) > 0));

    return [
      'total' => $total,
      'aktif' => $aktif,
      'nonaktif' => $nonaktif,
      'dipakai_guru' => $dipakaiGuru,
    ];
  }

  private function existsByName(string $nama, ?string $excludeId = null): bool
  {
    $params = [$nama];
    $sql = "SELECT COUNT(*) FROM mapel WHERE LOWER(nama) = LOWER(?)";

    if ($excludeId !== null && trim($excludeId) !== '') {
      $sql .= " AND id <> ?";
      $params[] = $excludeId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
  }

  private function normalizeStatus(string $status): string
  {
    $status = strtolower(trim($status));
    return $status === 'nonaktif' ? 'nonaktif' : 'aktif';
  }

  private function ensureSchema(): void
  {
    $hasStatus = $this->columnExists('mapel', 'status');
    if (!$hasStatus) {
      $this->db->exec("
        ALTER TABLE mapel
        ADD COLUMN status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif' AFTER deskripsi
      ");
    }

    $this->db->exec("
      UPDATE mapel
      SET status = 'aktif'
      WHERE status IS NULL OR status = ''
    ");
  }

  private function columnExists(string $table, string $column): bool
  {
    $stmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = ?
        AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
  }

  private function friendlyDuplicateMessage(PDOException $e): string
  {
    $code = $e->errorInfo[1] ?? null;
    if ($code === 1062) {
      return 'Nama mapel sudah ada. Gunakan nama lain.';
    }
    return $e->getMessage();
  }
}
