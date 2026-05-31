<?php
// Navbar khusus area dashboard (semua role)
$rawTitle = $pageTitle ?? 'Dashboard';
$cleanTitle = preg_replace('/\\s*-\\s*Bimbel Orion$/i', '', $rawTitle);
$pageHeading = trim($cleanTitle ?: 'Dashboard');
$nama = (string)($_SESSION['nama'] ?? 'User');
$role = (string)($_SESSION['role'] ?? '');
$roleLabel = match ($role) {
  'admin' => 'Administrator',
  'guru' => 'Pengajar',
  'siswa' => 'Siswa',
  'wali_murid' => 'Wali Murid',
  default => 'Pengguna',
};
$initial = strtoupper(substr($nama, 0, 1));
?>

<div class="dashboard-navbar">
  <div class="navbar-left">
    <button class="burger-btn" id="sidebarToggle" aria-label="Tampilkan/sembunyikan sidebar" aria-expanded="false">
      <i class="fas fa-bars"></i>
    </button>
    <div class="navbar-title">
      <span class="navbar-label">Halaman</span>
      <h2 title="<?= htmlspecialchars($pageHeading) ?>"><?= htmlspecialchars($pageHeading) ?></h2>
    </div>
  </div>
  <div class="navbar-right">
    <div class="navbar-user-chip" title="<?= htmlspecialchars($nama) ?>">
      <span class="avatar"><?= htmlspecialchars($initial) ?></span>
      <div class="user-meta">
        <strong><?= htmlspecialchars($nama) ?></strong>
        <small><?= htmlspecialchars($roleLabel) ?></small>
      </div>
    </div>
    <a href="index.php?page=logout" class="navbar-logout-btn">
      <i class="fas fa-right-from-bracket"></i>
      <span>Logout</span>
    </a>
    <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
      <i class="fas fa-moon"></i>
    </button>
  </div>
</div>
