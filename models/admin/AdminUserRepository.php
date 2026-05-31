<?php
// ============================================================
// models/admin/AdminUserRepository.php
// Fokus: CRUD user admin + sinkronisasi profil guru/siswa
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';
require_once __DIR__ . '/../../helpers/RoleHelper.php';

class AdminUserRepository
{

  private PDO $db;
  private IdCounterModel $idCounterModel;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null)
  {
    $this->db = $db ?? getDB();
    $this->idCounterModel = $idCounterModel ?? new IdCounterModel($this->db);
  }

  public function findById(string $id): array|false
  {
    $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) {
      return false;
    }
    $user['role'] = normalizeRole((string)($user['role'] ?? ''));
    return $user;
  }

  public function unlock(string $userId): bool
  {
    $stmt = $this->db->prepare("UPDATE users SET is_locked = 0, attempts = 0 WHERE id = ?");
    return $stmt->execute([$userId]);
  }

  public function getAllUsersWithDetail(): array
  {
    $stmt = $this->db->query("
      SELECT
        u.id,
        u.email,
        u.role,
        u.is_locked,
        u.attempts,
        u.created_at,
        g.id AS guru_id,
        g.nama AS guru_nama,
        mg.nama AS mapel,
        s.id AS siswa_id,
        s.nama AS siswa_nama,
        s.kelas_sekolah AS kelas,
        w.id AS wali_id,
        w.nama AS wali_nama
      FROM users u
      LEFT JOIN guru g ON g.id = u.id
      LEFT JOIN mapel mg ON mg.id = g.mapel_id
      LEFT JOIN siswa s ON s.id = u.id
      LEFT JOIN wali_murid w ON w.id = u.id
      ORDER BY u.created_at DESC
    ");
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
      $row['role'] = normalizeRole((string)($row['role'] ?? ''));
    }
    unset($row);
    return $rows;
  }

  public function getUserWithDetail(string $userId): array|false
  {
    $stmt = $this->db->prepare("
      SELECT
        u.*,
        g.id AS guru_id,
        g.nama AS guru_nama,
        mg.nama AS mapel,
        s.id AS siswa_id,
        s.nama AS siswa_nama,
        s.kelas_sekolah AS kelas,
        w.id AS wali_id,
        w.nama AS wali_nama
      FROM users u
      LEFT JOIN guru g ON g.id = u.id
      LEFT JOIN mapel mg ON mg.id = g.mapel_id
      LEFT JOIN siswa s ON s.id = u.id
      LEFT JOIN wali_murid w ON w.id = u.id
      WHERE u.id = ?
      LIMIT 1
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
      return false;
    }
    $user['role'] = normalizeRole((string)($user['role'] ?? ''));
    return $user;
  }

  public function createUser(array $data): array
  {
    $this->db->beginTransaction();
    try {
      $role = normalizeRole((string)($data['role'] ?? ''));

      [$tabel, $prefix] = match ($role) {
        'guru'      => ['guru',        'GRU'],
        'siswa'     => ['siswa',       'SSW'],
        'wali_murid'=> ['wali_murid',  'WLM'],
        'admin'     => ['users',       'ADM'],
        default     => ['users',       'USR'],
      };

      $userId = $this->idCounterModel->generateId($tabel, $prefix);

      $stmt = $this->db->prepare("
        INSERT INTO users (id, email, password, role, is_locked, attempts)
        VALUES (?, ?, ?, ?, 0, 0)
      ");
      $stmt->execute([
        $userId,
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $role,
      ]);

      $namaProfil = $this->buildDefaultProfileName(
        (string)$data['email'],
        $role,
        (string)($data['nama'] ?? '')
      );

      if ($role === 'guru') {
        $mapelId = $this->getDefaultMapelId();
        $stmt = $this->db->prepare("
          INSERT INTO guru (id, mapel_id, nama)
          VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $mapelId, $namaProfil]);
      } elseif ($role === 'siswa') {
        $kelas = ($data['kelas'] ?? '') !== '' ? trim((string)$data['kelas']) : 'Privat';
        $stmt = $this->db->prepare("
          INSERT INTO siswa (id, nama, kelas_sekolah)
          VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $namaProfil, $kelas]);
      } elseif ($role === 'wali_murid') {
        $stmt = $this->db->prepare("
          INSERT INTO wali_murid (id, nama)
          VALUES (?, ?)
        ");
        $stmt->execute([$userId, $namaProfil]);
      }

      $this->db->commit();
      return ['status' => 'success', 'id' => $userId];
    } catch (PDOException $e) {
      $this->db->rollBack();
      return ['status' => 'error', 'message' => $this->friendlyDuplicateMessage($e)];
    } catch (Throwable $e) {
      $this->db->rollBack();
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  public function updateUser(string $userId, array $data): array
  {
    $current = $this->getUserWithDetail($userId);
    if (!$current) {
      return ['status' => 'error', 'message' => 'User tidak ditemukan'];
    }

    $role = normalizeRole((string)($data['role'] ?? ''));

    if (($current['role'] ?? '') !== $role) {
      return ['status' => 'error', 'message' => 'Role tidak boleh diubah. Hapus dan buat ulang bila ingin pindah role.'];
    }

    $this->db->beginTransaction();
    try {
      $params = [$data['email']];
      $sql = "UPDATE users SET email = ?";

      if (!empty($data['password'])) {
        $sql .= ", password = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
      }

      $sql .= " WHERE id = ?";
      $params[] = $userId;

      $stmt = $this->db->prepare($sql);
      $stmt->execute($params);

      $namaProfil = $this->buildDefaultProfileName(
        (string)($data['email'] ?? ($current['email'] ?? '')),
        $role,
        (string)($data['nama'] ?? '')
      );

      if ($role === 'guru') {
        $namaGuru = trim((string)($data['nama'] ?? '')) !== ''
          ? trim((string)$data['nama'])
          : (trim((string)($current['guru_nama'] ?? '')) !== '' ? (string)$current['guru_nama'] : $namaProfil);

        $stmt = $this->db->prepare("UPDATE guru SET nama = ? WHERE id = ?");
        $stmt->execute([$namaGuru, $userId]);
      } elseif ($role === 'siswa') {
        $namaSiswa = trim((string)($data['nama'] ?? '')) !== ''
          ? trim((string)$data['nama'])
          : (trim((string)($current['siswa_nama'] ?? '')) !== '' ? (string)$current['siswa_nama'] : $namaProfil);

        $kelas = ($data['kelas'] ?? '') !== '' ? trim((string)$data['kelas']) : ($current['kelas'] ?? 'Privat');
        $stmt = $this->db->prepare("UPDATE siswa SET nama = ?, kelas_sekolah = ? WHERE id = ?");
        $stmt->execute([$namaSiswa, $kelas, $userId]);
      } elseif ($role === 'wali_murid') {
        $namaWali = trim((string)($data['nama'] ?? '')) !== ''
          ? trim((string)$data['nama'])
          : (trim((string)($current['wali_nama'] ?? '')) !== '' ? (string)$current['wali_nama'] : $namaProfil);

        $stmt = $this->db->prepare("UPDATE wali_murid SET nama = ? WHERE id = ?");
        $stmt->execute([$namaWali, $userId]);
      }

      $this->db->commit();
      return ['status' => 'success'];
    } catch (PDOException $e) {
      $this->db->rollBack();
      return ['status' => 'error', 'message' => $this->friendlyDuplicateMessage($e)];
    } catch (Throwable $e) {
      $this->db->rollBack();
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  public function deleteUser(string $userId): array
  {
    $current = $this->getUserWithDetail($userId);
    if (!$current) {
      return ['status' => 'error', 'message' => 'User tidak ditemukan'];
    }

    if (($current['role'] ?? '') === 'admin') {
      return ['status' => 'error', 'message' => 'User dengan role admin tidak boleh dihapus.'];
    }

    if ($current['role'] === 'guru' && $this->hasRelasiGuru($current['guru_id'])) {
      return ['status' => 'error', 'message' => 'Guru masih terhubung dengan relasi. Gunakan force delete untuk menghapus beserta relasi.'];
    }

    if ($current['role'] === 'siswa' && $this->hasRelasiSiswa($current['siswa_id'] ?? '')) {
      return ['status' => 'error', 'message' => 'Siswa masih terhubung dengan relasi. Gunakan force delete untuk menghapus beserta relasi.'];
    }

    if ($current['role'] === 'wali_murid' && $this->hasRelasiWali($current['wali_id'])) {
      return ['status' => 'error', 'message' => 'Wali Murid masih memiliki anak yang terdaftar. Gunakan force delete untuk menghapus beserta relasi.'];
    }

    $this->db->beginTransaction();
    try {
      $this->db->prepare("DELETE FROM guru WHERE id = ?")->execute([$userId]);
      $this->db->prepare("DELETE FROM siswa WHERE id = ?")->execute([$userId]);
      $this->db->prepare("DELETE FROM wali_murid WHERE id = ?")->execute([$userId]);
      $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
      $this->db->commit();
      return ['status' => 'success'];
    } catch (Throwable $e) {
      $this->db->rollBack();
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  // Get all relasi untuk konfirmasi sebelum force delete
  public function getRelasiDetail(string $userId): array
  {
    $current = $this->getUserWithDetail($userId);
    if (!$current) {
      return [];
    }

    $relasi = [];

    if ($current['role'] === 'siswa') {
      $siswaId = $current['siswa_id'] ?? '';
      if (!empty($siswaId)) {
        // Kelas
        $stmtKelas = $this->db->prepare("
          SELECT k.id, g.nama as guru_nama
          FROM kelas k
          JOIN guru g ON g.id = k.guru_id
          WHERE k.siswa_id = ?
        ");
        $stmtKelas->execute([$siswaId]);
        $kelasRecords = $stmtKelas->fetchAll();
        if (!empty($kelasRecords)) {
          $relasi['kelas'] = $kelasRecords;
        }

        // Siswa Mapel
        $stmtMapel = $this->db->prepare("
          SELECT sm.id, m.nama as mapel_nama
          FROM siswa_mapel sm
          JOIN mapel m ON m.id = sm.mapel_id
          WHERE sm.siswa_id = ?
        ");
        $stmtMapel->execute([$siswaId]);
        $mapelRecords = $stmtMapel->fetchAll();
        if (!empty($mapelRecords)) {
          $relasi['siswa_mapel'] = $mapelRecords;
        }
      }
    }

    if ($current['role'] === 'guru') {
      $guruId = $current['guru_id'] ?? '';
      if (!empty($guruId)) {
        // Kelas
        $stmtKelas = $this->db->prepare("
          SELECT k.id, s.nama as siswa_nama
          FROM kelas k
          JOIN siswa s ON s.id = k.siswa_id
          WHERE k.guru_id = ?
        ");
        $stmtKelas->execute([$guruId]);
        $kelasRecords = $stmtKelas->fetchAll();
        if (!empty($kelasRecords)) {
          $relasi['kelas'] = $kelasRecords;
        }
      }
    }

    if ($current['role'] === 'wali_murid') {
      $waliId = $current['wali_id'] ?? '';
      if (!empty($waliId)) {
        $stmtAnak = $this->db->prepare("
          SELECT id, nama AS siswa_nama
          FROM siswa
          WHERE wali_id = ?
          ORDER BY nama ASC
        ");
        $stmtAnak->execute([$waliId]);
        $anakRecords = $stmtAnak->fetchAll();
        if (!empty($anakRecords)) {
          $relasi['anak'] = $anakRecords;
        }
      }
    }

    return $relasi;
  }

  // Force delete user dengan relasi
  public function deleteUserForce(string $userId): array
  {
    $current = $this->getUserWithDetail($userId);
    if (!$current) {
      return ['status' => 'error', 'message' => 'User tidak ditemukan'];
    }

    if (($current['role'] ?? '') === 'admin') {
      return ['status' => 'error', 'message' => 'User dengan role admin tidak boleh dihapus.'];
    }

    $this->db->beginTransaction();
    try {
      if ($current['role'] === 'siswa') {
        $siswaId = $current['siswa_id'] ?? '';
        if (!empty($siswaId)) {
          $this->db->prepare("DELETE FROM kelas WHERE siswa_id = ?")->execute([$siswaId]);
          $this->db->prepare("DELETE FROM siswa_mapel WHERE siswa_id = ?")->execute([$siswaId]);
        }
      }

      if ($current['role'] === 'guru') {
        $guruId = $current['guru_id'] ?? '';
        if (!empty($guruId)) {
          $this->db->prepare("DELETE FROM kelas WHERE guru_id = ?")->execute([$guruId]);
        }
      }

      if ($current['role'] === 'wali_murid') {
        $waliId = $current['wali_id'];
        if (!empty($waliId)) {
          // Set wali_id ke NULL untuk semua siswa yang terhubung
          $this->db->prepare("UPDATE siswa SET wali_id = NULL WHERE wali_id = ?")->execute([$waliId]);
        }
      }

      $this->db->prepare("DELETE FROM guru WHERE id = ?")->execute([$userId]);
      $this->db->prepare("DELETE FROM siswa WHERE id = ?")->execute([$userId]);
      $this->db->prepare("DELETE FROM wali_murid WHERE id = ?")->execute([$userId]);
      $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
      $this->db->commit();
      return ['status' => 'success'];
    } catch (Throwable $e) {
      $this->db->rollBack();
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }

  private function hasRelasiGuru(?string $guruId): bool
  {
    if (empty($guruId)) {
      return false;
    }

    $stmt = $this->db->prepare("SELECT COUNT(*) FROM kelas WHERE guru_id = ?");
    $stmt->execute([$guruId]);
    return (int)$stmt->fetchColumn() > 0;
  }

  // Helper method untuk get detail relasi guru (untuk error message)
  private function getDetailRelasiGuru(?string $guruId): array
  {
    $details = [];

    if (empty($guruId)) {
      return $details;
    }

    $stmtKelas = $this->db->prepare("SELECT COUNT(*) AS count FROM kelas WHERE guru_id = ?");
    $stmtKelas->execute([$guruId]);
    $countKelas = (int)($stmtKelas->fetch()['count'] ?? 0);
    if ($countKelas > 0) {
      $details[] = "$countKelas kelas";
    }

    return $details;
  }

  private function hasRelasiSiswa(?string $siswaId): bool
  {
    if (empty($siswaId)) {
      return false;
    }

    // Check kelas
    $stmtKelas = $this->db->prepare("SELECT COUNT(*) FROM kelas WHERE siswa_id = ?");
    $stmtKelas->execute([$siswaId]);
    if ((int)$stmtKelas->fetchColumn() > 0) {
      return true;
    }

    // Check siswa_mapel
    $stmtMapel = $this->db->prepare("SELECT COUNT(*) FROM siswa_mapel WHERE siswa_id = ?");
    $stmtMapel->execute([$siswaId]);
    if ((int)$stmtMapel->fetchColumn() > 0) {
      return true;
    }

    // Check jadwal (melalui kelas - untuk safety)
    $stmtJadwal = $this->db->prepare("
      SELECT COUNT(*) FROM jadwal j
      WHERE EXISTS (SELECT 1 FROM kelas k WHERE k.id = j.kelas_id AND k.siswa_id = ?)
    ");
    $stmtJadwal->execute([$siswaId]);
    return (int)$stmtJadwal->fetchColumn() > 0;
  }

  private function hasRelasiWali(?string $waliId): bool
  {
    if (empty($waliId)) {
      return false;
    }

    $stmt = $this->db->prepare("SELECT COUNT(*) FROM siswa WHERE wali_id = ?");
    $stmt->execute([$waliId]);
    return (int)$stmt->fetchColumn() > 0;
  }

  // Helper method untuk get detail relasi siswa (untuk error message)
  private function getDetailRelasiSiswa(?string $siswaId): array
  {
    $details = [];

    if (empty($siswaId)) {
      return $details;
    }

    $stmtKelas = $this->db->prepare("SELECT COUNT(*) AS count FROM kelas WHERE siswa_id = ?");
    $stmtKelas->execute([$siswaId]);
    $countKelas = (int)($stmtKelas->fetch()['count'] ?? 0);
    if ($countKelas > 0) {
      $details[] = "$countKelas kelas";
    }

    $stmtMapel = $this->db->prepare("SELECT COUNT(*) AS count FROM siswa_mapel WHERE siswa_id = ?");
    $stmtMapel->execute([$siswaId]);
    $countMapel = (int)($stmtMapel->fetch()['count'] ?? 0);
    if ($countMapel > 0) {
      $details[] = "$countMapel mapel";
    }

    return $details;
  }

  private function ensureMapelByName(string $name): string
  {
    $name = trim($name);
    if ($name === '') {
      return $this->getDefaultMapelId();
    }

    $stmt = $this->db->prepare("SELECT id FROM mapel WHERE LOWER(nama) = LOWER(?) LIMIT 1");
    $stmt->execute([$name]);
    $existing = $stmt->fetchColumn();
    if ($existing) {
      return (string)$existing;
    }

    $mapelId = $this->idCounterModel->generateId('mapel', 'MPL');
    $ins = $this->db->prepare("INSERT INTO mapel (id, nama, deskripsi) VALUES (?, ?, ?)");
    $ins->execute([$mapelId, $name, '']);

    return $mapelId;
  }

  private function getDefaultMapelId(): string
  {
    $stmt = $this->db->prepare("SELECT id FROM mapel WHERE LOWER(nama) = 'privat' LIMIT 1");
    $stmt->execute();
    $id = $stmt->fetchColumn();
    if ($id) {
      return (string)$id;
    }
    return $this->ensureMapelByName('Privat');
  }

  private function buildDefaultProfileName(string $email, string $role, string $candidate = ''): string
  {
    $candidate = trim($candidate);
    if ($candidate !== '') {
      return $candidate;
    }

    $local = trim((string)explode('@', $email)[0]);
    $local = preg_replace('/[^a-zA-Z0-9]+/', ' ', $local);
    $local = trim((string)$local);

    if ($local !== '') {
      return ucwords(strtolower($local));
    }

    return match ($role) {
      'guru' => 'Guru Baru',
      'siswa' => 'Siswa Baru',
      'wali_murid' => 'Wali Murid Baru',
      'admin' => 'Admin Baru',
      default => 'User Baru',
    };
  }

  private function friendlyDuplicateMessage(PDOException $e): string
  {
    $code = $e->errorInfo[1] ?? null;
    if ($code === 1062) {
      return 'Data duplikat. Cek kembali email atau relasi yang sudah ada.';
    }
    return $e->getMessage();
  }
}
