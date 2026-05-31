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
      <h1>Input Nilai Siswa</h1>
      <p>Kelola dan input nilai siswa Anda di sini.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-calendar-days"></i></div>
        <div class="info">
          <h3><?= (int)($totalJadwal ?? 0) ?></h3>
          <p>Total Jadwal Aktif</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-star"></i></div>
        <div class="info">
          <h3><?= (int)($totalNilai ?? 0) ?></h3>
          <p>Total Nilai Input</p>
        </div>
      </div>
    </div>

    <!-- Form Input Nilai -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-plus-circle"></i> Form Input Nilai</h3>
      </div>
      <?php if (!empty($jadwalList)): ?>
        <form id="nilaiForm" method="POST" class="form-container">
          <input type="hidden" name="action" value="save-nilai">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <div class="form-row">
            <div class="form-group">
              <label for="jadwalSelect">Pilih Jadwal <span class="text-danger">*</span></label>
              <select id="jadwalSelect" name="jadwal_id" required class="form-control">
                <option value="">-- Pilih Jadwal --</option>
                <?php foreach ($jadwalList as $jadwal): ?>
                  <?php
                    $jamMulai = substr((string)$jadwal['jam_mulai'], 0, 5);
                    $jamSelesai = substr((string)$jadwal['jam_selesai'], 0, 5);
                    $kelas = trim((string)($jadwal['siswa_kelas'] ?? '')) !== '' ? $jadwal['siswa_kelas'] : 'Privat';
                    $mapelDisplay = trim((string)($jadwal['mata_pelajaran'] ?? '')) !== ''
                      ? $jadwal['mata_pelajaran']
                      : (trim((string)($jadwal['guru_mapel'] ?? '')) !== '' ? $jadwal['guru_mapel'] : '-');
                  ?>
                  <option value="<?= htmlspecialchars($jadwal['id']) ?>">
                    <?= htmlspecialchars($jadwal['siswa_nama']) ?> - <?= htmlspecialchars($jadwal['hari']) ?> (<?= $jamMulai ?> - <?= $jamSelesai ?>) - <?= htmlspecialchars($mapelDisplay) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="pertemuanKe">Pertemuan Ke <span class="text-danger">*</span></label>
              <input type="number" id="pertemuanKe" name="pertemuan_ke" min="1" value="1" required class="form-control">
            </div>

            <div class="form-group">
              <label for="tipeNilai">Tipe Nilai <span class="text-danger">*</span></label>
              <select id="tipeNilai" name="tipe_nilai" required class="form-control">
                <option value="utama">Utama</option>
                <option value="susulan">Susulan</option>
                <option value="remedial">Remedial</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="nilaiScore">Nilai/Score <span class="text-danger">*</span></label>
              <input type="number" id="nilaiScore" name="predikat" min="0" max="100" step="0.01" required placeholder="0-100" class="form-control">
            </div>

            <div class="form-group">
              <label for="catatanGuru">Catatan Guru</label>
              <input type="text" id="catatanGuru" name="catatan_guru" placeholder="Catatan (opsional)" class="form-control">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group form-full">
              <label for="catatanDetail">Catatan Detail</label>
              <textarea id="catatanDetail" name="catatan_detail" rows="3" placeholder="Tambahkan catatan detail jika diperlukan..." class="form-control"></textarea>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Simpan Nilai
            </button>
            <button type="reset" class="btn btn-secondary">
              <i class="fas fa-redo"></i> Reset
            </button>
          </div>
        </form>
      <?php else: ?>
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> Anda belum memiliki jadwal aktif. Silakan hubungi admin untuk mengatur jadwal.
        </div>
      <?php endif; ?>
    </div>

    <!-- Tabel Nilai yang Sudah Diinput -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Daftar Nilai Input</h3>
        <span class="badge bg-primary">Total <?= (int)($totalNilai ?? 0) ?></span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Hari</th>
              <th>Jam</th>
              <th>Siswa</th>
              <th>Kelas</th>
              <th>Mapel</th>
              <th>Pertemuan</th>
              <th>Tipe</th>
              <th>Nilai</th>
              <th>Predikat</th>
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
                  <td><?= htmlspecialchars($row['hari']) ?></td>
                  <td><?= htmlspecialchars($jamMulai . ' - ' . $jamSelesai) ?></td>
                  <td><?= htmlspecialchars($row['siswa_nama'] ?? 'Belum diisi') ?></td>
                  <td><?= htmlspecialchars($kelas) ?></td>
                  <td><?= htmlspecialchars($mapel) ?></td>
                  <td class="text-center-custom"><?= (int)($row['pertemuan_ke'] ?? 1) ?></td>
                  <td><?= $tipeDisplay ?></td>
                  <td class="text-center-custom"><strong><?= htmlspecialchars($nilaiScore) ?></strong></td>
                  <td><?= htmlspecialchars($row['catatan_guru'] ?? '-') ?></td>
                  <td>
                    <div class="action-buttons">
                      <a href="#" class="btn btn-sm btn-warning" title="Edit" onclick="editNilai('<?= htmlspecialchars($row['id']) ?>')">
                        <i class="fas fa-edit"></i>
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
                <td colspan="10" class="text-center empty-state-md">
                  Belum ada nilai yang diinput. Silakan isi form di atas untuk menambahkan nilai.
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
  .form-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
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

  .form-group.form-full {
    grid-column: 1 / -1;
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
    transition: all 0.3s ease;
  }

  .form-control:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
  }

  .form-control textarea {
    font-family: inherit;
    resize: vertical;
  }

  .text-danger {
    color: #dc3545;
  }

  .form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
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

  .btn-primary {
    background-color: #0d6efd;
    color: white;
  }

  .btn-primary:hover {
    background-color: #0b5ed7;
    transform: translateY(-2px);
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

  .btn-warning {
    background-color: #ffc107;
    color: #000;
  }

  .btn-warning:hover {
    background-color: #ffb800;
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

  .alert {
    padding: 1rem 1.5rem;
    border-radius: 6px;
    border-left: 4px solid;
    margin: 1rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .alert-info {
    background-color: rgba(13, 202, 240, 0.1);
    border-left-color: #0dcaf0;
    color: #0d6efd;
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

