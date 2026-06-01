<?php
// ============================================================
// models/ProfileRepository.php
// Query ringkasan profil pengguna lintas role
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/RoleHelper.php';

class ProfileRepository
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }

  public function getProfile(string $userId, string $role): array
  {
    $role = normalizeRole($role);

    return match ($role) {
      'guru' => $this->getGuruProfile($userId),
      'siswa' => $this->getSiswaProfile($userId),
      'wali_murid' => $this->getWaliProfile($userId),
      default => $this->getAdminProfile($userId),
    };
  }

  public function getAdminStats(): array
  {
    return [
      'total_user' => $this->countTable('users'),
      'total_guru' => $this->countWhere('users', 'role', 'guru'),
      'total_siswa' => $this->countWhere('users', 'role', 'siswa'),
      'total_wali' => $this->countWhere('users', 'role', 'wali_murid'),
      'total_mapel' => $this->countTable('mapel'),
      'total_jadwal' => $this->countTable('jadwal'),
    ];
  }

  public function getRecentUsers(int $limit = 5): array
  {
    $stmt = $this->db->prepare("
      SELECT
        u.id,
        u.email,
        u.role,
        u.created_at,
        COALESCE(g.nama, s.nama, w.nama, u.email) AS nama
      FROM users u
      LEFT JOIN guru g ON g.id = u.id
      LEFT JOIN siswa s ON s.id = u.id
      LEFT JOIN wali_murid w ON w.id = u.id
      ORDER BY u.created_at DESC, u.id DESC
      LIMIT {$limit}
    ");
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function getGuruSchedule(string $guruId): array
  {
    $stmt = $this->db->prepare("
      SELECT
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        s.nama AS siswa_nama,
        s.kelas_sekolah,
        m.nama AS mapel_nama
      FROM jadwal j
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      INNER JOIN guru g ON g.id = k.guru_id
      LEFT JOIN mapel m ON m.id = g.mapel_id
      WHERE k.guru_id = ?
      ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai ASC
      LIMIT 6
    ");
    $stmt->execute([$guruId]);
    return $stmt->fetchAll();
  }

  public function getGuruStats(string $guruId): array
  {
    $stmt = $this->db->prepare("
      SELECT
        COUNT(DISTINCT k.id) AS total_kelas,
        COUNT(DISTINCT k.siswa_id) AS total_siswa,
        COUNT(DISTINCT j.id) AS total_jadwal
      FROM kelas k
      LEFT JOIN jadwal j ON j.kelas_id = k.id
      WHERE k.guru_id = ?
    ");
    $stmt->execute([$guruId]);
    $stats = $stmt->fetch() ?: [];

    $absensi = $this->db->prepare("
      SELECT
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
        COUNT(a.id) AS total
      FROM absensi a
      INNER JOIN jadwal j ON j.id = a.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE k.guru_id = ?
    ");
    $absensi->execute([$guruId]);
    $row = $absensi->fetch() ?: [];
    $total = (int)($row['total'] ?? 0);
    $hadir = (int)($row['hadir'] ?? 0);
    $stats['kehadiran_persen'] = $total > 0 ? round(($hadir / $total) * 100) : 0;

    return $stats;
  }

  public function getSiswaSubjects(string $siswaId): array
  {
    $stmt = $this->db->prepare("
      SELECT m.nama, sm.status
      FROM siswa_mapel sm
      INNER JOIN mapel m ON m.id = sm.mapel_id
      WHERE sm.siswa_id = ?
      ORDER BY m.nama ASC
    ");
    $stmt->execute([$siswaId]);
    return $stmt->fetchAll();
  }

  public function getSiswaStats(string $siswaId): array
  {
    $stmt = $this->db->prepare("
      SELECT
        COUNT(DISTINCT j.id) AS total_jadwal,
        COUNT(DISTINCT sm.mapel_id) AS total_mapel
      FROM siswa s
      LEFT JOIN kelas k ON k.siswa_id = s.id
      LEFT JOIN jadwal j ON j.kelas_id = k.id
      LEFT JOIN siswa_mapel sm ON sm.siswa_id = s.id AND sm.status = 'aktif'
      WHERE s.id = ?
    ");
    $stmt->execute([$siswaId]);
    $stats = $stmt->fetch() ?: [];

    $absensi = $this->db->prepare("
      SELECT
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
        COUNT(id) AS total
      FROM absensi
      WHERE siswa_id = ?
    ");
    $absensi->execute([$siswaId]);
    $row = $absensi->fetch() ?: [];
    $total = (int)($row['total'] ?? 0);
    $hadir = (int)($row['hadir'] ?? 0);
    $stats['kehadiran_persen'] = $total > 0 ? round(($hadir / $total) * 100) : 0;

    $nilai = $this->db->prepare("
      SELECT
        COUNT(n.id) AS total_nilai,
        COALESCE(ROUND(AVG(CAST(n.predikat AS DECIMAL(5,2)))), 0) AS rata_nilai
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      WHERE k.siswa_id = ?
    ");
    $nilai->execute([$siswaId]);
    $nilaiRow = $nilai->fetch() ?: [];
    return array_merge($stats, $nilaiRow);
  }

  public function getWaliChildren(string $waliId): array
  {
    $stmt = $this->db->prepare("
      SELECT
        s.id,
        s.nama,
        s.kelas_sekolah,
        COALESCE(GROUP_CONCAT(DISTINCT m.nama ORDER BY m.nama SEPARATOR ', '), '-') AS mapel
      FROM siswa s
      LEFT JOIN siswa_mapel sm ON sm.siswa_id = s.id AND sm.status = 'aktif'
      LEFT JOIN mapel m ON m.id = sm.mapel_id
      WHERE s.wali_id = ?
      GROUP BY s.id, s.nama, s.kelas_sekolah
      ORDER BY s.nama ASC
    ");
    $stmt->execute([$waliId]);
    return $stmt->fetchAll();
  }

  public function getWaliStats(string $waliId): array
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM siswa WHERE wali_id = ?");
    $stmt->execute([$waliId]);
    $totalAnak = (int)$stmt->fetchColumn();

    $absensi = $this->db->prepare("
      SELECT
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
        COUNT(a.id) AS total
      FROM absensi a
      INNER JOIN siswa s ON s.id = a.siswa_id
      WHERE s.wali_id = ?
    ");
    $absensi->execute([$waliId]);
    $row = $absensi->fetch() ?: [];
    $total = (int)($row['total'] ?? 0);
    $hadir = (int)($row['hadir'] ?? 0);

    $nilai = $this->db->prepare("
      SELECT COALESCE(ROUND(AVG(CAST(n.predikat AS DECIMAL(5,2)))), 0) AS rata_nilai
      FROM nilai n
      INNER JOIN jadwal j ON j.id = n.jadwal_id
      INNER JOIN kelas k ON k.id = j.kelas_id
      INNER JOIN siswa s ON s.id = k.siswa_id
      WHERE s.wali_id = ?
    ");
    $nilai->execute([$waliId]);

    return [
      'total_anak' => $totalAnak,
      'kehadiran_persen' => $total > 0 ? round(($hadir / $total) * 100) : 0,
      'rata_nilai' => (int)($nilai->fetchColumn() ?: 0),
    ];
  }

  public function updateProfile(string $userId, string $role, array $data): array
  {
    $role = normalizeRole($role);
    $email = strtolower(trim((string)($data['email'] ?? '')));
    $noTelp = $this->normalizeNullableText($data['no_telp'] ?? null);
    $alamat = $this->normalizeNullableText($data['alamat'] ?? null);

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return ['status' => 'error', 'message' => 'Email tidak valid.'];
    }
    if ($noTelp !== null && !preg_match('/^[0-9+\\-\\s]{8,20}$/', $noTelp)) {
      return ['status' => 'error', 'message' => 'Nomor telepon tidak valid (8-20 karakter, angka/+/-/spasi).'];
    }

    try {
      $this->db->beginTransaction();

      if (!$this->emailAvailable($email, $userId)) {
        $this->db->rollBack();
        return ['status' => 'error', 'message' => 'Email sudah digunakan akun lain.'];
      }

      $userStmt = $this->db->prepare("UPDATE users SET email = ? WHERE id = ?");
      $userStmt->execute([$email, $userId]);

      $result = match ($role) {
        'guru' => $this->updateGuruProfile($userId, $noTelp, $alamat, $data),
        'siswa' => $this->updateSiswaProfile($userId, $noTelp, $alamat, $data),
        'wali_murid' => $this->updateWaliProfile($userId, $noTelp, $alamat, $data),
        default => ['status' => 'success', 'message' => 'Profil admin berhasil diperbarui.'],
      };

      if (($result['status'] ?? 'error') !== 'success') {
        $this->db->rollBack();
        return $result;
      }

      $this->db->commit();
      return ['status' => 'success', 'message' => $result['message'] ?? 'Profil berhasil diperbarui.'];
    } catch (PDOException $e) {
      if ($this->db->inTransaction()) {
        $this->db->rollBack();
      }
      error_log('[ProfileRepository::updateProfile] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Terjadi kesalahan saat memperbarui profil.'];
    }
  }

  public function changePassword(string $userId, string $currentPassword, string $newPassword, string $confirmPassword): array
  {
    $currentPassword = trim($currentPassword);
    $newPassword = trim($newPassword);
    $confirmPassword = trim($confirmPassword);

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
      return ['status' => 'error', 'message' => 'Semua kolom password wajib diisi.'];
    }
    if ($newPassword !== $confirmPassword) {
      return ['status' => 'error', 'message' => 'Konfirmasi password baru tidak cocok.'];
    }
    if (strlen($newPassword) < 8) {
      return ['status' => 'error', 'message' => 'Password minimal 8 karakter.'];
    }
    if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
      return ['status' => 'error', 'message' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.'];
    }

    $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $hash = (string)($stmt->fetchColumn() ?: '');
    if ($hash === '' || !password_verify($currentPassword, $hash)) {
      return ['status' => 'error', 'message' => 'Password saat ini salah.'];
    }
    if (password_verify($newPassword, $hash)) {
      return ['status' => 'error', 'message' => 'Password baru harus berbeda dari password saat ini.'];
    }

    $up = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $up->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);

    return ['status' => 'success', 'message' => 'Password berhasil diubah.'];
  }

  private function getAdminProfile(string $userId): array
  {
    $stmt = $this->db->prepare("SELECT id, email, role, is_locked, attempts, created_at FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch() ?: [];
    $email = (string)($user['email'] ?? '');
    $name = $_SESSION['nama'] ?? ($email !== '' ? strtok($email, '@') : 'Admin');

    return array_merge($user, [
      'nama' => $name,
      'alamat' => '-',
      'no_telp' => '-',
      'foto_path' => null,
    ]);
  }

  private function getGuruProfile(string $userId): array
  {
    $stmt = $this->db->prepare("
      SELECT u.id, u.email, u.role, u.is_locked, u.attempts, u.created_at,
             g.nama, g.alamat, g.no_telp, g.foto_path, g.bio,
             m.nama AS mapel_nama
      FROM users u
      LEFT JOIN guru g ON g.id = u.id
      LEFT JOIN mapel m ON m.id = g.mapel_id
      WHERE u.id = ?
      LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: [];
  }

  private function getSiswaProfile(string $userId): array
  {
    $stmt = $this->db->prepare("
      SELECT u.id, u.email, u.role, u.is_locked, u.attempts, u.created_at,
             s.nama, s.kelas_sekolah, s.asal_sekolah, s.alamat, s.no_telp, s.foto_path,
             w.nama AS wali_nama, w.hubungan AS wali_hubungan
      FROM users u
      LEFT JOIN siswa s ON s.id = u.id
      LEFT JOIN wali_murid w ON w.id = s.wali_id
      WHERE u.id = ?
      LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: [];
  }

  private function getWaliProfile(string $userId): array
  {
    $stmt = $this->db->prepare("
      SELECT u.id, u.email, u.role, u.is_locked, u.attempts, u.created_at,
             w.nama, w.no_telp, w.hubungan, w.pekerjaan, w.alamat, w.foto_path
      FROM users u
      LEFT JOIN wali_murid w ON w.id = u.id
      WHERE u.id = ?
      LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: [];
  }

  private function countTable(string $table): int
  {
    return (int)$this->db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
  }

  private function countWhere(string $table, string $column, string $value): int
  {
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
    $stmt->execute([$value]);
    return (int)$stmt->fetchColumn();
  }

  private function emailAvailable(string $email, string $excludeUserId): bool
  {
    $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
    $stmt->execute([$email, $excludeUserId]);
    return !$stmt->fetchColumn();
  }

  private function updateGuruProfile(string $userId, ?string $noTelp, ?string $alamat, array $data): array
  {
    $nama = trim((string)($data['nama'] ?? ''));
    $bio = $this->normalizeNullableText($data['bio'] ?? null);
    if ($nama === '') {
      return ['status' => 'error', 'message' => 'Nama guru wajib diisi.'];
    }
    $stmt = $this->db->prepare("UPDATE guru SET nama = ?, no_telp = ?, alamat = ?, bio = ? WHERE id = ?");
    $stmt->execute([$nama, $noTelp, $alamat, $bio, $userId]);
    return ['status' => 'success', 'message' => 'Profil guru berhasil diperbarui.'];
  }

  private function updateSiswaProfile(string $userId, ?string $noTelp, ?string $alamat, array $data): array
  {
    $nama = trim((string)($data['nama'] ?? ''));
    $asalSekolah = $this->normalizeNullableText($data['asal_sekolah'] ?? null);
    if ($nama === '') {
      return ['status' => 'error', 'message' => 'Nama siswa wajib diisi.'];
    }
    $stmt = $this->db->prepare("UPDATE siswa SET nama = ?, no_telp = ?, alamat = ?, asal_sekolah = ? WHERE id = ?");
    $stmt->execute([$nama, $noTelp, $alamat, $asalSekolah, $userId]);
    return ['status' => 'success', 'message' => 'Profil siswa berhasil diperbarui.'];
  }

  private function updateWaliProfile(string $userId, ?string $noTelp, ?string $alamat, array $data): array
  {
    $nama = trim((string)($data['nama'] ?? ''));
    $hubungan = $this->normalizeNullableText($data['hubungan'] ?? null);
    $pekerjaan = $this->normalizeNullableText($data['pekerjaan'] ?? null);
    if ($nama === '') {
      return ['status' => 'error', 'message' => 'Nama wali murid wajib diisi.'];
    }
    $stmt = $this->db->prepare("UPDATE wali_murid SET nama = ?, no_telp = ?, alamat = ?, hubungan = ?, pekerjaan = ? WHERE id = ?");
    $stmt->execute([$nama, $noTelp, $alamat, $hubungan, $pekerjaan, $userId]);
    return ['status' => 'success', 'message' => 'Profil wali murid berhasil diperbarui.'];
  }

  private function normalizeNullableText(mixed $value): ?string
  {
    $text = trim((string)($value ?? ''));
    return $text === '' ? null : $text;
  }
}
