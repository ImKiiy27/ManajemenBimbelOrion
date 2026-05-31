<?php
// ============================================================
// controllers/admin/actions/AdminUserActionHandler.php
// Fokus: proses aksi POST untuk kelola user (admin-user)
// ============================================================

require_once __DIR__ . '/../../../models/admin/AdminUserRepository.php';
require_once __DIR__ . '/../../../helpers/RoleHelper.php';

class AdminUserActionHandler
{

  private AdminUserRepository $userRepository;

  public function __construct(AdminUserRepository $userRepository)
  {
    $this->userRepository = $userRepository;
  }

  public function handleCreate(): void
  {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = normalizeRole((string)($_POST['role'] ?? ''));
    $nama     = trim($_POST['nama'] ?? '');
    $kelas    = trim($_POST['kelas'] ?? '');

    $error = $this->validateUserForm($email, $password, $role, $nama, true);
    if ($error !== null) {
      $_SESSION['flash_error'] = $error;
      return;
    }

    $result = $this->userRepository->createUser([
      'email'    => $email,
      'password' => $password,
      'role'     => $role,
      'nama'     => $nama,
      'kelas'    => $kelas,
    ]);

    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'User baru berhasil dibuat.'
      : ($result['message'] ?? 'Gagal membuat user.');
  }

  public function handleUpdate(): void
  {
    $userId   = trim($_POST['user_id'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = normalizeRole((string)($_POST['role'] ?? ''));
    $nama     = trim($_POST['nama'] ?? '');
    $kelas    = trim($_POST['kelas'] ?? '');

    if ($userId === '') {
      $_SESSION['flash_error'] = 'User ID tidak valid.';
      return;
    }

    $error = $this->validateUserForm($email, $password, $role, $nama, false);
    if ($error !== null) {
      $_SESSION['flash_error'] = $error;
      return;
    }

    $result = $this->userRepository->updateUser($userId, [
      'email'    => $email,
      'password' => $password,
      'role'     => $role,
      'nama'     => $nama,
      'kelas'    => $kelas,
    ]);

    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'User berhasil diperbarui.'
      : ($result['message'] ?? 'Gagal memperbarui user.');
  }

  public function handleDelete(): void
  {
    $userId = trim($_POST['user_id'] ?? '');
    if ($userId === '') {
      $_SESSION['flash_error'] = 'User ID tidak valid.';
      return;
    }

    $result = $this->userRepository->deleteUser($userId);
    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'User berhasil dihapus.'
      : ($result['message'] ?? 'Gagal menghapus user.');
  }

  public function handleDeleteForce(): void
  {
    $userId = trim($_POST['user_id'] ?? '');
    if ($userId === '') {
      $_SESSION['flash_error'] = 'User ID tidak valid.';
      return;
    }

    $result = $this->userRepository->deleteUserForce($userId);
    $_SESSION['flash_' . $result['status']] = $result['status'] === 'success'
      ? 'User dan semua relasinya berhasil dihapus.'
      : ($result['message'] ?? 'Gagal menghapus user.');
  }

  public function handleUnlock(): void
  {
    $userId = trim($_POST['user_id'] ?? '');
    if ($userId === '') {
      $_SESSION['flash_error'] = 'User ID tidak valid.';
      return;
    }

    $this->userRepository->unlock($userId);
    $_SESSION['flash_success'] = 'Status kunci/percobaan login sudah direset.';
  }

  private function validateUserForm(string $email, string $password, string $role, string $nama, bool $isCreate): ?string
  {
    $allowedRoles = ['admin', 'guru', 'siswa', 'wali_murid'];

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return 'Email tidak valid.';
    }

    if ($isCreate) {
      $passError = $this->validatePassword($password);
      if ($passError !== null) {
        return $passError;
      }
    } elseif ($password !== '') {
      $passError = $this->validatePassword($password);
      if ($passError !== null) {
        return $passError;
      }
    }

    if (!in_array($role, $allowedRoles, true)) {
      return 'Role tidak valid.';
    }

    if (in_array($role, ['guru', 'siswa', 'wali_murid'], true) && $nama === '') {
      return 'Nama wajib diisi untuk role guru, siswa, dan wali murid.';
    }

    return null;
  }

  private function validatePassword(string $password): ?string
  {
    if (strlen($password) < 8) {
      return 'Password minimal 8 karakter.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
      return 'Password harus mengandung minimal 1 huruf besar (A-Z).';
    }

    if (!preg_match('/[a-z]/', $password)) {
      return 'Password harus mengandung minimal 1 huruf kecil (a-z).';
    }

    if (!preg_match('/[0-9]/', $password)) {
      return 'Password harus mengandung minimal 1 angka (0-9).';
    }

    return null;
  }
}
