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

    <?php
      $totalAnak = (int)($summary['total_anak'] ?? 0);
      $totalJadwal = (int)($summary['total_jadwal'] ?? 0);
      $totalPredikatA = (int)($summary['nilai_summary']['predikat_a'] ?? 0);
      $totalHadir = (int)($summary['absensi_summary']['total_hadir'] ?? 0);
    ?>

    <section class="wali-hero animate-fade-in">
      <div class="wali-hero-content">
        <div class="wali-hero-copy">
          <span class="wali-hero-label"><i class="fas fa-shield-heart"></i> Portal Wali Murid</span>
          <h1>Dashboard Wali Murid</h1>
          <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Wali Murid') ?>. Pantau jadwal, nilai, dan kehadiran anak dalam satu tampilan yang lebih rapi dan mudah dibaca.</p>
        </div>
        <div class="wali-hero-badge">
          <i class="fas fa-users"></i>
          <div>
            <strong><?= $totalAnak ?></strong>
            <span>Anak Terhubung</span>
          </div>
        </div>
      </div>
      <div class="wali-hero-meta">
        <div class="wali-hero-meta-card">
          <span>Jadwal Aktif</span>
          <strong><?= $totalJadwal ?></strong>
        </div>
        <div class="wali-hero-meta-card">
          <span>Predikat A</span>
          <strong><?= $totalPredikatA ?></strong>
        </div>
        <div class="wali-hero-meta-card">
          <span>Total Hadir</span>
          <strong><?= $totalHadir ?></strong>
        </div>
      </div>
    </section>

    <div class="page-header animate-fade-in">
      <h1>Ringkasan Keluarga</h1>
      <p>Semua informasi penting anak-anak Anda dirangkum dalam kartu dan tabel yang konsisten.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-child"></i></div>
        <div class="info">
          <h3><?= $summary['total_anak'] ?? 0 ?></h3>
          <p>Jumlah Anak</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-calendar-days"></i></div>
        <div class="info">
          <h3><?= $summary['total_jadwal'] ?? 0 ?></h3>
          <p>Total Jadwal Aktif</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-star"></i></div>
        <div class="info">
          <h3><?= ($summary['nilai_summary']['predikat_a'] ?? 0) ?></h3>
          <p>Nilai Terbaik (A)</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-4">
        <div class="icon purple"><i class="fas fa-check-circle"></i></div>
        <div class="info">
          <h3><?= ($summary['absensi_summary']['total_hadir'] ?? 0) ?? 0 ?></h3>
          <p>Total Kehadiran</p>
        </div>
      </div>
    </div>

    <?php if (!empty($anak)): ?>
      <section class="wali-panel animate-fade-in delay-5">
        <div class="wali-panel-header">
          <div class="wali-panel-title">
            <div class="wali-panel-icon"><i class="fas fa-children"></i></div>
            <div>
              <h2>Profil Anak</h2>
              <p>Lihat status belajar, kelas sekolah, dan mapel aktif setiap anak.</p>
            </div>
          </div>
          <span class="wali-chip"><i class="fas fa-list-check"></i> <?= count($anak) ?> data</span>
        </div>

        <div class="wali-student-grid">
          <?php foreach ($anak as $a): ?>
            <article class="wali-student-card">
              <div class="wali-student-card-header">
                <div class="flex-gap-md">
                  <div class="wali-student-avatar"><i class="fas fa-user-graduate"></i></div>
                  <div>
                    <h3><?= htmlspecialchars($a['nama']) ?></h3>
                    <p><?= htmlspecialchars($a['kelas_sekolah']) ?></p>
                  </div>
                </div>
                <span class="wali-status-pill status-<?= htmlspecialchars((string)$a['status']) ?>"><?= ucfirst((string)$a['status']) ?></span>
              </div>
              <div class="wali-student-meta">
                <div class="wali-student-meta-item">
                  <span>Mata Pelajaran</span>
                  <strong><?= htmlspecialchars($a['mapel_aktif']) ?></strong>
                </div>
                <div class="wali-student-meta-item">
                  <span>Total Kelas Aktif</span>
                  <strong><?= (int)$a['total_kelas'] ?> kelas</strong>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <div class="wali-table-wrap">
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Nama Anak</th>
                  <th>Kelas Sekolah</th>
                  <th>Mata Pelajaran</th>
                  <th>Total Kelas</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($anak as $a): ?>
                  <tr>
                    <td><?= htmlspecialchars($a['nama']) ?></td>
                    <td><?= htmlspecialchars($a['kelas_sekolah']) ?></td>
                    <td><?= htmlspecialchars($a['mapel_aktif']) ?></td>
                    <td><?= (int)$a['total_kelas'] ?></td>
                    <td><span class="wali-status-pill status-<?= htmlspecialchars((string)$a['status']) ?>"><?= ucfirst((string)$a['status']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    <?php else: ?>
      <section class="wali-panel animate-fade-in delay-5">
        <div class="wali-empty">
          <i class="fas fa-inbox"></i>
          <h3>Belum Ada Data Anak</h3>
          <p>Hubungi admin untuk menghubungkan anak Anda dengan akun wali murid ini.</p>
        </div>
      </section>
    <?php endif; ?>

  </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
