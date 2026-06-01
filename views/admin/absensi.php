<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="bg-shapes"><div class="shape shape-1"></div><div class="shape shape-2"></div></div>
<div class="dashboard-container">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
  <main class="main-content admin-absensi-page">
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
              <option value="">Semua Guru</option>
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
              <option value="">Semua Siswa</option>
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
              <option value="">Semua Status</option>
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

          <div class="filter-actions">
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
                    <button type="button" class="btn-action btn-correction" data-id="<?= htmlspecialchars($absensi['id']) ?>">
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
<div id="correction-modal" class="correction-modal">
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
<script>
const apiBase = <?= json_encode(basename($_SERVER['SCRIPT_NAME']) . '?page=admin-absensi&action=') ?>;
const csrfToken = <?= json_encode($csrf_token) ?>;
let currentAbsensiId = null;

function parseJsonResponse(raw) {
  const text = String(raw !== undefined && raw !== null ? raw : '').trim();
  try {
    return JSON.parse(text);
  } catch (_) {
    const start = text.indexOf('{');
    const end = text.lastIndexOf('}');
    if (start !== -1 && end > start) {
      try {
        return JSON.parse(text.slice(start, end + 1));
      } catch (__){
        console.error('Invalid JSON payload (trimmed) from API:', text);
      }
    }
    throw new Error('Response server tidak valid. Silakan login ulang atau cek console/network.');
  }
}

async function fetchJson(url, options = {}) {
  const headers = {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...(options.headers || {})
  };

  const response = await fetch(url, {
    ...options,
    credentials: 'same-origin',
    headers
  });

  const raw = await response.text();
  const contentType = (response.headers.get('Content-Type') || '').toLowerCase();

  if (!contentType.includes('application/json')) {
    console.error('Non-JSON response from API:', raw);
    if (response.status === 401 || raw.includes('<title>Login')) {
      throw new Error('Sesi login habis. Silakan login ulang.');
    }
    throw new Error('Response server tidak valid. Silakan login ulang atau cek console/network.');
  }

  const data = parseJsonResponse(raw);
  if (response.status === 401) {
    throw new Error(data.message || 'Sesi login habis. Silakan login ulang.');
  }
  if (!response.ok) {
    throw new Error(data.message || ('Permintaan gagal dengan status ' + response.status + '.'));
  }
  return data;
}

document.addEventListener('DOMContentLoaded', function () {
  // Correction button handler (delegation biar tetap jalan meskipun DOM berubah)
  document.addEventListener('click', function (e) {
    const button = e.target.closest('.btn-correction');
    if (!button) return;
    currentAbsensiId = button.getAttribute('data-id') || '';
    if (!currentAbsensiId) {
      alert('ID absensi tidak ditemukan.');
      return;
    }
    loadAbsensiDetail(currentAbsensiId);
  });

  // Modal close handlers
  const modalCloseBtn = document.getElementById('modal-close');
  const modalCancelBtn = document.getElementById('modal-cancel');
  const modalSaveBtn = document.getElementById('modal-save');
  const correctionModal = document.getElementById('correction-modal');

  if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
  if (modalCancelBtn) modalCancelBtn.addEventListener('click', closeModal);

  // Modal outside click to close
  if (correctionModal) {
    correctionModal.addEventListener('click', function (e) {
      if (e.target.id === 'correction-modal') {
        closeModal();
      }
    });
  }

  // Save correction
  if (modalSaveBtn) {
    modalSaveBtn.addEventListener('click', async function () {
      const statusEl = document.getElementById('correction-status');
      const alasanEl = document.getElementById('correction-alasan');
      const reasonEl = document.getElementById('correction-reason');

      if (!statusEl || !alasanEl || !reasonEl) {
        alert('Form koreksi belum siap. Klik tombol Koreksi lagi.');
        return;
      }

      const newStatus = statusEl.value;
      const alasan = alasanEl.value.trim();
      const reason = reasonEl.value.trim();

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

        const result = await fetchJson(apiBase + 'save-correction', {
          method: 'POST',
          body: formData
        });

        if (result.status === 'success') {
          alert('Koreksi berhasil disimpan!');
          location.reload();
        } else {
          alert('Error: ' + result.message);
        }

      } catch (error) {
        if ((error.message || '').toLowerCase().includes('sesi login habis')) {
          alert(error.message);
          window.location.href = 'index.php?page=login';
          return;
        }
        alert('Error: ' + error.message);
      }
    });
  }
});

function closeModal() {
  const modal = document.getElementById('correction-modal');
  if (!modal) return;
  modal.classList.remove('show');
  modal.style.display = 'none';
  modal.setAttribute('aria-hidden', 'true');
}

// Load absensi detail
async function loadAbsensiDetail(absensiId) {
  try {
    const data = await fetchJson(apiBase + 'get-detail&id=' + encodeURIComponent(absensiId));

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
              ${item.old_status ? `<br><small>Status: ${htmlEscape(item.old_status)} -> ${htmlEscape(item.new_status)}</small>` : ''}
              ${item.reason ? `<br><small>Alasan: ${htmlEscape(item.reason)}</small>` : ''}
            </div>
          `).join('')}
        </div>
      ` : ''}
    `;

    document.getElementById('modal-body').innerHTML = html;
    const modal = document.getElementById('correction-modal');
    if (modal) {
      modal.classList.add('show');
      modal.style.display = 'flex';
      modal.setAttribute('aria-hidden', 'false');
    }

  } catch (error) {
    if ((error.message || '').toLowerCase().includes('sesi login habis')) {
      alert(error.message);
      window.location.href = 'index.php?page=login';
      return;
    }
    alert('Error: ' + error.message);
  }
}

function htmlEscape(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>


