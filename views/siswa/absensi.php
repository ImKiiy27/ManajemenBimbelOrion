<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="bg-shapes"><div class="shape shape-1"></div><div class="shape shape-2"></div></div>
<div class="dashboard-container">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
  <main class="main-content">
    <?php
      $rawTitle = $pageTitle ?? 'Dashboard';
      $cleanTitle = preg_replace('/\s*-\s*Bimbel Orion$/i', '', $rawTitle);
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
      <p>Lihat riwayat absensi Anda di semua kelas.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-list"></i></div>
        <div class="info"><h3><?= (int)$metrics['total'] ?></h3><p>Total Absensi</p></div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-check"></i></div>
        <div class="info"><h3><?= (int)$metrics['hadir'] ?></h3><p>Hadir</p></div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-user-clock"></i></div>
        <div class="info"><h3><?= (int)$metrics['izin'] + (int)$metrics['sakit'] ?></h3><p>Izin + Sakit</p></div>
      </div>
      <div class="stat-card animate-fade-in delay-4">
        <div class="icon purple"><i class="fas fa-user-times"></i></div>
        <div class="info"><h3><?= (int)$metrics['alpa'] ?></h3><p>Alpa</p></div>
      </div>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-filter"></i> Filter Tanggal</h3>
      </div>
      <form method="GET" class="filter-form-inline">
        <input type="hidden" name="page" value="siswa-absensi">
        <div class="filter-group-inline">
          <label for="tgl_dari">Dari:</label>
          <input type="date" id="tgl_dari" name="tgl_dari" value="<?= htmlspecialchars($tanggalStart) ?>">
        </div>
        <div class="filter-group-inline">
          <label for="tgl_sampai">Sampai:</label>
          <input type="date" id="tgl_sampai" name="tgl_sampai" value="<?= htmlspecialchars($tanggalEnd) ?>">
        </div>
        <button type="submit" class="btn btn-login"><i class="fas fa-filter"></i> Filter</button>
      </form>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Riwayat Absensi</h3>
      </div>
      <?php if (empty($absensiList)): ?>
        <div class="padding-center-lg">
          <i class="fas fa-inbox fa-2x mb-3 d-block" class="text-primary-custom"></i>
          Tidak ada data absensi
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table-custom">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Jam</th>
                <th>Guru</th>
                <th>Status</th>
                <th>Alasan</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($absensiList as $item): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($item['tanggal']) ?></strong></td>
                  <td><?= htmlspecialchars($item['hari']) ?></td>
                  <td><?= htmlspecialchars(substr($item['jam_mulai'], 0, 5)) ?>-<?= htmlspecialchars(substr($item['jam_selesai'], 0, 5)) ?></td>
                  <td><?= htmlspecialchars($item['guru_nama']) ?></td>
                  <td><span class="status-badge status-<?= strtolower((string)$item['status']) ?>"><?= htmlspecialchars($item['status']) ?></span></td>
                  <td><?= htmlspecialchars((string)($item['alasan'] ?: '-')) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<style>
.filter-form-inline { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
.filter-group-inline { display:flex; flex-direction:column; gap:6px; }
.filter-group-inline label { font-weight:500; color:var(--text-color); }
.filter-group-inline input { padding:10px 12px; border:1px solid var(--border-color); border-radius:10px; background:var(--card-bg); color:var(--text-color); }
.status-badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:.8rem; font-weight:600; }
.status-hadir { background:#d4edda; color:#155724; }
.status-izin { background:#cfe2ff; color:#084298; }
.status-sakit { background:#fff3cd; color:#664d03; }
.status-alpa { background:#f8d7da; color:#721c24; }
@media (max-width: 768px) {
  .filter-form-inline { flex-direction:column; align-items:stretch; }
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
