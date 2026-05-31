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
      <h1>Dashboard Guru</h1>
      <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Guru') ?>! Kelola jadwal dan nilai siswa Anda.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-calendar-days"></i></div>
        <div class="info"><h3>-</h3><p>Jadwal Mengajar</p></div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-user-graduate"></i></div>
        <div class="info"><h3>-</h3><p>Total Siswa</p></div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-clipboard-list"></i></div>
        <div class="info"><h3>-</h3><p>Absensi Hari Ini</p></div>
      </div>
    </div>

    <!-- Jadwal Hari Ini -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-calendar-day"></i> Jadwal Mengajar Hari Ini</h3>
        <a href="index.php?page=guru-jadwal" class="btn btn-sm btn-login">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Siswa</th>
              <th>Mata Pelajaran</th>
              <th>Jam Mulai</th>
              <th>Jam Selesai</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="4" class="text-center empty-state-md">
                Tidak ada jadwal hari ini
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

