<?php
// ============================================================
// models/absensi/AbsensiCommandService.php
// Command: create/update/delete absensi dengan full validation
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';

class AbsensiCommandService
{
  private PDO $db;
  private IdCounterModel $idCounterModel;

  private const EDIT_WINDOW_HOURS = 24;
  private const VALID_STATUSES = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
  private const MANDATORY_ALASAN_STATUSES = ['Izin', 'Sakit', 'Alpa'];

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null)
  {
    $this->db = $db ?? getDB();
    $this->idCounterModel = $idCounterModel ?? new IdCounterModel($this->db);
  }

  /**
   * Upsert absensi dengan validasi penuh.
   */
  public function createOrUpdateAbsensi(
    string $jadwalId,
    string $tanggal,
    string $siswaId,
    string $status,
    ?string $alasan,
    ?string $catatanGuru,
    string $updatedBy
  ): array {
    $jadwalId = trim($jadwalId);
    $tanggal = trim($tanggal);
    $siswaId = trim($siswaId);
    $status = trim($status);
    $updatedBy = trim($updatedBy);
    $alasan = $this->normalizeNullableText($alasan);
    $catatanGuru = $this->normalizeNullableText($catatanGuru);

    $validationError = $this->validateInput($jadwalId, $tanggal, $siswaId, $status, $alasan, $updatedBy);
    if ($validationError !== null) {
      return ['status' => 'error', 'message' => $validationError];
    }

    $actor = $this->getUserById($updatedBy);
    if (!$actor) {
      return ['status' => 'error', 'message' => 'User pelaku update tidak ditemukan.'];
    }

    $jadwal = $this->getJadwalWithKelas($jadwalId);
    if (!$jadwal) {
      return ['status' => 'error', 'message' => 'Jadwal tidak ditemukan.'];
    }

    if (!$this->canModifyAbsensi($actor, $jadwal, $tanggal)) {
      return ['status' => 'error', 'message' => 'Anda tidak memiliki akses untuk mengubah absensi ini atau melewati batas waktu edit.'];
    }

    if (!$this->validateTanggalSesuaiHari($tanggal, (string)$jadwal['hari'])) {
      return ['status' => 'error', 'message' => 'Tanggal input tidak sesuai dengan hari pada jadwal.'];
    }

    if ($this->isDateFuture($tanggal)) {
      return ['status' => 'error', 'message' => 'Tidak boleh input absensi untuk hari yang belum terjadi.'];
    }

    if (!$this->validateSiswaInKelas($siswaId, (string)$jadwal['kelas_id'])) {
      return ['status' => 'error', 'message' => 'Siswa tidak terdaftar dalam kelas jadwal ini.'];
    }

    try {
      $this->db->beginTransaction();

      $existing = $this->getExistingAbsensi($jadwalId, $tanggal, $siswaId);
      if ($existing) {
        $result = $this->updateExistingAbsensi(
          (string)$existing['id'],
          $status,
          $alasan,
          $catatanGuru,
          $updatedBy,
          $existing
        );
      } else {
        $result = $this->insertNewAbsensi(
          $jadwalId,
          $tanggal,
          $siswaId,
          $status,
          $alasan,
          $catatanGuru,
          $updatedBy
        );
      }

      $this->db->commit();
      return $result;
    } catch (PDOException $e) {
      if ($this->db->inTransaction()) {
        $this->db->rollBack();
      }
      return ['status' => 'error', 'message' => $this->friendlyError($e)];
    } catch (Throwable $e) {
      if ($this->db->inTransaction()) {
        $this->db->rollBack();
      }
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  public function deleteAbsensi(string $absensiId, string $deletedBy): array {
    $absensiId = trim($absensiId);
    $deletedBy = trim($deletedBy);

    if ($absensiId === '') {
      return ['status' => 'error', 'message' => 'ID absensi tidak valid.'];
    }
    if ($deletedBy === '') {
      return ['status' => 'error', 'message' => 'User pelaku delete tidak valid.'];
    }

    try {
      $absensi = $this->getAbsensiById($absensiId);
      if (!$absensi) {
        return ['status' => 'error', 'message' => 'Data absensi tidak ditemukan.'];
      }

      $this->db->beginTransaction();

      $this->logAudit(
        $absensiId,
        (string)($absensi['status'] ?? ''),
        null,
        $this->normalizeNullableText($absensi['alasan'] ?? null),
        null,
        $deletedBy,
        'DELETE'
      );

      $stmt = $this->db->prepare('DELETE FROM absensi WHERE id = ?');
      $stmt->execute([$absensiId]);

      $this->db->commit();
      return ['status' => 'success'];
    } catch (Throwable $e) {
      if ($this->db->inTransaction()) {
        $this->db->rollBack();
      }
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  private function validateInput(
    string $jadwalId,
    string $tanggal,
    string $siswaId,
    string $status,
    ?string $alasan,
    string $updatedBy
  ): ?string {
    if ($jadwalId === '') {
      return 'Jadwal wajib dipilih.';
    }

    if ($tanggal === '' || !$this->isValidDate($tanggal)) {
      return 'Format tanggal tidak valid (YYYY-MM-DD).';
    }

    if ($siswaId === '') {
      return 'Siswa wajib dipilih.';
    }

    if ($updatedBy === '') {
      return 'User pelaku update tidak valid.';
    }

    if (!in_array($status, self::VALID_STATUSES, true)) {
      return 'Status tidak valid.';
    }

    if (in_array($status, self::MANDATORY_ALASAN_STATUSES, true) && $alasan === null) {
      return "Alasan wajib diisi untuk status {$status}.";
    }

    return null;
  }

  private function normalizeNullableText(?string $value): ?string {
    $value = trim((string)$value);
    return $value === '' ? null : $value;
  }

  private function isValidDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d instanceof DateTime && $d->format('Y-m-d') === $date;
  }

  private function isDateFuture(string $tanggal): bool {
    return strtotime($tanggal) > strtotime(date('Y-m-d'));
  }

  private function validateTanggalSesuaiHari(string $tanggal, string $hariJadwal): bool {
    $hariMap = [
      'Senin' => 'Monday',
      'Selasa' => 'Tuesday',
      'Rabu' => 'Wednesday',
      'Kamis' => 'Thursday',
      'Jumat' => 'Friday',
      'Sabtu' => 'Saturday',
      'Minggu' => 'Sunday',
    ];

    $expected = $hariMap[$hariJadwal] ?? null;
    if ($expected === null) {
      return false;
    }

    return date('l', strtotime($tanggal)) === $expected;
  }

  private function canModifyAbsensi(array $actor, array $jadwal, string $tanggal): bool {
    $role = (string)($actor['role'] ?? '');
    $actorId = (string)($actor['id'] ?? '');

    if ($role === 'admin') {
      return true;
    }

    if ($role !== 'guru') {
      return false;
    }

    if ((string)$jadwal['guru_id'] !== $actorId) {
      return false;
    }

    return $this->isWithinEditWindow($tanggal);
  }

  private function isWithinEditWindow(string $tanggal): bool {
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $tanggal);
    if (!$date) {
      return false;
    }

    $deadline = $date->setTime(23, 59, 59)->modify('+' . self::EDIT_WINDOW_HOURS . ' hours');
    $now = new DateTimeImmutable('now');

    return $now <= $deadline;
  }

  private function getUserById(string $userId): array|false {
    $stmt = $this->db->prepare('SELECT id, role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    return $stmt->fetch();
  }

  private function getJadwalWithKelas(string $jadwalId): array|false {
    $stmt = $this->db->prepare('
      SELECT j.id, j.kelas_id, j.hari, j.jam_mulai, j.jam_selesai,
             k.guru_id, k.siswa_id, k.status AS kelas_status
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE j.id = ?
      LIMIT 1
    ');
    $stmt->execute([$jadwalId]);
    return $stmt->fetch();
  }

  private function validateSiswaInKelas(string $siswaId, string $kelasId): bool {
    $stmt = $this->db->prepare('
      SELECT 1
      FROM kelas
      WHERE id = ? AND siswa_id = ?
      LIMIT 1
    ');
    $stmt->execute([$kelasId, $siswaId]);
    return (bool)$stmt->fetch();
  }

  private function getExistingAbsensi(string $jadwalId, string $tanggal, string $siswaId): array|false {
    $stmt = $this->db->prepare('
      SELECT id, status, alasan, catatan_guru
      FROM absensi
      WHERE jadwal_id = ? AND tanggal = ? AND siswa_id = ?
      LIMIT 1
    ');
    $stmt->execute([$jadwalId, $tanggal, $siswaId]);
    return $stmt->fetch();
  }

  private function insertNewAbsensi(
    string $jadwalId,
    string $tanggal,
    string $siswaId,
    string $status,
    ?string $alasan,
    ?string $catatanGuru,
    string $updatedBy
  ): array {
    $absensiId = $this->idCounterModel->generateId('absensi', 'ABS');

    $stmt = $this->db->prepare('
      INSERT INTO absensi (
        id, jadwal_id, tanggal, siswa_id, status, alasan, catatan_guru, updated_by
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
      $absensiId,
      $jadwalId,
      $tanggal,
      $siswaId,
      $status,
      $alasan,
      $catatanGuru,
      $updatedBy,
    ]);

    $this->logAudit($absensiId, null, $status, null, $alasan, $updatedBy, 'INSERT');

    return ['status' => 'success', 'id' => $absensiId, 'action' => 'insert'];
  }

  private function updateExistingAbsensi(
    string $absensiId,
    string $newStatus,
    ?string $newAlasan,
    ?string $newCatatan,
    string $updatedBy,
    array $oldData
  ): array {
    $oldStatus = (string)($oldData['status'] ?? '');
    $oldAlasan = $this->normalizeNullableText($oldData['alasan'] ?? null);
    $oldCatatan = $this->normalizeNullableText($oldData['catatan_guru'] ?? null);

    $stmt = $this->db->prepare('
      UPDATE absensi
      SET status = ?, alasan = ?, catatan_guru = ?, updated_by = ?, updated_at = NOW()
      WHERE id = ?
    ');

    $stmt->execute([$newStatus, $newAlasan, $newCatatan, $updatedBy, $absensiId]);

    $hasStateChanged = ($oldStatus !== $newStatus) || ($oldAlasan !== $newAlasan) || ($oldCatatan !== $newCatatan);
    $actionType = $hasStateChanged ? 'CORRECTION' : 'UPDATE';

    $this->logAudit(
      $absensiId,
      $oldStatus,
      $newStatus,
      $oldAlasan,
      $newAlasan,
      $updatedBy,
      $actionType
    );

    return ['status' => 'success', 'id' => $absensiId, 'action' => 'update'];
  }

  private function getAbsensiById(string $absensiId): array|false {
    $stmt = $this->db->prepare('SELECT id, status, alasan FROM absensi WHERE id = ? LIMIT 1');
    $stmt->execute([$absensiId]);
    return $stmt->fetch();
  }

  private function logAudit(
    string $absensiId,
    ?string $oldStatus,
    ?string $newStatus,
    ?string $oldAlasan,
    ?string $newAlasan,
    string $changedBy,
    string $actionType
  ): void {
    $stmt = $this->db->prepare('
      INSERT INTO absensi_audit (
        absensi_id, old_status, new_status, old_alasan, new_alasan, changed_by, action_type
      ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
      $absensiId,
      $oldStatus,
      $newStatus,
      $oldAlasan,
      $newAlasan,
      $changedBy,
      $actionType,
    ]);
  }

  private function friendlyError(PDOException $e): string {
    $code = $e->errorInfo[1] ?? null;
    if ($code === 1062) {
      return 'Absensi untuk jadwal, tanggal, dan siswa ini sudah ada.';
    }
    if ($code === 1452) {
      return 'Relasi data tidak valid (jadwal/siswa/user).';
    }
    return 'Database error: ' . ($e->getMessage() ?? 'Unknown');
  }

  public function logCorrectionReason(string $absensiId, string $adminId, ?string $reason): void {
    $stmt = $this->db->prepare("
      INSERT INTO absensi_audit (
        absensi_id, changed_by, action_type, reason
      ) VALUES (?, ?, 'CORRECTION_REASON', ?)
    ");
    $stmt->execute([$absensiId, $adminId, $reason ?: null]);
  }
}
