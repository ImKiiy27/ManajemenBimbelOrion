<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="bg-shapes">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
</div>

<div class="dashboard-container">

  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <?php require __DIR__ . '/../layouts/dashboard-navbar.php'; ?>

    <div class="page-header animate-fade-in">
      <h1>Dashboard Siswa</h1>
      <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Siswa') ?>! Pantau jadwal dan perkembangan belajar Anda.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-book-open"></i></div>
        <div class="info"><h3>-</h3><p>Mata Pelajaran</p></div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-calendar-days"></i></div>
        <div class="info"><h3>-</h3><p>Total Jadwal</p></div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-clipboard-check"></i></div>
        <div class="info"><h3>-</h3><p>Kehadiran</p></div>
      </div>
    </div>

    <!-- Jadwal Les -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-calendar-days"></i> Jadwal Les Saya</h3>
        <a href="index.php?page=siswa-jadwal" class="btn btn-sm btn-login">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Mata Pelajaran</th>
              <th>Guru</th>
              <th>Hari</th>
              <th>Jam</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="4" class="text-center empty-state-md">
                Belum ada jadwal
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Nilai Terbaru -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-chart-bar"></i> Nilai Terbaru</h3>
        <a href="index.php?page=siswa-nilai" class="btn btn-sm btn-login">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Mata Pelajaran</th>
              <th>Tipe</th>
              <th>Skor</th>
              <th>Predikat</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="4" class="text-center empty-state-md">
                Belum ada nilai
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

