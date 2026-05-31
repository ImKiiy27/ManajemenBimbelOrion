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
      $rawTitle = $pageTitle ?? 'Dashboard';
      $cleanTitle = preg_replace('/\\s*-\\s*Bimbel Orion$/i', '', $rawTitle);
      $pageHeading = trim($cleanTitle ?: 'Dashboard');
      $siswaMapelJson = json_encode($siswaMapelMatrix ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      $guruOptionsJson = json_encode($guruOptions ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
      <h1>Atur Pengajar Siswa</h1>
      <p>Tentukan guru untuk mapel yang sudah diambil siswa agar siap masuk ke jadwal belajar.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-diagram-project"></i></div>
        <div class="info">
          <h3><?= (int)($totalRelasi ?? 0) ?></h3>
          <p>Total Relasi</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-link"></i></div>
        <div class="info">
          <h3><?= (int)($relasiAktif ?? 0) ?></h3>
          <p>Relasi Aktif</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-calendar-check"></i></div>
        <div class="info">
          <h3><?= (int)($siswaSiapJadwal ?? 0) ?></h3>
          <p>Siswa Siap Dijadwalkan</p>
        </div>
      </div>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-plus-circle"></i> Tambah Relasi Belajar</h3>
      </div>
      <form method="POST" class="form-container">
        <input type="hidden" name="action" value="create-relasi">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="form-row">
          <div class="form-group">
            <label for="createSiswaSelect">Siswa</label>
            <select id="createSiswaSelect" name="siswa_id" class="form-control" required>
              <option value="">-- Pilih Siswa --</option>
              <?php foreach ($siswaOptions as $siswa): ?>
                <option value="<?= htmlspecialchars($siswa['id']) ?>">
                  <?= htmlspecialchars($siswa['nama']) ?> (<?= htmlspecialchars($siswa['kelas'] ?: 'Privat') ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="createMapelSelect">Mapel Siswa</label>
            <select id="createMapelSelect" name="mapel_id" class="form-control" required>
              <option value="">-- Pilih siswa dulu --</option>
            </select>
          </div>

          <div class="form-group">
            <label for="createGuruSelect">Guru</label>
            <select id="createGuruSelect" name="guru_id" class="form-control" required>
              <option value="">-- Pilih guru sesuai mapel siswa --</option>
            </select>
          </div>

          <div class="form-group">
            <label for="createStatusSelect">Status</label>
            <select id="createStatusSelect" name="status" class="form-control">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
        </div>

        <div class="modal-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan Relasi
          </button>
        </div>
      </form>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Daftar Relasi Belajar</h3>
        <span class="badge bg-primary">Total <?= (int)($totalRelasi ?? 0) ?></span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Siswa</th>
              <th>Kelas Sekolah</th>
              <th>Mapel</th>
              <th>Guru</th>
              <th>Status</th>
              <th>Jadwal</th>
              <th>Dibuat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($relasiList)): ?>
              <?php foreach ($relasiList as $row): ?>
                <?php
                  $status = (string)($row['status'] ?? 'nonaktif');
                  $badgeStatus = $status === 'aktif'
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>';
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['siswa_nama'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($row['siswa_kelas'] ?: 'Privat') ?></td>
                  <td><?= htmlspecialchars($row['mata_pelajaran'] ?: '-') ?></td>
                  <td><?= htmlspecialchars($row['guru_nama'] ?? '-') ?></td>
                  <td><?= $badgeStatus ?></td>
                  <td class="text-center-custom"><?= (int)($row['total_jadwal'] ?? 0) ?></td>
                  <td><?= htmlspecialchars(date('d M Y H:i', strtotime((string)$row['created_at']))) ?></td>
                  <td>
                    <div class="action-buttons">
                      <button
                        type="button"
                        class="btn btn-sm btn-warning js-edit-relasi"
                        data-id="<?= htmlspecialchars($row['id']) ?>"
                        data-siswa-id="<?= htmlspecialchars($row['siswa_id']) ?>"
                        data-mapel-id="<?= htmlspecialchars($row['mapel_id']) ?>"
                        data-guru-id="<?= htmlspecialchars($row['guru_id']) ?>"
                        data-status="<?= htmlspecialchars($row['status']) ?>"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <form method="POST" class="show-inline">
                        <input type="hidden" name="action" value="delete-relasi">
                        <input type="hidden" name="relasi_id" value="<?= htmlspecialchars($row['id']) ?>">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button
                          type="submit"
                          class="btn btn-sm btn-danger"
                          onclick="return confirm('Yakin hapus relasi belajar ini?')"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center empty-state">
                  Belum ada relasi belajar. Tambahkan dulu agar siswa bisa masuk ke jadwal.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<div class="modal-overlay" id="editRelasiModal">
  <div class="custom-modal">
    <div class="modal-header">
      <h3>Edit Relasi Belajar</h3>
      <button type="button" class="close-modal" data-close-relasi>&times;</button>
    </div>
    <form method="POST" class="modal-form">
      <input type="hidden" name="action" value="update-relasi">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <input type="hidden" name="relasi_id" id="editRelasiId">

      <div class="form-group">
        <label for="editRelasiSiswa">Siswa</label>
        <select id="editRelasiSiswa" name="siswa_id" class="form-control" required>
          <option value="">-- Pilih Siswa --</option>
          <?php foreach ($siswaOptions as $siswa): ?>
            <option value="<?= htmlspecialchars($siswa['id']) ?>">
              <?= htmlspecialchars($siswa['nama']) ?> (<?= htmlspecialchars($siswa['kelas'] ?: 'Privat') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="editMapelSelect">Mapel Siswa</label>
        <select id="editMapelSelect" name="mapel_id" class="form-control" required>
          <option value="">-- Pilih siswa dulu --</option>
        </select>
      </div>

      <div class="form-group">
        <label for="editRelasiGuru">Guru</label>
        <select id="editRelasiGuru" name="guru_id" class="form-control" required>
          <option value="">-- Pilih guru sesuai mapel siswa --</option>
        </select>
      </div>

      <div class="form-group">
        <label for="editRelasiStatus">Status</label>
        <select id="editRelasiStatus" name="status" class="form-control">
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <button type="button" class="btn btn-secondary" data-close-relasi>Batal</button>
      </div>
    </form>
  </div>
</div>

<style>
  .form-container,
  .modal-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding: 1.5rem;
    background-color: var(--bg-secondary);
    border-radius: 10px;
  }

  .form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .form-group label {
    font-weight: 600;
    color: var(--text-primary);
  }

  .form-control {
    padding: 0.85rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-primary);
    color: var(--text-primary);
  }

  .table-responsive {
    overflow-x: auto;
  }

  .table-custom {
    width: 100%;
    border-collapse: collapse;
    background: var(--bg-primary);
  }

  .table-custom th,
  .table-custom td {
    padding: 0.95rem 1rem;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
    vertical-align: middle;
  }

  .table-custom th {
    background: var(--bg-secondary);
    text-align: left;
    white-space: nowrap;
  }

  .table-custom tbody tr:hover {
    background: var(--bg-secondary);
  }

  .action-buttons {
    display: flex;
    gap: 0.5rem;
  }

  .btn {
    padding: 0.75rem 1.1rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
  }

  .btn-sm {
    padding: 0.45rem 0.75rem;
  }

  .btn-primary {
    background: #0d6efd;
    color: #fff;
  }

  .btn-warning {
    background: #ffc107;
    color: #000;
  }

  .btn-danger {
    background: #dc3545;
    color: #fff;
  }

  .btn-secondary {
    background: var(--bg-tertiary, #e9ecef);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
  }

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
  }

  .bg-primary { background: #0d6efd; color: #fff; }
  .bg-success { background: #198754; color: #fff; }
  .bg-secondary { background: #6c757d; color: #fff; }

  .empty-state {
    color: var(--text-muted);
    padding: 2rem;
  }

  .modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
  }

  .modal-overlay.active {
    display: flex;
  }

  .custom-modal {
    width: min(560px, 100%);
    background: var(--bg-primary);
    border-radius: 18px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
    overflow: hidden;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.1rem 1.3rem;
    border-bottom: 1px solid var(--border-color);
  }

  .close-modal {
    border: none;
    background: transparent;
    font-size: 1.6rem;
    cursor: pointer;
    color: var(--text-primary);
  }

  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
  }

  @media (max-width: 768px) {
    .form-row {
      grid-template-columns: 1fr;
    }

    .action-buttons {
      flex-direction: column;
    }
  }
</style>

<script>
  const siswaMapelMatrix = <?= $siswaMapelJson ?: '{}' ?>;
  const guruOptions = <?= $guruOptionsJson ?: '[]' ?>;

  function getMapelBySiswa(siswaId) {
    return Array.isArray(siswaMapelMatrix[siswaId]) ? siswaMapelMatrix[siswaId] : [];
  }

  function filterGuruByMapel(mapelId) {
    return guruOptions.filter(guru => guru.mapel_id === mapelId);
  }

  function fillMapelOptions(selectEl, siswaId, selectedMapelId = '') {
    const mapelList = getMapelBySiswa(siswaId);
    selectEl.innerHTML = '';

    if (!siswaId) {
      selectEl.innerHTML = '<option value="">-- Pilih siswa dulu --</option>';
      return;
    }

    if (mapelList.length === 0) {
      selectEl.innerHTML = '<option value="">Siswa belum memiliki mapel</option>';
      return;
    }

    mapelList.forEach((mapel) => {
      const option = document.createElement('option');
      option.value = mapel.id;
      option.textContent = mapel.nama;
      if (selectedMapelId && selectedMapelId === mapel.id) {
        option.selected = true;
      }
      selectEl.appendChild(option);
    });
  }

  function fillGuruOptions(selectEl, mapelId, selectedGuruId = '') {
    const filtered = filterGuruByMapel(mapelId);
    selectEl.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = filtered.length > 0
      ? '-- Pilih guru sesuai mapel siswa --'
      : '-- Tidak ada guru yang cocok --';
    selectEl.appendChild(placeholder);

    filtered.forEach((guru) => {
      const option = document.createElement('option');
      option.value = guru.id;
      option.textContent = `${guru.nama} - ${guru.mata_pelajaran ?? '-'}`;
      if (selectedGuruId && selectedGuruId === guru.id) {
        option.selected = true;
      }
      selectEl.appendChild(option);
    });
  }

  const createSiswaSelect = document.getElementById('createSiswaSelect');
  const createMapelSelect = document.getElementById('createMapelSelect');
  const createGuruSelect = document.getElementById('createGuruSelect');

  createSiswaSelect?.addEventListener('change', function () {
    fillMapelOptions(createMapelSelect, this.value);
    fillGuruOptions(createGuruSelect, '');
  });

  createMapelSelect?.addEventListener('change', function () {
    fillGuruOptions(createGuruSelect, this.value);
  });

  const editModal = document.getElementById('editRelasiModal');
  const editRelasiId = document.getElementById('editRelasiId');
  const editRelasiSiswa = document.getElementById('editRelasiSiswa');
  const editMapelSelect = document.getElementById('editMapelSelect');
  const editRelasiGuru = document.getElementById('editRelasiGuru');
  const editRelasiStatus = document.getElementById('editRelasiStatus');

  editRelasiSiswa?.addEventListener('change', function () {
    fillMapelOptions(editMapelSelect, this.value);
    fillGuruOptions(editRelasiGuru, '');
  });

  editMapelSelect?.addEventListener('change', function () {
    fillGuruOptions(editRelasiGuru, this.value);
  });

  document.querySelectorAll('.js-edit-relasi').forEach((button) => {
    button.addEventListener('click', function () {
      const siswaId = this.dataset.siswaId || '';
      const mapelId = this.dataset.mapelId || '';
      const guruId = this.dataset.guruId || '';

      editRelasiId.value = this.dataset.id || '';
      editRelasiSiswa.value = siswaId;
      editRelasiStatus.value = this.dataset.status || 'aktif';
      fillMapelOptions(editMapelSelect, siswaId, mapelId);
      fillGuruOptions(editRelasiGuru, mapelId, guruId);
      editModal.classList.add('active');
    });
  });

  document.querySelectorAll('[data-close-relasi]').forEach((button) => {
    button.addEventListener('click', function () {
      editModal.classList.remove('active');
    });
  });

  editModal?.addEventListener('click', function (event) {
    if (event.target === editModal) {
      editModal.classList.remove('active');
    }
  });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
