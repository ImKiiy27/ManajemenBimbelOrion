<?php
// ============================================================
// controllers/guru/profil/GuruProfilActionHandler.php
// Handler aksi profil guru
// ============================================================

class GuruProfilActionHandler
{
  private ProfileRepository $profileRepository;

  public function __construct(ProfileRepository $profileRepository)
  {
    $this->profileRepository = $profileRepository;
  }

  public function handle(string $userId): void
  {
    if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
      $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
      header('Location: index.php?page=guru-profil');
      exit;
    }

    $action = (string)($_POST['action'] ?? '');
    $result = ['status' => 'error', 'message' => 'Aksi tidak dikenal.'];

    if ($action === 'update-profile') {
      $result = $this->profileRepository->updateProfile($userId, 'guru', $_POST);
      if (($result['status'] ?? '') === 'success') {
        $_SESSION['email'] = strtolower(trim((string)($_POST['email'] ?? $_SESSION['email'] ?? '')));
        $_SESSION['nama'] = trim((string)($_POST['nama'] ?? $_SESSION['nama'] ?? ''));
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
    header('Location: index.php?page=guru-profil');
    exit;
  }
}
