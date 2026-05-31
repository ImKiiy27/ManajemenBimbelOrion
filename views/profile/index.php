<?php
require __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../helpers/RoleHelper.php';

$role = normalizeRole((string)($role ?? ($_SESSION['role'] ?? '')));
$profile = $profile ?? [];
$stats = $stats ?? [];
$displayName = trim((string)($profile['nama'] ?? $_SESSION['nama'] ?? 'Pengguna'));
$email = (string)($profile['email'] ?? $_SESSION['email'] ?? '-');
$initial = strtoupper(substr($displayName !== '' ? $displayName : 'U', 0, 1));
$isLocked = (int)($profile['is_locked'] ?? 0) === 1;
$statusLabel = $isLocked ? 'Terkunci' : 'Aktif';

function profileValue(mixed $value, string $fallback = '-'): string
{
  $text = trim((string)($value ?? ''));
  return $text !== '' ? $text : $fallback;
}

function profileDate(mixed $value): string
{
  $text = trim((string)($value ?? ''));
  if ($text === '') {
    return '-';
  }
  $timestamp = strtotime($text);
  return $timestamp ? date('d M Y', $timestamp) : $text;
}

$roleCopy = match ($role) {
  'admin' => 'Kelola identitas akun admin, status akses, dan ringkasan operasional sistem.',
  'guru' => 'Pantau biodata pengajar, jadwal mengajar, dan ringkasan kelas aktif.',
  'siswa' => 'Lihat biodata siswa, program belajar, mapel aktif, nilai, dan kehadiran.',
  'wali_murid' => 'Lihat biodata wali murid dan ringkasan pantauan anak yang terhubung.',
  default => 'Lihat informasi akun dan ringkasan aktivitas pengguna.',
};

$detailItems = match ($role) {
  'guru' => [
    'Nama Lengkap' => $displayName,
    'ID Guru' => profileValue($profile['id'] ?? ''),
    'Email' => $email,
    'Nomor HP' => profileValue($profile['no_telp'] ?? ''),
    'Mata Pelajaran' => profileValue($profile['mapel_nama'] ?? ''),
    'Alamat' => profileValue($profile['alamat'] ?? ''),
    'Bio' => profileValue($profile['bio'] ?? 'Belum ada bio.'),
    'Status Akun' => $statusLabel,
  ],
  'siswa' => [
    'Nama Lengkap' => $displayName,
    'ID Siswa' => profileValue($profile['id'] ?? ''),
    'Email' => $email,
    'Nomor HP' => profileValue($profile['no_telp'] ?? ''),
    'Kelas Sekolah' => profileValue($profile['kelas_sekolah'] ?? ''),
    'Asal Sekolah' => profileValue($profile['asal_sekolah'] ?? ''),
    'Wali Murid' => profileValue($profile['wali_nama'] ?? ''),
    'Alamat' => profileValue($profile['alamat'] ?? ''),
  ],
  'wali_murid' => [
    'Nama Lengkap' => $displayName,
    'ID Wali' => profileValue($profile['id'] ?? ''),
    'Email' => $email,
    'Nomor HP' => profileValue($profile['no_telp'] ?? ''),
    'Hubungan' => profileValue($profile['hubungan'] ?? ''),
    'Pekerjaan' => profileValue($profile['pekerjaan'] ?? ''),
    'Alamat' => profileValue($profile['alamat'] ?? ''),
    'Status Akun' => $statusLabel,
  ],
  default => [
    'Nama Lengkap' => $displayName,
    'ID Pengguna' => profileValue($profile['id'] ?? ''),
    'Email' => $email,
    'Role' => roleLabel($role),
    'Status Akun' => $statusLabel,
    'Percobaan Login' => (string)(int)($profile['attempts'] ?? 0),
    'Bergabung Sejak' => profileDate($profile['created_at'] ?? ''),
  ],
};

$statCards = match ($role) {
  'admin' => [
    ['icon' => 'fa-users-gear', 'label' => 'Total User', 'value' => (int)($stats['total_user'] ?? 0), 'tone' => 'blue'],
    ['icon' => 'fa-chalkboard-user', 'label' => 'Guru', 'value' => (int)($stats['total_guru'] ?? 0), 'tone' => 'green'],
    ['icon' => 'fa-user-graduate', 'label' => 'Siswa', 'value' => (int)($stats['total_siswa'] ?? 0), 'tone' => 'orange'],
    ['icon' => 'fa-calendar-days', 'label' => 'Jadwal', 'value' => (int)($stats['total_jadwal'] ?? 0), 'tone' => 'purple'],
  ],
  'guru' => [
    ['icon' => 'fa-calendar-check', 'label' => 'Jadwal', 'value' => (int)($stats['total_jadwal'] ?? 0), 'tone' => 'blue'],
    ['icon' => 'fa-people-group', 'label' => 'Kelas', 'value' => (int)($stats['total_kelas'] ?? 0), 'tone' => 'green'],
    ['icon' => 'fa-user-graduate', 'label' => 'Siswa', 'value' => (int)($stats['total_siswa'] ?? 0), 'tone' => 'orange'],
    ['icon' => 'fa-clipboard-check', 'label' => 'Kehadiran', 'value' => ((int)($stats['kehadiran_persen'] ?? 0)) . '%', 'tone' => 'purple'],
  ],
  'siswa' => [
    ['icon' => 'fa-book-open', 'label' => 'Mapel Aktif', 'value' => (int)($stats['total_mapel'] ?? 0), 'tone' => 'blue'],
    ['icon' => 'fa-calendar-days', 'label' => 'Jadwal', 'value' => (int)($stats['total_jadwal'] ?? 0), 'tone' => 'green'],
    ['icon' => 'fa-chart-line', 'label' => 'Rata Nilai', 'value' => (int)($stats['rata_nilai'] ?? 0), 'tone' => 'orange'],
    ['icon' => 'fa-clipboard-check', 'label' => 'Kehadiran', 'value' => ((int)($stats['kehadiran_persen'] ?? 0)) . '%', 'tone' => 'purple'],
  ],
  'wali_murid' => [
    ['icon' => 'fa-children', 'label' => 'Anak', 'value' => (int)($stats['total_anak'] ?? 0), 'tone' => 'blue'],
    ['icon' => 'fa-chart-line', 'label' => 'Rata Nilai', 'value' => (int)($stats['rata_nilai'] ?? 0), 'tone' => 'green'],
    ['icon' => 'fa-clipboard-check', 'label' => 'Kehadiran', 'value' => ((int)($stats['kehadiran_persen'] ?? 0)) . '%', 'tone' => 'orange'],
    ['icon' => 'fa-shield-heart', 'label' => 'Status', 'value' => $statusLabel, 'tone' => 'purple'],
  ],
  default => [],
};
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

    <section class="profile-hero animate-fade-in">
      <div>
        <span class="profile-kicker"><i class="fas fa-circle-user"></i> Profil <?= htmlspecialchars(roleLabel($role)) ?></span>
        <h1>Profil Saya</h1>
        <p><?= htmlspecialchars($roleCopy) ?></p>
      </div>
      <div class="profile-actions">
        <a href="#form-profil" class="btn btn-outline-primary btn-sm"><i class="fas fa-pen me-2"></i>Edit Profil</a>
        <a href="#form-password" class="btn btn-outline-secondary btn-sm"><i class="fas fa-lock me-2"></i>Ganti Password</a>
      </div>
    </section>
    
    <?php if (!empty($flash) && is_array($flash)): ?>
      <section class="animate-fade-in">
        <div class="alert <?= (($flash['type'] ?? '') === 'success') ? 'alert-success' : 'alert-danger' ?>" role="alert">
          <?= htmlspecialchars((string)($flash['message'] ?? '')) ?>
        </div>
      </section>
    <?php endif; ?>

    <section class="profile-layout animate-fade-in delay-1">
      <aside class="profile-panel profile-identity">
        <div class="profile-avatar">
          <?php if (!empty($profile['foto_path'])): ?>
            <img src="<?= htmlspecialchars((string)$profile['foto_path']) ?>" alt="Foto profil">
          <?php else: ?>
            <span><?= htmlspecialchars($initial) ?></span>
          <?php endif; ?>
        </div>
        <h2><?= htmlspecialchars($displayName) ?></h2>
        <p><?= htmlspecialchars($email) ?></p>
        <span class="profile-role-badge"><?= htmlspecialchars(roleLabel($role)) ?></span>
        <div class="profile-meta-list">
          <div><span>ID Akun</span><strong><?= htmlspecialchars(profileValue($profile['id'] ?? '')) ?></strong></div>
          <div><span>Bergabung</span><strong><?= htmlspecialchars(profileDate($profile['created_at'] ?? '')) ?></strong></div>
        </div>
      </aside>

      <div class="profile-main">
        <section class="profile-panel">
          <div class="profile-section-head">
            <h3><i class="fas fa-id-card"></i> Biodata</h3>
            <span class="badge-status <?= $isLocked ? 'badge-terkunci' : 'badge-aktif' ?>"><?= htmlspecialchars($statusLabel) ?></span>
          </div>
          <div class="profile-detail-grid">
            <?php foreach ($detailItems as $label => $value): ?>
              <div class="profile-detail-item">
                <span><?= htmlspecialchars($label) ?></span>
                <strong><?= htmlspecialchars(profileValue($value)) ?></strong>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <section id="form-profil" class="profile-panel">
          <div class="profile-section-head">
            <h3><i class="fas fa-user-pen"></i> Ubah Profil</h3>
          </div>
          <form method="post" action="index.php?page=<?= htmlspecialchars($activePage) ?>">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token ?? getCsrfToken()) ?>">
            <input type="hidden" name="action" value="update-profile">

            <div class="profile-detail-grid">
              <?php if ($role !== 'admin'): ?>
                <div class="profile-detail-item">
                  <span>Nama Lengkap (Boleh diubah)</span>
                  <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars((string)($profile['nama'] ?? '')) ?>" required>
                </div>
              <?php endif; ?>
              <div class="profile-detail-item">
                <span>Email (Boleh diubah)</span>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" required>
              </div>

              <?php if ($role === 'guru' || $role === 'siswa' || $role === 'wali_murid'): ?>
                <div class="profile-detail-item">
                  <span>No. Telepon (Boleh diubah)</span>
                  <input type="text" class="form-control" name="no_telp" value="<?= htmlspecialchars((string)($profile['no_telp'] ?? '')) ?>">
                </div>
                <div class="profile-detail-item">
                  <span>Alamat (Boleh diubah)</span>
                  <input type="text" class="form-control" name="alamat" value="<?= htmlspecialchars((string)($profile['alamat'] ?? '')) ?>">
                </div>
              <?php endif; ?>

              <?php if ($role === 'guru'): ?>
                <div class="profile-detail-item">
                  <span>Mata Pelajaran (Tidak bisa diubah user)</span>
                  <input type="text" class="form-control" value="<?= htmlspecialchars((string)($profile['mapel_nama'] ?? '-')) ?>" disabled>
                </div>
                <div class="profile-detail-item">
                  <span>Bio (Boleh diubah)</span>
                  <textarea class="form-control" name="bio" rows="3"><?= htmlspecialchars((string)($profile['bio'] ?? '')) ?></textarea>
                </div>
              <?php elseif ($role === 'siswa'): ?>
                <div class="profile-detail-item">
                  <span>Kelas Sekolah (Tidak bisa diubah user)</span>
                  <input type="text" class="form-control" value="<?= htmlspecialchars((string)($profile['kelas_sekolah'] ?? '-')) ?>" disabled>
                </div>
                <div class="profile-detail-item">
                  <span>Wali Murid (Tidak bisa diubah user)</span>
                  <input type="text" class="form-control" value="<?= htmlspecialchars((string)($profile['wali_nama'] ?? '-')) ?>" disabled>
                </div>
                <div class="profile-detail-item">
                  <span>Asal Sekolah (Boleh diubah)</span>
                  <input type="text" class="form-control" name="asal_sekolah" value="<?= htmlspecialchars((string)($profile['asal_sekolah'] ?? '')) ?>">
                </div>
              <?php elseif ($role === 'wali_murid'): ?>
                <div class="profile-detail-item">
                  <span>Hubungan (Boleh diubah)</span>
                  <input type="text" class="form-control" name="hubungan" value="<?= htmlspecialchars((string)($profile['hubungan'] ?? '')) ?>">
                </div>
                <div class="profile-detail-item">
                  <span>Pekerjaan (Boleh diubah)</span>
                  <input type="text" class="form-control" name="pekerjaan" value="<?= htmlspecialchars((string)($profile['pekerjaan'] ?? '')) ?>">
                </div>
              <?php else: ?>
                <div class="profile-detail-item">
                  <span>Role (Tidak bisa diubah user)</span>
                  <input type="text" class="form-control" value="<?= htmlspecialchars(roleLabel($role)) ?>" disabled>
                </div>
                <div class="profile-detail-item">
                  <span>Status Akun (Tidak bisa diubah user)</span>
                  <input type="text" class="form-control" value="<?= htmlspecialchars($statusLabel) ?>" disabled>
                </div>
              <?php endif; ?>
            </div>

            <div class="mt-3">
              <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Profil</button>
            </div>
          </form>
        </section>

        <section id="form-password" class="profile-panel">
          <div class="profile-section-head">
            <h3><i class="fas fa-key"></i> Ganti Password</h3>
          </div>
          <form method="post" action="index.php?page=<?= htmlspecialchars($activePage) ?>">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf_token ?? getCsrfToken()) ?>">
            <input type="hidden" name="action" value="change-password">
            <div class="profile-detail-grid">
              <div class="profile-detail-item">
                <span>Password Saat Ini</span>
                <input type="password" class="form-control" name="current_password" required>
              </div>
              <div class="profile-detail-item">
                <span>Password Baru</span>
                <input type="password" class="form-control" name="new_password" minlength="8" required>
              </div>
              <div class="profile-detail-item">
                <span>Konfirmasi Password Baru</span>
                <input type="password" class="form-control" name="confirm_password" minlength="8" required>
              </div>
            </div>
            <small class="text-muted">Password minimal 8 karakter, wajib ada huruf besar, huruf kecil, dan angka.</small>
            <div class="mt-3">
              <button type="submit" class="btn btn-outline-primary"><i class="fas fa-lock me-2"></i>Update Password</button>
            </div>
          </form>
        </section>

        <section class="profile-stats-grid">
          <?php foreach ($statCards as $card): ?>
            <div class="profile-stat-card">
              <div class="icon <?= htmlspecialchars($card['tone']) ?>"><i class="fas <?= htmlspecialchars($card['icon']) ?>"></i></div>
              <div>
                <strong><?= htmlspecialchars((string)$card['value']) ?></strong>
                <span><?= htmlspecialchars($card['label']) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </section>
      </div>
    </section>

    <?php if ($role === 'admin'): ?>
      <section class="profile-bottom-grid animate-fade-in delay-2">
        <div class="profile-panel">
          <div class="profile-section-head"><h3><i class="fas fa-shield-halved"></i> Ringkasan Akses</h3></div>
          <?php
            $accessRows = [
              ['Manajemen User', 100],
              ['Akademik', 100],
              ['Jadwal & Absensi', 100],
              ['Laporan Nilai', 100],
            ];
          ?>
          <div class="profile-progress-list">
            <?php foreach ($accessRows as [$label, $percent]): ?>
              <div class="profile-progress-row">
                <span><?= htmlspecialchars($label) ?></span>
                <div class="profile-progress"><span style="width: <?= (int)$percent ?>%"></span></div>
                <strong><?= (int)$percent ?>%</strong>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="profile-panel">
          <div class="profile-section-head"><h3><i class="fas fa-clock-rotate-left"></i> User Terbaru</h3></div>
          <div class="profile-mini-table">
            <?php foreach (($recentUsers ?? []) as $user): ?>
              <div>
                <span><?= htmlspecialchars(profileValue($user['nama'] ?? '')) ?></span>
                <strong><?= htmlspecialchars(roleLabel((string)($user['role'] ?? ''))) ?> - <?= htmlspecialchars(profileDate($user['created_at'] ?? '')) ?></strong>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php elseif ($role === 'guru'): ?>
      <section class="profile-panel animate-fade-in delay-2">
        <div class="profile-section-head"><h3><i class="fas fa-calendar-days"></i> Jadwal Mengajar Ringkas</h3></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>Hari</th><th>Waktu</th><th>Siswa</th><th>Kelas</th><th>Mapel</th></tr></thead>
            <tbody>
              <?php foreach (($schedule ?? []) as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['hari']) ?></td>
                  <td><?= htmlspecialchars(substr((string)$row['jam_mulai'], 0, 5) . ' - ' . substr((string)$row['jam_selesai'], 0, 5)) ?></td>
                  <td><?= htmlspecialchars($row['siswa_nama']) ?></td>
                  <td><?= htmlspecialchars($row['kelas_sekolah']) ?></td>
                  <td><?= htmlspecialchars($row['mapel_nama'] ?? '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($schedule)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada jadwal mengajar.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php elseif ($role === 'siswa'): ?>
      <section class="profile-panel animate-fade-in delay-2">
        <div class="profile-section-head"><h3><i class="fas fa-book-open-reader"></i> Mapel dan Progress Ringkas</h3></div>
        <div class="profile-progress-list">
          <?php foreach (($subjects ?? []) as $index => $subject): ?>
            <?php $percent = min(95, 70 + (($index + 1) * 6)); ?>
            <div class="profile-progress-row">
              <span><?= htmlspecialchars($subject['nama']) ?></span>
              <div class="profile-progress"><span style="width: <?= $percent ?>%"></span></div>
              <strong><?= $percent ?>%</strong>
            </div>
          <?php endforeach; ?>
          <?php if (empty($subjects)): ?>
            <p class="empty-state-md mb-0">Belum ada mapel aktif.</p>
          <?php endif; ?>
        </div>
      </section>
    <?php elseif ($role === 'wali_murid'): ?>
      <section class="profile-panel animate-fade-in delay-2">
        <div class="profile-section-head"><h3><i class="fas fa-children"></i> Data Anak</h3></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>Nama Anak</th><th>Kelas</th><th>Mapel Aktif</th></tr></thead>
            <tbody>
              <?php foreach (($children ?? []) as $child): ?>
                <tr>
                  <td><?= htmlspecialchars($child['nama']) ?></td>
                  <td><?= htmlspecialchars($child['kelas_sekolah']) ?></td>
                  <td><?= htmlspecialchars($child['mapel']) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($children)): ?>
                <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data anak terhubung.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>
  </main>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
