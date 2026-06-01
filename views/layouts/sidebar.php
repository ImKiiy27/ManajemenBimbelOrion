<?php
// views/layouts/sidebar.php
// Dipanggil dari view dashboard dengan variabel $role & $activePage
require_once __DIR__ . '/../../helpers/RoleHelper.php';

$roleFromView = (string)($current_user_role ?? '');
$roleSession = (string)($_SESSION['role'] ?? '');
$role = normalizeRole($roleFromView !== '' ? $roleFromView : $roleSession);
$activePage = $activePage        ?? '';

$menus = match ($role) {
  'admin' => [
    ['page' => 'admin-dashboard', 'icon' => 'fa-gauge',          'label' => 'Dashboard'],
    ['page' => 'admin-mapel',     'icon' => 'fa-book-open',      'label' => 'Kelola Mapel'],
    ['page' => 'admin-user',      'icon' => 'fa-users-gear',     'label' => 'Kelola User'],
    [
      'label' => 'Data',
      'icon' => 'fa-database',
      'submenu' => [
        ['page' => 'admin-guru',      'label' => 'Data Guru', 'icon' => 'fa-chalkboard-user'],
        ['page' => 'admin-siswa',     'label' => 'Data Siswa', 'icon' => 'fa-user-graduate'],
        ['page' => 'admin-wali-murid', 'label' => 'Data Wali Murid', 'icon' => 'fa-users'],
      ]
    ],
    ['page' => 'admin-relasi',    'icon' => 'fa-diagram-project', 'label' => 'Atur Pengajar'],
    ['page' => 'admin-jadwal',    'icon' => 'fa-calendar-days',  'label' => 'Jadwal'],
    ['page' => 'admin-absensi',   'icon' => 'fa-clipboard-list', 'label' => 'Absensi'],
    ['page' => 'admin-nilai',     'icon' => 'fa-chart-bar',      'label' => 'Nilai'],
    ['page' => 'admin-profil',    'icon' => 'fa-circle-user',    'label' => 'Profil'],
  ],
  'guru' => [
    ['page' => 'guru-dashboard', 'icon' => 'fa-gauge',          'label' => 'Dashboard'],
    ['page' => 'guru-jadwal',    'icon' => 'fa-calendar-days',  'label' => 'Jadwal Mengajar'],
    ['page' => 'guru-absensi',   'icon' => 'fa-clipboard-list', 'label' => 'Input Absensi'],
    ['page' => 'guru-nilai',     'icon' => 'fa-chart-bar',      'label' => 'Input Nilai'],
    ['page' => 'guru-profil',    'icon' => 'fa-circle-user',    'label' => 'Profil'],
  ],
  'siswa' => [
    ['page' => 'siswa-dashboard', 'icon' => 'fa-gauge',         'label' => 'Dashboard'],
    ['page' => 'siswa-jadwal',    'icon' => 'fa-calendar-days', 'label' => 'Jadwal Les'],
    ['page' => 'siswa-absensi',   'icon' => 'fa-clipboard-list', 'label' => 'Absensi'],
    ['page' => 'siswa-nilai',     'icon' => 'fa-chart-bar',     'label' => 'Nilai'],
    ['page' => 'siswa-profil',    'icon' => 'fa-circle-user',   'label' => 'Profil'],
  ],
  'wali_murid' => [
    ['page' => 'wali-dashboard', 'icon' => 'fa-gauge',         'label' => 'Dashboard'],
    ['page' => 'wali-jadwal',    'icon' => 'fa-calendar-days', 'label' => 'Jadwal Anak'],
    ['page' => 'wali-nilai',     'icon' => 'fa-chart-bar',     'label' => 'Nilai Anak'],
    ['page' => 'wali-absensi',   'icon' => 'fa-clipboard-list', 'label' => 'Absensi Anak'],
    ['page' => 'wali-profil',    'icon' => 'fa-circle-user',   'label' => 'Profil'],
  ],
  default => [],
};

?>

<div class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="logo-icon"><img src="public/image/logo-bimbel-orion.jpg" alt="Logo Bimbel Orion" style="width: 100%; height: 100%; object-fit: contain;"></div>
    <span class="logo-text">Bimbel Orion</span>
  </div>

  <div class="sidebar-menu">
    <h6>Menu Utama</h6>
    <?php foreach ($menus as $menu): ?>
      <?php if (isset($menu['submenu'])): ?>
        <div class="sidebar-dropdown" id="dropdown-<?= md5($menu['label']) ?>">
          <button type="button" class="sidebar-dropdown-toggle">
            <span class="dropdown-content">
              <i class="fas <?= htmlspecialchars($menu['icon']) ?>"></i>
              <?= htmlspecialchars($menu['label']) ?>
            </span>
            <i class="fas fa-chevron-down dropdown-arrow"></i>
          </button>
          <div class="sidebar-dropdown-menu">
            <div>
              <?php foreach ($menu['submenu'] as $submenu): ?>
                <a href="index.php?page=<?= htmlspecialchars($submenu['page']) ?>"
                  class="sidebar-dropdown-item <?= $activePage === $submenu['page'] ? 'active' : '' ?>">
                  <?php if (isset($submenu['icon'])): ?>
                    <i class="fas <?= htmlspecialchars($submenu['icon']) ?>"></i>
                  <?php endif; ?>
                  <?= htmlspecialchars($submenu['label']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php else: ?>
        <a href="index.php?page=<?= htmlspecialchars($menu['page']) ?>"
          class="<?= $activePage === $menu['page'] ? 'active' : '' ?>">
          <?php if (($menu['icon'] ?? '') === 'fa-book-open'): ?>
            <img src="public/image/logo-bimbel-orion.jpg" alt="Logo Bimbel Orion" style="width: 16px; height: 16px; object-fit: contain;">
          <?php else: ?>
            <i class="fas <?= htmlspecialchars($menu['icon']) ?>"></i>
          <?php endif; ?>
          <?= htmlspecialchars($menu['label']) ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const dropdownToggles = document.querySelectorAll('.sidebar-dropdown-toggle');

  dropdownToggles.forEach((button) => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      const dropdown = this.closest('.sidebar-dropdown');
      if (!dropdown) return;

      const isOpen = dropdown.classList.contains('open');

      // Close all dropdowns first
      document.querySelectorAll('.sidebar-dropdown').forEach(item => {
        item.classList.remove('open');
      });

      // Only open this dropdown if it was closed before
      if (!isOpen) {
        dropdown.classList.add('open');
      }
    });
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    // Check if click is outside sidebar
    const sidebar = document.querySelector('.sidebar');
    if (sidebar && !sidebar.contains(e.target)) {
      document.querySelectorAll('.sidebar-dropdown').forEach(item => {
        item.classList.remove('open');
      });
    }
  });

  // Auto-open dropdown if any submenu item is active
  const activeItem = document.querySelector('.sidebar-dropdown-item.active');
  if (activeItem) {
    const dropdown = activeItem.closest('.sidebar-dropdown');
    if (dropdown) {
      dropdown.classList.add('open');
    }
  }
});
</script>

