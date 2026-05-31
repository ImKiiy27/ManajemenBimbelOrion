<?php
// ============================================================
// controllers/auth/AuthController.php
// Orkestrasi halaman auth (index/login/logout/pendaftaran)
// ============================================================

require_once __DIR__ . '/../../models/auth/AuthModel.php';
require_once __DIR__ . '/../../models/pendaftaran/PendaftaranModel.php';
require_once __DIR__ . '/AuthActionHandler.php';
require_once __DIR__ . '/../../helpers/RoleHelper.php';

class AuthController
{
  private AuthModel $authModel;
  private PendaftaranModel $pendaftaranModel;
  private AuthActionHandler $authActionHandler;

  public function __construct()
  {
    $this->authModel = new AuthModel();
    $this->pendaftaranModel = new PendaftaranModel();
    $this->authActionHandler = new AuthActionHandler();
  }

  public function index(): void
  {
    $this->render('auth/index');
  }

  public function login(): void
  {
    if (isset($_SESSION['user_id'])) {
      $_SESSION['role'] = normalizeRole((string)($_SESSION['role'] ?? ''));
      $this->redirectByRole((string)$_SESSION['role']);
    }

    $this->authActionHandler->handleLogin($this->authModel);

    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    $oldInput = $_SESSION['old_input'] ?? [];
    if (!is_array($oldInput)) {
      $oldInput = [];
    }
    unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['old_input']);

    $this->render('auth/login', compact('error', 'success', 'oldInput'));
  }

  public function logout(): void
  {
    $this->authActionHandler->handleLogout();
  }

  public function pendaftaran(): void
  {
    $mapelOptions = $this->pendaftaranModel->getMapelOptions();
    $this->authActionHandler->handlePendaftaran($this->pendaftaranModel, $mapelOptions);

    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    $oldInput = $_SESSION['old_input'] ?? [];
    if (!is_array($oldInput)) {
      $oldInput = [];
    }
    unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['old_input']);

    $this->render('auth/pendaftaran', compact('error', 'success', 'mapelOptions', 'oldInput'));
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

    header("Location: index.php?page={$page}");
    exit;
  }

  private function render(string $view, array $data = []): void
  {
    extract($data);
    require __DIR__ . "/../../views/{$view}.php";
  }
}
