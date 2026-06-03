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
      <p>Input absensi siswa untuk kelas yang Anda ajar.</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs-navigation">
      <button class="tab-btn active" data-tab="input-tab">Input Absensi</button>
      <button class="tab-btn" data-tab="history-tab">Riwayat</button>
    </div>

    <!-- ============ INPUT ABSENSI TAB ============ -->
    <div id="input-tab" class="tab-content active">
      <div class="content-card animate-fade-in">
        <h3 class="card-title">Input Absensi Siswa</h3>

        <!-- Jadwal Selection -->
        <div class="form-group">
          <label for="jadwal_select">Pilih Jadwal:</label>
          <select id="jadwal_select" class="form-control" required>
            <option value="">-- Pilih Jadwal --</option>
            <?php foreach ($jadwalList as $jadwal): ?>
              <option value="<?= htmlspecialchars($jadwal['id']) ?>">
                <?= htmlspecialchars($jadwal['hari']) ?>
                <?= htmlspecialchars(substr($jadwal['jam_mulai'], 0, 5)) ?>-<?= htmlspecialchars(substr($jadwal['jam_selesai'], 0, 5)) ?>
                <?php if (!empty($jadwal['siswa_names'])): ?>
                  - <?= htmlspecialchars($jadwal['siswa_names']) ?>
                <?php endif; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Date Selection -->
        <div class="form-group">
          <label for="tanggal_select">Pilih Tanggal:</label>
          <input type="date" id="tanggal_select" class="form-control" required>
          <small class="text-muted">Hanya bisa input absensi hari ini atau kemarin</small>
        </div>

        <!-- Siswa List (Dynamic) -->
        <div class="form-group">
          <label>Absensi Siswa</label>
          <div id="siswa_list" class="siswa-grid hide-element">
            <!-- Loaded dynamically -->
          </div>
          <div id="siswa_loading" class="hide-element text-center-custom">
            <i class="fas fa-spinner fa-spin"></i> Loading...
          </div>
          <div id="siswa_empty" class="padding-center-custom">
            Pilih jadwal dan tanggal untuk melihat daftar siswa
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-actions absensi-actions">
          <button type="button" class="btn btn-primary hide-element" id="btn_save_all">
            <i class="fas fa-save"></i> Simpan Semua
          </button>
        </div>
      </div>
    </div>

    <!-- ============ HISTORY TAB ============ -->
    <div id="history-tab" class="tab-content hide-element">
      <div class="content-card animate-fade-in">
        <h3 class="card-title">Riwayat Absensi</h3>
        <p class="padding-center-lg">
          <i class="fas fa-history fa-2x mb-3 d-block text-primary-custom"></i>
          <a href="index.php?page=guru-absensi&action=riwayat">Lihat riwayat absensi lengkap</a>
        </p>
      </div>
    </div>
  </main>
</div>

<style>
.tabs-navigation {
  display: flex;
  gap: 10px;
  margin: 20px 0;
  border-bottom: 2px solid var(--border-color);
}

.tab-btn {
  padding: 10px 20px;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-muted);
  font-weight: 500;
  border-bottom: 3px solid transparent;
  transition: all 0.3s ease;
}

.tab-btn.active {
  color: var(--primary-color);
  border-bottom-color: var(--primary-color);
}

.tab-btn:hover {
  color: var(--primary-color);
}

.tab-content {
  animation: fadeIn 0.3s ease;
}

.siswa-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin: 12px 0;
}

.siswa-card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 16px;
  transition: all 0.3s ease;
  max-width: 920px;
}

.siswa-card:hover {
  border-color: var(--primary-color);
  box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
}

.siswa-name {
  font-weight: 600;
  margin-bottom: 12px;
  color: var(--text-primary);
  text-transform: capitalize;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: var(--text-primary);
}

.form-control,
select {
  width: 100%;
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

.status-group {
  display: grid;
  grid-template-columns: repeat(4, minmax(96px, 1fr));
  gap: 8px;
  margin: 8px 0 14px;
}

.status-radio {
  min-width: 0;
}

.status-radio input[type="radio"] {
  display: none;
}

.status-radio label {
  display: block;
  padding: 9px 12px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  cursor: pointer;
  text-align: center;
  font-weight: 500;
  transition: all 0.2s;
  margin: 0;
}

.status-radio input[type="radio"]:checked + label {
  background-color: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.status-radio label:hover {
  border-color: var(--primary-color);
}

.textarea-field {
  width: 100%;
  padding: 8px;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  resize: vertical;
  font-size: 13px;
  font-family: inherit;
  background-color: var(--card-bg);
  color: var(--text-primary);
  min-height: 60px;
}

.absensi-notes-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.absensi-actions {
  display: flex;
  justify-content: flex-end;
  max-width: 920px;
  margin-top: 8px;
}

@media (max-width: 768px) {
  .status-group {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .absensi-notes-grid {
    grid-template-columns: 1fr;
  }

  .absensi-actions {
    justify-content: stretch;
  }

  .absensi-actions .btn {
    width: 100%;
    justify-content: center;
  }
}

.textarea-field:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}

.btn {
  padding: 10px 20px;
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

.alert {
  padding: 15px 20px;
  border-radius: 6px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  animation: slideDown 0.3s ease;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
  border-left: 4px solid #28a745;
}

.alert-error {
  background-color: #f8d7da;
  color: #721c24;
  border-left: 4px solid #dc3545;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<script>
const apiBase = 'index.php?page=guru-absensi&action=';
const csrfToken = '<?= htmlspecialchars($csrf_token) ?>';

// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const tabName = btn.dataset.tab;

    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
      tab.classList.add('hide-element');
      tab.classList.remove('active');
    });

    // Deactivate all buttons
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

    // Show selected tab
    document.getElementById(tabName).classList.remove('hide-element');
    document.getElementById(tabName).classList.add('active');
    btn.classList.add('active');
  });
});

// Jadwal change event
document.getElementById('jadwal_select').addEventListener('change', loadSiswaList);
document.getElementById('tanggal_select').addEventListener('change', loadSiswaList);

async function loadSiswaList() {
  const jadwalId = document.getElementById('jadwal_select').value;
  const tanggal = document.getElementById('tanggal_select').value;

  if (!jadwalId || !tanggal) {
    document.getElementById('siswa_list').classList.add('hide-element');
    document.getElementById('siswa_empty').style.display = 'block';
    document.getElementById('btn_save_all').classList.add('hide-element');
    return;
  }

  showLoading(true);

  try {
    // Load siswa
    const siswaRes = await fetch(apiBase + 'load-siswa', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `jadwal_id=${encodeURIComponent(jadwalId)}`
    });
    const siswaData = await siswaRes.json();

    if (siswaData.status !== 'success' || !siswaData.data.length) {
      showError('Tidak ada siswa dalam jadwal ini');
      return;
    }

    // Load existing absensi data
    const dataRes = await fetch(apiBase + 'load-data', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `jadwal_id=${encodeURIComponent(jadwalId)}&tanggal=${encodeURIComponent(tanggal)}`
    });
    const absensiData = await dataRes.json();
    const existingAbsensi = (absensiData.data || []).reduce((acc, a) => {
      acc[a.siswa_id] = a;
      return acc;
    }, {});

    // Render siswa cards
    renderSiswaCards(siswaData.data, existingAbsensi);
    document.getElementById('siswa_list').classList.remove('hide-element');
    document.getElementById('siswa_empty').style.display = 'none';
    document.getElementById('btn_save_all').classList.remove('hide-element');

  } catch (error) {
    showError('Error loading data: ' + error.message);
  } finally {
    showLoading(false);
  }
}

function renderSiswaCards(siswaList, existingAbsensi) {
  const container = document.getElementById('siswa_list');
  container.innerHTML = '';

  siswaList.forEach(siswa => {
    const existing = existingAbsensi[siswa.id];
    const status = existing?.status || 'Hadir';
    const alasan = existing?.alasan || '';
    const catatan = existing?.catatan_guru || '';

    const card = document.createElement('div');
    card.className = 'siswa-card';
    card.innerHTML = `
      <div class="siswa-name">${htmlEscape(siswa.nama)}</div>

      <div class="status-group">
        <div class="status-radio">
          <input type="radio" name="status_${siswa.id}" id="status_${siswa.id}_hadir" value="Hadir" ${status === 'Hadir' ? 'checked' : ''}>
          <label for="status_${siswa.id}_hadir">Hadir</label>
        </div>
        <div class="status-radio">
          <input type="radio" name="status_${siswa.id}" id="status_${siswa.id}_izin" value="Izin" ${status === 'Izin' ? 'checked' : ''}>
          <label for="status_${siswa.id}_izin">Izin</label>
        </div>
        <div class="status-radio">
          <input type="radio" name="status_${siswa.id}" id="status_${siswa.id}_sakit" value="Sakit" ${status === 'Sakit' ? 'checked' : ''}>
          <label for="status_${siswa.id}_sakit">Sakit</label>
        </div>
        <div class="status-radio">
          <input type="radio" name="status_${siswa.id}" id="status_${siswa.id}_alpa" value="Alpa" ${status === 'Alpa' ? 'checked' : ''}>
          <label for="status_${siswa.id}_alpa">Alpa</label>
        </div>
      </div>

      <div class="absensi-notes-grid">
        <div class="form-group">
          <label for="alasan_${siswa.id}" class="margin-bottom-xs">Alasan</label>
          <textarea class="textarea-field alasan-field" id="alasan_${siswa.id}" data-siswa="${siswa.id}" placeholder="Wajib jika Izin, Sakit, atau Alpa">${htmlEscape(alasan)}</textarea>
        </div>

        <div class="form-group">
          <label for="catatan_${siswa.id}" class="margin-bottom-xs">Catatan Guru</label>
          <textarea class="textarea-field" id="catatan_${siswa.id}" data-siswa="${siswa.id}" placeholder="Opsional">${htmlEscape(catatan)}</textarea>
        </div>
      </div>
    `;
    container.appendChild(card);
  });
}

// Save all absensi
document.getElementById('btn_save_all').addEventListener('click', saveAllAbsensi);

async function saveAllAbsensi() {
  const jadwalId = document.getElementById('jadwal_select').value;
  const tanggal = document.getElementById('tanggal_select').value;
  const siswaCards = document.querySelectorAll('.siswa-card');

  if (!jadwalId || !tanggal || !siswaCards.length) {
    showError('Data tidak lengkap');
    return;
  }

  let hasError = false;

  for (let card of siswaCards) {
    const siswaName = card.querySelector('.siswa-name').textContent;
    const statusInput = card.querySelector('input[type="radio"]:checked');
    const status = statusInput?.value || '';
    const alasan = card.querySelector('.alasan-field').value.trim();

    if (!status) {
      showError(`${siswaName}: Pilih status absensi terlebih dahulu`);
      hasError = true;
      continue;
    }

    // Validate mandatory alasan
    if (['Izin', 'Sakit', 'Alpa'].includes(status) && !alasan) {
      showError(`${siswaName}: Alasan wajib diisi untuk status ${status}`);
      hasError = true;
      continue;
    }

    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('jadwal_id', jadwalId);
    formData.append('tanggal', tanggal);
    formData.append('siswa_id', card.querySelector('[id^="alasan_"]').dataset.siswa);
    formData.append('status', status);
    formData.append('alasan', alasan);
    formData.append('catatan_guru', card.querySelector('[id^="catatan_"]').value.trim());

    try {
      const response = await fetch(apiBase + 'save', {
        method: 'POST',
        body: formData
      });
      const result = await response.json();

      if (result.status !== 'success') {
        showError(`${siswaName}: ${result.message}`);
        hasError = true;
      }
    } catch (error) {
      showError(`${siswaName}: ${error.message}`);
      hasError = true;
    }
  }

  if (!hasError) {
    showSuccess('Semua absensi berhasil disimpan!');
    setTimeout(() => location.reload(), 1500);
  }
}

function showLoading(show) {
  document.getElementById('siswa_loading').classList.toggle('hide-element', !show);
}

function showError(message) {
  const alert = document.createElement('div');
  alert.className = 'alert alert-error animate-fade-in';
  alert.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${htmlEscape(message)}`;
  document.querySelector('.page-header').after(alert);
  setTimeout(() => alert.remove(), 5000);
}

function showSuccess(message) {
  const alert = document.createElement('div');
  alert.className = 'alert alert-success animate-fade-in';
  alert.innerHTML = `<i class="fas fa-check-circle"></i> ${htmlEscape(message)}`;
  document.querySelector('.page-header').after(alert);
}

function htmlEscape(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Set default tanggal to today
document.getElementById('tanggal_select').valueAsDate = new Date();
document.getElementById('tanggal_select').max = new Date().toISOString().split('T')[0];
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

