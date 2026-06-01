<?php
// ============================================================
// models/jadwal/JadwalCommandService.php
// Fokus: create/update/delete data jadwal
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';

class JadwalCommandService {

  private PDO $db;
  private IdCounterModel $idCounterModel;
  private array $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null) {
    $this->db = $db ?? getDB();
    $this->idCounterModel = $idCounterModel ?? new IdCounterModel($this->db);
  }

  public function createJadwal(array $data): array {
    $siswaMapelId = trim((string)($data['siswa_mapel_id'] ?? ''));
    $kelasId = trim((string)($data['kelas_id'] ?? ''));
    $hari = trim((string)($data['hari'] ?? ''));
    $jamMulai = trim((string)($data['jam_mulai'] ?? ''));
    $jamSelesai = trim((string)($data['jam_selesai'] ?? ''));

    if ($kelasId === '' && $siswaMapelId !== '') {
      $kelasId = $this->resolveKelasIdFromSiswaMapel($siswaMapelId);
    }

    $validasi = $this->validateInput($kelasId, $hari, $jamMulai, $jamSelesai);
    if ($validasi !== null) {
      return ['status' => 'error', 'message' => $validasi];
    }

    $kelas = $this->getKelasById($kelasId);
    if (!$kelas) {
      return ['status' => 'error', 'message' => 'Relasi kelas tidak ditemukan.'];
    }

    $konflik = $this->findTimeConflict(
      (string)$kelas['siswa_id'],
      (string)$kelas['guru_id'],
      $hari,
      $jamMulai,
      $jamSelesai
    );
    if ($konflik) {
      return ['status' => 'error', 'message' => $this->buildConflictMessage($konflik)];
    }

    try {
      $jadwalId = $this->idCounterModel->generateId('jadwal', 'JDL');
      $stmt = $this->db->prepare("
        INSERT INTO jadwal (id, kelas_id, hari, jam_mulai, jam_selesai)
        VALUES (?, ?, ?, ?, ?)
      ");
      $stmt->execute([$jadwalId, $kelasId, $hari, $jamMulai, $jamSelesai]);
      return ['status' => 'success', 'id' => $jadwalId];
    } catch (PDOException $e) {
      error_log('[JadwalCommandService::createJadwal] ' . $e->getMessage());
      return ['status' => 'error', 'message' => $this->friendlyError($e)];
    } catch (Throwable $e) {
      error_log('[JadwalCommandService::createJadwal] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Terjadi kesalahan saat membuat jadwal.'];
    }
  }

  public function updateJadwal(string $jadwalId, array $data): array {
    $jadwalId = trim($jadwalId);
    $siswaMapelId = trim((string)($data['siswa_mapel_id'] ?? ''));
    $kelasId = trim((string)($data['kelas_id'] ?? ''));
    $hari = trim((string)($data['hari'] ?? ''));
    $jamMulai = trim((string)($data['jam_mulai'] ?? ''));
    $jamSelesai = trim((string)($data['jam_selesai'] ?? ''));

    if ($jadwalId === '') {
      return ['status' => 'error', 'message' => 'ID jadwal tidak valid.'];
    }

    if ($kelasId === '' && $siswaMapelId !== '') {
      $kelasId = $this->resolveKelasIdFromSiswaMapel($siswaMapelId);
    }

    $validasi = $this->validateInput($kelasId, $hari, $jamMulai, $jamSelesai);
    if ($validasi !== null) {
      return ['status' => 'error', 'message' => $validasi];
    }

    $current = $this->getJadwalById($jadwalId);
    if (!$current) {
      return ['status' => 'error', 'message' => 'Data jadwal tidak ditemukan.'];
    }

    $kelas = $this->getKelasById($kelasId);
    if (!$kelas) {
      return ['status' => 'error', 'message' => 'Relasi kelas tidak ditemukan.'];
    }

    $konflik = $this->findTimeConflict(
      (string)$kelas['siswa_id'],
      (string)$kelas['guru_id'],
      $hari,
      $jamMulai,
      $jamSelesai,
      $jadwalId
    );
    if ($konflik) {
      return ['status' => 'error', 'message' => $this->buildConflictMessage($konflik)];
    }

    try {
      $stmt = $this->db->prepare("
        UPDATE jadwal
        SET kelas_id = ?, hari = ?, jam_mulai = ?, jam_selesai = ?
        WHERE id = ?
      ");
      $stmt->execute([$kelasId, $hari, $jamMulai, $jamSelesai, $jadwalId]);
      return ['status' => 'success'];
    } catch (PDOException $e) {
      error_log('[JadwalCommandService::updateJadwal] ' . $e->getMessage());
      return ['status' => 'error', 'message' => $this->friendlyError($e)];
    } catch (Throwable $e) {
      error_log('[JadwalCommandService::updateJadwal] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Terjadi kesalahan saat memperbarui jadwal.'];
    }
  }

  public function deleteJadwal(string $jadwalId): array {
    $jadwalId = trim($jadwalId);
    if ($jadwalId === '') {
      return ['status' => 'error', 'message' => 'ID jadwal tidak valid.'];
    }

    try {
      $usedBy = $this->getJadwalUsage($jadwalId);
      if (($usedBy['nilai'] ?? 0) > 0 || ($usedBy['absensi'] ?? 0) > 0) {
        return [
          'status' => 'error',
          'message' => 'Jadwal tidak dapat dihapus karena sudah memiliki data absensi atau nilai.'
        ];
      }

      $stmt = $this->db->prepare("DELETE FROM jadwal WHERE id = ?");
      $stmt->execute([$jadwalId]);
      if ($stmt->rowCount() === 0) {
        return ['status' => 'error', 'message' => 'Data jadwal tidak ditemukan.'];
      }
      return ['status' => 'success'];
    } catch (Throwable $e) {
      error_log('[JadwalCommandService::deleteJadwal] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Terjadi kesalahan saat menghapus jadwal.'];
    }
  }

  private function getJadwalUsage(string $jadwalId): array {
    $stmt = $this->db->prepare("
      SELECT
        (SELECT COUNT(*) FROM absensi WHERE jadwal_id = ?) AS total_absensi,
        (SELECT COUNT(*) FROM nilai WHERE jadwal_id = ?) AS total_nilai
    ");
    $stmt->execute([$jadwalId, $jadwalId]);
    $row = $stmt->fetch() ?: [];

    return [
      'absensi' => (int)($row['total_absensi'] ?? 0),
      'nilai' => (int)($row['total_nilai'] ?? 0),
    ];
  }

  private function getJadwalById(string $jadwalId): array|false {
    $stmt = $this->db->prepare("
      SELECT id, kelas_id, hari, jam_mulai, jam_selesai
      FROM jadwal
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->execute([$jadwalId]);
    return $stmt->fetch();
  }

  private function getKelasById(string $kelasId): array|false {
    $stmt = $this->db->prepare("
      SELECT id, siswa_id, guru_id, status
      FROM kelas
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->execute([$kelasId]);
    return $stmt->fetch();
  }

  private function resolveKelasIdFromSiswaMapel(string $siswaMapelId): string {
    if ($siswaMapelId === '') {
      return '';
    }

    $stmt = $this->db->prepare("
      SELECT k.id
      FROM siswa_mapel sm
      INNER JOIN kelas k ON k.siswa_id = sm.siswa_id AND k.status = 'aktif'
      INNER JOIN guru g ON g.id = k.guru_id
      WHERE sm.id = ?
        AND sm.status = 'aktif'
        AND g.mapel_id = sm.mapel_id
      ORDER BY k.id ASC
      LIMIT 1
    ");
    $stmt->execute([$siswaMapelId]);
    return (string)$stmt->fetchColumn();
  }

  private function validateInput(string $kelasId, string $hari, string $jamMulai, string $jamSelesai): ?string {
    if ($kelasId === '') {
      return 'Relasi siswa-mapel-guru wajib dipilih.';
    }

    if (!$this->isValidKelasRelation($kelasId)) {
      return 'Relasi siswa, mapel, dan guru tidak valid atau sudah tidak aktif.';
    }

    if (!in_array($hari, $this->hariList, true)) {
      return 'Hari jadwal tidak valid.';
    }

    if (!$this->isValidTime($jamMulai) || !$this->isValidTime($jamSelesai)) {
      return 'Format jam tidak valid.';
    }

    if ($jamSelesai <= $jamMulai) {
      return 'Jam selesai harus lebih besar dari jam mulai.';
    }

    return null;
  }

  private function isValidKelasRelation(string $kelasId): bool {
    $stmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM kelas k
      INNER JOIN guru g ON g.id = k.guru_id
      INNER JOIN siswa_mapel sm
        ON sm.siswa_id = k.siswa_id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      WHERE k.id = ?
        AND k.status = 'aktif'
    ");
    $stmt->execute([$kelasId]);
    return (int)$stmt->fetchColumn() > 0;
  }

  private function isValidTime(string $value): bool {
    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
      return false;
    }
    return strtotime("1970-01-01 {$value}") !== false;
  }

  private function findTimeConflict(
    string $siswaId,
    string $guruId,
    string $hari,
    string $jamMulai,
    string $jamSelesai,
    ?string $excludeJadwalId = null
  ): array|false {
    $sql = "
      SELECT
        j.id,
        j.jam_mulai,
        j.jam_selesai,
        s.nama AS siswa_nama,
        g.nama AS guru_nama
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      WHERE j.hari = ?
        AND (? < j.jam_selesai AND ? > j.jam_mulai)
        AND (k.siswa_id = ? OR k.guru_id = ?)
    ";

    $params = [$hari, $jamMulai, $jamSelesai, $siswaId, $guruId];
    if ($excludeJadwalId !== null && $excludeJadwalId !== '') {
      $sql .= " AND j.id <> ?";
      $params[] = $excludeJadwalId;
    }
    $sql .= " LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
  }

  private function buildConflictMessage(array $konflik): string {
    $jam = substr((string)$konflik['jam_mulai'], 0, 5) . '-' . substr((string)$konflik['jam_selesai'], 0, 5);
    return 'Bentrok jadwal pada jam ' . $jam
      . ' (Siswa: ' . ($konflik['siswa_nama'] ?? '-')
      . ', Guru: ' . ($konflik['guru_nama'] ?? '-') . ').';
  }

  private function friendlyError(PDOException $e): string {
    $code = $e->errorInfo[1] ?? null;
    if ($code === 1062) {
      return 'Jadwal dengan kombinasi kelas, hari, dan jam mulai tersebut sudah ada.';
    }
    return 'Terjadi kesalahan database saat memproses jadwal.';
  }
}
