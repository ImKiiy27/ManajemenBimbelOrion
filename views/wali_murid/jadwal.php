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
      <h1>Daftar Jadwal Anak</h1>
      <p>Setiap anak memiliki panel sendiri agar informasi belajarnya lebih mudah dibaca.</p>
    </div>

    <?php if (!empty($anak)): ?>
      <div class="wali-section-stack">
      <?php foreach ($anak as $a): ?>
        <?php $jadwalAnak = $jadwalPerAnak[$a['id']] ?? []; ?>
        <section class="wali-panel animate-fade-in">
          <div class="wali-panel-header">
            <div class="wali-panel-title">
              <div class="wali-panel-icon"><i class="fas fa-user-graduate"></i></div>
              <div>
                <h2><?= htmlspecialchars($a['nama']) ?></h2>
                <p><?= htmlspecialchars($a['kelas_sekolah']) ?> &bull; <?= count($jadwalAnak) ?> jadwal terdaftar</p>
              </div>
            </div>
            <span class="wali-chip"><img src="public/image/logo-bimbel-orion.jpg" alt="Logo Bimbel Orion" style="width: 16px; height: 16px; object-fit: contain;"> <?= htmlspecialchars($a['mapel_aktif'] ?? '-') ?></span>
          </div>

          <?php if (!empty($jadwalAnak)): ?>
            <div class="wali-table-wrap">
              <div class="table-responsive">
                <table class="table-custom">
                  <thead>
                    <tr>
                      <th>Hari</th>
                      <th>Waktu</th>
                      <th>Guru</th>
                      <th>Mata Pelajaran</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($jadwalAnak as $j): ?>
                      <tr>
                        <td><?= htmlspecialchars($j['hari']) ?></td>
                        <td><?= htmlspecialchars(substr((string)$j['jam_mulai'], 0, 5)) ?> - <?= htmlspecialchars(substr((string)$j['jam_selesai'], 0, 5)) ?></td>
                        <td><?= htmlspecialchars($j['guru_nama']) ?></td>
                        <td><?= htmlspecialchars($j['mata_pelajaran']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php else: ?>
            <div class="wali-empty">
              <i class="fas fa-calendar-xmark"></i>
              <h3>Belum Ada Jadwal</h3>
              <p>Belum ada jadwal pembelajaran untuk anak ini.</p>
            </div>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
      </div>
    <?php else: ?>
      <section class="wali-panel animate-fade-in">
        <div class="wali-empty">
          <i class="fas fa-calendar-times"></i>
          <h3>Belum Ada Data Anak</h3>
          <p>Belum ada anak yang terhubung dengan akun Anda.</p>
        </div>
      </section>
    <?php endif; ?>

  </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
