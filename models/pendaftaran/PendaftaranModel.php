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

  // Cek apakah email sudah mendaftar dalam 24 jam terakhir
  // Mengembalikan sisa detik jika masih dalam cooldown, 0 jika bebas
  public function cekCooldownPendaftaran(string $email): int {
    $stmt = $this->db->prepare("
      SELECT created_at FROM pendaftaran
      WHERE email = ?
      ORDER BY created_at DESC
      LIMIT 1
    ");
    $stmt->execute([$email]);
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

  // Simpan data pendaftaran baru
  public function daftar(
    string $nama,
    string $email,
    string $telepon,
    string $kelasSekolah,
    array $mapelIds
  ): bool {
    // Tolak jika sudah jadi user aktif
    if ($this->emailExistsInUsers($email)) {
      return false;
    }

    $kelasSekolah = trim($kelasSekolah) !== '' ? trim($kelasSekolah) : 'Privat';
    if (strlen($kelasSekolah) > 50) {
      $kelasSekolah = substr($kelasSekolah, 0, 50);
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
        INSERT INTO pendaftaran (id, nama, email, telepon, kelas_sekolah, program, status)
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
      ");
      $stmt->execute([$id, $nama, $email, $telepon, $kelasSekolah, $program]);

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
      if ($manageTxn && $this->db->inTransaction()) {
        $this->db->rollBack();
      }
      return false;
    }
  }

  public function getPendaftaranTerbaru(int $limit = 10): array {
    $stmt = $this->db->prepare("
      SELECT
        p.id,
        p.nama,
        p.email,
        p.telepon,
        p.kelas_sekolah,
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
        p.kelas_sekolah,
        p.status,
        p.created_at
      ORDER BY p.created_at DESC
      LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
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

