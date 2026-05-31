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
      $rawTitle = $pageTitle ?? 'Nilai';
      $cleanTitle = preg_replace('/\\s*-\\s*Bimbel Orion$/i', '', $rawTitle);
      $pageHeading = trim($cleanTitle ?: 'Nilai');
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

    <?php
      $totalAnak = count($anak ?? []);
      $totalNilai = 0;
      $totalPredikatA = 0;
      foreach (($nilaiPerAnak ?? []) as $nilaiList) {
        $totalNilai += count($nilaiList);
        foreach ($nilaiList as $n) {
          if (($n['predikat'] ?? '') === 'A') {
            $totalPredikatA++;
          }
        }
      }
    ?>

    <section class="wali-hero animate-fade-in">
      <div class="wali-hero-content">
        <div class="wali-hero-copy">
          <span class="wali-hero-label"><i class="fas fa-chart-line"></i> Nilai Anak</span>
          <h1>Perkembangan Belajar Lebih Jelas</h1>
          <p>Pantau hasil belajar setiap anak berdasarkan guru, mapel, pertemuan, dan catatan evaluasi yang diberikan.</p>
        </div>
        <div class="wali-hero-badge">
          <i class="fas fa-star"></i>
          <div>
            <strong><?= $totalPredikatA ?></strong>
            <span>Predikat A</span>
          </div>
        </div>
      </div>
      <div class="wali-hero-meta">
        <div class="wali-hero-meta-card">
          <span>Anak Dipantau</span>
          <strong><?= $totalAnak ?></strong>
        </div>
        <div class="wali-hero-meta-card">
          <span>Total Nilai</span>
          <strong><?= $totalNilai ?></strong>
        </div>
      </div>
    </section>

    <div class="page-header animate-fade-in">
      <h1>Rekap Nilai Anak</h1>
      <p>Nilai dikelompokkan per anak agar progres akademik lebih mudah dipahami.</p>
    </div>

    <?php if (!empty($anak)): ?>
      <div class="wali-section-stack">
      <?php foreach ($anak as $a): ?>
        <?php $nilaiAnak = $nilaiPerAnak[$a['id']] ?? []; ?>
        <section class="wali-panel animate-fade-in">
          <div class="wali-panel-header">
            <div class="wali-panel-title">
              <div class="wali-panel-icon"><i class="fas fa-user-graduate"></i></div>
              <div>
                <h2><?= htmlspecialchars($a['nama']) ?></h2>
                <p><?= htmlspecialchars($a['kelas_sekolah']) ?> â€¢ <?= count($nilaiAnak) ?> penilaian tercatat</p>
              </div>
            </div>
            <span class="wali-chip"><i class="fas fa-book"></i> <?= htmlspecialchars($a['mapel_aktif'] ?? '-') ?></span>
          </div>

          <?php if (!empty($nilaiAnak)): ?>
            <div class="wali-table-wrap">
              <div class="table-responsive">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Guru</th>
                      <th>Mata Pelajaran</th>
                      <th>Pertemuan</th>
                      <th>Tipe Nilai</th>
                      <th>Predikat</th>
                      <th>Catatan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($nilaiAnak as $n): ?>
                      <tr>
                        <td><?= htmlspecialchars($n['guru_nama']) ?></td>
                        <td><?= htmlspecialchars($n['mata_pelajaran']) ?></td>
                        <td>Ke-<?= htmlspecialchars($n['pertemuan_ke']) ?></td>
                        <td><?= htmlspecialchars($n['tipe_nilai']) ?></td>
                        <td>
                          <span class="wali-status-pill status-<?= strtolower((string)match($n['predikat']) {
                            'A' => 'hadir',
                            'B' => 'izin',
                            'C' => 'sakit',
                            default => 'alpa'
                          }) ?>">
                            <?= htmlspecialchars($n['predikat']) ?>
                          </span>
                        </td>
                        <td><?= htmlspecialchars($n['catatan_guru'] ?? '-') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php else: ?>
            <div class="wali-empty">
              <i class="fas fa-chart-column"></i>
              <h3>Belum Ada Nilai</h3>
              <p>Belum ada nilai yang tercatat untuk anak ini.</p>
            </div>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
      </div>
    <?php else: ?>
      <section class="wali-panel animate-fade-in">
        <div class="wali-empty">
          <i class="fas fa-chart-bar"></i>
          <h3>Belum Ada Data Anak</h3>
          <p>Belum ada anak yang terhubung dengan akun Anda.</p>
        </div>
      </section>
    <?php endif; ?>

  </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
