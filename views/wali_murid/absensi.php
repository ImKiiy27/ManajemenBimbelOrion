<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="bg-shapes"><div class="shape shape-1"></div><div class="shape shape-2"></div></div>
<div class="dashboard-container">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
  <main class="main-content">
    <?php require __DIR__ . '/../layouts/dashboard-navbar.php'; ?>

    <div class="page-header animate-fade-in">
      <h1>Rekap Kehadiran</h1>
      <p>Absensi setiap anak ditampilkan dalam panel terpisah agar lebih ringkas dan mudah ditelusuri.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-list"></i></div>
        <div class="info"><h3><?= (int)$metrics['total'] ?></h3><p>Total Absensi</p></div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-check"></i></div>
        <div class="info"><h3><?= (int)$metrics['hadir'] ?></h3><p>Total Hadir</p></div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-user-clock"></i></div>
        <div class="info"><h3><?= (int)$metrics['izin'] + (int)$metrics['sakit'] ?></h3><p>Izin + Sakit</p></div>
      </div>
      <div class="stat-card animate-fade-in delay-4">
        <div class="icon purple"><i class="fas fa-user-times"></i></div>
        <div class="info"><h3><?= (int)$metrics['alpa'] ?></h3><p>Total Alpa</p></div>
      </div>
    </div>

    <?php if (empty($groupedByStudent)): ?>
      <section class="wali-panel animate-fade-in">
        <div class="wali-empty">
          <i class="fas fa-inbox"></i>
          <h3>Belum Ada Data Absensi</h3>
          <p>Tidak ada data absensi anak-anak Anda.</p>
        </div>
      </section>
    <?php else: ?>
      <div class="wali-section-stack">
      <?php foreach ($groupedByStudent as $student): ?>
        <section class="wali-panel animate-fade-in">
          <div class="wali-panel-header">
            <div class="wali-panel-title">
              <div class="wali-panel-icon"><i class="fas fa-user-check"></i></div>
              <div>
                <h3><?= htmlspecialchars($student['siswa_nama']) ?></h3>
                <p><?= count($student['data']) ?> riwayat kehadiran tercatat</p>
              </div>
            </div>
            <span class="wali-chip"><i class="fas fa-clock-rotate-left"></i> Riwayat terbaru</span>
          </div>

          <div class="wali-table-wrap">
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
                    <th>Catatan</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($student['data'] as $item): ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($item['tanggal']) ?></strong></td>
                      <td><?= htmlspecialchars($item['hari']) ?></td>
                      <td><?= htmlspecialchars(substr($item['jam_mulai'], 0, 5)) ?>-<?= htmlspecialchars(substr($item['jam_selesai'], 0, 5)) ?></td>
                      <td><?= htmlspecialchars($item['guru_nama']) ?></td>
                      <td><span class="wali-status-pill status-<?= strtolower((string)$item['status']) ?>"><?= htmlspecialchars($item['status']) ?></span></td>
                      <td><?= htmlspecialchars((string)($item['alasan'] ?: '-')) ?></td>
                      <td><?= htmlspecialchars((string)($item['catatan_guru'] ?: '-')) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
