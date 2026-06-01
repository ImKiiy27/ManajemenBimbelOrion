<?php
$pageTitle = 'Pendaftaran - Bimbel Orion';
require __DIR__ . '/../layouts/header.php';
$mapelOptions = $mapelOptions ?? [];
$oldInput = is_array($oldInput ?? null) ? $oldInput : [];
$selectedMapelIds = isset($oldInput['mapel_ids']) && is_array($oldInput['mapel_ids'])
  ? array_map('strval', $oldInput['mapel_ids'])
  : [];
?>

<div class="bg-animation">
  <div class="floating-shape shape-1"></div>
  <div class="floating-shape shape-2"></div>
</div>

<nav class="navbar navbar-expand-lg navbar-light fixed-top" id="navbar">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <div class="logo-icon small-icon-wrapper"><img src="public/image/logo-bimbel-orion.jpg" alt="Logo Bimbel Orion" style="width: 100%; height: 100%; object-fit: contain;"></div>
      <span class="brand-text">Bimbel Orion</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
        <li class="nav-item ms-3">
          <button class="theme-toggle small-icon-wrapper" id="themeToggle" aria-label="Toggle theme">
            <i class="fas fa-moon"></i>
          </button>
        </li>
        <li class="nav-item ms-3"><a href="index.php?page=login" class="btn btn-login px-4">Login</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="padding-top-xl padding-bottom-lg z-index-relative registration-page">
  <div class="container">

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger alert-custom alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success alert-custom alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

      <div class="glass-card registration-card animate-fade-in">
        <div class="registration-heading">
          <span><i class="fas fa-user-plus"></i> Pendaftaran Siswa</span>
          <h1>Form Pendaftaran Bimbel Orion</h1>
          <p>Lengkapi data berikut untuk mendaftar. Admin akan memverifikasi dan menghubungi Anda setelah formulir dikirim.</p>
        </div>

      <div class="card-body-custom registration-body">
        <form method="POST" action="index.php?page=pendaftaran" id="pendaftaranForm">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
          <div class="hidden-form-fields" aria-hidden="true">
            <label for="website">Website</label>
            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
          </div>
          <div class="registration-section-layout">
            <section class="registration-section">
              <div class="registration-section-title">
                <i class="fas fa-user"></i>
                <h2>Data Pribadi</h2>
              </div>
              <div class="registration-grid two">
                <div class="form-group">
                  <label for="nama">Nama Lengkap</label>
                  <input type="text" name="nama" id="nama" class="form-control-custom" placeholder="Masukkan nama lengkap" value="<?= htmlspecialchars((string)($oldInput['nama'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" name="email" id="email" class="form-control-custom" placeholder="Masukkan email" value="<?= htmlspecialchars((string)($oldInput['email'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                  <label for="telepon">No. HP</label>
                  <input type="text" name="telepon" id="telepon" class="form-control-custom" placeholder="Masukkan nomor HP" value="<?= htmlspecialchars((string)($oldInput['telepon'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                  <label for="alamat">Alamat</label>
                  <textarea name="alamat" id="alamat" class="form-control-custom" rows="3" placeholder="Masukkan alamat lengkap" required><?= htmlspecialchars((string)($oldInput['alamat'] ?? '')) ?></textarea>
                </div>
              </div>
            </section>

            <section class="registration-section">
              <div class="registration-section-title">
                <i class="fas fa-graduation-cap"></i>
                <h2>Data Akademik</h2>
              </div>
              <div class="registration-grid two">
                <div class="form-group">
                  <label for="jenjang">Jenjang</label>
                  <select name="jenjang" id="jenjang" class="form-control-custom" required>
                    <option value="">Pilih jenjang</option>
                    <option value="SD" <?= (($oldInput['jenjang'] ?? '') === 'SD') ? 'selected' : '' ?>>SD</option>
                    <option value="SMP" <?= (($oldInput['jenjang'] ?? '') === 'SMP') ? 'selected' : '' ?>>SMP</option>
                    <option value="SMA" <?= (($oldInput['jenjang'] ?? '') === 'SMA') ? 'selected' : '' ?>>SMA</option>
                    <option value="SMK" <?= (($oldInput['jenjang'] ?? '') === 'SMK') ? 'selected' : '' ?>>SMK</option>
                    <option value="Lainnya" <?= (($oldInput['jenjang'] ?? '') === 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="kelas_sekolah">Kelas</label>
                  <input type="text" name="kelas_sekolah" id="kelas_sekolah" class="form-control-custom" placeholder="Contoh: 6 SD, 12 MIPA" maxlength="50" value="<?= htmlspecialchars((string)($oldInput['kelas_sekolah'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                  <label for="asal_sekolah">Asal Sekolah</label>
                  <input type="text" name="asal_sekolah" id="asal_sekolah" class="form-control-custom" placeholder="Contoh: SMPN 1 Jember" maxlength="150" value="<?= htmlspecialchars((string)($oldInput['asal_sekolah'] ?? '')) ?>" required>
                </div>
              </div>
            </section>

            <section class="registration-section">
              <div class="registration-section-title">
                <i class="fas fa-user-shield"></i>
                <h2>Data Wali</h2>
              </div>
              <div class="registration-grid two">
                <div class="form-group">
                  <label for="nama_wali">Nama Wali</label>
                  <input type="text" name="nama_wali" id="nama_wali" class="form-control-custom" placeholder="Masukkan nama wali" maxlength="150" value="<?= htmlspecialchars((string)($oldInput['nama_wali'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                  <label for="no_hp_wali">No. HP Wali</label>
                  <input type="text" name="no_hp_wali" id="no_hp_wali" class="form-control-custom" placeholder="Masukkan nomor HP wali" maxlength="30" value="<?= htmlspecialchars((string)($oldInput['no_hp_wali'] ?? '')) ?>" required>
                </div>
              </div>
            </section>

            <section class="registration-section">
              <div class="registration-section-title">
                <img src="public/image/logo-bimbel-orion.jpg" alt="Logo Bimbel Orion" style="width: 20px; height: 20px; object-fit: contain;">
                <h2>Pilihan Program / Mapel</h2>
              </div>
              <div class="form-group">
                <label>Program / Mapel yang Dipilih</label>
                <div class="mapel-helper">Bisa pilih lebih dari satu mapel.</div>

                <?php if (!empty($mapelOptions)): ?>
                  <div class="dropdown mapel-dropdown">
                    <button class="form-control-custom mapel-dropdown-toggle dropdown-toggle" type="button" id="mapelDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                      <span data-mapel-summary>Pilih program / mapel</span>
                    </button>
                    <div class="dropdown-menu mapel-dropdown-menu w-100" aria-labelledby="mapelDropdownBtn">
                      <?php foreach ($mapelOptions as $mapel): ?>
                        <?php
                          $mapelId = (string)($mapel['id'] ?? '');
                          $mapelNama = trim((string)($mapel['nama'] ?? ''));
                          $mapelLabel = $mapelNama !== '' ? $mapelNama : '-';
                        ?>
                        <label class="mapel-dropdown-item">
                          <input type="checkbox" class="form-check-input mapel-check" name="mapel_ids[]" value="<?= htmlspecialchars($mapelId) ?>" data-mapel-label="<?= htmlspecialchars($mapelLabel) ?>" <?= in_array($mapelId, $selectedMapelIds, true) ? 'checked' : '' ?>>
                          <span><?= htmlspecialchars($mapelLabel) ?></span>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning py-2 px-3 mb-0 mt-2">Mapel belum tersedia. Silakan hubungi admin.</div>
                <?php endif; ?>
              </div>
            </section>

            <section class="registration-section registration-section-full">
              <div class="registration-section-title">
                <i class="fas fa-note-sticky"></i>
                <h2>Catatan Tambahan</h2>
              </div>
              <div class="form-group">
                <label for="catatan">Catatan</label>
                <textarea name="catatan" id="catatan" class="form-control-custom" rows="3" placeholder="Opsional: tulis kebutuhan belajar khusus, preferensi jadwal, atau info penting lainnya"><?= htmlspecialchars((string)($oldInput['catatan'] ?? '')) ?></textarea>
              </div>
            </section>

            <section class="registration-section registration-section-full">
              <div class="registration-section-title">
                <i class="fas fa-lock"></i>
                <h2>Akun</h2>
              </div>
              <p class="mapel-helper">Akun login dibuat oleh admin setelah pendaftaran disetujui.</p>
              <div class="registration-grid two">
                <div class="form-group">
                  <label for="username">Username</label>
                  <input type="text" id="username" class="form-control-custom" placeholder="Dibuat setelah verifikasi" disabled>
                </div>
                <div class="form-group">
                  <label for="password_request">Password</label>
                  <input type="password" id="password_request" class="form-control-custom" placeholder="Dikirim setelah akun aktif" disabled>
                </div>
              </div>
            </section>
          </div>

          <div class="registration-actions">
            <button type="submit" class="btn-submit" id="submitBtn">
              <i class="fas fa-paper-plane me-2"></i>Simpan Pendaftaran
            </button>
            <button type="reset" class="btn-back">
              Reset
            </button>
          </div>

          <div class="register-link">
            <p>Sudah punya akun? <a href="index.php?page=login">Masuk</a></p>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<footer id="kontak" class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand">
          <div class="logo-icon medium-icon-wrapper"><img src="public/image/logo-bimbel-orion.jpg" alt="Logo Bimbel Orion" style="width: 100%; height: 100%; object-fit: contain;"></div>
          <span class="logo-text">Bimbel Orion</span>
        </div>
        <p class="opacity-light">Platform bimbel modern terbaik untuk masa depan cerah anak Anda.</p>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-2">
        <h5>Menu</h5>
        <ul class="footer-links">
          <li><a href="index.php">Beranda</a></li>
          <li><a href="index.php#fitur">Fitur</a></li>
          <li><a href="index.php#testimoni">Testimoni</a></li>
          <li><a href="index.php?page=login">Login</a></li>
        </ul>
      </div>
      <div class="col-lg-3">
        <h5>Kontak</h5>
        <ul class="footer-links">
          <li><a href="#"><i class="fas fa-map-marker-alt me-2"></i>Jember, Indonesia</a></li>
          <li><a href="#"><i class="fas fa-phone me-2"></i>+62 812-3456-7890</a></li>
          <li><a href="#"><i class="fas fa-envelope me-2"></i>info@Bimbel Orion.com</a></li>
        </ul>
      </div>
      <div class="col-lg-3">
        <h5>Jam Operasional</h5>
        <ul class="footer-links">
          <li><a href="#">Senin - Jumat: 08.00 - 21.00</a></li>
          <li><a href="#">Sabtu: 09.00 - 17.00</a></li>
          <li><a href="#">Minggu: 10.00 - 15.00</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 Bimbel Orion, Tugas Semester Kelompok 4 Prodi MIF 25 GOL E dengan sepenuh <i class="fas fa-heart text-danger"></i> Indonesia</p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/js/main.js"></script>
</body>
</html>


