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
      <h1>Nilai Saya</h1>
      <p>Lihat nilai yang telah diberikan oleh guru Anda.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-star"></i></div>
        <div class="info">
          <h3><?= (int)($totalNilai ?? 0) ?></h3>
          <p>Total Nilai Input</p>
        </div>
      </div>
    </div>

    <!-- Tabel Nilai Saya -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-star"></i> Daftar Nilai Saya</h3>
        <span class="badge bg-primary">Total <?= (int)($totalNilai ?? 0) ?></span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Guru</th>
              <th>Mapel</th>
              <th>Hari/Jam</th>
              <th>Pertemuan</th>
              <th>Tipe</th>
              <th>Nilai</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($nilaiSiswa)): ?>
              <?php foreach ($nilaiSiswa as $row): ?>
                <?php
                  $jamMulai = substr((string)$row['jam_mulai'], 0, 5);
                  $jamSelesai = substr((string)$row['jam_selesai'], 0, 5);
                  $mapel = trim((string)($row['guru_mapel'] ?? '')) !== '' ? $row['guru_mapel'] : 'Privat';
                  $nilaiScore = trim((string)($row['predikat'] ?? '')) !== '' ? $row['predikat'] : '-';
                  $tipeDisplay = [
                    'utama' => '<span class="badge bg-primary">Utama</span>',
                    'susulan' => '<span class="badge bg-warning">Susulan</span>',
                    'remedial' => '<span class="badge bg-info">Remedial</span>'
                  ][$row['tipe_nilai'] ?? 'utama'] ?? $row['tipe_nilai'];
                ?>
                <tr>
                  <td><strong><?= htmlspecialchars($row['guru_nama'] ?? 'Belum diisi') ?></strong></td>
                  <td><?= htmlspecialchars($mapel) ?></td>
                  <td><?= htmlspecialchars($row['hari']) ?> (<?= $jamMulai ?> - <?= $jamSelesai ?>)</td>
                  <td class="text-center-custom"><?= (int)($row['pertemuan_ke'] ?? 1) ?></td>
                  <td><?= $tipeDisplay ?></td>
                  <td class="text-center-custom"><strong><?= htmlspecialchars($nilaiScore) ?></strong></td>
                  <td><?= htmlspecialchars($row['catatan_guru'] ?? '-') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center empty-state">
                  <i class="fas fa-inbox title-xl-white"></i>
                  Belum ada nilai yang diberikan. Nilai akan ditampilkan di sini setelah guru memberikan penilaian.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Info Box -->
    <?php if (!empty($nilaiSiswa)): ?>
      <div class="info-box animate-fade-in">
        <div class="info-content">
          <h3><i class="fas fa-info-circle"></i> Penjelasan Tipe Nilai</h3>
          <ul class="info-list">
            <li><span class="badge bg-primary">Utama</span> - Nilai dari tes utama/formatif</li>
            <li><span class="badge bg-warning">Susulan</span> - Nilai dari tes susulan (karena tidak hadir)</li>
            <li><span class="badge bg-info">Remedial</span> - Nilai dari tes remedial (pengulangan)</li>
          </ul>
        </div>
      </div>
    <?php endif; ?>
  </main>
</div>

<style>
  .table-responsive {
    overflow-x: auto;
  }

  .table-custom {
    width: 100%;
    border-collapse: collapse;
    background-color: var(--bg-primary);
  }

  .table-custom thead {
    background-color: var(--bg-secondary);
    border-bottom: 2px solid var(--border-color);
  }

  .table-custom th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
  }

  .table-custom td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
  }

  .table-custom tbody tr:hover {
    background-color: var(--bg-secondary);
  }

  .text-center {
    text-align: center;
  }

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
  }

  .bg-primary {
    background-color: #0d6efd;
    color: white;
  }

  .bg-warning {
    background-color: #ffc107;
    color: #000;
  }

  .bg-info {
    background-color: #0dcaf0;
    color: #000;
  }

  .info-box {
    background-color: rgba(13, 202, 240, 0.1);
    border: 1px solid #0dcaf0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-top: 2rem;
  }

  .info-box h3 {
    color: var(--text-primary);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .info-box h3 i {
    color: #0dcaf0;
  }

  .info-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .info-list li {
    padding: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--text-primary);
  }

  @media (max-width: 768px) {
    .table-custom {
      font-size: 0.9rem;
    }

    .table-custom th,
    .table-custom td {
      padding: 0.5rem;
    }

    .table-custom th {
      white-space: normal;
    }

    .info-list li {
      flex-direction: column;
      align-items: flex-start;
    }
  }
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

