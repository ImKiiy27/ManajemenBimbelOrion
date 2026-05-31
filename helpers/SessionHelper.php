<?php
// ============================================================
// core/SessionHelper.php
// Session & CSRF token helper
// ============================================================

class SessionHelper
{
  /**
   * Generate atau get existing CSRF token
   */
  public static function getCsrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
  }

  /**
   * Validate CSRF token
   */
  public static function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
  }

  /**
   * Regenerate CSRF token (after sensitive operations)
   */
  public static function regenerateCsrfToken(): string {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
  }

  /**
   * Set flash message
   */
  public static function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
  }

  /**
   * Get and clear flash message
   */
  public static function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
  }

  /**
   * Check if user is authenticated
   */
  public static function isAuthenticated(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
  }

  /**
   * Get current user role
   */
  public static function getUserRole(): ?string {
    return $_SESSION['role'] ?? null;
  }

  /**
   * Get current user ID
   */
  public static function getUserId(): ?string {
    return $_SESSION['user_id'] ?? null;
  }
}