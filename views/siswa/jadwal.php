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
      <h1>Jadwal Les Saya</h1>
      <p>Jadwal les Anda berdasarkan penjadwalan dari admin.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-calendar-days"></i></div>
        <div class="info">
          <h3><?= (int)($totalJadwal ?? 0) ?></h3>
          <p>Total Jadwal</p>
        </div>
      </div>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Tabel Jadwal Les</h3>
        <span class="badge bg-primary">Total <?= (int)($totalJadwal ?? 0) ?></span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Hari</th>
              <th>Jam</th>
              <th>Guru</th>
              <th>Mapel Siswa</th>
              <th>Mapel Guru</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($jadwal)): ?>
              <?php foreach ($jadwal as $row): ?>
                <?php
                  $jamMulai = substr((string)$row['jam_mulai'], 0, 5);
                  $jamSelesai = substr((string)$row['jam_selesai'], 0, 5);
                  $mapelSiswa = trim((string)($row['mata_pelajaran'] ?? '')) !== '' ? $row['mata_pelajaran'] : '-';
                  $mapelGuru = trim((string)($row['guru_mapel'] ?? '')) !== '' ? $row['guru_mapel'] : 'Privat';
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['hari']) ?></td>
                  <td><?= htmlspecialchars($jamMulai . ' - ' . $jamSelesai) ?></td>
                  <td><?= htmlspecialchars($row['guru_nama'] ?? 'Belum diisi') ?></td>
                  <td><?= htmlspecialchars($mapelSiswa) ?></td>
                  <td><?= htmlspecialchars($mapelGuru) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center empty-state-md">
                  Belum ada jadwal les.
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

