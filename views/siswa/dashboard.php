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
      <a href="index.php?page=siswa-jadwal" class="stat-card stat-card-link animate-fade-in delay-1">
        <div class="icon blue"><img src="public/image/logo-bimbel-orion.jpg" alt="Logo Bimbel Orion" style="width: 20px; height: 20px; object-fit: contain;"></div>
        <div class="info"><h3><?= (int)($metrics['total_mapel'] ?? 0) ?></h3><p>Mata Pelajaran</p></div>
      </a>
      <a href="index.php?page=siswa-jadwal" class="stat-card stat-card-link animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-calendar-days"></i></div>
        <div class="info"><h3><?= (int)($metrics['total_jadwal'] ?? 0) ?></h3><p>Total Jadwal</p></div>
      </a>
      <a href="index.php?page=siswa-absensi" class="stat-card stat-card-link animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-clipboard-check"></i></div>
        <div class="info"><h3><?= (int)($metrics['total_hadir'] ?? 0) ?></h3><p>Kehadiran</p></div>
      </a>
    </div>

    <!-- Jadwal Hari Ini -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-calendar-days"></i> Jadwal Hari Ini</h3>
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
            <?php if (!empty($jadwalRingkas)): ?>
              <?php foreach ($jadwalRingkas as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['mata_pelajaran'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($row['guru_nama'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($row['hari'] ?? '-') ?></td>
                  <td>
                    <?= htmlspecialchars(substr((string)($row['jam_mulai'] ?? ''), 0, 5)) ?>
                    -
                    <?= htmlspecialchars(substr((string)($row['jam_selesai'] ?? ''), 0, 5)) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="text-center empty-state-md">
                  Hari ini tidak ada jadwal
                </td>
              </tr>
            <?php endif; ?>
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
            <?php if (!empty($nilaiTerbaru)): ?>
              <?php foreach ($nilaiTerbaru as $row): ?>
                <?php
                  $tipeNilai = [
                    'utama' => 'Utama',
                    'susulan' => 'Susulan',
                    'remedial' => 'Remedial',
                  ][$row['tipe_nilai'] ?? 'utama'] ?? ($row['tipe_nilai'] ?? '-');
                  $skor = trim((string)($row['predikat'] ?? '')) !== '' ? (string)$row['predikat'] : '-';
                  $predikat = is_numeric($skor) ? match (true) {
                    (float)$skor >= 90 => 'A',
                    (float)$skor >= 80 => 'B',
                    (float)$skor >= 70 => 'C',
                    (float)$skor >= 60 => 'D',
                    default => 'E',
                  } : '-';
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['mata_pelajaran'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($tipeNilai) ?></td>
                  <td><?= htmlspecialchars($skor) ?></td>
                  <td><?= htmlspecialchars($predikat) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="text-center empty-state-md">
                  Belum ada nilai
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

