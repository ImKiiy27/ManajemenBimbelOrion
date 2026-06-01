<?php
// ============================================================
// models/pendaftaran/PendaftaranModel.php
// Tugasnya: query yang berkaitan dengan pendaftaran calon siswa
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';

class PendaftaranModel {

  private PDO $db;
  private IdCounterModel $idCounterModel;
  private ?bool $pendaftaranHasAlamat = null;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null) {
    $this->db = $db ?? getDB();
    $this->idCounterModel = $idCounterModel ?? new IdCounterModel($this->db);
  }

  // Cek apakah email sudah terdaftar sebagai user aktif
  public function emailExistsInUsers(string $email): bool {
    $stmt = $this->db->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    return (bool)$stmt->fetchColumn();
  }

  public function countPendaftaran(): int {
    $stmt = $this->db->query("SELECT COUNT(*) FROM pendaftaran");
    return (int)$stmt->fetchColumn();
  }

  public function getMapelOptions(): array {
    $stmt = $this->db->query("
      SELECT id, nama
      FROM mapel
      ORDER BY
        CASE WHEN LOWER(nama) = 'privat' THEN 1 ELSE 0 END,
        nama ASC
    ");
    return $stmt->fetchAll();
  }

  // Cek apakah email/no hp sudah mendaftar dalam 24 jam terakhir
  // Mengembalikan sisa detik jika masih dalam cooldown, 0 jika bebas
  public function cekCooldownPendaftaran(string $email, string $telepon = '', string $noHpWali = ''): int {
    $email = trim($email);
    $telepon = trim($telepon);
    $noHpWali = trim($noHpWali);

    $conditions = [];
    $params = [];
    if ($email !== '') {
      $conditions[] = 'email = ?';
      $params[] = $email;
    }
    if ($telepon !== '') {
      $conditions[] = '(telepon = ? OR no_hp_wali = ?)';
      $params[] = $telepon;
      $params[] = $telepon;
    }
    if ($noHpWali !== '') {
      $conditions[] = '(telepon = ? OR no_hp_wali = ?)';
      $params[] = $noHpWali;
      $params[] = $noHpWali;
    }

    if (empty($conditions)) {
      return 0;
    }

    $where = implode(' OR ', $conditions);
    $stmt = $this->db->prepare("
      SELECT created_at FROM pendaftaran
      WHERE {$where}
      ORDER BY created_at DESC
      LIMIT 1
    ");
    $stmt->execute($params);
    $row = $stmt->fetch();

    if (!$row) {
      return 0;
    }

    $selisih  = time() - strtotime($row['created_at']);
    $cooldown = 24 * 60 * 60; // 24 jam dalam detik

    // Kalau masih dalam 24 jam, kembalikan sisa detik
    if ($selisih < $cooldown) {
      return $cooldown - $selisih;
    }

    return 0;
  }

  public function hasActivePendaftaranByEmailOrTelepon(string $email, string $telepon, string $noHpWali = ''): bool {
    $email = trim($email);
    $telepon = trim($telepon);
    $noHpWali = trim($noHpWali);

    $conditions = [];
    $params = [];
    if ($email !== '') {
      $conditions[] = 'email = ?';
      $params[] = $email;
    }
    if ($telepon !== '') {
      $conditions[] = '(telepon = ? OR no_hp_wali = ?)';
      $params[] = $telepon;
      $params[] = $telepon;
    }
    if ($noHpWali !== '') {
      $conditions[] = '(telepon = ? OR no_hp_wali = ?)';
      $params[] = $noHpWali;
      $params[] = $noHpWali;
    }

    if (empty($conditions)) {
      return false;
    }

    $where = implode(' OR ', $conditions);
    $stmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM pendaftaran
      WHERE ({$where})
        AND status IN ('pending', 'diproses', 'diterima')
    ");
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
  }

  // Simpan data pendaftaran baru
  public function daftar(
    string $nama,
    string $email,
    string $telepon,
    string $alamat,
    string $jenjang,
    string $kelasSekolah,
    string $asalSekolah,
    string $namaWali,
    string $noHpWali,
    string $catatan,
    array $mapelIds
  ): bool {
    $nama = trim($nama);
    $email = trim($email);
    $telepon = trim($telepon);
    $alamat = trim($alamat);
    $jenjang = trim($jenjang);
    $kelasSekolah = trim($kelasSekolah);
    $asalSekolah = trim($asalSekolah);
    $namaWali = trim($namaWali);
    $noHpWali = trim($noHpWali);
    $catatan = trim($catatan);

    if ($nama === '' || $email === '' || $telepon === '' || $alamat === '' || $jenjang === '' || $kelasSekolah === '' || $asalSekolah === '' || $namaWali === '' || $noHpWali === '') {
      return false;
    }

    if (!in_array($jenjang, ['SD', 'SMP', 'SMA', 'SMK', 'Lainnya'], true)) {
      return false;
    }

    // Tolak jika sudah jadi user aktif
    if ($this->emailExistsInUsers($email)) {
      return false;
    }

    if ($this->phoneExistsInProfiles($telepon) || $this->phoneExistsInProfiles($noHpWali)) {
      return false;
    }

    if ($this->hasActivePendaftaranByEmailOrTelepon($email, $telepon, $noHpWali)) {
      return false;
    }

    $kelasSekolah = trim($kelasSekolah) !== '' ? trim($kelasSekolah) : 'Privat';
    if (strlen($kelasSekolah) > 50) {
      $kelasSekolah = substr($kelasSekolah, 0, 50);
    }
    if (strlen($nama) > 150) {
      $nama = substr($nama, 0, 150);
    }
    if (strlen($asalSekolah) > 150) {
      $asalSekolah = substr($asalSekolah, 0, 150);
    }
    if (strlen($namaWali) > 150) {
      $namaWali = substr($namaWali, 0, 150);
    }
    if (strlen($telepon) > 30) {
      $telepon = substr($telepon, 0, 30);
    }
    if (strlen($noHpWali) > 30) {
      $noHpWali = substr($noHpWali, 0, 30);
    }
    if (strlen($catatan) > 2000) {
      $catatan = substr($catatan, 0, 2000);
    }
    if (strlen($alamat) > 2000) {
      $alamat = substr($alamat, 0, 2000);
    }

    $mapelIds = array_values(array_unique(array_filter(
      array_map(static fn($value) => trim((string)$value), $mapelIds),
      static fn($value) => $value !== ''
    )));
    if (empty($mapelIds)) {
      return false;
    }

    $validMapelIds = $this->resolveValidMapelIds($mapelIds);
    if (count($validMapelIds) !== count($mapelIds)) {
      return false;
    }

    $program = $this->buildProgramLabel($validMapelIds);
    $manageTxn = !$this->db->inTransaction();
    if ($manageTxn) {
      $this->db->beginTransaction();
    }

    try {
      $id = $this->idCounterModel->generateId('pendaftaran', 'PDT');

      $stmt = $this->db->prepare("
        INSERT INTO pendaftaran (
          id, nama, email, telepon, alamat, jenjang, kelas_sekolah, asal_sekolah, nama_wali, no_hp_wali, catatan, program, status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
      ");
      $stmt->execute([
        $id,
        $nama,
        $email,
        $telepon,
        $alamat,
        $jenjang,
        $kelasSekolah,
        $asalSekolah,
        $namaWali,
        $noHpWali,
        $catatan !== '' ? $catatan : null,
        $program
      ]);

      $mapelStmt = $this->db->prepare("
        INSERT INTO pendaftaran_mapel (id, pendaftaran_id, mapel_id)
        VALUES (?, ?, ?)
      ");

      foreach ($validMapelIds as $mapelId) {
        $relasiId = $this->idCounterModel->generateId('pendaftaran_mapel', 'PMA');
        $mapelStmt->execute([$relasiId, $id, $mapelId]);
      }

      if ($manageTxn) {
        $this->db->commit();
      }
      return true;
    } catch (Throwable $e) {
      error_log('[PendaftaranModel::daftar] ' . $e->getMessage());
      if ($manageTxn && $this->db->inTransaction()) {
        $this->db->rollBack();
      }
      return false;
    }
  }

  public function getPendaftaranTerbaru(int $limit = 10): array {
    $alamatSelect = $this->pendaftaranHasAlamatColumn() ? 'p.alamat' : "'' AS alamat";
    $alamatGroupBy = $this->pendaftaranHasAlamatColumn() ? "p.alamat,\n        " : '';

    $stmt = $this->db->prepare("
      SELECT
        p.id,
        p.nama,
        p.email,
        p.telepon,
        {$alamatSelect},
        p.jenjang,
        p.kelas_sekolah,
        p.asal_sekolah,
        p.nama_wali,
        p.no_hp_wali,
        p.catatan,
        p.status,
        p.created_at,
        COALESCE(NULLIF(GROUP_CONCAT(DISTINCT m.nama ORDER BY m.nama SEPARATOR ', '), ''), '-') AS mapel_diikuti
      FROM pendaftaran p
      LEFT JOIN pendaftaran_mapel pm ON pm.pendaftaran_id = p.id
      LEFT JOIN mapel m ON m.id = pm.mapel_id
      GROUP BY
        p.id,
        p.nama,
        p.email,
        p.telepon,
        {$alamatGroupBy}p.jenjang,
        p.kelas_sekolah,
        p.asal_sekolah,
        p.nama_wali,
        p.no_hp_wali,
        p.catatan,
        p.status,
        p.created_at
      ORDER BY p.created_at DESC
      LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  private function pendaftaranHasAlamatColumn(): bool {
    if ($this->pendaftaranHasAlamat !== null) {
      return $this->pendaftaranHasAlamat;
    }

    $stmt = $this->db->prepare("
      SELECT COUNT(*)
      FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'pendaftaran'
        AND COLUMN_NAME = 'alamat'
    ");
    $stmt->execute();
    $this->pendaftaranHasAlamat = (int)$stmt->fetchColumn() > 0;
    return $this->pendaftaranHasAlamat;
  }

  private function phoneExistsInProfiles(string $phone): bool {
    $phone = trim($phone);
    if ($phone === '') {
      return false;
    }

    $stmt = $this->db->prepare("
      SELECT COUNT(*) FROM (
        SELECT no_telp FROM siswa WHERE no_telp = ?
        UNION ALL
        SELECT no_telp FROM guru WHERE no_telp = ?
        UNION ALL
        SELECT no_telp FROM wali_murid WHERE no_telp = ?
      ) t
    ");
    $stmt->execute([$phone, $phone, $phone]);
    return (int)$stmt->fetchColumn() > 0;
  }

  private function resolveValidMapelIds(array $mapelIds): array {
    if (empty($mapelIds)) {
      return [];
    }

    $placeholders = implode(',', array_fill(0, count($mapelIds), '?'));
    $stmt = $this->db->prepare("SELECT id FROM mapel WHERE id IN ({$placeholders})");
    $stmt->execute($mapelIds);
    return array_values(array_unique(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN))));
  }

  private function buildProgramLabel(array $mapelIds): string {
    if (empty($mapelIds)) {
      return 'Privat';
    }

    $placeholders = implode(',', array_fill(0, count($mapelIds), '?'));
    $stmt = $this->db->prepare("SELECT nama FROM mapel WHERE id IN ({$placeholders}) ORDER BY nama ASC");
    $stmt->execute($mapelIds);

    $mapelNames = array_values(array_filter(
      array_map(static fn($value) => trim((string)$value), $stmt->fetchAll(PDO::FETCH_COLUMN)),
      static fn($value) => $value !== ''
    ));

    if (empty($mapelNames)) {
      return 'Privat';
    }

    $program = implode(', ', $mapelNames);
    if (function_exists('mb_strimwidth')) {
      return mb_strimwidth($program, 0, 50, '...');
    }
    return strlen($program) > 50 ? substr($program, 0, 47) . '...' : $program;
  }
}

