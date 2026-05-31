<?php
// ============================================================
// models/auth/AuthModel.php
// Tugasnya: query yang berkaitan dengan autentikasi login
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/RoleHelper.php';

class AuthModel {

  private PDO $db;
  private int $maxAttempts = 5;

  public function __construct(?PDO $db = null) {
    $this->db = $db ?? getDB();
  }

  // Cek email, password, locked status, dan attempts
  public function login(string $email, string $password): array {
    $stmt = $this->db->prepare("
      SELECT u.*,
             COALESCE(g.nama, s.nama, w.nama) AS nama
      FROM users u
      LEFT JOIN guru  g ON g.id = u.id
      LEFT JOIN siswa s ON s.id = u.id
      LEFT JOIN wali_murid w ON w.id = u.id
      WHERE u.email = ?
      LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Email tidak ditemukan, jangan tampilkan info percobaan
    if (!$user) {
      return ['status' => 'not_found'];
    }

    // Akun terkunci
    if ($user['is_locked']) {
      return ['status' => 'locked'];
    }

    // Password salah
    if (!password_verify($password, $user['password'])) {
      $this->tambahAttempt($user['id'], (int)$user['attempts']);
      $sisa = $this->maxAttempts - ((int)$user['attempts'] + 1);
      return ['status' => 'failed', 'sisa' => max(0, $sisa)];
    }

    // Login berhasil, reset attempts
    $this->resetAttempt($user['id']);
    $user['role'] = normalizeRole((string)($user['role'] ?? ''));

    return ['status' => 'success', 'user' => $user];
  }

  // Tambah attempt, kunci akun jika sudah melebihi batas
  private function tambahAttempt(string $userId, int $currentAttempts): void {
    $newAttempts = $currentAttempts + 1;
    $locked      = $newAttempts >= $this->maxAttempts ? 1 : 0;

    $stmt = $this->db->prepare(
      "UPDATE users SET attempts = ?, is_locked = ? WHERE id = ?"
    );
    $stmt->execute([$newAttempts, $locked, $userId]);
  }

  // Reset attempt setelah login berhasil
  private function resetAttempt(string $userId): void {
    $stmt = $this->db->prepare(
      "UPDATE users SET attempts = 0, is_locked = 0 WHERE id = ?"
    );
    $stmt->execute([$userId]);
  }
}

