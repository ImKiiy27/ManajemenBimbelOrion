<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="bg-shapes">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
</div>

<div class="dashboard-container">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content admin-relasi-page">
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
              <option value="">Pilih Siswa</option>
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
              <option value="">Pilih siswa dulu</option>
            </select>
          </div>

          <div class="form-group">
            <label for="createGuruSelect">Guru</label>
            <select id="createGuruSelect" name="guru_id" class="form-control" required>
              <option value="">Pilih guru sesuai mapel siswa</option>
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
          <option value="">Pilih Siswa</option>
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
          <option value="">Pilih siswa dulu</option>
        </select>
      </div>

      <div class="form-group">
        <label for="editRelasiGuru">Guru</label>
        <select id="editRelasiGuru" name="guru_id" class="form-control" required>
          <option value="">Pilih guru sesuai mapel siswa</option>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
  var siswaMapelMatrix = <?= json_encode($siswaMapelMatrix ?? new stdClass(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var guruOptions = <?= json_encode($guruOptions ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  function getMapelBySiswa(siswaId) {
    return Array.isArray(siswaMapelMatrix[siswaId]) ? siswaMapelMatrix[siswaId] : [];
  }

  function filterGuruByMapel(mapelId) {
    var targetMapelId = String(mapelId || '');
    return guruOptions.filter(function (guru) {
      return String(guru.mapel_id || '') === targetMapelId;
    });
  }

  function fillMapelOptions(selectEl, siswaId, selectedMapelId) {
    selectedMapelId = selectedMapelId || '';
    var mapelList = getMapelBySiswa(siswaId);
    selectEl.innerHTML = '';

    if (!siswaId) {
      selectEl.innerHTML = '<option value="">Pilih siswa dulu</option>';
      return '';
    }

    if (mapelList.length === 0) {
      selectEl.innerHTML = '<option value="">Siswa belum memiliki mapel</option>';
      return '';
    }

    if (!selectedMapelId && mapelList.length > 1) {
      var placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = 'Pilih mapel siswa';
      selectEl.appendChild(placeholder);
    }

    mapelList.forEach(function (mapel) {
      var option = document.createElement('option');
      option.value = mapel.id;
      option.textContent = mapel.nama;
      if (
        (selectedMapelId && String(selectedMapelId) === String(mapel.id)) ||
        (!selectedMapelId && mapelList.length === 1)
      ) {
        option.selected = true;
      }
      selectEl.appendChild(option);
    });

    return selectEl.value || '';
  }

  function fillGuruOptions(selectEl, mapelId, selectedGuruId) {
    selectedGuruId = selectedGuruId || '';
    var filtered = filterGuruByMapel(mapelId);
    selectEl.innerHTML = '';

    var placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = filtered.length > 0
      ? 'Pilih guru sesuai mapel siswa'
      : 'Tidak ada guru yang cocok';
    selectEl.appendChild(placeholder);

    filtered.forEach(function (guru) {
      var option = document.createElement('option');
      option.value = guru.id;
      option.textContent = (guru.nama || '-') + ' - ' + (guru.mata_pelajaran ? guru.mata_pelajaran : '-');
      if (selectedGuruId && String(selectedGuruId) === String(guru.id)) {
        option.selected = true;
      }
      selectEl.appendChild(option);
    });
  }

  function findClosestByClass(el, className) {
    while (el && el !== document) {
      if (el.classList && el.classList.contains(className)) return el;
      el = el.parentNode;
    }
    return null;
  }

  var createSiswaSelect = document.getElementById('createSiswaSelect');
  var createMapelSelect = document.getElementById('createMapelSelect');
  var createGuruSelect = document.getElementById('createGuruSelect');

  if (createSiswaSelect && createMapelSelect && createGuruSelect) {
    createSiswaSelect.addEventListener('change', function () {
      var selectedMapelId = fillMapelOptions(createMapelSelect, this.value);
      fillGuruOptions(createGuruSelect, selectedMapelId);
    });

    createMapelSelect.addEventListener('change', function () {
      fillGuruOptions(createGuruSelect, this.value);
    });
  }

  var editModal = document.getElementById('editRelasiModal');
  var editRelasiId = document.getElementById('editRelasiId');
  var editRelasiSiswa = document.getElementById('editRelasiSiswa');
  var editMapelSelect = document.getElementById('editMapelSelect');
  var editRelasiGuru = document.getElementById('editRelasiGuru');
  var editRelasiStatus = document.getElementById('editRelasiStatus');

  if (editRelasiSiswa && editMapelSelect && editRelasiGuru) {
    editRelasiSiswa.addEventListener('change', function () {
      var selectedMapelId = fillMapelOptions(editMapelSelect, this.value);
      fillGuruOptions(editRelasiGuru, selectedMapelId);
    });

    editMapelSelect.addEventListener('change', function () {
      fillGuruOptions(editRelasiGuru, this.value);
    });
  }

  document.addEventListener('click', function (event) {
    var button = findClosestByClass(event.target, 'js-edit-relasi');
    if (!button) return;
    if (!editModal || !editRelasiId || !editRelasiSiswa || !editRelasiStatus || !editMapelSelect || !editRelasiGuru) {
      return;
    }

    var siswaId = button.getAttribute('data-siswa-id') || '';
    var mapelId = button.getAttribute('data-mapel-id') || '';
    var guruId = button.getAttribute('data-guru-id') || '';

    editRelasiId.value = button.getAttribute('data-id') || '';
    editRelasiSiswa.value = siswaId;
    editRelasiStatus.value = button.getAttribute('data-status') || 'aktif';
    fillMapelOptions(editMapelSelect, siswaId, mapelId);
    fillGuruOptions(editRelasiGuru, mapelId, guruId);
    editModal.classList.add('active');
  });

  document.querySelectorAll('[data-close-relasi]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (editModal) editModal.classList.remove('active');
    });
  });

  if (editModal) {
    editModal.addEventListener('click', function (event) {
      if (event.target === editModal) {
        editModal.classList.remove('active');
      }
    });
  }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>



