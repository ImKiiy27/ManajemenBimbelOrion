<?php
// ============================================================
// controllers/wali_murid/WaliMuridProfilController.php
// Halaman profil wali murid
// ============================================================

require_once __DIR__ . '/../BaseWaliMuridController.php';
require_once __DIR__ . '/../../../models/ProfileRepository.php';
require_once __DIR__ . '/WaliMuridProfilActionHandler.php';

class WaliMuridProfilController extends BaseWaliMuridController
{
  private ProfileRepository $profileRepository;
  private WaliMuridProfilActionHandler $actionHandler;

  public function __construct()
  {
    $this->profileRepository = new ProfileRepository();
    $this->actionHandler = new WaliMuridProfilActionHandler($this->profileRepository);
  }

  public function profil(): void
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->handlePost();
      return;
    }

    $pageTitle = 'Profil Wali Murid - Bimbel Orion';
    $activePage = 'wali-profil';
    $role = 'wali_murid';
    $userId = (string)($_SESSION['user_id'] ?? '');
    $profile = $this->profileRepository->getProfile($userId, $role);
    $stats = $this->profileRepository->getWaliStats($userId);
    $children = $this->profileRepository->getWaliChildren($userId);

    $this->render('profile/index', compact(
      'pageTitle',
      'activePage',
      'role',
      'profile',
      'stats',
      'children'
    ));
  }

  private function handlePost(): void
  {
    $userId = (string)($_SESSION['user_id'] ?? '');
    $this->actionHandler->handle($userId);
  }
}
