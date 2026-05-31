<?php
// ============================================================
// controllers/BaseController.php
// Base render helper dengan auto CSRF + user context + flash
// ============================================================

require_once __DIR__ . '/../helpers/SessionHelper.php';

abstract class BaseController
{
  protected function render(string $view, array $data = []): void
  {
    $data['csrf_token'] = $data['csrf_token'] ?? SessionHelper::getCsrfToken();
    $data['current_user_id'] = $data['current_user_id'] ?? SessionHelper::getUserId();
    $data['current_user_role'] = $data['current_user_role'] ?? SessionHelper::getUserRole();
    $data['current_user_name'] = $data['current_user_name'] ?? ($_SESSION['nama'] ?? null);
    $data['flash'] = $data['flash'] ?? SessionHelper::getFlash();

    extract($data);

    $viewRelative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $view) . '.php';
    $viewPath = __DIR__ . '/../views/' . $viewRelative;

    clearstatcache(true, $viewPath);
    if (!is_file($viewPath)) {
      throw new RuntimeException('View tidak ditemukan: ' . $viewPath);
    }

    require $viewPath;
  }
}
