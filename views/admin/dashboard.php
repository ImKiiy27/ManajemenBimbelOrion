<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="bg-shapes">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
</div>

<div class="dashboard-container">

  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content admin-dashboard-page">
    <?php require __DIR__ . '/../layouts/dashboard-navbar.php'; ?>

    <div class="page-header animate-fade-in">
      <h1>Dashboard Admin</h1>
      <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?>! Kelola semua data bimbingan belajar di sini.</p>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-user-graduate"></i></div>
        <div class="info">
          <h3>-</h3>
          <p>Total Siswa</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-chalkboard-user"></i></div>
        <div class="info">
          <h3>-</h3>
          <p>Total Guru</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-calendar-days"></i></div>
        <div class="info">
          <h3><?= isset($totalJadwal) ? (int)$totalJadwal : '-' ?></h3>
          <p>Total Jadwal</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-4">
        <div class="icon purple"><i class="fas fa-envelope-open-text"></i></div>
        <div class="info">
          <h3><?= isset($totalPendaftar) ? (int)$totalPendaftar : '-' ?></h3>
          <p>Pendaftaran Baru</p>
        </div>
      </div>
    </div>

    <!-- Pendaftaran Terbaru -->
    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-envelope-open-text"></i> Pendaftaran Terbaru</h3>
        <a href="index.php?page=admin-user" class="btn btn-sm btn-login">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Email</th>
              <th>Telepon</th>
              <th>Jenjang</th>
              <th>Kelas</th>
              <th>Mapel Diikuti</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($pendaftaran)): ?>
              <?php foreach ($pendaftaran as $row): ?>
                <?php
                  $kelasSekolah = trim((string)($row['kelas_sekolah'] ?? '')) !== '' ? $row['kelas_sekolah'] : 'Privat';
                  $mapelDiikuti = trim((string)($row['mapel_diikuti'] ?? '')) !== '' ? $row['mapel_diikuti'] : '-';
                  $alamat = trim((string)($row['alamat'] ?? '')) !== '' ? $row['alamat'] : '-';
                  $asalSekolah = trim((string)($row['asal_sekolah'] ?? '')) !== '' ? $row['asal_sekolah'] : '-';
                  $namaWali = trim((string)($row['nama_wali'] ?? '')) !== '' ? $row['nama_wali'] : '-';
                  $noHpWali = trim((string)($row['no_hp_wali'] ?? '')) !== '' ? $row['no_hp_wali'] : '-';
                  $catatan = trim((string)($row['catatan'] ?? '')) !== '' ? $row['catatan'] : '-';
                  $jenjang = trim((string)($row['jenjang'] ?? '')) !== '' ? $row['jenjang'] : '-';
                  $createdAt = trim((string)($row['created_at'] ?? ''));
                  $tanggalDaftar = '-';
                  if ($createdAt !== '') {
                    $timestamp = strtotime($createdAt);
                    if ($timestamp !== false) {
                      $tanggalDaftar = date('d-m-Y H:i', $timestamp);
                    }
                  }
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['nama']) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= htmlspecialchars($row['telepon']) ?></td>
                  <td><?= htmlspecialchars($jenjang) ?></td>
                  <td><?= htmlspecialchars($kelasSekolah) ?></td>
                  <td><?= htmlspecialchars($mapelDiikuti) ?></td>
                  <td><span class="badge-status badge-aktif capitalize-text"><?= htmlspecialchars($row['status']) ?></span></td>
                  <td class="text-nowrap">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-info admin-action-icon"
                      title="Lihat detail pendaftaran"
                      data-bs-toggle="modal"
                      data-bs-target="#detailPendaftaranModal"
                      data-id="<?= htmlspecialchars($row['id'] ?? '-', ENT_QUOTES) ?>"
                      data-nama="<?= htmlspecialchars($row['nama'] ?? '-', ENT_QUOTES) ?>"
                      data-email="<?= htmlspecialchars($row['email'] ?? '-', ENT_QUOTES) ?>"
                      data-telepon="<?= htmlspecialchars($row['telepon'] ?? '-', ENT_QUOTES) ?>"
                      data-jenjang="<?= htmlspecialchars($jenjang, ENT_QUOTES) ?>"
                      data-kelas="<?= htmlspecialchars($kelasSekolah, ENT_QUOTES) ?>"
                      data-asal-sekolah="<?= htmlspecialchars($asalSekolah, ENT_QUOTES) ?>"
                      data-nama-wali="<?= htmlspecialchars($namaWali, ENT_QUOTES) ?>"
                      data-no-hp-wali="<?= htmlspecialchars($noHpWali, ENT_QUOTES) ?>"
                      data-alamat="<?= htmlspecialchars($alamat, ENT_QUOTES) ?>"
                      data-catatan="<?= htmlspecialchars($catatan, ENT_QUOTES) ?>"
                      data-mapel="<?= htmlspecialchars($mapelDiikuti, ENT_QUOTES) ?>"
                      data-status="<?= htmlspecialchars($row['status'] ?? '-', ENT_QUOTES) ?>"
                      data-tanggal-daftar="<?= htmlspecialchars($tanggalDaftar, ENT_QUOTES) ?>"
                    >
                      <i class="fas fa-eye"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center empty-state-md">
                  Belum ada data pendaftaran
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<div class="modal fade" id="detailPendaftaranModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Pendaftaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="admin-detail-panel p-3">
          <h6 class="mb-3">Detail Lengkap Pendaftaran</h6>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">ID Pendaftaran</label>
              <div id="detailPendaftaranId">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Tanggal Daftar</label>
              <div id="detailPendaftaranTanggal">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Nama</label>
              <div id="detailPendaftaranNama">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Email</label>
              <div id="detailPendaftaranEmail">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Telepon</label>
              <div id="detailPendaftaranTelepon">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">No HP Wali</label>
              <div id="detailPendaftaranNoHpWali">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold mb-1">Jenjang</label>
              <div id="detailPendaftaranJenjang">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold mb-1">Kelas</label>
              <div id="detailPendaftaranKelas">-</div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold mb-1">Status</label>
              <div id="detailPendaftaranStatus">-</div>
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold mb-1">Mapel Diikuti</label>
              <div id="detailPendaftaranMapel">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Asal Sekolah</label>
              <div id="detailPendaftaranAsalSekolah">-</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold mb-1">Nama Wali</label>
              <div id="detailPendaftaranNamaWali">-</div>
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold mb-1">Alamat</label>
              <div id="detailPendaftaranAlamat" class="text-break">-</div>
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold mb-1">Catatan</label>
              <div id="detailPendaftaranCatatan" class="text-break">-</div>
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
  const modal = document.getElementById('detailPendaftaranModal');
  if (!modal) return;

  const setFieldValue = (id, value) => {
    const element = document.getElementById(id);
    if (!element) return;
    const normalized = (value || '').toString().trim();
    element.textContent = normalized !== '' ? normalized : '-';
  };

  modal.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    if (!button) return;

    setFieldValue('detailPendaftaranId', button.getAttribute('data-id'));
    setFieldValue('detailPendaftaranTanggal', button.getAttribute('data-tanggal-daftar'));
    setFieldValue('detailPendaftaranNama', button.getAttribute('data-nama'));
    setFieldValue('detailPendaftaranEmail', button.getAttribute('data-email'));
    setFieldValue('detailPendaftaranTelepon', button.getAttribute('data-telepon'));
    setFieldValue('detailPendaftaranNoHpWali', button.getAttribute('data-no-hp-wali'));
    setFieldValue('detailPendaftaranJenjang', button.getAttribute('data-jenjang'));
    setFieldValue('detailPendaftaranKelas', button.getAttribute('data-kelas'));
    setFieldValue('detailPendaftaranStatus', button.getAttribute('data-status'));
    setFieldValue('detailPendaftaranMapel', button.getAttribute('data-mapel'));
    setFieldValue('detailPendaftaranAsalSekolah', button.getAttribute('data-asal-sekolah'));
    setFieldValue('detailPendaftaranNamaWali', button.getAttribute('data-nama-wali'));
    setFieldValue('detailPendaftaranAlamat', button.getAttribute('data-alamat'));
    setFieldValue('detailPendaftaranCatatan', button.getAttribute('data-catatan'));
  });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

