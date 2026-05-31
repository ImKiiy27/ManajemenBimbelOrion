<?php
// ============================================================
// controllers/auth/actions/AuthLogoutActionHandler.php
// Fokus: logout session + redirect login
// ============================================================

class AuthLogoutActionHandler {

  public function handle(): void {
    $_SESSION = [];

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
    session_start();

    $_SESSION['flash_success'] = 'Anda berhasil logout.';
    header('Location: index.php?page=login');
    exit;
  }
}
