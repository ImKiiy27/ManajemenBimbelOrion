<?php
// ============================================================
// controllers/admin/AdminUserController.php
// Halaman kelola user admin + aksi user
// ============================================================

require_once __DIR__ . '/../BaseAdminController.php';
require_once __DIR__ . '/../../../models/admin/AdminUserRepository.php';
require_once __DIR__ . '/AdminUserActionHandler.php';

class AdminUserController extends BaseAdminController
{
  private AdminUserRepository $userRepository;
  private AdminUserActionHandler $userActionHandler;

  public function __construct()
  {
    $this->userRepository = new AdminUserRepository();
    $this->userActionHandler = new AdminUserActionHandler($this->userRepository);
  }

  public function user(): void
  {
    $pageTitle = 'Kelola User - Bimbel Orion';
    $activePage = 'admin-user';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
        header('Location: index.php?page=admin-user');
        exit;
      }

      $action = trim($_POST['action'] ?? '');
      match ($action) {
        'create' => $this->userActionHandler->handleCreate(),
        'update' => $this->userActionHandler->handleUpdate(),
        'delete' => $this->userActionHandler->handleDelete(),
        'delete-force' => $this->userActionHandler->handleDeleteForce(),
        'unlock' => $this->userActionHandler->handleUnlock(),
        default  => $_SESSION['flash_error'] = 'Aksi tidak dikenal.',
      };

      header('Location: index.php?page=admin-user');
      exit;
    }

    $users = $this->userRepository->getAllUsersWithDetail();
    $error = $_SESSION['flash_error'] ?? null;
    $success = $_SESSION['flash_success'] ?? null;
    unset($_SESSION['flash_error'], $_SESSION['flash_success']);

    $userRelasi = [];
    foreach ($users as $user) {
      $userRelasi[$user['id']] = $this->userRepository->getRelasiDetail($user['id']);
    }

    $this->render('admin/user', compact('pageTitle', 'activePage', 'users', 'userRelasi', 'error', 'success'));
  }
}
