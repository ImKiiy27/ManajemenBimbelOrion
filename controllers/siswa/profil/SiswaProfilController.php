<?php
// ============================================================
// controllers/siswa/SiswaProfilController.php
// Halaman profil siswa
// ============================================================

require_once __DIR__ . '/../BaseSiswaController.php';
require_once __DIR__ . '/../../../models/ProfileRepository.php';
require_once __DIR__ . '/SiswaProfilActionHandler.php';

class SiswaProfilController extends BaseSiswaController
{
  private ProfileRepository $profileRepository;
  private SiswaProfilActionHandler $actionHandler;

  public function __construct()
  {
    $this->profileRepository = new ProfileRepository();
    $this->actionHandler = new SiswaProfilActionHandler($this->profileRepository);
  }

  public function profil(): void
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->handlePost();
      return;
    }

    $pageTitle  = 'Profil Siswa - Bimbel Orion';
    $activePage = 'siswa-profil';
    $role = 'siswa';
    $userId = (string)($_SESSION['user_id'] ?? '');
    $profile = $this->profileRepository->getProfile($userId, $role);
    $stats = $this->profileRepository->getSiswaStats($userId);
    $subjects = $this->profileRepository->getSiswaSubjects($userId);

    $this->render('profile/index', compact(
      'pageTitle',
      'activePage',
      'role',
      'profile',
      'stats',
      'subjects'
    ));
  }

  private function handlePost(): void
  {
    $userId = (string)($_SESSION['user_id'] ?? '');
    $this->actionHandler->handle($userId);
  }
}
