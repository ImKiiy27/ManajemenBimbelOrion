<?php
// ============================================================
// controllers/auth/actions/AuthLoginActionHandler.php
// Fokus: proses POST login + session setup + redirect
// ============================================================

require_once __DIR__ . '/../../../models/auth/AuthModel.php';
require_once __DIR__ . '/../../../helpers/RoleHelper.php';
require_once __DIR__ . '/../../../config/RateLimiter.php';

class AuthLoginActionHandler
{

  private AuthModel $authModel;

  public function __construct(AuthModel $authModel)
  {
    $this->authModel = $authModel;
  }

  public function handlePost(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return;
    }

    RateLimiter::check('login');

    if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
      $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
      $this->redirect('login');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $_SESSION['old_input'] = [
      'email' => $email,
      'remember' => isset($_POST['remember']) ? '1' : '',
    ];

    if ($email === '' || $password === '') {
      $_SESSION['flash_error'] = 'Email dan password wajib diisi.';
      $this->redirect('login');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $_SESSION['flash_error'] = 'Format email tidak valid.';
      $this->redirect('login');
    }

    $result = $this->authModel->login($email, $password);
    switch ($result['status']) {
      case 'success':
        $user = $result['user'];
        $role = normalizeRole((string)($user['role'] ?? ''));
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'] ?? $email;
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $role;
        $_SESSION['last_activity'] = time();
        $_SESSION['created_at'] = time();
        $_SESSION['regenerated_at'] = time();
        unset($_SESSION['old_input']);

        $this->redirectByRole($role);
        break;

      case 'locked':
        $_SESSION['flash_error'] = 'Akun Anda terkunci karena terlalu banyak percobaan login. Silakan hubungi admin.';
        break;

      case 'not_found':
        $_SESSION['flash_error'] = 'Email tidak terdaftar.';
        break;

      default:
        $sisa = $result['sisa'] ?? null;
        $_SESSION['flash_error'] = is_null($sisa)
          ? 'Email atau password salah.'
          : ($sisa > 0
            ? "Email atau password salah. Sisa percobaan: {$sisa}x."
            : 'Akun Anda terkunci. Silakan hubungi admin.');
        break;
    }

    $this->redirect('login');
  }

  private function redirectByRole(string $role): void
  {
    $role = normalizeRole($role);
    $page = match ($role) {
      'admin' => 'admin-dashboard',
      'guru' => 'guru-dashboard',
      'siswa' => 'siswa-dashboard',
      'wali_murid' => 'wali-dashboard',
      default => 'login',
    };
    $this->redirect($page);
  }

  private function redirect(string $page): void
  {
    header("Location: index.php?page={$page}");
    exit;
  }
}
