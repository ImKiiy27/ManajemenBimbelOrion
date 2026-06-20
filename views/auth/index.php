<?php
$pageTitle = 'Bimbel Orion - Bimbingan Belajar Privat Jember';
require __DIR__ . '/../layouts/header.php';
?>

<div class="bg-animation landing-bg-animation"></div>

<nav class="navbar navbar-expand-lg navbar-light fixed-top" id="navbar">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <div class="logo-icon small-icon-wrapper"><i class="fas fa-book-open"></i></div>
      <span class="brand-text">Bimbel Orion</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="#beranda">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="#fitur">Program</a></li>
        <li class="nav-item"><a class="nav-link" href="#keunggulan">Keunggulan</a></li>
        <li class="nav-item"><a class="nav-link" href="#testimoni">Kenapa Orion</a></li>
        <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
        <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
          <button class="theme-toggle small-icon-wrapper" id="themeToggle" aria-label="Toggle theme">
            <i class="fas fa-moon"></i>
          </button>
        </li>
        <li class="nav-item ms-lg-3 mt-2 mt-lg-0"><a href="index.php?page=login" class="btn btn-login px-4">Login</a></li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0"><a href="index.php?page=pendaftaran" class="btn btn-daftar px-4">Daftar</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="hero landing-hero d-flex align-items-center" id="beranda">
  <div class="container hero-content">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <span class="hero-badge"><i class="fas fa-graduation-cap me-2"></i>Bimbel Privat Orion Jember</span>
        <h1 class="fade-in">Belajar Menyenangkan, Prestasi Gemilang!</h1>
        <p class="fade-in">
          Belajar privat atau kelompok kecil dengan tutor berpengalaman, ramah, dan sabar.
          Jadwal fleksibel, metode menyenangkan, dan tutor bisa datang ke rumah.
        </p>
        <div class="d-flex gap-3 flex-wrap fade-in hero-buttons">
          <a href="index.php?page=pendaftaran" class="btn btn-hero btn-hero-primary">
            <i class="fas fa-bullhorn me-2"></i>Daftar Trial Class
          </a>
          <a href="#kontak" class="btn btn-hero btn-hero-outline">
            <i class="fab fa-whatsapp me-2"></i>Hubungi Kami
          </a>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="hero-dashboard-card fade-in">
          <div class="hero-dashboard-head">
            <span><i class="fas fa-circle me-2 text-danger"></i><i class="fas fa-circle me-2 text-warning"></i><i class="fas fa-circle text-success"></i></span>
            <strong>Keunggulan Kami</strong>
          </div>
          <div class="hero-dashboard-body">
            <div class="mini-stat-card full"><div><small>Privat atau kelompok kecil</small><h4>Siswa lebih fokus</h4></div><i class="fas fa-circle-check"></i></div>
            <div class="mini-stat-card"><div><small>Tutor</small><h4>Ramah & sabar</h4></div><i class="fas fa-chalkboard-user"></i></div>
            <div class="mini-stat-card"><div><small>Jadwal</small><h4>Lebih fleksibel</h4></div><i class="fas fa-calendar-days"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="stats-section" id="keunggulan">
  <div class="container">
    <div class="row g-4">
      <div class="col-6 col-lg-3">
        <div class="stat-item">
          <div class="number"><i class="fas fa-user-check"></i></div>
          <div class="label">Tutor Berpengalaman</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-item">
          <div class="number"><i class="fas fa-calendar-days"></i></div>
          <div class="label">Jadwal Fleksibel</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-item">
          <div class="number"><i class="fas fa-house-user"></i></div>
          <div class="label">Tutor Datang ke Rumah</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-item">
          <div class="number"><i class="fas fa-coins"></i></div>
          <div class="label">Biaya Terjangkau</div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="fitur" class="py-5 landing-section">
  <div class="container py-lg-4">
    <div class="text-center mb-5">
      <h2 class="feature-title fw-bold">Program Belajar</h2>
      <p class="feature-intro">Pilihan program belajar untuk anak usia dini, SD, SMP, SMA, hingga UTBK.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-book-open"></i></div>
          <h5>English Class</h5>
          <p>Grammar, vocabulary, speaking, listening, dan reading.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-1"></i><i class="fas fa-2"></i><i class="fas fa-3"></i></div>
          <h5>Calistung</h5>
          <p>Membaca, menulis, dan berhitung untuk anak usia dini dan SD.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-calculator"></i></div>
          <h5>Matematika</h5>
          <p>Pendampingan belajar matematika untuk jenjang SD, SMP, dan SMA.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-user-group"></i></div>
          <h5>Private / Kelompok</h5>
          <p>Belajar lebih fokus sesuai kebutuhan siswa.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-graduation-cap"></i></div>
          <h5>Untuk Berbagai Jenjang</h5>
          <p>Pre-school, SD, SMP, SMA, hingga persiapan UTBK.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-star"></i></div>
          <h5>Suasana Belajar Nyaman</h5>
          <p>Belajar dibuat menyenangkan, interaktif, dan tidak kaku.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="dashboard-preview-section py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="feature-title fw-bold">Yuk, Gabung di Orion!</h2>
      <p class="feature-intro">Belajar menyenangkan, prestasi gemilang. Daftar sekarang untuk trial class.</p>
    </div>
    <div class="preview-dashboard">
      <aside>
        <span><i class="fas fa-bullhorn"></i> Daftar Trial Class</span>
        <span><i class="fas fa-calendar-days"></i> Jadwal Fleksibel</span>
        <span><i class="fas fa-house-user"></i> Tutor ke Rumah</span>
        <span><i class="fas fa-user-group"></i> Privat / Kelompok</span>
        <span><i class="fas fa-star"></i> Belajar Interaktif</span>
      </aside>
      <div class="preview-main">
        <div class="preview-top">
          <div><strong>SD</strong><span>Matematika</span></div>
          <div><strong>SMP</strong><span>English Class</span></div>
          <div><strong>SMA</strong><span>Private Class</span></div>
          <div><strong>UTBK</strong><span>Persiapan</span></div>
        </div>
        <div class="preview-bottom">
          <div class="preview-chart">
            <span style="height: 42%"></span>
            <span style="height: 68%"></span>
            <span style="height: 54%"></span>
            <span style="height: 78%"></span>
            <span style="height: 48%"></span>
            <span style="height: 64%"></span>
          </div>
          <div class="preview-donut"><span>Trial</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 role-section">
  <div class="container py-lg-3">
    <div class="text-center mb-5">
      <h2 class="feature-title fw-bold">Pilihan Belajar</h2>
      <p class="feature-intro">Belajar bisa disesuaikan dengan kebutuhan, kemampuan, dan jadwal siswa.</p>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-sm-6 col-lg-3">
        <div class="role-card">
          <div class="role-icon"><i class="fas fa-user-shield"></i></div>
          <h6>Privat</h6>
          <p>Belajar satu-satu agar siswa lebih fokus dan nyaman bertanya.</p>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="role-card">
          <div class="role-icon"><i class="fas fa-person-chalkboard"></i></div>
          <h6>Kelompok Kecil</h6>
          <p>Belajar bersama teman dengan suasana yang tetap kondusif.</p>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="role-card">
          <div class="role-icon"><i class="fas fa-user-graduate"></i></div>
          <h6>Datang ke Rumah</h6>
          <p>Lebih praktis karena tutor dapat datang ke rumah siswa.</p>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="role-card">
          <div class="role-icon"><i class="fas fa-users"></i></div>
          <h6>Jadwal Fleksibel</h6>
          <p>Waktu belajar bisa disesuaikan dengan kesepakatan.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="testimoni" class="testimonial-section py-5">
  <div class="container py-lg-3">
    <div class="text-center mb-5">
      <h2 class="fw-bold title-large">Kenapa Pilih Orion?</h2>
      <p class="opacity-medium">Pendampingan belajar yang fokus, menyenangkan, dan mudah dijangkau.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="testimonial-card fade-in"><i class="fas fa-quote-left quote-icon"></i>
          <p>"Belajar privat atau kelompok kecil membantu siswa lebih fokus sesuai kebutuhan."</p>
          <div class="testimonial-author">
            <div class="avatar">R</div>
            <div class="info">
              <h6>Fokus Belajar</h6><span>Privat / Kelompok kecil</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial-card fade-in"><i class="fas fa-quote-left quote-icon"></i>
          <p>"Tutor berpengalaman, ramah, sabar, dan menggunakan metode belajar yang interaktif."</p>
          <div class="testimonial-author">
            <div class="avatar">A</div>
            <div class="info">
              <h6>Tutor Berkualitas</h6><span>Ramah dan sabar</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial-card fade-in"><i class="fas fa-quote-left quote-icon"></i>
          <p>"Biaya terjangkau, jadwal lebih fleksibel, dan tutor bisa datang ke rumah."</p>
          <div class="testimonial-author">
            <div class="avatar">M</div>
            <div class="info">
              <h6>Praktis</h6><span>Fleksibel dan terjangkau</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 cta-section">
  <div class="container text-center py-5 px-4">
    <h2 class="fw-bold mb-3 title-xl">Daftar Sekarang untuk Trial Class!</h2>
    <p class="mb-4 subtitle-md">Bimbel Orion siap membantu belajar lebih fokus, menyenangkan, dan sesuai kebutuhan.</p>
    <a href="index.php?page=pendaftaran" class="btn btn-login btn-lg px-4">
      <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
    </a>
  </div>
</section>

<footer id="kontak" class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand">
          <div class="logo-icon"><i class="fas fa-book-open"></i></div><span class="logo-text">Bimbel Orion</span>
        </div>
        <p class="opacity-light">Belajar privat atau kelompok kecil bersama tutor berpengalaman, ramah, dan sabar di Jember.</p>
        <div class="social-links">
          <a href="https://www.instagram.com/bimbel_orion/" target="_blank"><i class="fab fa-instagram"></i></a>
          <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>
      <div class="col-lg-2">
        <h5>Navigasi</h5>
        <ul class="footer-links">
          <li><a href="#fitur">Program</a></li>
          <li><a href="#keunggulan">Keunggulan</a></li>
          <li><a href="#testimoni">Kenapa Orion</a></li>
          <li><a href="index.php?page=login">Login</a></li>
        </ul>
      </div>
      <div class="col-lg-3">
        <h5>Kontak</h5>
        <ul class="footer-links">
          <li><a href="#"><i class="fas fa-map-marker-alt me-2"></i>Jember, Indonesia</a></li>
          <li><a href="#"><i class="fab fa-whatsapp me-2"></i>+62 819-7535-0033 (Kak Ferdi)</a></li>
          <li><a href="https://www.instagram.com/bimbel_orion/" target="_blank"><i class="fab fa-instagram me-2"></i>@bimbel_orion</a></li>
          <li><a href="#"><i class="fas fa-user-circle me-2"></i>Bimbel Privat Orion Jember</a></li>
        </ul>
      </div>
      <div class="col-lg-3">
        <h5>Jam Operasional</h5>
        <ul class="footer-links">
          <li><a href="#">Dapat di atur sesuai kesepakan guru dan siswa</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 Bimbel Orion. Semua hak cipta dilindungi.</p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="public/js/main.js"></script>
</body>
</html>
