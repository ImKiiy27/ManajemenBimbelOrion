/* ============================================================
   Bimbel Orion - main.js
   Gabungan semua JS dari: index, login, pendaftaran, dashboard
   ============================================================ */

/* ------------------------------------------------------------
   1. THEME TOGGLE (semua halaman)
   ------------------------------------------------------------ */
function initTheme() {
  const html = document.documentElement;
  const savedTheme = getSavedTheme();
  applyTheme(savedTheme);
  updateThemeIcon(savedTheme);

  const themeToggle = document.getElementById('themeToggle');
  if (!themeToggle) return;

  themeToggle.addEventListener('click', function () {
    const current = html.getAttribute('data-theme');
    const next = current === 'light' ? 'dark' : 'light';
    applyTheme(next);
    try {
      localStorage.setItem('theme', next);
    } catch (error) {
      // Theme still changes for the current page if storage is unavailable.
    }
    updateThemeIcon(next);
  });
}

function getSavedTheme() {
  try {
    return localStorage.getItem('theme') || 'light';
  } catch (error) {
    return 'light';
  }
}

function applyTheme(theme) {
  const html = document.documentElement;
  html.setAttribute('data-theme', theme);
  html.style.backgroundColor = theme === 'dark' ? '#1a1a2e' : '#f8f9fa';
  html.style.colorScheme = theme;
}

function updateThemeIcon(theme) {
  const icon = document.querySelector('#themeToggle i');
  if (!icon) return;
  icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

function isLandingPage() {
  return Boolean(document.querySelector('.landing-hero'));
}

function disableLandingJsAnimations() {
  if (!isLandingPage()) return false;

  document.body.classList.remove('enable-reveal');
  document.body.classList.add('landing-reveal-disabled', 'landing-js-animation-disabled');
  document.querySelectorAll('.fade-in').forEach(el => el.classList.add('show'));
  return true;
}

/* ------------------------------------------------------------
   2. NAVBAR SCROLL EFFECT (index)
   ------------------------------------------------------------ */
function initNavbarScroll() {
  if (isLandingPage()) return;

  const navbar = document.getElementById('navbar');
  if (!navbar) return;
  window.addEventListener('scroll', function () {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
  });
}

/* ------------------------------------------------------------
   3. FADE IN ON SCROLL (index)
   ------------------------------------------------------------ */
function initFadeIn() {
  if (disableLandingJsAnimations()) return;

  const faders = document.querySelectorAll('.fade-in');
  if (!faders.length) return;

  if (!('IntersectionObserver' in window)) {
    faders.forEach(el => el.classList.add('show'));
    return;
  }

  document.body.classList.add('enable-reveal');

  const observer = new IntersectionObserver(function (entries, obs) {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('show');
      obs.unobserve(entry.target);
    });
  }, { threshold: 0.2, rootMargin: '0px 0px -50px 0px' });

  faders.forEach(el => observer.observe(el));
}

/* ------------------------------------------------------------
   4. PASSWORD TOGGLE (login)
   ------------------------------------------------------------ */
function initPasswordToggle() {
  const toggleBtn = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');
  if (!toggleBtn || !passwordInput) return;

  toggleBtn.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    eyeIcon.classList.toggle('fa-eye');
    eyeIcon.classList.toggle('fa-eye-slash');
  });
}

/* ------------------------------------------------------------
   5. LOGIN FORM SUBMIT (login)
   ------------------------------------------------------------ */
function initLoginForm() {
  const form = document.getElementById('loginForm');
  const loginBtn = document.getElementById('loginBtn');
  const loadingOverlay = document.getElementById('loadingOverlay');
  if (!form) return;

  // Floating label: hapus placeholder agar CSS :not(:placeholder-shown) bekerja
  document.querySelectorAll('.input-group input').forEach(input => {
    input.setAttribute('placeholder', ' ');
  });

  form.addEventListener('submit', function () {
    if (loginBtn) {
      loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memproses...';
      loginBtn.disabled = true;
    }
    if (loadingOverlay) loadingOverlay.classList.add('active');
  });
}

/* ------------------------------------------------------------
   6. PENDAFTARAN FORM (pendaftaran)
   ------------------------------------------------------------ */
function initPendaftaranForm() {
  const form = document.getElementById('pendaftaranForm');
  const submitBtn = document.getElementById('submitBtn');
  const mapelChecks = form ? form.querySelectorAll('input[name="mapel_ids[]"]') : [];
  const mapelSummary = form ? form.querySelector('[data-mapel-summary]') : null;
  if (!form) return;

  const updateMapelSummary = () => {
    if (!mapelSummary || mapelChecks.length === 0) return;

    const selectedLabels = Array.from(mapelChecks)
      .filter((check) => check.checked)
      .map((check) => check.getAttribute('data-mapel-label') || '')
      .filter((label) => label.trim() !== '');

    if (selectedLabels.length === 0) {
      mapelSummary.textContent = 'Pilih mapel';
      return;
    }

    if (selectedLabels.length <= 2) {
      mapelSummary.textContent = selectedLabels.join(', ');
      return;
    }

    mapelSummary.textContent = `${selectedLabels.length} mapel dipilih`;
  };

  mapelChecks.forEach((check) => {
    check.addEventListener('change', updateMapelSummary);
  });
  updateMapelSummary();

  form.addEventListener('submit', function (event) {
    if (mapelChecks.length > 0) {
      const hasSelectedMapel = Array.from(mapelChecks).some((check) => check.checked);
      if (!hasSelectedMapel) {
        event.preventDefault();
        alert('Pilih minimal satu mapel yang ingin diikuti.');
        return;
      }
    }

    if (submitBtn) {
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mengirim...';
      submitBtn.disabled = true;
    }
  });
}

/* ------------------------------------------------------------
   7. SIDEBAR TOGGLE (dashboard - mobile)
   ------------------------------------------------------------ */
function initSidebar() {
  const toggleBtn = document.getElementById('sidebarToggle');
  const sidebar = document.querySelector('.sidebar');
  if (!toggleBtn || !sidebar) return;

  const isDesktop = () => window.innerWidth > 992;

  const setMobileState = (isOpen) => {
    sidebar.classList.toggle('active', isOpen);
    document.body.classList.toggle('sidebar-open', isOpen);
    toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  };

  const setDesktopState = (collapsed) => {
    sidebar.classList.toggle('collapsed', collapsed);
    document.body.classList.toggle('sidebar-collapsed', collapsed);
    toggleBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
  };

  toggleBtn.addEventListener('click', function () {
    if (isDesktop()) {
      const willCollapse = !sidebar.classList.contains('collapsed');
      setDesktopState(willCollapse);
    } else {
      const willOpen = !sidebar.classList.contains('active');
      setMobileState(willOpen);
    }
  });

  // Tutup sidebar kalau klik di luar (mobile)
  document.addEventListener('click', function (e) {
    if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
      const clickedToggle = toggleBtn.contains(e.target);
      if (!sidebar.contains(e.target) && !clickedToggle) {
        setMobileState(false);
      }
    }
  });

  // Sinkronisasi state saat resize
  window.addEventListener('resize', function () {
    if (isDesktop()) {
      // pastikan mode mobile dimatikan
      setMobileState(false);
    } else {
      // kembali ke mobile: tampilkan sidebar jika sebelumnya tidak collapsed
      sidebar.classList.remove('collapsed');
      document.body.classList.remove('sidebar-collapsed');
    }
  });
}

/* ------------------------------------------------------------
   8. AUTO DISMISS ALERT
   ------------------------------------------------------------ */
function initAlertDismiss() {
  const alerts = document.querySelectorAll('.alert-custom');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.5s ease';
      setTimeout(() => alert.remove(), 500);
    }, 4000);
  });
}

/* ------------------------------------------------------------
   8.5 CARD ANIMATIONS & BUTTON EFFECTS
   ------------------------------------------------------------ */
function initCardAnimations() {
  if (isLandingPage()) return;

  const cards = document.querySelectorAll('.feature-card, .testimonial-card');
  if (!cards.length) return;

  cards.forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.transition = 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
    });

    card.addEventListener('mouseleave', function() {
      this.style.transition = 'all 0.3s ease';
    });
  });
}

function initButtonAnimations() {
  if (isLandingPage()) return;

  const buttons = document.querySelectorAll('a.btn-login, a.btn-daftar, a.btn-hero-primary');
  if (!buttons.length) return;

  buttons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      ripple.style.position = 'absolute';
      ripple.style.width = '20px';
      ripple.style.height = '20px';
      ripple.style.background = 'rgba(255, 255, 255, 0.6)';
      ripple.style.borderRadius = '50%';
      ripple.style.pointerEvents = 'none';
      ripple.style.animation = 'ripple 0.6s ease-out';
      this.style.position = 'relative';
      this.style.overflow = 'hidden';
    });
  });
}

/* Offset anchor targets for fixed navbar */
document.addEventListener('DOMContentLoaded', function () {
  initTheme();
  disableLandingJsAnimations();
  initNavbarScroll();
  initFadeIn();
  initSmoothScroll();
  initPasswordToggle();
  initLoginForm();
  initPendaftaranForm();
  initSidebar();
  initAlertDismiss();
  initCardAnimations();
  initButtonAnimations();
});

/* ------------------------------------------------------------
   10. SMOOTH SCROLL (landing)
   ------------------------------------------------------------ */
function initSmoothScroll() {
  if (isLandingPage()) return;

  const links = document.querySelectorAll('a[href^="#"]');
  if (!links.length) return;

  const closeNavbar = () => {
    const nav = document.getElementById('navbarNav');
    if (!nav || !nav.classList.contains('show')) return;
    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
      const bsCollapse = bootstrap.Collapse.getInstance(nav) || new bootstrap.Collapse(nav, { toggle: false });
      bsCollapse.hide();
    } else {
      nav.classList.remove('show');
      const toggler = document.querySelector('.navbar-toggler');
      if (toggler) toggler.setAttribute('aria-expanded', 'false');
    }
  };

  links.forEach(link => {
    const href = link.getAttribute('href');
    if (!href || href === '#') return;
    link.addEventListener('click', (e) => {
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      closeNavbar();
    });
  });
}
