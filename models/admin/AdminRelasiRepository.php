<?php
// ============================================================
// models/admin/AdminRelasiRepository.php
// Fokus: relasi siswa-guru (kelas belajar) untuk area admin
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';

class AdminRelasiRepository {

  private PDO $db;
  private IdCounterModel $idCounterModel;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null) {
    $this->db = $db ?? getDB();
    $this->idCounterModel = $idCounterModel ?? new IdCounterModel($this->db);
  }

  public function getRelasiList(): array {
    $stmt = $this->db->query("
      SELECT
        k.id,
        k.siswa_id,
        k.guru_id,
        k.status,
        k.created_at,
        s.nama AS siswa_nama,
        s.kelas_sekolah AS siswa_kelas,
        g.nama AS guru_nama,
        g.mapel_id,
        m.nama AS mata_pelajaran,
        (
          SELECT COUNT(*)
          FROM jadwal j
          WHERE j.kelas_id = k.id
        ) AS total_jadwal
      FROM kelas k
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN mapel m ON m.id = g.mapel_id
      ORDER BY s.nama ASC, m.nama ASC, g.nama ASC, k.created_at DESC
    ");
    return $stmt->fetchAll();
  }

  public function getSiswaOptions(): array {
    $stmt = $this->db->query("
      SELECT
        s.id,
        s.nama,
        s.kelas_sekolah AS kelas
      FROM siswa s
      INNER JOIN users u ON u.id = s.id
      WHERE u.role = 'siswa'
      ORDER BY s.nama ASC
    ");
    return $stmt->fetchAll();
  }

  public function getGuruOptions(): array {
    $stmt = $this->db->query("
      SELECT
        g.id,
        g.nama,
        g.mapel_id,
        m.nama AS mata_pelajaran
      FROM guru g
      LEFT JOIN mapel m ON m.id = g.mapel_id
      ORDER BY g.nama ASC
    ");
    return $stmt->fetchAll();
  }

  public function getSiswaMapelMatrix(): array {
    $stmt = $this->db->query("
      SELECT
        sm.siswa_id,
        sm.mapel_id,
        m.nama AS mata_pelajaran
      FROM siswa_mapel sm
      INNER JOIN mapel m ON m.id = sm.mapel_id
      WHERE sm.status = 'aktif'
      ORDER BY sm.siswa_id ASC, m.nama ASC
    ");

    $matrix = [];
    foreach ($stmt->fetchAll() as $row) {
      $siswaId = (string)$row['siswa_id'];
      $matrix[$siswaId][] = [
        'id' => (string)$row['mapel_id'],
        'nama' => (string)$row['mata_pelajaran'],
      ];
    }

    return $matrix;
  }

  public function createRelasi(string $siswaId, string $mapelId, string $guruId, string $status = 'aktif'): array {
    $status = $this->normalizeStatus($status);
    $validation = $this->validateRelationPayload($siswaId, $mapelId, $guruId, null);
    if ($validation !== null) {
      return ['status' => 'error', 'message' => $validation];
    }

    $existingSamePair = $this->findBySiswaAndGuru($siswaId, $guruId);
    if ($existingSamePair) {
      $stmt = $this->db->prepare("UPDATE kelas SET status = ? WHERE id = ?");
      $stmt->execute([$status, $existingSamePair['id']]);
      return ['status' => 'success', 'message' => 'Relasi yang sama sudah ada. Status berhasil diperbarui.'];
    }

    try {
      $relasiId = $this->idCounterModel->generateId('kelas', 'KLS');
      $stmt = $this->db->prepare("
        INSERT INTO kelas (id, siswa_id, guru_id, status)
        VALUES (?, ?, ?, ?)
      ");
      $stmt->execute([$relasiId, $siswaId, $guruId, $status]);
      return ['status' => 'success'];
    } catch (Throwable $e) {
      error_log('[AdminRelasiRepository::createRelasi] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Terjadi kesalahan saat menambahkan relasi belajar.'];
    }
  }

  public function updateRelasi(string $relasiId, string $siswaId, string $mapelId, string $guruId, string $status = 'aktif'): array {
    $relasiId = trim($relasiId);
    if ($relasiId === '') {
      return ['status' => 'error', 'message' => 'ID relasi tidak valid.'];
    }

    $current = $this->findById($relasiId);
    if (!$current) {
      return ['status' => 'error', 'message' => 'Data relasi tidak ditemukan.'];
    }

    $status = $this->normalizeStatus($status);
    $validation = $this->validateRelationPayload($siswaId, $mapelId, $guruId, $relasiId);
    if ($validation !== null) {
      return ['status' => 'error', 'message' => $validation];
    }

    try {
      $stmt = $this->db->prepare("
        UPDATE kelas
        SET siswa_id = ?, guru_id = ?, status = ?
        WHERE id = ?
      ");
      $stmt->execute([$siswaId, $guruId, $status, $relasiId]);
      return ['status' => 'success'];
    } catch (Throwable $e) {
      error_log('[AdminRelasiRepository::updateRelasi] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Terjadi kesalahan saat memperbarui relasi belajar.'];
    }
  }

  public function deleteRelasi(string $relasiId): array {
    $relasiId = trim($relasiId);
    if ($relasiId === '') {
      return ['status' => 'error', 'message' => 'ID relasi tidak valid.'];
    }

    $current = $this->findById($relasiId);
    if (!$current) {
      return ['status' => 'error', 'message' => 'Data relasi tidak ditemukan.'];
    }

    $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM jadwal WHERE kelas_id = ?");
    $stmtCount->execute([$relasiId]);
    if ((int)$stmtCount->fetchColumn() > 0) {
      return ['status' => 'error', 'message' => 'Relasi yang sudah dipakai jadwal tidak bisa dihapus. Ubah statusnya menjadi nonaktif jika perlu.'];
    }

    $stmt = $this->db->prepare("DELETE FROM kelas WHERE id = ?");
    $stmt->execute([$relasiId]);
    return ['status' => 'success'];
  }

  public function countRelasiAktif(): int {
    $stmt = $this->db->query("SELECT COUNT(*) FROM kelas WHERE status = 'aktif'");
    return (int)$stmt->fetchColumn();
  }

  public function countSiswaSiapJadwal(): int {
    $stmt = $this->db->query("
      SELECT COUNT(DISTINCT k.siswa_id)
      FROM kelas k
      INNER JOIN guru g ON g.id = k.guru_id
      INNER JOIN siswa_mapel sm
        ON sm.siswa_id = k.siswa_id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      WHERE k.status = 'aktif'
    ");
    return (int)$stmt->fetchColumn();
  }

  private function validateRelationPayload(string $siswaId, string $mapelId, string $guruId, ?string $excludeRelasiId): ?string {
    $siswaId = trim($siswaId);
    $selectedMapelId = trim($mapelId);
    $guruId = trim($guruId);

    if ($siswaId === '') {
      return 'Siswa wajib dipilih.';
    }

    if ($selectedMapelId === '') {
      return 'Mapel siswa wajib dipilih.';
    }

    if ($guruId === '') {
      return 'Guru wajib dipilih.';
    }

    if (!$this->profileExists('siswa', $siswaId)) {
      return 'Data siswa tidak ditemukan.';
    }

    $guru = $this->getGuruById($guruId);
    if (!$guru) {
      return 'Data guru tidak ditemukan.';
    }

    $guruMapelId = trim((string)($guru['mapel_id'] ?? ''));
    if ($guruMapelId === '') {
      return 'Guru ini belum memiliki mapel ajar.';
    }

    if ($selectedMapelId !== $guruMapelId) {
      return 'Guru yang dipilih tidak mengajar mapel tersebut.';
    }

    if (!$this->siswaHasMapel($siswaId, $selectedMapelId)) {
      return 'Siswa ini tidak terdaftar pada mapel yang dipilih.';
    }

    if ($this->existsOtherGuruForSameMapel($siswaId, $selectedMapelId, $excludeRelasiId, $guruId)) {
      return 'Siswa ini sudah memiliki pengajar lain untuk mapel tersebut.';
    }

    return null;
  }

  private function findById(string $relasiId): array|false {
    $stmt = $this->db->prepare("SELECT id, siswa_id, guru_id, status FROM kelas WHERE id = ? LIMIT 1");
    $stmt->execute([$relasiId]);
    return $stmt->fetch();
  }

  private function findBySiswaAndGuru(string $siswaId, string $guruId): array|false {
    $stmt = $this->db->prepare("SELECT id, siswa_id, guru_id, status FROM kelas WHERE siswa_id = ? AND guru_id = ? LIMIT 1");
    $stmt->execute([$siswaId, $guruId]);
    return $stmt->fetch();
  }

  private function getGuruById(string $guruId): array|false {
    $stmt = $this->db->prepare("SELECT id, nama, mapel_id FROM guru WHERE id = ? LIMIT 1");
    $stmt->execute([$guruId]);
    return $stmt->fetch();
  }

  private function siswaHasMapel(string $siswaId, string $mapelId): bool {
    $stmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM siswa_mapel
      WHERE siswa_id = ?
        AND mapel_id = ?
        AND status = 'aktif'
    ");
    $stmt->execute([$siswaId, $mapelId]);
    return (int)$stmt->fetchColumn() > 0;
  }

  private function existsOtherGuruForSameMapel(string $siswaId, string $mapelId, ?string $excludeRelasiId, string $guruId): bool {
    $sql = "
      SELECT COUNT(*)
      FROM kelas k
      INNER JOIN guru g ON g.id = k.guru_id
      WHERE k.siswa_id = ?
        AND g.mapel_id = ?
        AND k.guru_id <> ?
    ";
    $params = [$siswaId, $mapelId, $guruId];

    if ($excludeRelasiId !== null && trim($excludeRelasiId) !== '') {
      $sql .= " AND k.id <> ?";
      $params[] = trim($excludeRelasiId);
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
  }

  private function profileExists(string $table, string $id): bool {
    if (!in_array($table, ['siswa', 'guru'], true)) {
      return false;
    }

    $stmt = $this->db->prepare("SELECT 1 FROM {$table} WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return (bool)$stmt->fetchColumn();
  }

  private function normalizeStatus(string $status): string {
    return in_array($status, ['aktif', 'nonaktif'], true) ? $status : 'aktif';
  }
}
