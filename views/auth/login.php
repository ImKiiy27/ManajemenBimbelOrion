<?php
$pageTitle = 'Login - Bimbel Orion';
require __DIR__ . '/../layouts/header.php';
?>

<div class="login-page">
  <div class="bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
  </div>

  <div class="spinner-overlay" id="loadingOverlay">
    <div class="custom-spinner"></div>
  </div>

  <div class="login-container login-wireframe-container">
    <div class="login-card login-wireframe-card">
      <div class="login-card-inner">

        <div class="login-illustration login-wireframe-illustration">
          <div class="login-side-brand">
            <div class="logo-icon"><i class="fas fa-book-open"></i></div>
            <div>
              <h3>Bimbel Orion</h3>
              <p>Sistem manajemen bimbel terintegrasi untuk semua pihak.</p>
            </div>
          </div>

          <div class="login-scene" aria-hidden="true">
            <div class="scene-sun"></div>
            <div class="scene-mountain mountain-one"></div>
            <div class="scene-mountain mountain-two"></div>
            <div class="scene-ground"></div>
          </div>

          <div class="login-benefits">
            <div>
              <i class="fas fa-shield-halved"></i>
              <span>Aman & Terpercaya</span>
              <small>Data Anda aman bersama kami.</small>
            </div>
            <div>
              <i class="fas fa-clock"></i>
              <span>Akses Kapan Saja</span>
              <small>Kelola bimbel dari mana pun.</small>
            </div>
            <div>
              <i class="fas fa-users"></i>
              <span>Untuk Semua Role</span>
              <small>Admin, guru, siswa, dan wali murid.</small>
            </div>
          </div>
        </div>

        <div class="login-form-section login-wireframe-form-wrap">
          <div class="brand-logo">
            <div class="logo-wrapper">
              <div class="logo-icon"><i class="fas fa-book-open"></i></div>
              <span class="logo-text">Bimbel Orion</span>
            </div>
            <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
              <i class="fas fa-moon"></i>
            </button>
          </div>

          <div class="login-form-card">
            <h2 class="login-title">Masuk ke Bimbel Orion</h2>
            <p class="login-subtitle">Silakan masuk untuk melanjutkan</p>

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
              <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login" id="loginForm" novalidate>
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars(getCsrfToken()) ?>">
              <div class="input-group">
                <input type="email" name="email" id="email" placeholder=" "
                      value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <label for="email">Email / Username</label>
                <i class="fas fa-envelope input-icon"></i>
              </div>

              <div class="input-group">
                <input type="password" name="password" id="password" placeholder=" " >
                <label for="password">Password</label>
                <i class="fas fa-lock input-icon"></i>
                <button type="button" class="password-toggle" id="togglePassword">
                  <i class="fas fa-eye" id="eyeIcon"></i>
                </button>
              </div>

              <div class="form-options">
                <label class="remember-me">
                  <input type="checkbox" name="remember">
                  <span>Ingat saya</span>
                </label>
                <a href="#" class="forgot-password">Lupa password?</a>
              </div>

              <button type="submit" class="btn-submit-login" id="loginBtn">
                Masuk
              </button>

              <div class="register-link">
                <p>Belum punya akun? <a href="index.php?page=pendaftaran">Daftar</a></p>
              </div>

              <div class="divider"><span>atau masuk dengan</span></div>
              <div class="social-login">
                <button type="button" class="social-btn google"><i class="fab fa-google"></i> Google</button>
                <button type="button" class="social-btn facebook"><i class="fab fa-facebook-f"></i> Facebook</button>
              </div>
            </form>
          </div>

          <p class="login-terms">Dengan masuk, Anda setuju dengan <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a>.</p>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/js/main.js"></script>
</body>
</html>
