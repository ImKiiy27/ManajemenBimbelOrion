<?php
/**
 * FILE: views/admin/wali_murid.php
 * TUGAS: Template HTML untuk halaman Data Wali Murid
 *
 * BAGIAN-BAGIAN:
 * 1. Header & Navbar - Navigation dan judul halaman
 * 2. Alert Messages - Tampilkan error/success
 * 3. Stats Card - Total wali murid
 * 4. Form Tambah Wali - Form untuk create wali murid baru
 * 5. Tabel Data - Display semua wali murid
 * 6. Modal Edit - Form untuk edit data wali
 * 7. Modal View Siswa - Lihat siswa yang terkait
 * 8. Modal Delete Confirmation - Konfirmasi hapus wali
 * 9. JavaScript - Event handler untuk modal
 */
require __DIR__ . '/../layouts/header.php';
?>

<div class="bg-shapes">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
</div>

<div class="dashboard-container">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>

  <main class="main-content">
    <!-- ===============================================
         BAGIAN 1: HEADER & NAVBAR
         ============================================== -->
    <?php require __DIR__ . '/../layouts/dashboard-navbar.php'; ?>

    <div class="page-header animate-fade-in">
      <h1>Data Wali Murid</h1>
      <p>Daftar seluruh wali murid terdaftar beserta informasi kontak dan siswa yang diwalinya.</p>
    </div>

    <!-- ===============================================
         BAGIAN 2: ALERT MESSAGES (Error/Success)
         ============================================== -->
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

    <!-- ===============================================
         BAGIAN 3: STATISTICS CARD
         Menampilkan total wali murid
         ============================================== -->
    <div class="stats-grid">
      <div class="stat-card animate-fade-in delay-1">
        <div class="icon blue"><i class="fas fa-users"></i></div>
        <div class="info">
          <h3><?= (int)($totalWali ?? 0) ?></h3>
          <p>Total Wali Murid</p>
        </div>
      </div>
    </div>

    <!-- ===============================================
         BAGIAN 4: FORM TAMBAH WALI MURID
         Input form untuk menambahkan wali murid baru
         ============================================== -->
    <div class="row g-4 mt-2">
      <div class="col-12">
        <div class="content-card animate-fade-in w-100">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
              <p class="text-muted mb-1">Form</p>
              <h5 class="mb-0">Tambah Wali Murid</h5>
            </div>
            <i class="fas fa-user-plus text-primary fs-4"></i>
          </div>

          <!-- FORM: POST ke controller dengan action = 'create-wali' -->
          <form method="POST" action="index.php?page=admin-wali-murid" autocomplete="off" id="createWaliForm">
            <!-- CSRF Token: Keamanan anti-CSRF attack -->
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
            <!-- ACTION: Beritahu controller ini aksi CREATE -->
            <input type="hidden" name="action" value="create-wali">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control" maxlength="100" required placeholder="Nama lengkap wali murid">
              </div>
              <div class="col-md-6">
                <label class="form-label">No. Telp</label>
                <input type="text" name="no_telp" class="form-control" maxlength="20" placeholder="Contoh: 08xxxxxxxxxx">
              </div>
              <div class="col-md-4">
                <label class="form-label">Hubungan</label>
                <input type="text" name="hubungan" class="form-control" maxlength="30" placeholder="Contoh: Ayah, Ibu">
              </div>
              <div class="col-md-4">
                <label class="form-label">Pekerjaan</label>
                <input type="text" name="pekerjaan" class="form-control" maxlength="100">
              </div>
              <div class="col-md-4">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="1" maxlength="255"></textarea>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
              <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Simpan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ===============================================
           BAGIAN 5: TABEL DATA WALI MURID
           Menampilkan semua data wali murid
           ============================================== -->
      <div class="col-12">
        <div class="content-card animate-fade-in w-100">
          <div class="card-header">
            <h3><i class="fas fa-table"></i> Tabel Data Wali Murid</h3>
            <span class="badge bg-primary">Total <?= (int)($totalWali ?? 0) ?></span>
          </div>

          <div class="table-responsive">
            <table class="table-custom">
              <thead>
                <tr>
                  <th>ID Wali</th>
                  <th>Nama</th>
                  <th>No. Telp</th>
                  <th>Hubungan</th>
                  <th>Pekerjaan</th>
                  <th>Siswa</th>
                  <th>Status User</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($waliMurid)): ?>
                  <?php foreach ($waliMurid as $row): ?>
                    <?php
                    $totalSiswa = (int)($row['total_siswa'] ?? 0);
                    ?>
                    <tr>
                      <td><?= htmlspecialchars($row['id']) ?></td>
                      <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($row['no_telp'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($row['hubungan'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($row['pekerjaan'] ?? '-') ?></td>
                      <td>
                        <span class="badge bg-info"><?= $totalSiswa ?> Siswa</span>
                      </td>
                      <td>
                        <?php if (!empty($row['email'])): ?>
                          <span class="badge bg-success">Ada</span>
                        <?php else: ?>
                          <span class="badge bg-warning text-dark">Belum Ada</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-nowrap">
                        <!-- ACTION: Edit button - Trigger modal edit -->
                        <button
                          type="button"
                          class="btn btn-sm btn-outline-primary me-1 edit-wali-btn"
                          data-bs-toggle="modal"
                          data-bs-target="#editWaliModal"
                          data-wali-id="<?= htmlspecialchars($row['id']) ?>"
                          data-nama="<?= htmlspecialchars($row['nama'] ?? '') ?>"
                          data-no-telp="<?= htmlspecialchars($row['no_telp'] ?? '') ?>"
                          data-hubungan="<?= htmlspecialchars($row['hubungan'] ?? '') ?>"
                          data-pekerjaan="<?= htmlspecialchars($row['pekerjaan'] ?? '') ?>"
                          data-alamat="<?= htmlspecialchars($row['alamat'] ?? '') ?>">
                          <i class="fas fa-pen"></i>
                        </button>

                        <!-- ACTION: View button - Trigger modal view siswa dengan AJAX -->
                        <button
                          type="button"
                          class="btn btn-sm btn-outline-info me-1 view-siswa-btn"
                          data-bs-toggle="modal"
                          data-bs-target="#viewSiswaModal"
                          data-wali-id="<?= htmlspecialchars($row['id']) ?>"
                          data-wali-nama="<?= htmlspecialchars($row['nama'] ?? '') ?>">
                          <i class="fas fa-eye"></i>
                        </button>

                        <!-- ACTION: Delete button - Trigger modal konfirmasi delete -->
                        <button
                          type="button"
                          class="btn btn-sm btn-outline-danger delete-wali-btn"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteWaliModal"
                          data-wali-id="<?= htmlspecialchars($row['id']) ?>"
                          data-nama="<?= htmlspecialchars($row['nama'] ?? '') ?>"
                          data-siswa-count="<?= $totalSiswa ?>">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8" class="text-center empty-state-md">
                      Belum ada data wali murid.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- ===============================================
     BAGIAN 6: MODAL EDIT WALI MURID
     Form untuk mengubah data wali murid
     ============================================== -->
<div class="modal fade" id="editWaliModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-primary">
        <h5 class="modal-title">
          <i class="fas fa-pen me-2"></i>Edit Data Wali Murid
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- FORM: POST ke controller dengan action = 'update-wali' -->
      <form method="POST" action="index.php?page=admin-wali-murid">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
        <!-- ACTION: Beritahu controller ini aksi UPDATE -->
        <input type="hidden" name="action" value="update-wali">
        <!-- Hidden ID: Beritahu database record mana yang diupdate -->
        <input type="hidden" name="wali_id" id="editWaliId">

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label">Nama <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nama" id="editWaliNama" maxlength="100" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">No. Telp</label>
              <input type="text" class="form-control" name="no_telp" id="editWaliNoTelp" maxlength="20" placeholder="Contoh: 08xxxxxxxxxx">
            </div>
            <div class="col-md-6">
              <label class="form-label">Hubungan</label>
              <input type="text" class="form-control" name="hubungan" id="editWaliHubungan" maxlength="30" placeholder="Contoh: Ayah, Ibu">
            </div>
            <div class="col-md-6">
              <label class="form-label">Pekerjaan</label>
              <input type="text" class="form-control" name="pekerjaan" id="editWaliPekerjaan" maxlength="100">
            </div>
            <div class="col-md-12">
              <label class="form-label">Alamat</label>
              <textarea class="form-control" name="alamat" id="editWaliAlamat" rows="2" maxlength="255"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ===============================================
     BAGIAN 7: MODAL VIEW SISWA
     Menampilkan daftar siswa yang terkait dengan wali
     Data diload via AJAX saat modal dibuka
     ============================================== -->
<div class="modal fade" id="viewSiswaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Siswa dari <span id="viewWaliNama"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Loading spinner saat AJAX fetch -->
        <div id="siswaLoading" class="text-center">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <!-- Table siswa (ditampilkan setelah AJAX selesai) -->
        <div id="siswaContainer" class="hide-element">
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>ID Siswa</th>
                  <th>Nama</th>
                  <th>Email</th>
                  <th>Kelas</th>
                </tr>
              </thead>
              <tbody id="siswaTbody">
              </tbody>
            </table>
          </div>
        </div>

        <!-- Empty state (jika tidak ada siswa) -->
        <div id="siswaEmpty" class="hide-element text-center text-muted py-4">
          <p>Tidak ada siswa untuk wali murid ini.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===============================================
     BAGIAN 8: MODAL DELETE CONFIRMATION
     Konfirmasi sebelum menghapus wali murid
     ============================================== -->
<div class="modal fade" id="deleteWaliModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-danger">
        <h5 class="modal-title text-danger">
          <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Wali Murid
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Anda akan menghapus wali murid:</p>
        <div class="alert alert-light border border-secondary">
          <strong id="deleteWaliNama"></strong>
        </div>

        <!-- Info: Wali masih memiliki siswa -->
        <div id="deleteWarning" class="hide-element alert alert-warning mb-3">
          <p class="mb-2"><strong>Wali murid ini masih memiliki <span id="siswaCounts"></span> siswa</strong></p>
          <p class="mb-0 small">
            Untuk menghapus wali murid yang masih memiliki siswa,<br>
            relasi dengan siswa akan diputuskan terlebih dahulu.
          </p>
        </div>

        <div id="safeDeleteInfo" class="hide-element alert alert-info mb-0">
          <i class="fas fa-check-circle me-2"></i>Wali murid ini tidak memiliki siswa yang terkait. Aman untuk dihapus.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <!-- FORM: POST ke controller dengan action = 'delete-wali' atau 'delete-wali-force' -->
        <form method="POST" action="index.php?page=admin-wali-murid" class="d-inline" id="deleteWaliForm">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
          <!-- ACTION: Akan diubah ke 'delete-wali' atau 'delete-wali-force' via JavaScript -->
          <input type="hidden" name="action" id="deleteAction" value="delete-wali">
          <input type="hidden" name="wali_id" id="deleteWaliId">
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash me-1"></i>Hapus Wali Murid
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===============================================
     BAGIAN 9: JAVASCRIPT
     Event handler untuk modal dan AJAX
     ============================================== -->
<script>
(() => {
  // ===============================================
  // MODAL EDIT: Populate form fields dengan data
  // Dipanggil saat user klik tombol edit
  // ===============================================
  const editWaliModal = document.getElementById('editWaliModal');
  if (editWaliModal) {
    editWaliModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      // Ambil data dari button attributes dan isi form
      document.getElementById('editWaliId').value = button.getAttribute('data-wali-id') || '';
      document.getElementById('editWaliNama').value = button.getAttribute('data-nama') || '';
      document.getElementById('editWaliNoTelp').value = button.getAttribute('data-no-telp') || '';
      document.getElementById('editWaliHubungan').value = button.getAttribute('data-hubungan') || '';
      document.getElementById('editWaliPekerjaan').value = button.getAttribute('data-pekerjaan') || '';
      document.getElementById('editWaliAlamat').value = button.getAttribute('data-alamat') || '';
    });
  }

  // ===============================================
  // MODAL VIEW SISWA: Load data via AJAX
  // Dipanggil saat user klik tombol view (mata)
  // ===============================================
  const viewSiswaModal = document.getElementById('viewSiswaModal');
  if (viewSiswaModal) {
    viewSiswaModal.addEventListener('show.bs.modal', async (event) => {
      const button = event.relatedTarget;
      const waliId = button.getAttribute('data-wali-id');
      const waliNama = button.getAttribute('data-wali-nama');

      // Set title modal dengan nama wali
      document.getElementById('viewWaliNama').textContent = waliNama;

      // Tampilkan loading, sembunyikan content
      document.getElementById('siswaLoading').classList.remove('hide-element');
      document.getElementById('siswaContainer').classList.add('hide-element');
      document.getElementById('siswaEmpty').classList.add('hide-element');

      try {
        // AJAX GET: Fetch data siswa dari controller
        const response = await fetch(`index.php?page=admin-wali-murid&action=get-siswa&wali_id=${encodeURIComponent(waliId)}`);
        const data = await response.json();

        // Sembunyikan loading
        document.getElementById('siswaLoading').classList.add('hide-element');

        // Cek apakah ada data siswa
        if (data.siswa && data.siswa.length > 0) {
          // Populate table dengan data siswa
          const tbody = document.getElementById('siswaTbody');
          tbody.innerHTML = '';
          data.siswa.forEach(siswa => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${htmlEscape(siswa.id)}</td>
              <td>${htmlEscape(siswa.nama)}</td>
              <td>${htmlEscape(siswa.email)}</td>
              <td>${htmlEscape(siswa.kelas)}</td>
            `;
            tbody.appendChild(tr);
          });
          document.getElementById('siswaContainer').classList.remove('hide-element');
        } else {
          // Tampilkan pesan empty
          document.getElementById('siswaEmpty').classList.remove('hide-element');
        }
      } catch (error) {
        console.error('Error loading siswa:', error);
        document.getElementById('siswaLoading').classList.add('hide-element');
        document.getElementById('siswaEmpty').classList.remove('hide-element');
      }
    });
  }

  // ===============================================
  // MODAL DELETE: Setup konfirmasi delete
  // Dipanggil saat user klik tombol delete (trash)
  // ===============================================
  const deleteWaliModal = document.getElementById('deleteWaliModal');
  if (deleteWaliModal) {
    deleteWaliModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      const waliId = button.getAttribute('data-wali-id');
      const waliNama = button.getAttribute('data-nama');
      const siswaCount = parseInt(button.getAttribute('data-siswa-count')) || 0;

      // Set nilai di form
      document.getElementById('deleteWaliNama').textContent = waliNama;
      document.getElementById('deleteWaliId').value = waliId;

      // Cek apakah masih ada siswa
      if (siswaCount > 0) {
        // Tampilkan warning jika masih ada siswa
        document.getElementById('deleteWarning').classList.remove('hide-element');
        document.getElementById('safeDeleteInfo').classList.add('hide-element');
        document.getElementById('siswaCounts').textContent = siswaCount;
        // Gunakan delete-force untuk putuskan relasi dulu
        document.getElementById('deleteAction').value = 'delete-wali-force';
      } else {
        // Tampilkan info aman jika tidak ada siswa
        document.getElementById('deleteWarning').classList.add('hide-element');
        document.getElementById('safeDeleteInfo').classList.remove('hide-element');
        // Gunakan delete normal
        document.getElementById('deleteAction').value = 'delete-wali';
      }
    });
  }

  // ===============================================
  // HELPER: HTML Escape
  // Untuk prevent XSS attack saat tampilkan data
  // ===============================================
  function htmlEscape(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
  }
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
