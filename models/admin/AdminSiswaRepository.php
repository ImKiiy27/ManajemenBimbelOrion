<?php
// ============================================================
// models/admin/AdminSiswaRepository.php
// Fokus: data siswa + mapel yang diikuti siswa untuk area admin
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';

class AdminSiswaRepository {

  private PDO $db;
  private IdCounterModel $idCounterModel;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null) {
    $this->db = $db ?? getDB();
    $this->idCounterModel = $idCounterModel ?? new IdCounterModel($this->db);
  }

  public function getSiswaList(): array {
    $stmt = $this->db->query("
      SELECT
        u.id,
        u.email,
        u.is_locked,
        u.attempts,
        u.created_at,
        s.nama,
        s.kelas_sekolah AS kelas,
        s.wali_id,
        wm.nama AS wali_nama,
        s.asal_sekolah,
        s.no_telp,
        COALESCE(mp.mapel_diikuti, '-') AS mapel_diikuti
      FROM users u
      LEFT JOIN siswa s ON s.id = u.id
      LEFT JOIN wali_murid wm ON wm.id = s.wali_id
      LEFT JOIN (
        SELECT
          sm.siswa_id,
          GROUP_CONCAT(m.nama ORDER BY m.nama SEPARATOR ', ') AS mapel_diikuti
        FROM siswa_mapel sm
        INNER JOIN mapel m ON m.id = sm.mapel_id
        WHERE sm.status = 'aktif'
        GROUP BY sm.siswa_id
      ) mp ON mp.siswa_id = s.id
      WHERE u.role = 'siswa'
      ORDER BY s.nama ASC, u.created_at DESC
    ");
    return $stmt->fetchAll();
  }

  public function getWaliOptions(): array {
    $stmt = $this->db->query("
      SELECT id, nama, hubungan, no_telp
      FROM wali_murid
      ORDER BY nama ASC
    ");
    return $stmt->fetchAll();
  }

  public function getSiswaOptions(): array {
    $stmt = $this->db->query("
      SELECT
        s.id AS id,
        s.id AS user_id,
        s.nama,
        s.kelas_sekolah AS kelas,
        u.email
      FROM siswa s
      INNER JOIN users u ON u.id = s.id
      WHERE u.role = 'siswa'
      ORDER BY s.nama ASC
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

  public function getMapelIdsBySiswa(string $siswaId): array {
    if (trim($siswaId) === '') {
      return [];
    }

    $stmt = $this->db->prepare("
      SELECT mapel_id
      FROM siswa_mapel
      WHERE siswa_id = ?
        AND status = 'aktif'
      ORDER BY mapel_id ASC
    ");
    $stmt->execute([$siswaId]);
    return array_values(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN)));
  }

  public function getAllMapelRowsBySiswa(string $siswaId): array {
    if (trim($siswaId) === '') {
      return [];
    }

    $stmt = $this->db->prepare("
      SELECT id, mapel_id, status
      FROM siswa_mapel
      WHERE siswa_id = ?
      ORDER BY mapel_id ASC
    ");
    $stmt->execute([$siswaId]);

    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
      $rows[(string)$row['mapel_id']] = [
        'id' => (string)$row['id'],
        'status' => (string)$row['status'],
      ];
    }

    return $rows;
  }

  public function updateSiswaProfileAndMapel(string $siswaId, string $nama, string $kelas, string $waliId, array $mapelIds): array {
    $nama = trim($nama);
    $kelas = trim($kelas) !== '' ? trim($kelas) : 'Privat';
    $waliId = trim($waliId);
    $mapelIds = array_values(array_unique(array_filter(
      array_map(static fn($value) => trim((string)$value), $mapelIds),
      static fn($value) => $value !== ''
    )));

    if ($nama === '') {
      return ['status' => 'error', 'message' => 'Nama siswa wajib diisi.'];
    }

    if (!$this->roleProfileExists('siswa', $siswaId)) {
      return ['status' => 'error', 'message' => 'Data siswa tidak ditemukan.'];
    }

    if ($waliId !== '' && !$this->roleProfileExists('wali_murid', $waliId)) {
      return ['status' => 'error', 'message' => 'Data wali murid tidak ditemukan.'];
    }

    if (empty($mapelIds)) {
      return ['status' => 'error', 'message' => 'Pilih minimal satu mapel yang diikuti siswa.'];
    }

    if (!$this->allMapelExistsAndActive($mapelIds)) {
      return ['status' => 'error', 'message' => 'Ada mapel yang dipilih tidak valid atau sedang nonaktif.'];
    }

    $existingMapelRows = $this->getAllMapelRowsBySiswa($siswaId);
    $currentActiveMapelIds = array_keys(array_filter(
      $existingMapelRows,
      static fn($row) => ($row['status'] ?? '') === 'aktif'
    ));
    $toAdd = array_values(array_diff($mapelIds, $currentActiveMapelIds));
    $toRemove = array_values(array_diff($currentActiveMapelIds, $mapelIds));

    $this->db->beginTransaction();
    try {
      $stmt = $this->db->prepare("UPDATE siswa SET nama = ?, kelas_sekolah = ?, wali_id = ? WHERE id = ?");
      $stmt->execute([$nama, $kelas, $waliId !== '' ? $waliId : null, $siswaId]);

      foreach ($toAdd as $mapelId) {
        if (isset($existingMapelRows[$mapelId])) {
          $reactivate = $this->db->prepare("
            UPDATE siswa_mapel
            SET status = 'aktif'
            WHERE siswa_id = ? AND mapel_id = ?
          ");
          $reactivate->execute([$siswaId, $mapelId]);
          continue;
        }

        $rowId = $this->idCounterModel->generateId('siswa_mapel', 'SMP');
        $ins = $this->db->prepare("
          INSERT INTO siswa_mapel (id, siswa_id, mapel_id, status)
          VALUES (?, ?, ?, 'aktif')
        ");
        $ins->execute([$rowId, $siswaId, $mapelId]);
      }

      foreach ($toRemove as $mapelId) {
        $deactivate = $this->db->prepare("
          UPDATE siswa_mapel
          SET status = 'nonaktif'
          WHERE siswa_id = ? AND mapel_id = ?
        ");
        $deactivate->execute([$siswaId, $mapelId]);
        $this->cleanupUnusedKelasByMapel($siswaId, $mapelId);
      }

      $this->db->commit();
      return ['status' => 'success'];
    } catch (Throwable $e) {
      $this->db->rollBack();
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  public function countActiveSiswaMapel(): int {
    $stmt = $this->db->query("SELECT COUNT(*) FROM siswa_mapel WHERE status = 'aktif'");
    return (int)$stmt->fetchColumn();
  }

  private function roleProfileExists(string $table, string $userId): bool {
    if (!in_array($table, ['guru', 'siswa', 'wali_murid'], true)) {
      return false;
    }

    $stmt = $this->db->prepare("SELECT 1 FROM {$table} WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    return (bool)$stmt->fetchColumn();
  }

  private function allMapelExistsAndActive(array $mapelIds): bool {
    if (empty($mapelIds)) {
      return false;
    }

    $placeholders = implode(',', array_fill(0, count($mapelIds), '?'));
    $stmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM mapel
      WHERE status = 'aktif'
        AND id IN ({$placeholders})
    ");
    $stmt->execute($mapelIds);
    return (int)$stmt->fetchColumn() === count($mapelIds);
  }

  private function cleanupUnusedKelasByMapel(string $siswaId, string $mapelId): void {
    if ($siswaId === '' || $mapelId === '') {
      return;
    }

    $stmt = $this->db->prepare("
      SELECT k.id
      FROM kelas k
      INNER JOIN guru g ON g.id = k.guru_id
      WHERE k.siswa_id = ?
        AND g.mapel_id = ?
    ");
    $stmt->execute([$siswaId, $mapelId]);
    $kelasIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($kelasIds as $kelasId) {
      $hasJadwalStmt = $this->db->prepare("SELECT COUNT(*) FROM jadwal WHERE kelas_id = ?");
      $hasJadwalStmt->execute([$kelasId]);
      if ((int)$hasJadwalStmt->fetchColumn() > 0) {
        continue;
      }

      $this->db->prepare("DELETE FROM kelas WHERE id = ?")->execute([$kelasId]);
    }
  }
}
