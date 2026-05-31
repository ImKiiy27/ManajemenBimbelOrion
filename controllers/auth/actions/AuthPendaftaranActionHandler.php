<?php
// ============================================================
// controllers/auth/actions/AuthPendaftaranActionHandler.php
// Fokus: proses POST pendaftaran publik + validasi + redirect
// ============================================================

require_once __DIR__ . '/../../../models/pendaftaran/PendaftaranModel.php';
require_once __DIR__ . '/../../../config/RateLimiter.php';

class AuthPendaftaranActionHandler
{

  private PendaftaranModel $pendaftaranModel;

  public function __construct(PendaftaranModel $pendaftaranModel)
  {
    $this->pendaftaranModel = $pendaftaranModel;
  }

  public function handlePost(array $mapelOptions): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return;
    }

    RateLimiter::check('register');

    if (!verifyCsrfToken($_POST['_csrf'] ?? null)) {
      $_SESSION['flash_error'] = 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.';
      $this->redirectToPendaftaran();
    }

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $kelasSekolah = trim($_POST['kelas_sekolah'] ?? '');
    $mapelIdsRaw = $_POST['mapel_ids'] ?? [];
    $mapelIds = is_array($mapelIdsRaw) ? $mapelIdsRaw : [];
    $_SESSION['old_input'] = $this->buildOldInput($_POST);

    if ($nama === '' || $email === '' || $telepon === '' || $kelasSekolah === '') {
      $_SESSION['flash_error'] = 'Semua field wajib diisi.';
      $this->redirectToPendaftaran();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $_SESSION['flash_error'] = 'Format email tidak valid.';
      $this->redirectToPendaftaran();
    }

    if (!preg_match('/^[0-9+\-\s]{8,15}$/', $telepon)) {
      $_SESSION['flash_error'] = 'Format nomor telepon tidak valid.';
      $this->redirectToPendaftaran();
    }

    if (strlen($kelasSekolah) > 50) {
      $_SESSION['flash_error'] = 'Kolom kelas maksimal 50 karakter.';
      $this->redirectToPendaftaran();
    }

    $mapelIds = array_values(array_unique(array_filter(
      array_map(static fn($value) => trim((string)$value), $mapelIds),
      static fn($value) => $value !== ''
    )));

    if (empty($mapelIds)) {
      $_SESSION['flash_error'] = 'Pilih minimal satu mapel yang ingin diikuti.';
      $this->redirectToPendaftaran();
    }

    $allowedMapelIds = array_map(static fn($row) => (string)($row['id'] ?? ''), $mapelOptions);
    $invalidMapelIds = array_diff($mapelIds, $allowedMapelIds);
    if (!empty($invalidMapelIds)) {
      $_SESSION['flash_error'] = 'Pilihan mapel tidak valid. Silakan pilih mapel yang tersedia.';
      $this->redirectToPendaftaran();
    }

    $sisaDetik = $this->pendaftaranModel->cekCooldownPendaftaran($email);
    if ($sisaDetik > 0) {
      $sisaJam = ceil($sisaDetik / 3600);
      $_SESSION['flash_error'] = "Email ini sudah pernah mendaftar. "
        . "Silakan tunggu sekitar {$sisaJam} jam lagi sebelum mendaftar ulang.";
      $this->redirectToPendaftaran();
    }

    $berhasil = $this->pendaftaranModel->daftar(
      $nama,
      $email,
      $telepon,
      $kelasSekolah,
      $mapelIds
    );

    if ($berhasil) {
      $_SESSION['flash_success'] = 'Pendaftaran berhasil dikirim! '
        . 'Admin akan memverifikasi dan menghubungi Anda.';
      unset($_SESSION['old_input']);
    } else {
      $_SESSION['flash_error'] = 'Email ini sudah terdaftar atau terjadi kesalahan. Jika sudah pernah jadi pengguna, silakan login.';
    }

    $this->redirectToPendaftaran();
  }

  private function redirectToPendaftaran(): void
  {
    header('Location: index.php?page=pendaftaran');
    exit;
  }

  private function buildOldInput(array $input): array
  {
    $old = [];
    $keys = [
      'nama',
      'email',
      'telepon',
      'alamat',
      'jenjang',
      'kelas_sekolah',
      'sekolah_asal',
      'nama_wali',
      'telepon_wali',
    ];

    foreach ($keys as $key) {
      $old[$key] = trim((string)($input[$key] ?? ''));
    }

    $rawMapel = $input['mapel_ids'] ?? [];
    $old['mapel_ids'] = is_array($rawMapel)
      ? array_values(array_map(static fn($v) => trim((string)$v), $rawMapel))
      : [];

    return $old;
  }
}
