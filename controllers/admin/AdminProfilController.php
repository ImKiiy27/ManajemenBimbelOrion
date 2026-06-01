<?php
// ============================================================
// controllers/admin/AdminProfilController.php
// Halaman profil admin
// ============================================================

require_once __DIR__ . '/BaseAdminController.php';
require_once __DIR__ . '/../../models/ProfileRepository.php';

class AdminProfilController extends BaseAdminController
{
  private ProfileRepository $profileRepository;

  public function __construct()
  {
    $this->profileRepository = new ProfileRepository();
  }

  public function profil(): void
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->handlePost();
      return;
    }

    $pageTitle = 'Profil Admin - Bimbel Orion';
    $activePage = 'admin-profil';
    $role = 'admin';
    $userId = (string)($_SESSION['user_id'] ?? '');
    $profile = $this->profileRepository->getProfile($userId, $role);
    $stats = $this->profileRepository->getAdminStats();
    $recentUsers = $this->profileRepository->getRecentUsers();

    $this->render('profile/index', compact(
      'pageTitle',
      'activePage',
      'role',
      'profile',
      'stats',
      'recentUsers'
    ));
  }

  private function handlePost(): void
  {
    if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
      $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
      header('Location: index.php?page=admin-profil');
      exit;
    }

    $action = (string)($_POST['action'] ?? '');
    $userId = (string)($_SESSION['user_id'] ?? '');
    $result = ['status' => 'error', 'message' => 'Aksi tidak dikenal.'];

    if ($action === 'update-profile') {
      $result = $this->profileRepository->updateProfile($userId, 'admin', $_POST);
      if (($result['status'] ?? '') === 'success') {
        $_SESSION['email'] = strtolower(trim((string)($_POST['email'] ?? $_SESSION['email'] ?? '')));
      }
    } elseif ($action === 'change-password') {
      $result = $this->profileRepository->changePassword(
        $userId,
        (string)($_POST['current_password'] ?? ''),
        (string)($_POST['new_password'] ?? ''),
        (string)($_POST['confirm_password'] ?? '')
      );
    }

    SessionHelper::setFlash(
      (($result['status'] ?? 'error') === 'success') ? 'success' : 'error',
      $result['message'] ?? 'Terjadi kesalahan.'
    );
    header('Location: index.php?page=admin-profil');
    exit;
  }
}
