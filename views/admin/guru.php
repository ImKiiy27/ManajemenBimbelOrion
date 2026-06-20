<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="bg-shapes">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
</div>

<div class="dashboard-container">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content admin-guru-page">
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
      <h1>Data Guru</h1>
      <p>Kelola profil guru dan tetapkan satu mapel ajar untuk setiap guru dari master mapel yang tersedia.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-chalkboard-user"></i></div>
        <div class="info">
          <h3><?= (int)($totalGuru ?? 0) ?></h3>
          <p>Total Guru</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-user-check"></i></div>
        <div class="info">
          <h3><?= (int)($guruAktif ?? 0) ?></h3>
          <p>Akun Aktif</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-user-lock"></i></div>
        <div class="info">
          <h3><?= (int)($guruLocked ?? 0) ?></h3>
          <p>Akun Terkunci</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-4">
        <div class="icon purple"><i class="fas fa-book-open"></i></div>
        <div class="info">
          <h3><?= (int)($guruSudahSetMapel ?? 0) ?></h3>
          <p>Guru Sudah Set Mapel</p>
        </div>
      </div>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Tabel Data Guru</h3>
        <span class="badge bg-primary">Total <?= (int)($totalGuru ?? 0) ?></span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>ID Guru</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Mata Pelajaran</th>
              <th>Status Akun</th>
              <th>Status User</th>
              <th>Tanggal Buat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($guru)): ?>
              <?php foreach ($guru as $row): ?>
                <?php
                  $isLocked = (int)($row['is_locked'] ?? 0) === 1;
                  $statusClass = $isLocked ? 'badge-terkunci' : 'badge-aktif';
                  $statusText = $isLocked ? 'Terkunci' : 'Aktif';
                  $mapel = trim((string)($row['mapel'] ?? '')) !== '' ? $row['mapel'] : 'Belum dipilih';
                  $namaGuru = trim((string)($row['nama'] ?? '')) !== '' ? $row['nama'] : 'Belum diisi';
                  $noTelp = trim((string)($row['no_telp'] ?? '')) !== '' ? $row['no_telp'] : '-';
                  $alamat = trim((string)($row['alamat'] ?? '')) !== '' ? $row['alamat'] : '-';
                  $bio = trim((string)($row['bio'] ?? '')) !== '' ? $row['bio'] : '-';
                  $statusUser = !empty($row['email']) ? 'Ada' : 'Belum Ada';
                  $tanggal = !empty($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : '-';
                  $tanggalLengkap = !empty($row['created_at']) ? date('d-m-Y H:i', strtotime($row['created_at'])) : '-';
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($namaGuru) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= htmlspecialchars($mapel) ?></td>
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
                      class="btn btn-sm btn-outline-primary edit-guru-btn admin-action-icon"
                      data-bs-toggle="modal"
                      data-bs-target="#editGuruModal"
                      data-guru-id="<?= htmlspecialchars($row['id']) ?>"
                      data-nama="<?= htmlspecialchars($namaGuru) ?>"
                      data-mapel-id="<?= htmlspecialchars($row['mapel_id'] ?? '') ?>"
                      data-email="<?= htmlspecialchars((string)($row['email'] ?? '-'), ENT_QUOTES) ?>"
                      data-mapel="<?= htmlspecialchars($mapel, ENT_QUOTES) ?>"
                      data-status-akun="<?= htmlspecialchars($statusText, ENT_QUOTES) ?>"
                      data-status-user="<?= htmlspecialchars($statusUser, ENT_QUOTES) ?>"
                      data-attempts="<?= htmlspecialchars((string)($row['attempts'] ?? 0), ENT_QUOTES) ?>"
                      data-no-telp="<?= htmlspecialchars($noTelp, ENT_QUOTES) ?>"
                      data-alamat="<?= htmlspecialchars($alamat, ENT_QUOTES) ?>"
                      data-bio="<?= htmlspecialchars($bio, ENT_QUOTES) ?>"
                      data-created-at="<?= htmlspecialchars($tanggalLengkap, ENT_QUOTES) ?>"
                    >
                      <i class="fas fa-pen"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center empty-state-md">
                  Belum ada data guru.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="editGuruModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Data Guru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="index.php?page=admin-guru">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
        <input type="hidden" name="action" value="update-guru">
        <input type="hidden" name="guru_id" id="editGuruId">
        <div class="modal-body">
          <div class="border rounded p-3 bg-light mb-3 admin-detail-panel">
            <h6 class="mb-3">Detail Guru</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold mb-1">ID Guru</label>
                <div id="detailGuruId">-</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold mb-1">Email</label>
                <div id="detailGuruEmail">-</div>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold mb-1">Status Akun</label>
                <div id="detailGuruStatusAkun">-</div>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold mb-1">Status User</label>
                <div id="detailGuruStatusUser">-</div>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold mb-1">Login Attempt</label>
                <div id="detailGuruAttempts">-</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold mb-1">No. Telp</label>
                <div id="detailGuruNoTelp">-</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold mb-1">Tanggal Buat</label>
                <div id="detailGuruCreatedAt">-</div>
              </div>
              <div class="col-md-12">
                <label class="form-label fw-semibold mb-1">Alamat</label>
                <div id="detailGuruAlamat" class="text-break">-</div>
              </div>
              <div class="col-md-12">
                <label class="form-label fw-semibold mb-1">Bio</label>
                <div id="detailGuruBio" class="text-break">-</div>
              </div>
            </div>
          </div>
          <div class="row g-3 admin-edit-fields">
            <div class="col-md-6">
              <label class="form-label">Nama Guru</label>
              <input type="text" class="form-control" name="nama" id="editGuruNama" maxlength="100" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Mapel Ajar</label>
              <select class="form-select" name="mapel_id" id="editGuruMapelId" required>
                <option value="">Pilih mapel</option>
                <?php foreach (($mapelOptions ?? []) as $opt): ?>
                  <option value="<?= htmlspecialchars($opt['id']) ?>">
                    <?= htmlspecialchars($opt['nama']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted">Setiap guru hanya boleh memiliki satu mapel ajar aktif.</small>
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
  const editGuruModal = document.getElementById('editGuruModal');
  if (!editGuruModal) return;

  const setDetailValue = (id, value) => {
    const element = document.getElementById(id);
    if (!element) return;
    const normalized = (value || '').toString().trim();
    element.textContent = normalized !== '' ? normalized : '-';
  };

  editGuruModal.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    document.getElementById('editGuruId').value = button.getAttribute('data-guru-id') || '';
    document.getElementById('editGuruNama').value = button.getAttribute('data-nama') || '';
    document.getElementById('editGuruMapelId').value = button.getAttribute('data-mapel-id') || '';

    setDetailValue('detailGuruId', button.getAttribute('data-guru-id'));
    setDetailValue('detailGuruEmail', button.getAttribute('data-email'));
    setDetailValue('detailGuruStatusAkun', button.getAttribute('data-status-akun'));
    setDetailValue('detailGuruStatusUser', button.getAttribute('data-status-user'));
    setDetailValue('detailGuruAttempts', button.getAttribute('data-attempts'));
    setDetailValue('detailGuruNoTelp', button.getAttribute('data-no-telp'));
    setDetailValue('detailGuruCreatedAt', button.getAttribute('data-created-at'));
    setDetailValue('detailGuruAlamat', button.getAttribute('data-alamat'));
    setDetailValue('detailGuruBio', button.getAttribute('data-bio'));
  });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
