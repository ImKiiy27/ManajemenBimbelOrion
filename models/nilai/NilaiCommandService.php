<?php
// ============================================================
// models/nilai/NilaiCommandService.php
// Fokus: create/update/delete data nilai
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';

class NilaiCommandService {

  private PDO $db;
  private IdCounterModel $idCounterModel;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null) {
    $this->db = $db ?? getDB();
    $this->idCounterModel = $idCounterModel ?? new IdCounterModel($this->db);
  }

  public function createNilai(array $data): array {
    $jadwalId = trim((string)($data['jadwal_id'] ?? ''));
    $pertemuanKe = isset($data['pertemuan_ke']) ? (int)$data['pertemuan_ke'] : 1;
    $tipeNilai = trim((string)($data['tipe_nilai'] ?? 'utama'));
    $predikat = trim((string)($data['predikat'] ?? ''));
    $catatanGuru = trim((string)($data['catatan_guru'] ?? ''));

    $validasi = $this->validateNilaiPayload($jadwalId, $pertemuanKe, $tipeNilai, $predikat);
    if ($validasi !== null) {
      return ['success' => false, 'message' => $validasi];
    }

    if (!$this->isValidJadwalRelation($jadwalId)) {
      return ['success' => false, 'message' => 'Jadwal tidak valid karena siswa tidak terdaftar pada mapel tersebut atau relasinya sudah nonaktif.'];
    }

    try {
      $nilaiId = $this->idCounterModel->generateId('nilai', 'NLI');

      $stmt = $this->db->prepare("
        INSERT INTO nilai (id, jadwal_id, pertemuan_ke, tipe_nilai, predikat, catatan_guru)
        VALUES (?, ?, ?, ?, ?, ?)
      ");

      $stmt->execute([
        $nilaiId,
        $jadwalId,
        $pertemuanKe,
        $tipeNilai,
        $predikat,
        $catatanGuru !== '' ? $catatanGuru : null
      ]);

      return [
        'success' => true,
        'id' => $nilaiId,
        'message' => 'Nilai berhasil ditambahkan'
      ];
    } catch (PDOException $e) {
      error_log('[NilaiCommandService::createNilai] ' . $e->getMessage());
      return ['success' => false, 'message' => $this->friendlyError($e)];
    } catch (Throwable $e) {
      error_log('[NilaiCommandService::createNilai] ' . $e->getMessage());
      return ['success' => false, 'message' => 'Terjadi kesalahan saat menambahkan nilai.'];
    }
  }

  public function updateNilai(string $nilaiId, array $data): array {
    $nilaiId = trim($nilaiId);
    if ($nilaiId === '') {
      return [
        'success' => false,
        'message' => 'ID nilai tidak valid.'
      ];
    }

    $current = $this->getNilaiRowById($nilaiId);
    if (!$current) {
      return [
        'success' => false,
        'message' => 'Data nilai tidak ditemukan.'
      ];
    }

    if (!$this->isValidJadwalRelation((string)$current['jadwal_id'])) {
      return [
        'success' => false,
        'message' => 'Nilai tidak bisa diperbarui karena jadwalnya tidak lagi memiliki relasi siswa-mapel-guru yang valid.'
      ];
    }

    $setClauses = [];
    $values = [];
    $allowedFields = ['pertemuan_ke', 'tipe_nilai', 'predikat', 'catatan_guru'];

    $nextPertemuanKe = array_key_exists('pertemuan_ke', $data) ? (int)$data['pertemuan_ke'] : (int)$current['pertemuan_ke'];
    $nextTipeNilai = array_key_exists('tipe_nilai', $data) ? trim((string)$data['tipe_nilai']) : (string)$current['tipe_nilai'];
    $nextPredikat = array_key_exists('predikat', $data) ? trim((string)$data['predikat']) : (string)$current['predikat'];

    $validasi = $this->validateNilaiPayload((string)$current['jadwal_id'], $nextPertemuanKe, $nextTipeNilai, $nextPredikat);
    if ($validasi !== null) {
      return [
        'success' => false,
        'message' => $validasi
      ];
    }

    foreach ($allowedFields as $field) {
      if (isset($data[$field])) {
        $setClauses[] = "{$field} = ?";
        $values[] = $field === 'catatan_guru'
          ? (trim((string)$data[$field]) !== '' ? trim((string)$data[$field]) : null)
          : $data[$field];
      }
    }

    if (empty($setClauses)) {
      return [
        'success' => false,
        'message' => 'Tidak ada data yang diupdate'
      ];
    }

    $values[] = $nilaiId;
    $sql = "UPDATE nilai SET " . implode(', ', $setClauses) . " WHERE id = ?";

    try {
      $stmt = $this->db->prepare($sql);
      $stmt->execute($values);

      return [
        'success' => true,
        'message' => 'Nilai berhasil diupdate'
      ];
    } catch (PDOException $e) {
      error_log('[NilaiCommandService::updateNilai] ' . $e->getMessage());
      return ['success' => false, 'message' => $this->friendlyError($e)];
    } catch (Throwable $e) {
      error_log('[NilaiCommandService::updateNilai] ' . $e->getMessage());
      return ['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui nilai.'];
    }
  }

  public function deleteNilai(string $nilaiId): array {
    try {
      $stmt = $this->db->prepare("DELETE FROM nilai WHERE id = ?");
      $stmt->execute([$nilaiId]);

      return [
        'success' => true,
        'message' => 'Nilai berhasil dihapus'
      ];
    } catch (Throwable $e) {
      error_log('[NilaiCommandService::deleteNilai] ' . $e->getMessage());
      return [
        'success' => false,
        'message' => 'Terjadi kesalahan saat menghapus nilai.'
      ];
    }
  }

  private function validateNilaiPayload(string $jadwalId, int $pertemuanKe, string $tipeNilai, string $predikat): ?string {
    if ($jadwalId === '') {
      return 'Jadwal harus dipilih.';
    }

    if ($pertemuanKe < 1) {
      return 'Pertemuan ke minimal 1.';
    }

    if (!in_array($tipeNilai, ['utama', 'susulan', 'remedial'], true)) {
      return 'Tipe nilai tidak valid.';
    }

    if ($predikat === '') {
      return 'Nilai/Score harus diisi.';
    }

    if (!is_numeric($predikat) || (float)$predikat < 0 || (float)$predikat > 100) {
      return 'Nilai harus angka antara 0-100.';
    }

    return null;
  }

  private function isValidJadwalRelation(string $jadwalId): bool {
    $stmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN guru g ON g.id = k.guru_id
      INNER JOIN siswa_mapel sm
        ON sm.siswa_id = k.siswa_id
       AND sm.mapel_id = g.mapel_id
       AND sm.status = 'aktif'
      WHERE j.id = ?
        AND k.status = 'aktif'
    ");
    $stmt->execute([$jadwalId]);
    return (int)$stmt->fetchColumn() > 0;
  }

  private function getNilaiRowById(string $nilaiId): array|false {
    $stmt = $this->db->prepare("
      SELECT id, jadwal_id, pertemuan_ke, tipe_nilai, predikat, catatan_guru
      FROM nilai
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->execute([$nilaiId]);
    return $stmt->fetch();
  }

  private function friendlyError(PDOException $e): string {
    $code = $e->errorInfo[1] ?? null;
    if ($code === 1062) {
      return 'Nilai dengan kombinasi jadwal, pertemuan, dan tipe tersebut sudah ada.';
    }
    return 'Terjadi kesalahan database saat memproses nilai.';
  }
}
