<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="bg-shapes"><div class="shape shape-1"></div><div class="shape shape-2"></div></div>
<div class="dashboard-container">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
  <main class="main-content">
    <?php require __DIR__ . '/../layouts/dashboard-navbar.php'; ?>
    <div class="page-header animate-fade-in">
      <h1><?= htmlspecialchars($pageTitle ?? 'Halaman') ?></h1>
      <p>Halaman ini sedang dalam pengembangan.</p>
    </div>
    <div class="content-card animate-fade-in">
      <p class="padding-center-lg">
        <i class="fas fa-tools fa-2x mb-3 d-block text-primary-custom"></i>
        Konten halaman ini akan segera tersedia.
      </p>
    </div>
  </main>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>

