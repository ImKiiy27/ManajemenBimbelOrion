<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="bg-shapes">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
</div>

<div class="dashboard-container">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content admin-nilai-page">
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
              <option value="">Semua Guru</option>
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
              <option value="">Semua Siswa</option>
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
                  $catatanGuru = trim((string)($row['catatan_guru'] ?? '')) !== '' ? $row['catatan_guru'] : '-';
                  $jadwalText = (string)($row['hari'] ?? '-') . ' (' . $jamMulai . ' - ' . $jamSelesai . ')';
                  $tipeNilai = (string)($row['tipe_nilai'] ?? 'utama');
                  $tipeDisplay = [
                    'utama' => '<span class="badge bg-primary">Utama</span>',
                    'susulan' => '<span class="badge bg-warning">Susulan</span>',
                    'remedial' => '<span class="badge bg-info">Remedial</span>'
                  ][$tipeNilai] ?? $tipeNilai;
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['siswa_nama'] ?? 'Belum diisi') ?></td>
                  <td><?= htmlspecialchars($row['guru_nama'] ?? 'Belum diisi') ?></td>
                  <td><?= htmlspecialchars($jadwalText) ?></td>
                  <td><?= htmlspecialchars($kelas) ?></td>
                  <td><?= htmlspecialchars($mapel) ?></td>
                  <td class="text-center-custom"><?= (int)($row['pertemuan_ke'] ?? 1) ?></td>
                  <td><?= $tipeDisplay ?></td>
                  <td class="text-center-custom"><strong><?= htmlspecialchars($nilaiScore) ?></strong></td>
                  <td><?= htmlspecialchars($catatanGuru) ?></td>
                  <td>
                    <div class="action-buttons">
                      <button
                        type="button"
                        class="btn btn-sm btn-info js-detail-nilai"
                        title="Detail"
                        data-bs-toggle="modal"
                        data-bs-target="#detailNilaiModal"
                        data-nilai-id="<?= htmlspecialchars($row['id']) ?>"
                        data-siswa="<?= htmlspecialchars($row['siswa_nama'] ?? 'Belum diisi') ?>"
                        data-guru="<?= htmlspecialchars($row['guru_nama'] ?? 'Belum diisi') ?>"
                        data-jadwal="<?= htmlspecialchars($jadwalText) ?>"
                        data-kelas="<?= htmlspecialchars($kelas) ?>"
                        data-mapel="<?= htmlspecialchars($mapel) ?>"
                        data-pertemuan="<?= (int)($row['pertemuan_ke'] ?? 1) ?>"
                        data-tipe="<?= htmlspecialchars(ucfirst($tipeNilai)) ?>"
                        data-nilai="<?= htmlspecialchars($nilaiScore) ?>"
                        data-catatan="<?= htmlspecialchars($catatanGuru) ?>"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
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

<div class="modal fade" id="detailNilaiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Nilai Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="border rounded p-3 bg-light admin-detail-panel">
          <h6 class="mb-3">Informasi Nilai</h6>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">ID Nilai</label>
              <div id="detailNilaiId">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Nilai</label>
              <div id="detailNilaiScore">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Siswa</label>
              <div id="detailNilaiSiswa">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Guru</label>
              <div id="detailNilaiGuru">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Jadwal</label>
              <div id="detailNilaiJadwal">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Kelas</label>
              <div id="detailNilaiKelas">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Mapel</label>
              <div id="detailNilaiMapel">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold mb-1">Pertemuan</label>
              <div id="detailNilaiPertemuan">-</div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold mb-1">Tipe</label>
              <div id="detailNilaiTipe">-</div>
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold mb-1">Catatan Guru</label>
              <div id="detailNilaiCatatan" class="text-break">-</div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
(() => {
  const detailNilaiModal = document.getElementById('detailNilaiModal');
  if (!detailNilaiModal) return;

  const setDetailValue = (id, value) => {
    const element = document.getElementById(id);
    if (!element) return;
    const normalized = (value || '').toString().trim();
    element.textContent = normalized !== '' ? normalized : '-';
  };

  detailNilaiModal.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    if (!button) return;

    setDetailValue('detailNilaiId', button.getAttribute('data-nilai-id'));
    setDetailValue('detailNilaiScore', button.getAttribute('data-nilai'));
    setDetailValue('detailNilaiSiswa', button.getAttribute('data-siswa'));
    setDetailValue('detailNilaiGuru', button.getAttribute('data-guru'));
    setDetailValue('detailNilaiJadwal', button.getAttribute('data-jadwal'));
    setDetailValue('detailNilaiKelas', button.getAttribute('data-kelas'));
    setDetailValue('detailNilaiMapel', button.getAttribute('data-mapel'));
    setDetailValue('detailNilaiPertemuan', button.getAttribute('data-pertemuan'));
    setDetailValue('detailNilaiTipe', button.getAttribute('data-tipe'));
    setDetailValue('detailNilaiCatatan', button.getAttribute('data-catatan'));
  });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


