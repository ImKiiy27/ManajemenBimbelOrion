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
      <h1>Kelola Mapel</h1>
      <p>Atur master mata pelajaran resmi yang tersedia di bimbel sebelum dipakai pada guru, siswa, dan pendaftaran.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-book-open"></i></div>
        <div class="info">
          <h3><?= (int)($summary['total'] ?? 0) ?></h3>
          <p>Total Mapel</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-circle-check"></i></div>
        <div class="info">
          <h3><?= (int)($summary['aktif'] ?? 0) ?></h3>
          <p>Mapel Aktif</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-ban"></i></div>
        <div class="info">
          <h3><?= (int)($summary['nonaktif'] ?? 0) ?></h3>
          <p>Mapel Nonaktif</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-4">
        <div class="icon purple"><i class="fas fa-chalkboard-user"></i></div>
        <div class="info">
          <h3><?= (int)($summary['dipakai_guru'] ?? 0) ?></h3>
          <p>Dipakai Guru</p>
        </div>
      </div>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-plus-circle"></i> Tambah Mapel</h3>
      </div>

      <form method="POST" action="index.php?page=admin-mapel" class="row g-3">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
        <input type="hidden" name="action" value="create">

        <div class="col-lg-4">
          <label class="form-label">Nama Mapel</label>
          <input type="text" class="form-control" name="nama" maxlength="100" placeholder="Contoh: Matematika" required>
        </div>

        <div class="col-lg-5">
          <label class="form-label">Deskripsi</label>
          <input type="text" class="form-control" name="deskripsi" maxlength="255" placeholder="Opsional">
        </div>

        <div class="col-lg-3">
          <label class="form-label">Status</label>
          <select class="form-select" name="status" required>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
          </select>
        </div>

        <div class="col-12 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-save me-1"></i> Simpan Mapel
          </button>
        </div>
      </form>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Tabel Master Mapel</h3>
        <span class="badge bg-primary">Total <?= (int)($summary['total'] ?? 0) ?></span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>ID Mapel</th>
              <th>Nama</th>
              <th>Deskripsi</th>
              <th>Status</th>
              <th>Dipakai Guru</th>
              <th>Dipakai Siswa</th>
              <th>Dipilih Pendaftar</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($mapel)): ?>
              <?php foreach ($mapel as $row): ?>
                <?php
                  $status = ($row['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';
                  $statusClass = $status === 'aktif' ? 'badge-aktif' : 'badge-nonaktif';
                  $statusLabel = $status === 'aktif' ? 'Aktif' : 'Nonaktif';
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                  <td><?= htmlspecialchars(trim((string)($row['deskripsi'] ?? '')) !== '' ? $row['deskripsi'] : '-') ?></td>
                  <td><span class="badge-status <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                  <td><?= (int)($row['total_guru'] ?? 0) ?></td>
                  <td><?= (int)($row['total_siswa'] ?? 0) ?></td>
                  <td><?= (int)($row['total_pendaftar'] ?? 0) ?></td>
                  <td class="text-nowrap">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary me-1 edit-mapel-btn"
                      data-bs-toggle="modal"
                      data-bs-target="#editMapelModal"
                      data-id="<?= htmlspecialchars($row['id']) ?>"
                      data-nama="<?= htmlspecialchars($row['nama'] ?? '') ?>"
                      data-deskripsi="<?= htmlspecialchars($row['deskripsi'] ?? '') ?>"
                      data-status="<?= htmlspecialchars($status) ?>"
                    >
                      <i class="fas fa-pen"></i>
                    </button>

                    <form method="POST" action="index.php?page=admin-mapel" class="d-inline">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                      <input type="hidden" name="action" value="toggle-status">
                      <input type="hidden" name="mapel_id" value="<?= htmlspecialchars($row['id']) ?>">
                      <input type="hidden" name="status" value="<?= $status === 'aktif' ? 'nonaktif' : 'aktif' ?>">
                      <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?= $status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>">
                        <i class="fas <?= $status === 'aktif' ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center empty-state-md">
                  Belum ada data mapel.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="editMapelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Mapel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="index.php?page=admin-mapel">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="mapel_id" id="editMapelId">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nama Mapel</label>
              <input type="text" class="form-control" name="nama" id="editMapelNama" maxlength="100" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status" id="editMapelStatus" required>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <textarea class="form-control" name="deskripsi" id="editMapelDeskripsi" rows="3" maxlength="255" placeholder="Opsional"></textarea>
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
  const editMapelModal = document.getElementById('editMapelModal');
  if (!editMapelModal) return;

  editMapelModal.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    document.getElementById('editMapelId').value = button.getAttribute('data-id') || '';
    document.getElementById('editMapelNama').value = button.getAttribute('data-nama') || '';
    document.getElementById('editMapelDeskripsi').value = button.getAttribute('data-deskripsi') || '';
    document.getElementById('editMapelStatus').value = button.getAttribute('data-status') || 'aktif';
  });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

