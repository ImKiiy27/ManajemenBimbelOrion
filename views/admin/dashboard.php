<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="bg-shapes">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
</div>

<div class="dashboard-container">

  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <?php
      $rawTitle = $pageTitle ?? 'Dashboard';
      $cleanTitle = preg_replace('/\s*-\\s*Bimbel Orion$/i', '', $rawTitle);
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
      <h1>Dashboard Admin</h1>
      <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?>! Kelola semua data bimbingan belajar di sini.</p>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-user-graduate"></i></div>
        <div class="info">
          <h3>-</h3>
          <p>Total Siswa</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-chalkboard-user"></i></div>
        <div class="info">
          <h3>-</h3>
          <p>Total Guru</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-calendar-days"></i></div>
        <div class="info">
          <h3><?= isset($totalJadwal) ? (int)$totalJadwal : '-' ?></h3>
          <p>Total Jadwal</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-4">
        <div class="icon purple"><i class="fas fa-envelope-open-text"></i></div>
        <div class="info">
          <h3><?= isset($totalPendaftar) ? (int)$totalPendaftar : '-' ?></h3>
          <p>Pendaftaran Baru</p>
        </div>
      </div>
    </div>

    <!-- Pendaftaran Terbaru -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-envelope-open-text"></i> Pendaftaran Terbaru</h3>
        <a href="index.php?page=admin-user" class="btn btn-sm btn-login">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Email</th>
              <th>Telepon</th>
              <th>Kelas</th>
              <th>Mapel Diikuti</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($pendaftaran)): ?>
              <?php foreach ($pendaftaran as $row): ?>
                <?php
                  $kelasSekolah = trim((string)($row['kelas_sekolah'] ?? '')) !== '' ? $row['kelas_sekolah'] : 'Privat';
                  $mapelDiikuti = trim((string)($row['mapel_diikuti'] ?? '')) !== '' ? $row['mapel_diikuti'] : '-';
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['nama']) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= htmlspecialchars($row['telepon']) ?></td>
                  <td><?= htmlspecialchars($kelasSekolah) ?></td>
                  <td><?= htmlspecialchars($mapelDiikuti) ?></td>
                  <td><span class="badge-status badge-aktif" class="capitalize-text"><?= htmlspecialchars($row['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center" class="empty-state-md">
                  Belum ada data pendaftaran
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
