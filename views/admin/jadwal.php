<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php
$hariOptions = (isset($hariList) && is_array($hariList) && count($hariList) > 0)
  ? $hariList
  : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
?>

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
      <h1>Sistem Atur Jadwal</h1>
      <p>Kelola jadwal belajar dari relasi pengajar yang sudah dibuat di menu Atur Pengajar.</p>
    </div>

    <?php if (empty($relasiMapelAktif)): ?>
      <div class="alert alert-warning alert-custom mt-3" role="alert">
        <i class="fas fa-circle-info me-2"></i>Belum ada relasi belajar yang siap dijadwalkan. Atur dulu di menu Atur Pengajar.
      </div>
    <?php endif; ?>

    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-calendar-days"></i></div>
        <div class="info">
          <h3><?= (int)($totalJadwal ?? 0) ?></h3>
          <p>Total Jadwal</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-2">
        <div class="icon green"><i class="fas fa-calendar-day"></i></div>
        <div class="info">
          <h3><?= (int)($jadwalHariIni ?? 0) ?></h3>
          <p>Jadwal Hari <?= htmlspecialchars((string)($hariIni ?? '-')) ?></p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-3">
        <div class="icon orange"><i class="fas fa-chalkboard-user"></i></div>
        <div class="info">
          <h3><?= (int)($totalGuruTerjadwal ?? 0) ?></h3>
          <p>Guru Terjadwal</p>
        </div>
      </div>
      <div class="stat-card animate-fade-in delay-4">
        <div class="icon purple"><i class="fas fa-user-graduate"></i></div>
        <div class="info">
          <h3><?= (int)($totalSiswaTerjadwal ?? 0) ?></h3>
          <p>Siswa Terjadwal</p>
        </div>
      </div>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-plus-circle"></i> Tambah Jadwal</h3>
      </div>
      <form method="POST" action="index.php?page=admin-jadwal" class="row g-3">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
        <input type="hidden" name="action" value="create-jadwal">

        <div class="col-lg-6">
          <label class="form-label">Relasi Belajar</label>
          <select class="form-select" name="siswa_mapel_id" required <?= empty($relasiMapelAktif) ? 'disabled' : '' ?>>
            <option value=""><?= empty($relasiMapelAktif) ? 'Belum ada relasi yang siap' : 'Pilih relasi belajar aktif' ?></option>
            <?php foreach (($relasiMapelAktif ?? []) as $relasi): ?>
              <?php
                $siswaLabel = trim((string)($relasi['siswa_nama'] ?? '')) !== '' ? $relasi['siswa_nama'] : 'Belum diisi';
                $guruLabel = trim((string)($relasi['guru_nama'] ?? '')) !== '' ? $relasi['guru_nama'] : 'Belum diisi';
                $mapelSiswa = trim((string)($relasi['mata_pelajaran'] ?? '')) !== '' ? $relasi['mata_pelajaran'] : '-';
                $kelasSiswa = trim((string)($relasi['siswa_kelas'] ?? '')) !== '' ? $relasi['siswa_kelas'] : 'Privat';
              ?>
              <option value="<?= htmlspecialchars($relasi['id']) ?>">
                <?= htmlspecialchars($siswaLabel . ' (' . $kelasSiswa . ') | ' . $mapelSiswa . ' | ' . $guruLabel) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-lg-2">
          <label class="form-label">Hari</label>
          <select class="form-select" name="hari" required <?= empty($relasiMapelAktif) ? 'disabled' : '' ?>>
            <?php foreach ($hariOptions as $hari): ?>
              <option value="<?= htmlspecialchars($hari) ?>" <?= ($hari === ($hariIni ?? '')) ? 'selected' : '' ?>>
                <?= htmlspecialchars($hari) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-lg-2">
          <label class="form-label">Jam Mulai</label>
          <input type="time" class="form-control" name="jam_mulai" required <?= empty($relasiMapelAktif) ? 'disabled' : '' ?>>
        </div>

        <div class="col-lg-2">
          <label class="form-label">Jam Selesai</label>
          <input type="time" class="form-control" name="jam_selesai" required <?= empty($relasiMapelAktif) ? 'disabled' : '' ?>>
        </div>

        <div class="col-12 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary px-4" <?= empty($relasiMapelAktif) ? 'disabled' : '' ?>>
            <i class="fas fa-save me-1"></i> Simpan Jadwal
          </button>
        </div>
      </form>
    </div>

    <div class="content-card animate-fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Tabel Jadwal</h3>
        <span class="badge bg-primary">Total <?= (int)($totalJadwal ?? 0) ?></span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>ID Jadwal</th>
              <th>Hari</th>
              <th>Jam</th>
              <th>Siswa</th>
              <th>Mapel</th>
              <th>Guru</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($jadwal)): ?>
              <?php foreach ($jadwal as $row): ?>
                <?php
                  $jamMulai = substr((string)$row['jam_mulai'], 0, 5);
                  $jamSelesai = substr((string)$row['jam_selesai'], 0, 5);
                  $mapelSiswa = trim((string)($row['mata_pelajaran'] ?? '')) !== '' ? $row['mata_pelajaran'] : '-';
                  $mapelGuru = trim((string)($row['guru_mapel'] ?? '')) !== '' ? $row['guru_mapel'] : 'Privat';
                  $kelasSiswa = trim((string)($row['siswa_kelas'] ?? '')) !== '' ? $row['siswa_kelas'] : 'Privat';
                ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['hari']) ?></td>
                  <td><?= htmlspecialchars($jamMulai . ' - ' . $jamSelesai) ?></td>
                  <td><?= htmlspecialchars(($row['siswa_nama'] ?? 'Belum diisi') . ' (' . $kelasSiswa . ')') ?></td>
                  <td><?= htmlspecialchars($mapelSiswa) ?></td>
                  <td><?= htmlspecialchars($row['guru_nama'] ?? 'Belum diisi') ?></td>
                  <td class="text-nowrap">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary me-1 edit-jadwal-btn"
                      data-bs-toggle="modal"
                      data-bs-target="#editJadwalModal"
                      data-jadwal-id="<?= htmlspecialchars($row['id']) ?>"
                      data-siswa-mapel-id="<?= htmlspecialchars($row['siswa_mapel_id']) ?>"
                      data-hari="<?= htmlspecialchars($row['hari']) ?>"
                      data-jam-mulai="<?= htmlspecialchars($jamMulai) ?>"
                      data-jam-selesai="<?= htmlspecialchars($jamSelesai) ?>"
                    >
                      <i class="fas fa-pen"></i>
                    </button>

                    <form method="POST" action="index.php?page=admin-jadwal" class="d-inline" onsubmit="return confirm('Hapus jadwal ini? Jika jadwal sudah memiliki data absensi atau nilai, penghapusan akan ditolak.');">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                      <input type="hidden" name="action" value="delete-jadwal">
                      <input type="hidden" name="jadwal_id" value="<?= htmlspecialchars($row['id']) ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center empty-state-md">
                  Belum ada data jadwal. Buat relasi di menu Atur Pengajar lalu tambahkan jadwal dari relasi tersebut.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="editJadwalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Jadwal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="index.php?page=admin-jadwal">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
        <input type="hidden" name="action" value="update-jadwal">
        <input type="hidden" name="jadwal_id" id="editJadwalId">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Relasi Belajar</label>
              <select class="form-select" name="siswa_mapel_id" id="editSiswaMapelId" required>
                <?php foreach (($relasiMapelAktif ?? []) as $relasi): ?>
                  <?php
                    $siswaLabel = trim((string)($relasi['siswa_nama'] ?? '')) !== '' ? $relasi['siswa_nama'] : 'Belum diisi';
                    $guruLabel = trim((string)($relasi['guru_nama'] ?? '')) !== '' ? $relasi['guru_nama'] : 'Belum diisi';
                    $mapelSiswa = trim((string)($relasi['mata_pelajaran'] ?? '')) !== '' ? $relasi['mata_pelajaran'] : '-';
                    $kelasSiswa = trim((string)($relasi['siswa_kelas'] ?? '')) !== '' ? $relasi['siswa_kelas'] : 'Privat';
                  ?>
                  <option value="<?= htmlspecialchars($relasi['id']) ?>">
                    <?= htmlspecialchars($siswaLabel . ' (' . $kelasSiswa . ') | ' . $mapelSiswa . ' | ' . $guruLabel) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label">Hari</label>
              <select class="form-select" name="hari" id="editHari" required>
                <?php foreach ($hariOptions as $hari): ?>
                  <option value="<?= htmlspecialchars($hari) ?>"><?= htmlspecialchars($hari) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label">Jam Mulai</label>
              <input type="time" class="form-control" name="jam_mulai" id="editJamMulai" required>
            </div>

            <div class="col-md-2">
              <label class="form-label">Jam Selesai</label>
              <input type="time" class="form-control" name="jam_selesai" id="editJamSelesai" required>
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
  const editJadwalModal = document.getElementById('editJadwalModal');
  if (!editJadwalModal) return;

  editJadwalModal.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    document.getElementById('editJadwalId').value = button.getAttribute('data-jadwal-id') || '';
    document.getElementById('editSiswaMapelId').value = button.getAttribute('data-siswa-mapel-id') || '';
    document.getElementById('editHari').value = button.getAttribute('data-hari') || 'Senin';
    document.getElementById('editJamMulai').value = button.getAttribute('data-jam-mulai') || '';
    document.getElementById('editJamSelesai').value = button.getAttribute('data-jam-selesai') || '';
  });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

