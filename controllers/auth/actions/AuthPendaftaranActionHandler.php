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

    $honeypot = trim((string)($_POST['website'] ?? ''));
    if ($honeypot !== '') {
      // Silent reject for bot traffic
      $_SESSION['flash_error'] = 'Permintaan tidak valid.';
      $this->redirectToPendaftaran();
    }

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $jenjang = trim($_POST['jenjang'] ?? '');
    $kelasSekolah = trim($_POST['kelas_sekolah'] ?? '');
    $asalSekolah = trim($_POST['asal_sekolah'] ?? '');
    $namaWali = trim($_POST['nama_wali'] ?? '');
    $noHpWali = trim($_POST['no_hp_wali'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');
    $mapelIdsRaw = $_POST['mapel_ids'] ?? [];
    $mapelIds = is_array($mapelIdsRaw) ? $mapelIdsRaw : [];
    $_SESSION['old_input'] = $this->buildOldInput($_POST);

    if (
      $nama === '' || $email === '' || $telepon === ''
      || $alamat === '' || $jenjang === '' || $kelasSekolah === '' || $asalSekolah === ''
      || $namaWali === '' || $noHpWali === ''
    ) {
      $_SESSION['flash_error'] = 'Semua field wajib diisi.';
      $this->redirectToPendaftaran();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $_SESSION['flash_error'] = 'Format email tidak valid.';
      $this->redirectToPendaftaran();
    }

    if (!$this->isValidPhone($telepon)) {
      $_SESSION['flash_error'] = 'Format nomor telepon tidak valid.';
      $this->redirectToPendaftaran();
    }

    if (!$this->isValidPhone($noHpWali)) {
      $_SESSION['flash_error'] = 'Format nomor HP wali tidak valid.';
      $this->redirectToPendaftaran();
    }

    if (strlen($nama) > 150 || strlen($namaWali) > 150) {
      $_SESSION['flash_error'] = 'Nama maksimal 150 karakter.';
      $this->redirectToPendaftaran();
    }

    if (strlen($alamat) > 2000) {
      $_SESSION['flash_error'] = 'Alamat terlalu panjang.';
      $this->redirectToPendaftaran();
    }

    if (strlen($kelasSekolah) > 50) {
      $_SESSION['flash_error'] = 'Kolom kelas maksimal 50 karakter.';
      $this->redirectToPendaftaran();
    }

    if (!in_array($jenjang, ['SD', 'SMP', 'SMA', 'SMK', 'Lainnya'], true)) {
      $_SESSION['flash_error'] = 'Pilihan jenjang tidak valid.';
      $this->redirectToPendaftaran();
    }

    if (strlen($asalSekolah) > 150) {
      $_SESSION['flash_error'] = 'Asal sekolah maksimal 150 karakter.';
      $this->redirectToPendaftaran();
    }

    if (strlen($catatan) > 2000) {
      $_SESSION['flash_error'] = 'Catatan terlalu panjang.';
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

    if ($this->pendaftaranModel->hasActivePendaftaranByEmailOrTelepon($email, $telepon, $noHpWali)) {
      $_SESSION['flash_error'] = 'Email atau nomor telepon sudah memiliki pendaftaran aktif. Mohon tunggu proses verifikasi admin.';
      $this->redirectToPendaftaran();
    }

    $sisaDetik = $this->pendaftaranModel->cekCooldownPendaftaran($email, $telepon, $noHpWali);
    if ($sisaDetik > 0) {
      $sisaJam = ceil($sisaDetik / 3600);
      $_SESSION['flash_error'] = "Data email/nomor telepon ini sudah pernah mendaftar. "
        . "Silakan tunggu sekitar {$sisaJam} jam lagi sebelum mendaftar ulang.";
      $this->redirectToPendaftaran();
    }

    $berhasil = $this->pendaftaranModel->daftar(
      $nama,
      $email,
      $telepon,
      $alamat,
      $jenjang,
      $kelasSekolah,
      $asalSekolah,
      $namaWali,
      $noHpWali,
      $catatan,
      $mapelIds
    );

    if ($berhasil) {
      $_SESSION['flash_success'] = 'Pendaftaran berhasil dikirim! '
        . 'Admin akan memverifikasi dan menghubungi Anda.';
      unset($_SESSION['old_input']);
    } else {
      $_SESSION['flash_error'] = 'Email/nomor telepon sudah terdaftar atau terjadi kesalahan. Jika sudah punya akun, silakan login.';
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
      'asal_sekolah',
      'nama_wali',
      'no_hp_wali',
      'catatan',
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

  private function isValidPhone(string $phone): bool
  {
    return preg_match('/^[0-9+\-\s]{8,30}$/', $phone) === 1;
  }
}
