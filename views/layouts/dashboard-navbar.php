<?php
// Navbar khusus area dashboard (semua role)
$rawTitle = $pageTitle ?? 'Dashboard';
$cleanTitle = preg_replace('/\\s*-\\s*Bimbel Orion$/i', '', $rawTitle);
$pageHeading = trim($cleanTitle ?: 'Dashboard');
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
    <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
      <i class="fas fa-moon"></i>
    </button>
  </div>
</div>
