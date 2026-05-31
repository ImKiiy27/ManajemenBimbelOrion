<?php
$pageTitle  = $pageTitle  ?? 'Kelola User - Bimbel Orion';
$activePage = $activePage ?? 'admin-user';
require_once __DIR__ . '/../../helpers/RoleHelper.php';
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

    <div class="row g-4 mt-2">
      <div class="col-12">
        <div class="content-card animate-fade-in w-100">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
              <p class="text-muted mb-1">Form</p>
              <h5 class="mb-0">Tambah User</h5>
            </div>
            <i class="fas fa-user-plus text-primary fs-4"></i>
          </div>
          <form method="POST" action="index.php?page=admin-user" autocomplete="off" id="createUserForm" onsubmit="return debugCreateForm()">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
            <input type="hidden" name="action" value="create">
            <div class="position-absolute opacity-0 pe-none hidden-form-fields" aria-hidden="true">
              <input type="text" tabindex="-1" autocomplete="username">
              <input type="password" tabindex="-1" autocomplete="new-password">
            </div>

            <div class="row g-3">
              <div class="col-md-5">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" autocomplete="off" autocapitalize="off" spellcheck="false" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password" required>
                <small class="text-muted">Minimal 8 karakter dengan kombinasi huruf besar, kecil, dan angka</small>
              </div>
              <div class="col-md-3">
                <label class="form-label">Role</label>
                <select class="form-select" name="role" id="createRole" required>
                  <option value="admin">Admin</option>
                  <option value="guru">Guru</option>
                  <option value="siswa">Siswa</option>
                  <option value="wali_murid">Wali Murid</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mt-1">
              <div class="col-md-6 hide-element" id="createNamaContainer">
                <label class="form-label" id="createNamaLabel">Nama</label>
                <input type="text" name="nama" id="createNama" class="form-control" maxlength="100" placeholder="Masukkan nama lengkap">
              </div>
              <div class="col-md-6 hide-element" id="createKelasContainer">
                <label class="form-label">Kelas</label>
                <input type="text" name="kelas" id="createKelas" class="form-control" maxlength="50" placeholder="Contoh: Kelas 10 / Privat">
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

      <?php
      $roles = [
        'admin' => ['label' => 'Admin', 'icon' => 'fa-shield'],
        'guru' => ['label' => 'Guru', 'icon' => 'fa-chalkboard-user'],
        'siswa' => ['label' => 'Siswa', 'icon' => 'fa-graduation-cap'],
        'wali_murid' => ['label' => 'Wali Murid', 'icon' => 'fa-users']
      ];

      foreach ($roles as $roleKey => $roleInfo):
        $usersForRole = array_filter($users ?? [], function($user) use ($roleKey) {
          return normalizeRole((string)($user['role'] ?? '')) === $roleKey;
        });
      ?>
      <div class="col-12">
        <div class="content-card animate-fade-in w-100">
          <div class="card-header">
            <h3><i class="fas <?= $roleInfo['icon'] ?>"></i> User <?= $roleInfo['label'] ?></h3>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-primary">Total <?= count($usersForRole) ?></span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table-custom" id="userTable-<?= $roleKey ?>">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Email</th>
                  <th>Status Akun</th>
                  <th>Data User</th>
                  <th>Tanggal Buat</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($usersForRole)): ?>
                  <?php foreach ($usersForRole as $user): ?>
                    <?php
                    $locked = (int)$user['is_locked'] === 1;
                    $created = !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-';
                    $normalizedRole = normalizeRole((string)($user['role'] ?? ''));
                    ?>
                    <tr data-role="<?= htmlspecialchars($normalizedRole) ?>">
                      <td><?= htmlspecialchars($user['id']) ?></td>
                      <td><?= htmlspecialchars($user['email']) ?></td>
                      <td>
                        <?php if ($locked): ?>
                          <span class="badge-status badge-terkunci">Terkunci</span>
                        <?php else: ?>
                          <span class="badge-status badge-aktif">Aktif</span>
                        <?php endif; ?>
                        <div class="small text-muted">Attempt: <?= (int)$user['attempts'] ?></div>
                      </td>
                      <td>
                        <?php
                          $hasData = false;
                          $dataType = '';
                          if (!empty($user['guru_nama'])) {
                            $hasData = true;
                            $dataType = 'Guru';
                          } elseif (!empty($user['siswa_nama'])) {
                            $hasData = true;
                            $dataType = 'Siswa';
                          } elseif (!empty($user['wali_nama'])) {
                            $hasData = true;
                            $dataType = 'Wali';
                          }
                        ?>
                        <?php if ($hasData): ?>
                          <span class="badge bg-success">Ada (<?= htmlspecialchars($dataType) ?>)</span>
                        <?php else: ?>
                          <span class="badge bg-warning text-dark">Belum Ada</span>
                        <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars($created) ?></td>
                      <td class="text-nowrap">
                        <button
                          type="button"
                          class="btn btn-sm btn-outline-primary me-1 edit-user-btn"
                          data-bs-toggle="modal"
                          data-bs-target="#editUserModal"
                          data-id="<?= htmlspecialchars($user['id']) ?>"
                          data-email="<?= htmlspecialchars($user['email']) ?>"
                          data-role="<?= htmlspecialchars($normalizedRole) ?>"
                          data-nama="<?= htmlspecialchars($user['guru_nama'] ?? $user['siswa_nama'] ?? $user['wali_nama'] ?? '') ?>"
                          data-kelas="<?= htmlspecialchars($user['kelas'] ?? '') ?>">
                          <i class="fas fa-pen"></i>
                        </button>

                        <form method="POST" action="index.php?page=admin-user" class="d-inline">
                          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                          <input type="hidden" name="action" value="unlock">
                          <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                          <button type="submit" class="btn btn-sm btn-outline-secondary me-1" <?= $locked ? '' : 'disabled' ?>>
                            <i class="fas fa-unlock"></i>
                          </button>
                        </form>

                        <?php if ($normalizedRole !== 'admin'): ?>
                          <button
                            type="button"
                            class="btn btn-sm btn-outline-danger delete-user-btn"
                            data-id="<?= htmlspecialchars($user['id']) ?>"
                            data-email="<?= htmlspecialchars($user['email']) ?>"
                            data-relasi='<?= htmlspecialchars(json_encode($userRelasi[$user['id']] ?? []), ENT_QUOTES, 'UTF-8') ?>'>
                            <i class="fas fa-trash"></i>
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">Belum ada data <?= strtolower($roleInfo['label']) ?>.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <div class="small text-muted" id="paginationInfo-<?= $roleKey ?>"></div>
            <nav aria-label="Pagination">
              <ul class="pagination pagination-sm mb-0" id="pagination-<?= $roleKey ?>"></ul>
            </nav>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </main>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-pen me-2"></i>Edit User
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="index.php?page=admin-user" id="editUserForm" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="user_id" id="editUserId">
          <input type="hidden" name="role" id="editRoleHidden">

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="editEmail" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password" id="editPassword" class="form-control" minlength="8" autocomplete="new-password">
            <small class="text-muted">Kosongkan jika password tidak ingin diubah.</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Role</label>
            <select class="form-select" id="editRole" disabled>
              <option value="admin">Admin</option>
              <option value="guru">Guru</option>
              <option value="siswa">Siswa</option>
              <option value="wali_murid">Wali Murid</option>
            </select>
            <small class="text-muted">Role tidak bisa diubah dari form edit user.</small>
          </div>

          <div class="mb-3" id="editNamaContainer">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" id="editNama" class="form-control" maxlength="100" placeholder="Masukkan nama lengkap">
          </div>

          <div class="mb-0 hide-element" id="editKelasContainer">
            <label class="form-label">Kelas</label>
            <input type="text" name="kelas" id="editKelas" class="form-control" maxlength="50" placeholder="Contoh: Kelas 10 / Privat">
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

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-danger">
        <h5 class="modal-title text-danger">
          <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus User
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Anda akan menghapus user:</p>
        <div class="alert alert-light border border-secondary">
          <strong id="deleteUserEmail"></strong>
        </div>

        <div id="relasiContainer"></div>

        <div id="noRelasiInfo" class="alert alert-info mb-0">
          <i class="fas fa-check-circle me-2"></i>User tidak memiliki relasi. Aman untuk dihapus.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <form method="POST" action="index.php?page=admin-user" class="d-inline" id="deleteUserForm">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
          <input type="hidden" name="action" id="deleteAction" value="delete">
          <input type="hidden" name="user_id" id="deleteUserId">
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash me-1"></i>Hapus User
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function debugCreateForm() {
    const form = document.getElementById('createUserForm');
    const role = form.querySelector('select[name="role"]').value;
    const email = form.querySelector('input[name="email"]').value;
    const nama = form.querySelector('input[name="nama"]').value;
    const kelas = form.querySelector('input[name="kelas"]').value;
    console.log('Form CREATE submit:', {
      email,
      role,
      nama,
      kelas
    });
    if (!role) {
      alert('ERROR: Role tidak terisi! Pilih role terlebih dahulu.');
      return false;
    }
    return true;
  }

  (() => {
    const editModal = document.getElementById('editUserModal');
    const deleteModal = document.getElementById('deleteUserModal');
    const editRoleSelect = document.getElementById('editRole');
    const editRoleHidden = document.getElementById('editRoleHidden');
    const createRoleSelect = document.getElementById('createRole');
    const createNamaContainer = document.getElementById('createNamaContainer');
    const createNamaLabel = document.getElementById('createNamaLabel');
    const createNamaInput = document.getElementById('createNama');
    const createKelasInput = document.getElementById('createKelas');
    const createKelasContainer = document.getElementById('createKelasContainer');

    const roles = ['admin', 'guru', 'siswa', 'wali_murid'];
    const pageSize = 8;
    const paginationState = {};

    roles.forEach(role => {
      paginationState[role] = 1;
    });

    const syncCreateRoleFields = () => {
      if (!createRoleSelect || !createNamaContainer || !createNamaLabel || !createKelasContainer || !createKelasInput || !createNamaInput) {
        return;
      }

      const role = createRoleSelect.value;
      const needsProfileName = role === 'guru' || role === 'siswa' || role === 'wali_murid';
      const needsKelas = role === 'siswa';
      const namaLabelMap = {
        guru: 'Nama Guru',
        siswa: 'Nama Siswa',
        wali_murid: 'Nama Wali Murid',
        admin: 'Nama'
      };

      needsProfileName ? createNamaContainer.classList.remove('hide-element') : createNamaContainer.classList.add('hide-element');
      createNamaLabel.textContent = namaLabelMap[role] || 'Nama';
      createNamaInput.required = needsProfileName;
      needsKelas ? createKelasContainer.classList.remove('hide-element') : createKelasContainer.classList.add('hide-element');

      if (!needsProfileName) {
        createNamaInput.value = '';
      }

      if (!needsKelas) {
        createKelasInput.value = '';
      }
    };

    const renderPaginationForRole = (role, totalPages) => {
      const pagination = document.getElementById(`pagination-${role}`);
      if (!pagination) return;
      pagination.innerHTML = '';
      if (totalPages <= 1) return;

      const currentPage = paginationState[role];

      const addItem = (label, page, disabled = false, active = false) => {
        const li = document.createElement('li');
        li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.dataset.page = page;
        a.dataset.role = role;
        a.textContent = label;
        li.appendChild(a);
        pagination.appendChild(li);
      };

      addItem('<', Math.max(1, currentPage - 1), currentPage === 1);
      for (let i = 1; i <= totalPages; i++) {
        addItem(i, i, false, i === currentPage);
      }
      addItem('>', Math.min(totalPages, currentPage + 1), currentPage === totalPages);
    };

    const renderTableForRole = (role) => {
      const tableId = `userTable-${role}`;
      const table = document.getElementById(tableId);
      if (!table) return;

      const tableBody = table.querySelector('tbody');
      if (!tableBody) return;

      const allRows = Array.from(tableBody.querySelectorAll('tr[data-role]'));
      let emptyRow = tableBody.querySelector('tr[data-empty]');

      const currentPage = paginationState[role];
      const total = allRows.length;
      const totalPages = Math.max(1, Math.ceil(total / pageSize));

      if (currentPage > totalPages) {
        paginationState[role] = totalPages;
      }

      allRows.forEach(row => {
        row.style.display = 'none';
      });

      if (total === 0) {
        if (!emptyRow) {
          emptyRow = document.createElement('tr');
          emptyRow.setAttribute('data-empty', 'true');
          const td = document.createElement('td');
          td.colSpan = 6;
          td.className = 'text-center text-muted py-4';
          td.textContent = `Belum ada data ${role === 'wali_murid' ? 'wali murid' : role}.`;
          emptyRow.appendChild(td);
          tableBody.appendChild(emptyRow);
        }
        emptyRow.style.display = '';
        const pagination = document.getElementById(`pagination-${role}`);
        if (pagination) pagination.innerHTML = '';
        const paginationInfo = document.getElementById(`paginationInfo-${role}`);
        if (paginationInfo) paginationInfo.textContent = '0 data';
        return;
      }

      if (emptyRow) emptyRow.style.display = 'none';

      const start = (currentPage - 1) * pageSize;
      const end = start + pageSize;
      allRows.slice(start, end).forEach(row => {
        row.style.display = '';
      });

      renderPaginationForRole(role, totalPages);
      const paginationInfo = document.getElementById(`paginationInfo-${role}`);
      if (paginationInfo) {
        paginationInfo.textContent = `Menampilkan ${start + 1}-${Math.min(end, total)} dari ${total} data`;
      }
    };

    // Delete user modal handling
    const deleteUserButtons = document.querySelectorAll('.delete-user-btn');
    const deleteUserEmail = document.getElementById('deleteUserEmail');
    const deleteUserId = document.getElementById('deleteUserId');
    const deleteUserForm = document.getElementById('deleteUserForm');
    const relasiContainer = document.getElementById('relasiContainer');
    const noRelasiInfo = document.getElementById('noRelasiInfo');

    deleteUserButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const userId = btn.getAttribute('data-id');
        const email = btn.getAttribute('data-email');
        const relasi = JSON.parse(btn.getAttribute('data-relasi') || '{}');

        deleteUserEmail.textContent = email;
        deleteUserId.value = userId;

        if (Object.keys(relasi).length === 0) {
          relasiContainer.innerHTML = '';
          noRelasiInfo.classList.remove('hide-element');
          document.getElementById('deleteAction').value = 'delete';
        } else {
          noRelasiInfo.classList.add('hide-element');
          let html = '<div class="alert alert-warning mb-3">';
          html += '<p class="mb-2"><strong>Relasi yang akan dihapus:</strong></p>';
          html += '<ul class="mb-0">';

          if (relasi.kelas) {
            html += `<li>${relasi.kelas.length} Kelas`;
            if (relasi.kelas.length > 0 && relasi.kelas[0].guru_nama) {
              html += ': ' + relasi.kelas.map(k => k.guru_nama || k.siswa_nama).join(', ');
            }
            html += '</li>';
          }

          if (relasi.siswa_mapel) {
            html += `<li>${relasi.siswa_mapel.length} Mapel: ` + relasi.siswa_mapel.map(m => m.mapel_nama).join(', ') + '</li>';
          }

          if (relasi.anak) {
            html += `<li>${relasi.anak.length} Anak: ` + relasi.anak.map(a => a.siswa_nama).join(', ') + '</li>';
          }

          html += '</ul></div>';
          relasiContainer.innerHTML = html;
          document.getElementById('deleteAction').value = 'delete-force';
        }

        const modal = new bootstrap.Modal(deleteModal);
        modal.show();
      });
    });

    if (editModal) {
      editModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const role = button.getAttribute('data-role') || '';

        console.log('Edit modal opened with role:', role);

        document.getElementById('editUserId').value = button.getAttribute('data-id') || '';
        document.getElementById('editEmail').value = button.getAttribute('data-email') || '';
        document.getElementById('editPassword').value = '';
        document.getElementById('editNama').value = button.getAttribute('data-nama') || '';
        document.getElementById('editKelas').value = button.getAttribute('data-kelas') || '';

        editRoleSelect.value = role;
        editRoleHidden.value = role;

        const namaContainer = document.getElementById('editNamaContainer');
        const kelasContainer = document.getElementById('editKelasContainer');
        if (namaContainer) {
          role === 'admin' ? namaContainer.classList.add('hide-element') : namaContainer.classList.remove('hide-element');
        }
        if (kelasContainer) {
          role === 'siswa' ? kelasContainer.classList.remove('hide-element') : kelasContainer.classList.add('hide-element');
        }
      });

      const editForm = document.getElementById('editUserForm');
      if (editForm) {
        editForm.addEventListener('submit', (e) => {
          const roleValue = editRoleHidden.value;
          console.log('Form submitted with role:', roleValue);
          if (!roleValue) {
            e.preventDefault();
            alert('Role tidak terisi. Muat ulang halaman dan coba lagi.');
          }
        });
      }
    }

    // Pagination click handler untuk semua role
    document.addEventListener('click', (e) => {
      if (e.target.tagName.toLowerCase() === 'a' && e.target.dataset.page && e.target.dataset.role) {
        e.preventDefault();
        const role = e.target.dataset.role;
        const page = parseInt(e.target.dataset.page, 10);
        paginationState[role] = page;
        renderTableForRole(role);
      }
    });

    if (createRoleSelect) {
      createRoleSelect.addEventListener('change', syncCreateRoleFields);
      syncCreateRoleFields();
    }

    roles.forEach(role => {
      renderTableForRole(role);
    });
  })();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
