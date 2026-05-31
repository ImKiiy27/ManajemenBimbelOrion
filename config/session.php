<?php
// ============================================================
// config/session.php
// Konfigurasi keamanan sesi
// ============================================================

// Sesi hangus setelah 30 menit tidak aktif
define('SESSION_TIMEOUT', 30 * 60);

// Sesi hangus total setelah 8 jam meskipun masih aktif
define('SESSION_MAX_LIFETIME', 8 * 60 * 60);

// Panjang token CSRF (bytes sebelum hex)
define('CSRF_BYTES', 32);

function initSession(): void
{
  // Konfigurasi cookie sesi agar lebih aman
  session_set_cookie_params([
    'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 3600,
    'path' => '/',
    'domain' => !empty($_ENV['APP_URL']) ? parse_url($_ENV['APP_URL'], PHP_URL_HOST) : '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
  ]);

  session_start();

  // --- Cek timeout tidak aktif ---
  if (isset($_SESSION['last_activity'])) {
    $idle = time() - $_SESSION['last_activity'];
    if ($idle > SESSION_TIMEOUT) {
      destroySession('timeout');
      return;
    }
  }

  // --- Cek max lifetime ---
  if (isset($_SESSION['created_at'])) {
    $age = time() - $_SESSION['created_at'];
    if ($age > SESSION_MAX_LIFETIME) {
      destroySession('expired');
      return;
    }
  }

  // Update waktu aktivitas terakhir
  $_SESSION['last_activity'] = time();

  // Set waktu buat sesi pertama kali
  if (!isset($_SESSION['created_at'])) {
    $_SESSION['created_at'] = time();
  }

  // Regenerate session ID setiap 10 menit (anti session fixation)
  if (!isset($_SESSION['regenerated_at'])) {
    $_SESSION['regenerated_at'] = time();
  } elseif (time() - $_SESSION['regenerated_at'] > 600) {
    session_regenerate_id(true);
    $_SESSION['regenerated_at'] = time();
  }
}

function destroySession(string $reason = ''): void
{
  // Simpan pesan flash sebelum destroy
  $flash = [];
  if ($reason === 'timeout') {
    $flash['flash_error'] = 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.';
  } elseif ($reason === 'expired') {
    $flash['flash_error'] = 'Sesi Anda telah berakhir. Silakan login kembali.';
  }

  // Hapus semua data sesi
  $_SESSION = [];

  // Hapus cookie sesi
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params['path'],
      $params['domain'],
      $params['secure'],
      $params['httponly']
    );
  }

  session_destroy();

  // Mulai sesi baru hanya untuk flash message
  session_start();
  foreach ($flash as $k => $v) {
    $_SESSION[$k] = $v;
  }

  header('Location: index.php?page=login');
  exit;
}

// ------------------------------------------------------------
// CSRF HELPERS
// ------------------------------------------------------------
function getCsrfToken(): string
{
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_BYTES));
  }
  return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
  if (empty($_SESSION['csrf_token']) || empty($token)) return false;
  return hash_equals($_SESSION['csrf_token'], $token);
}
