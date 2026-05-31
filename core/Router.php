<?php
// ============================================================
// core/Router.php
// Mengarahkan request ke controller yang tepat
// ============================================================

require_once __DIR__ . '/../controllers/auth/AuthController.php';
require_once __DIR__ . '/../controllers/admin/dashboard/AdminDashboardController.php';
require_once __DIR__ . '/../controllers/admin/siswa/AdminSiswaController.php';
require_once __DIR__ . '/../controllers/admin/guru/AdminGuruController.php';
require_once __DIR__ . '/../controllers/admin/jadwal/AdminJadwalController.php';
require_once __DIR__ . '/../controllers/admin/absensi/AdminAbsensiController.php';
require_once __DIR__ . '/../controllers/admin/nilai/AdminNilaiController.php';
require_once __DIR__ . '/../controllers/admin/user/AdminUserController.php';
require_once __DIR__ . '/../controllers/admin/mapel/AdminMapelController.php';
require_once __DIR__ . '/../controllers/admin/relasi/AdminRelasiController.php';
require_once __DIR__ . '/../controllers/admin/wali_murid/AdminWaliMuridController.php';
require_once __DIR__ . '/../controllers/admin/AdminProfilController.php';
require_once __DIR__ . '/../controllers/guru/dashboard/GuruDashboardController.php';
require_once __DIR__ . '/../controllers/guru/jadwal/GuruJadwalController.php';
require_once __DIR__ . '/../controllers/guru/absensi/GuruAbsensiController.php';
require_once __DIR__ . '/../controllers/guru/nilai/GuruNilaiController.php';
require_once __DIR__ . '/../controllers/guru/profil/GuruProfilController.php';
require_once __DIR__ . '/../controllers/siswa/dashboard/SiswaDashboardController.php';
require_once __DIR__ . '/../controllers/siswa/jadwal/SiswaJadwalController.php';
require_once __DIR__ . '/../controllers/siswa/absensi/SiswaAbsensiController.php';
require_once __DIR__ . '/../controllers/siswa/nilai/SiswaNilaiController.php';
require_once __DIR__ . '/../controllers/siswa/profil/SiswaProfilController.php';
require_once __DIR__ . '/../controllers/wali_murid/dashboard/WaliMuridDashboardController.php';
require_once __DIR__ . '/../controllers/wali_murid/jadwal/WaliMuridJadwalController.php';
require_once __DIR__ . '/../controllers/wali_murid/nilai/WaliMuridNilaiController.php';
require_once __DIR__ . '/../controllers/wali_murid/absensi/WaliMuridAbsensiController.php';
require_once __DIR__ . '/../controllers/wali_murid/profil/WaliMuridProfilController.php';
require_once __DIR__ . '/../helpers/RoleHelper.php';

class Router
{
  // Halaman yang bisa diakses tanpa login
  private array $publicPages = ['index', 'login', 'pendaftaran', 'logout'];

  public function dispatch(string $page): void
  {
    if (!in_array($page, $this->publicPages, true)) {
      $this->requireLogin();
    }

    // Handle AJAX actions (POST dengan ?action parameter)
    $action = $_GET['action'] ?? null;
    if ($action) {
      $this->handleAction($page, $action);
      return;
    }

    match ($page) {
      // --- Public ---
      'index'       => (new AuthController())->index(),
      'login'       => (new AuthController())->login(),
      'logout'      => (new AuthController())->logout(),
      'pendaftaran' => (new AuthController())->pendaftaran(),

      // --- Admin ---
      'admin-dashboard' => $this->requireRole('admin', fn() => (new AdminDashboardController())->dashboard()),
      'admin-siswa'     => $this->requireRole('admin', fn() => (new AdminSiswaController())->siswa()),
      'admin-guru'      => $this->requireRole('admin', fn() => (new AdminGuruController())->guru()),
      'admin-jadwal'    => $this->requireRole('admin', fn() => (new AdminJadwalController())->jadwal()),
      'admin-absensi'   => $this->requireRole('admin', fn() => (new AdminAbsensiController())->absensi()),
      'admin-nilai'     => $this->requireRole('admin', fn() => (new AdminNilaiController())->nilai()),
      'admin-user'      => $this->requireRole('admin', fn() => (new AdminUserController())->user()),
      'admin-mapel'     => $this->requireRole('admin', fn() => (new AdminMapelController())->mapel()),
      'admin-relasi'    => $this->requireRole('admin', fn() => (new AdminRelasiController())->relasi()),
      'admin-wali-murid' => $this->requireRole('admin', fn() => (new AdminWaliMuridController())->waliMurid()),
      'admin-profil'    => $this->requireRole('admin', fn() => (new AdminProfilController())->profil()),

      // --- Guru ---
      'guru-dashboard' => $this->requireRole('guru', fn() => (new GuruDashboardController())->dashboard()),
      'guru-jadwal'    => $this->requireRole('guru', fn() => (new GuruJadwalController())->jadwal()),
      'guru-absensi'   => $this->requireRole('guru', fn() => (new GuruAbsensiController())->absensi()),
      'guru-nilai'     => $this->requireRole('guru', fn() => (new GuruNilaiController())->nilai()),
      'guru-profil'    => $this->requireRole('guru', fn() => (new GuruProfilController())->profil()),

      // --- Siswa ---
      'siswa-dashboard' => $this->requireRole('siswa', fn() => (new SiswaDashboardController())->dashboard()),
      'siswa-jadwal'    => $this->requireRole('siswa', fn() => (new SiswaJadwalController())->jadwal()),
      'siswa-absensi'   => $this->requireRole('siswa', fn() => (new SiswaAbsensiController())->absensi()),
      'siswa-nilai'     => $this->requireRole('siswa', fn() => (new SiswaNilaiController())->nilai()),
      'siswa-profil'    => $this->requireRole('siswa', fn() => (new SiswaProfilController())->profil()),

      // --- Wali Murid ---
      'wali-dashboard' => $this->requireRole('wali_murid', fn() => (new WaliMuridDashboardController())->dashboard()),
      'wali-jadwal'    => $this->requireRole('wali_murid', fn() => (new WaliMuridJadwalController())->jadwal()),
      'wali-nilai'     => $this->requireRole('wali_murid', fn() => (new WaliMuridNilaiController())->nilai()),
      'wali-absensi'   => $this->requireRole('wali_murid', fn() => (new WaliMuridAbsensiController())->absensi()),
      'wali-profil'    => $this->requireRole('wali_murid', fn() => (new WaliMuridProfilController())->profil()),

      default => $this->notFound(),
    };
  }

  private function handleAction(string $page, string $action): void
  {
    $this->requireLogin();

    match ($page) {
      'guru-absensi' => $this->requireRole('guru', fn() => match ($action) {
        'load-siswa' => (new GuruAbsensiController())->loadSiswaInJadwal(),
        'load-data' => (new GuruAbsensiController())->loadAbsensiData(),
        'save' => (new GuruAbsensiController())->saveAbsensi(),
        'riwayat' => (new GuruAbsensiController())->riwayat(),
        default => $this->jsonError('Action tidak ditemukan')
      }),

      'admin-absensi' => $this->requireRole('admin', fn() => match ($action) {
        'get-detail' => (new AdminAbsensiController())->getAbsensiDetail(),
        'save-correction' => (new AdminAbsensiController())->saveCorrection(),
        default => $this->jsonError('Action tidak ditemukan')
      }),

      'admin-wali-murid' => $this->requireRole('admin', fn() => match ($action) {
        'get-siswa' => (new AdminWaliMuridController())->getSiswa(),
        default => $this->jsonError('Action tidak ditemukan')
      }),

      default => $this->jsonError('Page tidak ditemukan')
    };
  }

  private function jsonError(string $message): void
  {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => $message]);
  }

  private function requireLogin(): void
  {
    if (!isset($_SESSION['user_id'])) {
      header('Location: index.php?page=login');
      exit;
    }
  }

  private function requireRole(string $role, callable $callback): void
  {
    $this->requireLogin();
    $sessionRole = normalizeRole((string)($_SESSION['role'] ?? ''));
    $_SESSION['role'] = $sessionRole;

    if ($sessionRole !== $role) {
      $redirect = match ($sessionRole) {
        'admin' => 'admin-dashboard',
        'guru'  => 'guru-dashboard',
        'siswa' => 'siswa-dashboard',
        'wali_murid' => 'wali-dashboard',
        default => 'login',
      };
      header("Location: index.php?page={$redirect}");
      exit;
    }

    $callback();
  }

  private function notFound(): void
  {
    http_response_code(404);
    echo "<h1>404 - Halaman tidak ditemukan</h1>";
    echo "<a href='index.php'>Kembali ke beranda</a>";
  }
}
