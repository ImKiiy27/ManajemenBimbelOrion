<?php
// ============================================================
// controllers/auth/AuthActionHandler.php
// Orkestrasi action handler auth
// ============================================================

require_once __DIR__ . '/actions/AuthLoginActionHandler.php';
require_once __DIR__ . '/actions/AuthPendaftaranActionHandler.php';
require_once __DIR__ . '/actions/AuthLogoutActionHandler.php';

class AuthActionHandler
{
  public function handleLogin(AuthModel $authModel): void
  {
    $loginActionHandler = new AuthLoginActionHandler($authModel);
    $loginActionHandler->handlePost();
  }

  public function handlePendaftaran(PendaftaranModel $pendaftaranModel, array $mapelOptions): void
  {
    $pendaftaranActionHandler = new AuthPendaftaranActionHandler($pendaftaranModel);
    $pendaftaranActionHandler->handlePost($mapelOptions);
  }

  public function handleLogout(): void
  {
    $logoutActionHandler = new AuthLogoutActionHandler();
    $logoutActionHandler->handle();
  }
}
