<?php
// ============================================================
// core/RoleHelper.php
// Helper normalisasi role agar konsisten di seluruh aplikasi
// ============================================================

if (!function_exists('normalizeRole')) {
  function normalizeRole(string $role): string
  {
    $role = strtolower(trim($role));
    if ($role === '') {
      return '';
    }

    $role = str_replace(['-', ' '], '_', $role);
    $role = preg_replace('/_+/', '_', $role);
    $role = trim((string)$role, '_');

    return match ($role) {
      'wali', 'walimurid', 'wali_murid' => 'wali_murid',
      default => $role,
    };
  }
}

if (!function_exists('roleLabel')) {
  function roleLabel(string $role): string
  {
    $role = normalizeRole($role);

    return match ($role) {
      'admin' => 'Admin',
      'guru' => 'Guru',
      'siswa' => 'Siswa',
      'wali_murid' => 'Wali Murid',
      '' => '-',
      default => ucwords(str_replace('_', ' ', $role)),
    };
  }
}
