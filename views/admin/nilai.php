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

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger alert-custom mt-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success alert-custom mt-3" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <div class="page-header animate-fade-in">
      <h1>Data Nilai Siswa</h1>
      <p>Lihat dan kelola semua nilai yang telah diinput oleh guru.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-star"></i></div>
        <div class="info">
          <h3><?= (int)($totalNilai ?? 0) ?></h3>
          <p>Total Nilai</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-chalkboard-user"></i></div>
        <div class="info">
          <h3><?= (int)($totalGuru ?? 0) ?></h3>
          <p>Total Guru</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-user-graduate"></i></div>
        <div class="info">
          <h3><?= (int)($totalSiswa ?? 0) ?></h3>
          <p>Total Siswa</p>
        </div>
      </div>
    </div>

    <!-- Filter Section -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-filter"></i> Filter Data</h3>
      </div>
      <form method="GET" class="filter-form">
        <input type="hidden" name="page" value="admin-nilai">
        <div class="form-row">
          <div class="form-group">
            <label for="guruFilter">Filter Guru</label>
            <select id="guruFilter" name="guru_id" class="form-control" onchange="this.form.submit()">
              <option value="">-- Semua Guru --</option>
              <?php foreach ($guruOptions as $guru): ?>
                <?php $guruId = (string)($guru['id'] ?? $guru['user_id'] ?? ''); ?>
                <option value="<?= htmlspecialchars($guruId) ?>" <?= ($filterGuru === $guruId ? 'selected' : '') ?>>
                  <?= htmlspecialchars($guru['nama']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="siswaFilter">Filter Siswa</label>
            <select id="siswaFilter" name="siswa_id" class="form-control" onchange="this.form.submit()">
              <option value="">-- Semua Siswa --</option>
              <?php foreach ($siswaOptions as $siswa): ?>
                <?php $siswaId = (string)($siswa['id'] ?? $siswa['user_id'] ?? ''); ?>
                <option value="<?= htmlspecialchars($siswaId) ?>" <?= ($filterSiswa === $siswaId ? 'selected' : '') ?>>
                  <?= htmlspecialchars($siswa['nama']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>&nbsp;</label>
            <a href="index.php?page=admin-nilai" class="btn btn-secondary">
              <i class="fas fa-redo"></i> Reset Filter
            </a>
          </div>
        </div>
      </form>
    </div>

    <!-- Tabel Nilai -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Tabel Data Nilai</h3>
        <span class="badge bg-primary">Total <?= (int)($totalNilai ?? 0) ?></span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Siswa</th>
              <th>Guru</th>
              <th>Hari/Jam</th>
              <th>Kelas</th>
              <th>Mapel</th>
              <th>Pertemuan</th>
              <th>Tipe</th>
              <th>Nilai</th>
              <th>Catatan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($nilaiList)): ?>
              <?php foreach ($nilaiList as $row): ?>
                <?php
                  $jamMulai = substr((string)$row['jam_mulai'], 0, 5);
                  $jamSelesai = substr((string)$row['jam_selesai'], 0, 5);
                  $kelas = trim((string)($row['siswa_kelas'] ?? '')) !== '' ? $row['siswa_kelas'] : 'Privat';
                  $mapel = trim((string)($row['mata_pelajaran'] ?? '')) !== ''
                    ? $row['mata_pelajaran']
                    : (trim((string)($row['guru_mapel'] ?? '')) !== '' ? $row['guru_mapel'] : '-');
                  $nilaiScore = trim((string)($row['predikat'] ?? '')) !== '' ? $row['predikat'] : '-';
                  $tipeDisplay = [
                    'utama' => '<span class="badge bg-primary">Utama</span>',
                    'susulan' => '<span class="badge bg-warning">Susulan</span>',
                    'remedial' => '<span class="badge bg-info">Remedial</span>'
                  ][$row['tipe_nilai'] ?? 'utama'] ?? $row['tipe_nilai'];
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['siswa_nama'] ?? 'Belum diisi') ?></td>
                  <td><?= htmlspecialchars($row['guru_nama'] ?? 'Belum diisi') ?></td>
                  <td><?= htmlspecialchars($row['hari']) ?> (<?= $jamMulai ?> - <?= $jamSelesai ?>)</td>
                  <td><?= htmlspecialchars($kelas) ?></td>
                  <td><?= htmlspecialchars($mapel) ?></td>
                  <td class="text-center-custom"><?= (int)($row['pertemuan_ke'] ?? 1) ?></td>
                  <td><?= $tipeDisplay ?></td>
                  <td class="text-center-custom"><strong><?= htmlspecialchars($nilaiScore) ?></strong></td>
                  <td><?= htmlspecialchars($row['catatan_guru'] ?? '-') ?></td>
                  <td>
                    <div class="action-buttons">
                      <a href="#" class="btn btn-sm btn-info" title="Detail" onclick="viewNilaiDetail('<?= htmlspecialchars($row['id']) ?>')">
                        <i class="fas fa-eye"></i>
                      </a>
                      <form method="POST" class="show-inline">
                        <input type="hidden" name="action" value="delete-nilai">
                        <input type="hidden" name="nilai_id" value="<?= htmlspecialchars($row['id']) ?>">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Yakin hapus nilai ini?')">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="10" class="text-center empty-state">
                  <i class="fas fa-inbox title-xl-white"></i>
                  <?php if ($filterGuru !== '' || $filterSiswa !== ''): ?>
                    Tidak ada nilai sesuai filter yang dipilih.
                  <?php else: ?>
                    Belum ada nilai yang diinput.
                  <?php endif; ?>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<style>
  .filter-form {
    padding: 1.5rem;
    background-color: var(--bg-secondary);
    border-radius: 8px;
  }

  .form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-group label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-primary);
    font-size: 0.95rem;
  }

  .form-control {
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background-color: var(--bg-primary);
    color: var(--text-primary);
    font-size: 0.95rem;
    cursor: pointer;
  }

  .form-control:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
  }

  .btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    text-decoration: none;
  }

  .btn-secondary {
    background-color: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
  }

  .btn-secondary:hover {
    background-color: var(--bg-tertiary);
  }

  .btn-sm {
    padding: 0.35rem 0.75rem;
    font-size: 0.85rem;
  }

  .btn-info {
    background-color: #0dcaf0;
    color: #000;
  }

  .btn-info:hover {
    background-color: #0ba5d9;
  }

  .btn-danger {
    background-color: #dc3545;
    color: white;
  }

  .btn-danger:hover {
    background-color: #c82333;
  }

  .action-buttons {
    display: flex;
    gap: 0.5rem;
  }

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

  @media (max-width: 768px) {
    .form-row {
      grid-template-columns: 1fr;
    }

    .table-custom {
      font-size: 0.9rem;
    }

    .table-custom th,
    .table-custom td {
      padding: 0.5rem;
    }

    .action-buttons {
      flex-direction: column;
    }
  }
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

