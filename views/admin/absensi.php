<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="bg-shapes"><div class="shape shape-1"></div><div class="shape shape-2"></div></div>
<div class="dashboard-container">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
  <main class="main-content">
    <?php require __DIR__ . '/../layouts/dashboard-navbar.php'; ?>

    <!-- Flash Message -->
    <?php if ($flash): ?>
      <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> animate-fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
      </div>
    <?php endif; ?>

    <div class="page-header animate-fade-in">
      <h1><?= htmlspecialchars($pageTitle ?? 'Halaman') ?></h1>
      <p>Rekap absensi siswa dengan filter dan opsi koreksi data.</p>
    </div>

    <!-- Filter Form -->
    <div class="content-card animate-fade-in">
      <h3 class="card-title">Filter Data</h3>
      <form method="GET" action="" class="filter-form">
        <input type="hidden" name="page" value="admin-absensi">

        <div class="filter-grid">
          <div class="filter-group">
            <label for="guru_id">Guru:</label>
            <select id="guru_id" name="guru_id" class="form-control">
              <option value="">-- Semua Guru --</option>
              <?php foreach ($guruList as $guru): ?>
                <option value="<?= htmlspecialchars($guru['id']) ?>" <?= $guruId === $guru['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($guru['nama']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="filter-group">
            <label for="siswa_id">Siswa:</label>
            <select id="siswa_id" name="siswa_id" class="form-control">
              <option value="">-- Semua Siswa --</option>
              <?php foreach ($siswaList as $siswa): ?>
                <option value="<?= htmlspecialchars($siswa['id']) ?>" <?= $siswaId === $siswa['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($siswa['nama']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="filter-group">
            <label for="status">Status:</label>
            <select id="status" name="status" class="form-control">
              <option value="">-- Semua Status --</option>
              <option value="Hadir" <?= $status === 'Hadir' ? 'selected' : '' ?>>Hadir</option>
              <option value="Izin" <?= $status === 'Izin' ? 'selected' : '' ?>>Izin</option>
              <option value="Sakit" <?= $status === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
              <option value="Alpa" <?= $status === 'Alpa' ? 'selected' : '' ?>>Alpa</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="tgl_dari">Dari Tanggal:</label>
            <input type="date" id="tgl_dari" name="tgl_dari" class="form-control" value="<?= htmlspecialchars($tanggalStart) ?>">
          </div>

          <div class="filter-group">
            <label for="tgl_sampai">Sampai Tanggal:</label>
            <input type="date" id="tgl_sampai" name="tgl_sampai" class="form-control" value="<?= htmlspecialchars($tanggalEnd) ?>">
          </div>

          <div class="filter-group flex-gap-sm">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-filter"></i> Filter
            </button>
            <a href="index.php?page=admin-absensi" class="btn btn-secondary">
              <i class="fas fa-redo"></i> Reset
            </a>
          </div>
        </div>
      </form>
    </div>

    <!-- Rekap Data -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3 class="card-title">Rekap Absensi</h3>
        <div class="card-meta">Total: <?= $totalCount ?> data</div>
      </div>

      <?php if (empty($absensiList)): ?>
        <div class="padding-center-lg">
          <i class="fas fa-inbox fa-2x mb-3 d-block text-primary-custom"></i>
          Tidak ada data absensi
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table-custom">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Siswa</th>
                <th>Guru</th>
                <th>Jam</th>
                <th>Status</th>
                <th>Alasan</th>
                <th>Updated</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($absensiList as $absensi): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($absensi['tanggal']) ?></strong></td>
                  <td><?= htmlspecialchars($absensi['hari']) ?></td>
                  <td><?= htmlspecialchars($absensi['siswa_nama']) ?></td>
                  <td><?= htmlspecialchars($absensi['guru_nama']) ?></td>
                  <td><?= htmlspecialchars(substr($absensi['jam_mulai'], 0, 5)) ?>-<?= htmlspecialchars(substr($absensi['jam_selesai'], 0, 5)) ?></td>
                  <td>
                    <span class="status-badge status-<?= strtolower($absensi['status']) ?>">
                      <?= htmlspecialchars($absensi['status']) ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($absensi['alasan']): ?>
                      <span title="<?= htmlspecialchars($absensi['alasan']) ?>">
                        <?= htmlspecialchars(substr($absensi['alasan'], 0, 30)) ?>...
                      </span>
                    <?php else: ?>
                      <span class="text-primary-dark-custom">-</span>
                    <?php endif; ?>
                  </td>
                  <td><small><?= htmlspecialchars($absensi['updated_at']) ?></small></td>
                  <td>
                    <button class="btn-action btn-correction" data-id="<?= htmlspecialchars($absensi['id']) ?>">
                      <i class="fas fa-edit"></i> Koreksi
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <?php if ($page > 1): ?>
              <a href="?page=admin-absensi&guru_id=<?= urlencode($guruId) ?>&siswa_id=<?= urlencode($siswaId) ?>&status=<?= urlencode($status) ?>&tgl_dari=<?= urlencode($tanggalStart) ?>&tgl_sampai=<?= urlencode($tanggalEnd) ?>&p=1" class="page-link">
                <i class="fas fa-chevron-left"></i> Pertama
              </a>
              <a href="?page=admin-absensi&guru_id=<?= urlencode($guruId) ?>&siswa_id=<?= urlencode($siswaId) ?>&status=<?= urlencode($status) ?>&tgl_dari=<?= urlencode($tanggalStart) ?>&tgl_sampai=<?= urlencode($tanggalEnd) ?>&p=<?= $page - 1 ?>" class="page-link">
                <i class="fas fa-chevron-left"></i> Sebelumnya
              </a>
            <?php endif; ?>

            <span class="page-info">Halaman <?= $page ?> dari <?= $totalPages ?></span>

            <?php if ($page < $totalPages): ?>
              <a href="?page=admin-absensi&guru_id=<?= urlencode($guruId) ?>&siswa_id=<?= urlencode($siswaId) ?>&status=<?= urlencode($status) ?>&tgl_dari=<?= urlencode($tanggalStart) ?>&tgl_sampai=<?= urlencode($tanggalEnd) ?>&p=<?= $page + 1 ?>" class="page-link">
                Selanjutnya <i class="fas fa-chevron-right"></i>
              </a>
              <a href="?page=admin-absensi&guru_id=<?= urlencode($guruId) ?>&siswa_id=<?= urlencode($siswaId) ?>&status=<?= urlencode($status) ?>&tgl_dari=<?= urlencode($tanggalStart) ?>&tgl_sampai=<?= urlencode($tanggalEnd) ?>&p=<?= $totalPages ?>" class="page-link">
                Terakhir <i class="fas fa-chevron-right"></i>
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </main>
</div>

<!-- Modal Correction -->
<div id="correction-modal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Koreksi Absensi</h3>
      <button type="button" class="modal-close" id="modal-close">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div class="modal-body" id="modal-body">
      <!-- Loaded dynamically -->
    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" id="modal-cancel">Batal</button>
      <button type="button" class="btn btn-primary" id="modal-save">Simpan Koreksi</button>
    </div>
  </div>
</div>

<style>
.filter-form {
  display: grid;
  gap: 20px;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-group label {
  font-weight: 500;
  margin-bottom: 5px;
  color: var(--text-primary);
}

.form-control,
select {
  padding: 10px 12px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background-color: var(--card-bg);
  color: var(--text-primary);
  font-size: 14px;
}

.form-control:focus,
select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}

.btn {
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.btn-secondary {
  background-color: var(--border-color);
  color: var(--text-primary);
}

.btn-secondary:hover {
  background-color: #ccc;
}

.btn-action {
  padding: 6px 12px;
  border: 1px solid var(--border-color);
  background-color: transparent;
  color: var(--primary-color);
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.2s;
}

.btn-action:hover {
  background-color: var(--primary-color);
  color: white;
}

.btn-correction {
  border-color: #ffc107;
  color: #ffc107;
}

.btn-correction:hover {
  background-color: #ffc107;
  color: white;
}

.table-responsive {
  overflow-x: auto;
  margin: 20px 0;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.data-table thead {
  background-color: var(--bg-secondary);
  border-bottom: 2px solid var(--border-color);
}

.data-table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: var(--text-primary);
}

.data-table td {
  padding: 12px;
  border-bottom: 1px solid var(--border-color);
}

.data-table tbody tr:hover {
  background-color: var(--bg-secondary);
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-align: center;
  min-width: 70px;
}

.status-hadir {
  background-color: #d4edda;
  color: #155724;
}

.status-izin {
  background-color: #cfe2ff;
  color: #084298;
}

.status-sakit {
  background-color: #fff3cd;
  color: #664d03;
}

.status-alpa {
  background-color: #f8d7da;
  color: #721c24;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 20px;
  padding: 20px 0;
}

.page-link {
  padding: 8px 12px;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  color: var(--primary-color);
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
}

.page-link:hover {
  background-color: var(--primary-color);
  color: white;
}

.page-info {
  font-size: 14px;
  color: var(--text-muted);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.card-meta {
  font-size: 14px;
  color: var(--text-muted);
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

.modal.show {
  display: flex;
}

.modal-content {
  background-color: var(--card-bg);
  border-radius: 8px;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  color: var(--text-primary);
}

.modal-close {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  font-size: 20px;
  padding: 0;
}

.modal-close:hover {
  color: var(--text-primary);
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid var(--border-color);
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  color: var(--text-primary);
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background-color: var(--card-bg);
  color: var(--text-primary);
  font-size: 14px;
  font-family: inherit;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}

.audit-trail {
  background-color: var(--bg-secondary);
  border-radius: 6px;
  padding: 15px;
  margin-top: 15px;
  font-size: 13px;
}

.audit-trail h4 {
  margin-top: 0;
  color: var(--text-primary);
}

.audit-item {
  padding: 10px 0;
  border-bottom: 1px solid var(--border-color);
  color: var(--text-muted);
}

.audit-item:last-child {
  border-bottom: none;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .filter-grid {
    grid-template-columns: 1fr;
  }

  .data-table {
    font-size: 12px;
  }

  .data-table th,
  .data-table td {
    padding: 8px;
  }
}
</style>

<script>
const apiBase = 'index.php?page=admin-absensi&action=';
const csrfToken = '<?= htmlspecialchars($csrf_token) ?>';
let currentAbsensiId = null;

// Correction button handler
document.querySelectorAll('.btn-correction').forEach(btn => {
  btn.addEventListener('click', () => {
    currentAbsensiId = btn.dataset.id;
    loadAbsensiDetail(currentAbsensiId);
  });
});

// Modal close handlers
document.getElementById('modal-close').addEventListener('click', closeModal);
document.getElementById('modal-cancel').addEventListener('click', closeModal);

function closeModal() {
  document.getElementById('correction-modal').classList.remove('show');
}

// Modal outside click to close
document.getElementById('correction-modal').addEventListener('click', (e) => {
  if (e.target.id === 'correction-modal') {
    closeModal();
  }
});

// Load absensi detail
async function loadAbsensiDetail(absensiId) {
  try {
    const response = await fetch(apiBase + 'get-detail&id=' + encodeURIComponent(absensiId));
    const data = await response.json();

    if (data.status !== 'success') {
      alert('Error: ' + data.message);
      return;
    }

    const absensi = data.data;
    const trail = data.trail || [];

    const html = `
      <div class="form-group">
        <label>Siswa:</label>
        <p><strong>${htmlEscape(absensi.siswa_nama)}</strong></p>
      </div>

      <div class="form-group">
        <label>Tanggal:</label>
        <p><strong>${htmlEscape(absensi.tanggal)}</strong> (${htmlEscape(absensi.hari)})</p>
      </div>

      <div class="form-group">
        <label>Guru:</label>
        <p><strong>${htmlEscape(absensi.guru_nama)}</strong></p>
      </div>

      <div class="form-group">
        <label>Status Saat Ini:</label>
        <p><strong><span class="status-badge status-${absensi.status.toLowerCase()}">${htmlEscape(absensi.status)}</span></strong></p>
      </div>

      <div class="form-group">
        <label for="correction-status">Status Baru:</label>
        <select id="correction-status" class="form-control" required>
          <option value="">-- Pilih Status --</option>
          <option value="Hadir">Hadir</option>
          <option value="Izin">Izin</option>
          <option value="Sakit">Sakit</option>
          <option value="Alpa">Alpa</option>
        </select>
      </div>

      <div class="form-group">
        <label for="correction-alasan">Alasan (wajib jika tidak Hadir):</label>
        <textarea id="correction-alasan" class="form-control" placeholder="Alasan perubahan status"></textarea>
      </div>

      <div class="form-group">
        <label for="correction-reason">Alasan Koreksi:</label>
        <textarea id="correction-reason" class="form-control" placeholder="Mengapa data ini dikoreksi?" required></textarea>
      </div>

      ${trail.length > 0 ? `
        <div class="audit-trail">
          <h4><i class="fas fa-history"></i> Riwayat Perubahan</h4>
          ${trail.map(item => `
            <div class="audit-item">
              <strong>${htmlEscape(item.action_type)}</strong> - ${htmlEscape(item.changed_at)}
              <br>
              <small>Diubah oleh: ${htmlEscape(item.changed_by)}</small>
              ${item.old_status ? `<br><small>Status: ${htmlEscape(item.old_status)} â†’ ${htmlEscape(item.new_status)}</small>` : ''}
              ${item.reason ? `<br><small>Alasan: ${htmlEscape(item.reason)}</small>` : ''}
            </div>
          `).join('')}
        </div>
      ` : ''}
    `;

    document.getElementById('modal-body').innerHTML = html;
    document.getElementById('correction-modal').classList.add('show');

  } catch (error) {
    alert('Error: ' + error.message);
  }
}

// Save correction
document.getElementById('modal-save').addEventListener('click', async () => {
  const newStatus = document.getElementById('correction-status').value;
  const alasan = document.getElementById('correction-alasan').value.trim();
  const reason = document.getElementById('correction-reason').value.trim();

  if (!newStatus) {
    alert('Status baru wajib dipilih');
    return;
  }

  if (['Izin', 'Sakit', 'Alpa'].includes(newStatus) && !alasan) {
    alert('Alasan wajib diisi untuk status ' + newStatus);
    return;
  }

  if (!reason) {
    alert('Alasan koreksi wajib diisi');
    return;
  }

  try {
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('absensi_id', currentAbsensiId);
    formData.append('status', newStatus);
    formData.append('alasan', alasan);
    formData.append('reason', reason);

    const response = await fetch(apiBase + 'save-correction', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.status === 'success') {
      alert('Koreksi berhasil disimpan!');
      location.reload();
    } else {
      alert('Error: ' + result.message);
    }

  } catch (error) {
    alert('Error: ' + error.message);
  }
});

function htmlEscape(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

