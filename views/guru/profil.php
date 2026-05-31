<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="bg-shapes"><div class="shape shape-1"></div><div class="shape shape-2"></div></div>
<div class="dashboard-container">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
  <main class="main-content">
    <?php
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
    <div class="page-header animate-fade-in">
      <h1><?= htmlspecialchars($pageTitle ?? 'Halaman') ?></h1>
      <p>Halaman ini sedang dalam pengembangan.</p>
    </div>
    <div class="content-card animate-fade-in">
      <p class="padding-center-lg">
        <i class="fas fa-tools fa-2x mb-3 d-block" class="text-primary-custom"></i>
        Konten halaman ini akan segera tersedia.
      </p>
    </div>
  </main>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
