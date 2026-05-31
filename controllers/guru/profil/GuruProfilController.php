<?php
// ============================================================
// controllers/guru/GuruProfilController.php
// Halaman profil guru
// ============================================================

require_once __DIR__ . '/../BaseGuruController.php';
require_once __DIR__ . '/../../../models/ProfileRepository.php';
require_once __DIR__ . '/GuruProfilActionHandler.php';

class GuruProfilController extends BaseGuruController
{
  private ProfileRepository $profileRepository;
  private GuruProfilActionHandler $actionHandler;

  public function __construct()
  {
    $this->profileRepository = new ProfileRepository();
    $this->actionHandler = new GuruProfilActionHandler($this->profileRepository);
  }

  public function profil(): void
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->handlePost();
      return;
    }

    $pageTitle  = 'Profil Guru - Bimbel Orion';
    $activePage = 'guru-profil';
    $role = 'guru';
    $userId = (string)($_SESSION['user_id'] ?? '');
    $profile = $this->profileRepository->getProfile($userId, $role);
    $stats = $this->profileRepository->getGuruStats($userId);
    $schedule = $this->profileRepository->getGuruSchedule($userId);

    $this->render('profile/index', compact(
      'pageTitle',
      'activePage',
      'role',
      'profile',
      'stats',
      'schedule'
    ));
  }

  private function handlePost(): void
  {
    $userId = (string)($_SESSION['user_id'] ?? '');
    $this->actionHandler->handle($userId);
  }
}
