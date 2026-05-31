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
      <h1>Data Siswa</h1>
      <p>Kelola profil siswa, kelas sekolah, dan mapel yang diikuti siswa langsung dari form edit.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-user-graduate"></i></div>
        <div class="info">
          <h3><?= (int)($totalSiswa ?? 0) ?></h3>
          <p>Total Siswa</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-user-check"></i></div>
        <div class="info">
          <h3><?= (int)($siswaAktif ?? 0) ?></h3>
          <p>Akun Aktif</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-user-lock"></i></div>
        <div class="info">
          <h3><?= (int)($siswaLocked ?? 0) ?></h3>
          <p>Akun Terkunci</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-4">
        <div class="icon purple"><i class="fas fa-book-open"></i></div>
        <div class="info">
          <h3><?= (int)($totalMapelSiswa ?? 0) ?></h3>
          <p>Total Mapel Diikuti</p>
        </div>
      </div>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Tabel Data Siswa</h3>
        <span class="badge bg-primary">Total <?= (int)($totalSiswa ?? 0) ?></span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>ID Siswa</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Kelas Sekolah</th>
              <th>Wali Murid</th>
              <th>Mapel Diikuti</th>
              <th>Status Akun</th>
              <th>Status User</th>
              <th>Tanggal Buat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($siswa)): ?>
              <?php foreach ($siswa as $row): ?>
                <?php
                  $isLocked = (int)($row['is_locked'] ?? 0) === 1;
                  $statusClass = $isLocked ? 'badge-terkunci' : 'badge-aktif';
                  $statusText = $isLocked ? 'Terkunci' : 'Aktif';
                  $kelas = trim((string)($row['kelas'] ?? '')) !== '' ? $row['kelas'] : 'Privat';
                  $namaSiswa = trim((string)($row['nama'] ?? '')) !== '' ? $row['nama'] : 'Belum diisi';
                  $waliNama = trim((string)($row['wali_nama'] ?? '')) !== '' ? $row['wali_nama'] : '-';
                  $tanggal = !empty($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : '-';
                  $mapelDiikuti = trim((string)($row['mapel_diikuti'] ?? '')) !== '' ? $row['mapel_diikuti'] : '-';
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($namaSiswa) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= htmlspecialchars($kelas) ?></td>
                  <td><?= htmlspecialchars($waliNama) ?></td>
                  <td><?= htmlspecialchars($mapelDiikuti) ?></td>
                  <td>
                    <span class="badge-status <?= $statusClass ?>"><?= htmlspecialchars($statusText) ?></span>
                    <div class="small text-muted">Attempt: <?= (int)($row['attempts'] ?? 0) ?></div>
                  </td>
                  <td>
                    <?php if (!empty($row['email'])): ?>
                      <span class="badge bg-success">Ada</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">Belum Ada</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($tanggal) ?></td>
                  <td class="text-nowrap">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary edit-siswa-btn"
                      data-bs-toggle="modal"
                      data-bs-target="#editSiswaModal"
                      data-siswa-id="<?= htmlspecialchars($row['id']) ?>"
                      data-nama="<?= htmlspecialchars($namaSiswa) ?>"
                      data-kelas="<?= htmlspecialchars($kelas) ?>"
                      data-wali-id="<?= htmlspecialchars((string)($row['wali_id'] ?? '')) ?>"
                      data-mapel-ids='<?= htmlspecialchars(json_encode($siswaMapelSelected[$row['id']] ?? []), ENT_QUOTES, 'UTF-8') ?>'
                    >
                      <i class="fas fa-pen"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="10" class="text-center empty-state-md">
                  Belum ada data siswa.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="editSiswaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Data Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="index.php?page=admin-siswa">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
        <input type="hidden" name="action" value="update-siswa">
        <input type="hidden" name="siswa_id" id="editSiswaId">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama Siswa</label>
              <input type="text" class="form-control" name="nama" id="editSiswaNama" maxlength="100" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Kelas Sekolah</label>
              <input type="text" class="form-control" name="kelas" id="editSiswaKelas" maxlength="20" placeholder="Contoh: 10 IPA / Privat">
            </div>
            <div class="col-md-6">
              <label class="form-label">Wali Murid</label>
              <select class="form-select" name="wali_id" id="editSiswaWaliId">
                <option value="">-- Belum dipilih --</option>
                <?php foreach (($waliOptions ?? []) as $wali): ?>
                  <?php
                    $waliLabel = trim((string)($wali['nama'] ?? '')) !== '' ? $wali['nama'] : 'Tanpa Nama';
                    $hubungan = trim((string)($wali['hubungan'] ?? '')) !== '' ? $wali['hubungan'] : 'Wali';
                  ?>
                  <option value="<?= htmlspecialchars($wali['id']) ?>">
                    <?= htmlspecialchars($waliLabel . ' (' . $hubungan . ')') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Mapel yang Diikuti</label>
              <div class="row g-2">
                <?php foreach (($mapelOptions ?? []) as $opt): ?>
                  <div class="col-md-4">
                    <label class="d-flex align-items-center gap-2 border rounded px-3 py-2 h-100">
                      <input
                        type="checkbox"
                        class="form-check-input mt-0 siswa-mapel-check"
                        name="mapel_ids[]"
                        value="<?= htmlspecialchars($opt['id']) ?>"
                      >
                      <span><?= htmlspecialchars($opt['nama']) ?></span>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
              <small class="text-muted">Pilih semua mapel yang memang diikuti siswa ini.</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(() => {
  const editSiswaModal = document.getElementById('editSiswaModal');
  if (!editSiswaModal) return;

  editSiswaModal.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    document.getElementById('editSiswaId').value = button.getAttribute('data-siswa-id') || '';
    document.getElementById('editSiswaNama').value = button.getAttribute('data-nama') || '';
    document.getElementById('editSiswaKelas').value = button.getAttribute('data-kelas') || '';
    document.getElementById('editSiswaWaliId').value = button.getAttribute('data-wali-id') || '';

    const selectedMapelIds = JSON.parse(button.getAttribute('data-mapel-ids') || '[]');
    document.querySelectorAll('.siswa-mapel-check').forEach((checkbox) => {
      checkbox.checked = selectedMapelIds.includes(checkbox.value);
    });
  });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
