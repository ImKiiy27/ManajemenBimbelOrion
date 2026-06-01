<?php
$pageTitle = 'Bimbel Orion - Platform Pembelajaran Terbaik';
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
        <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
        <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
        <li class="nav-item"><a class="nav-link" href="#keunggulan">Keunggulan</a></li>
        <li class="nav-item"><a class="nav-link" href="#testimoni">Testimoni</a></li>
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
        <span class="hero-badge"><i class="fas fa-graduation-cap me-2"></i>Platform Bimbingan Belajar Privat Terbaik</span>
        <h1 class="fade-in">Tingkatkan Nilai dan Kepercayaan Diri Belajar Anak</h1>
        <p class="fade-in">
          Bimbel Orion membantu siswa belajar lebih terarah dengan tutor berpengalaman, jadwal fleksibel,
          dan pendampingan rutin agar hasil belajar lebih konsisten.
        </p>
        <div class="d-flex gap-3 flex-wrap fade-in hero-buttons">
          <a href="index.php?page=pendaftaran" class="btn btn-hero btn-hero-primary">
            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
          </a>
          <a href="#fitur" class="btn btn-hero btn-hero-outline">
            <i class="fas fa-list-check me-2"></i>Lihat Program Belajar
          </a>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="hero-dashboard-card fade-in">
          <div class="hero-dashboard-head">
            <span><i class="fas fa-circle me-2 text-danger"></i><i class="fas fa-circle me-2 text-warning"></i><i class="fas fa-circle text-success"></i></span>
            <strong>Ringkasan Dashboard</strong>
          </div>
          <div class="hero-dashboard-body">
            <div class="mini-stat-card">
              <div>
                <small>Siswa Aktif</small>
                <h4>248</h4>
              </div>
              <i class="fas fa-user-graduate"></i>
            </div>
            <div class="mini-stat-card">
              <div>
                <small>Kelas Hari Ini</small>
                <h4>18</h4>
              </div>
              <i class="fas fa-calendar-check"></i>
            </div>
            <div class="mini-stat-card full">
              <div>
                <small>Progress Absensi Mingguan</small>
                <div class="progress mt-2" role="progressbar" aria-label="Absensi" aria-valuenow="86" aria-valuemin="0" aria-valuemax="100">
                  <div class="progress-bar bg-primary" style="width: 86%">86%</div>
                </div>
              </div>
            </div>
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
          <div class="number">4</div>
          <div class="label">Role Pengguna</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-item">
          <div class="number"><i class="fas fa-calendar-days"></i></div>
          <div class="label">Jadwal Terintegrasi</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-item">
          <div class="number"><i class="fas fa-clipboard-check"></i></div>
          <div class="label">Rekap Absensi</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-item">
          <div class="number"><i class="fas fa-file-lines"></i></div>
          <div class="label">Laporan Nilai</div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="landing-about-section py-5" id="tentang">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-4">
        <div class="landing-visual-card" aria-hidden="true">
          <div class="visual-sun"></div>
          <div class="visual-mountain visual-one"></div>
          <div class="visual-mountain visual-two"></div>
          <div class="visual-line"></div>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="landing-about-copy">
          <h2>Satu sistem untuk semua kebutuhan bimbel Anda</h2>
          <ul>
            <li><i class="fas fa-circle-check"></i>Meningkatkan efisiensi administrasi bimbel.</li>
            <li><i class="fas fa-circle-check"></i>Memudahkan komunikasi antara pengajar, siswa, dan orang tua.</li>
            <li><i class="fas fa-circle-check"></i>Data tersimpan aman dan terstruktur.</li>
            <li><i class="fas fa-circle-check"></i>Akses kapan saja, di mana saja.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="fitur" class="py-5 landing-section">
  <div class="container py-lg-4">
    <div class="text-center mb-5">
      <h2 class="feature-title fw-bold">Fitur Inti Bimbel Orion</h2>
      <p class="feature-intro">Dirancang untuk operasional bimbel yang efisien dan profesional.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-users"></i></div>
          <h5>Manajemen Siswa</h5>
          <p>Kelola data siswa, status aktif, dan histori belajar dalam satu tampilan.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-chalkboard-teacher"></i></div>
          <h5>Manajemen Guru</h5>
          <p>Atur profil pengajar, jadwal mengajar, dan pembagian kelas secara fleksibel.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-calendar-days"></i></div>
          <h5>Jadwal Bimbel</h5>
          <p>Susun jadwal terstruktur agar aktivitas belajar berjalan tepat waktu.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-user-check"></i></div>
          <h5>Absensi</h5>
          <p>Catat kehadiran siswa dan guru dengan rekap otomatis per pertemuan.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
          <h5>Nilai</h5>
          <p>Input dan pantau hasil evaluasi siswa untuk melihat progres akademik.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card fade-in">
          <div class="feature-icon"><i class="fas fa-people-roof"></i></div>
          <h5>Pantauan Wali Murid</h5>
          <p>Wali murid dapat memonitor nilai dan kehadiran anak secara transparan.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="dashboard-preview-section py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="feature-title fw-bold">Preview Dashboard</h2>
      <p class="feature-intro">Gambaran ringkas data operasional yang bisa dipantau setiap hari.</p>
    </div>
    <div class="preview-dashboard">
      <aside>
        <span><i class="fas fa-gauge"></i> Dashboard</span>
        <span><i class="fas fa-calendar-days"></i> Jadwal</span>
        <span><i class="fas fa-user-graduate"></i> Siswa</span>
        <span><i class="fas fa-chart-bar"></i> Nilai</span>
        <span><i class="fas fa-clipboard-check"></i> Absensi</span>
      </aside>
      <div class="preview-main">
        <div class="preview-top">
          <div><strong>120</strong><span>Total Siswa</span></div>
          <div><strong>18</strong><span>Total Pengajar</span></div>
          <div><strong>32</strong><span>Kelas Aktif</span></div>
          <div><strong>85%</strong><span>Kehadiran</span></div>
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
          <div class="preview-donut"><span>85%</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 role-section">
  <div class="container py-lg-3">
    <div class="text-center mb-5">
      <h2 class="feature-title fw-bold">Role Pengguna</h2>
      <p class="feature-intro">Setiap peran memiliki akses sesuai kebutuhan masing-masing.</p>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-sm-6 col-lg-3">
        <div class="role-card">
          <div class="role-icon"><i class="fas fa-user-shield"></i></div>
          <h6>Admin</h6>
          <p>Mengelola data master, pengguna, kelas, dan keseluruhan sistem.</p>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="role-card">
          <div class="role-icon"><i class="fas fa-person-chalkboard"></i></div>
          <h6>Guru</h6>
          <p>Mengatur absensi, materi, jadwal, dan input nilai siswa.</p>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="role-card">
          <div class="role-icon"><i class="fas fa-user-graduate"></i></div>
          <h6>Siswa</h6>
          <p>Melihat jadwal, kehadiran, nilai, dan perkembangan belajar.</p>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="role-card">
          <div class="role-icon"><i class="fas fa-users"></i></div>
          <h6>Wali Murid</h6>
          <p>Memantau performa anak melalui laporan absensi dan nilai.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="testimoni" class="testimonial-section py-5">
  <div class="container py-lg-3">
    <div class="text-center mb-5">
      <h2 class="fw-bold title-large">Testimoni Pengguna</h2>
      <p class="opacity-medium">Cerita singkat dari pengguna Bimbel Orion.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="testimonial-card fade-in"><i class="fas fa-quote-left quote-icon"></i>
          <p>"Manajemen kelas jadi jauh lebih cepat, terutama saat membuat jadwal dan rekap siswa."</p>
          <div class="testimonial-author">
            <div class="avatar">R</div>
            <div class="info">
              <h6>Rina Putri</h6><span>Admin Bimbel</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial-card fade-in"><i class="fas fa-quote-left quote-icon"></i>
          <p>"Input absensi dan nilai jadi lebih rapi, saya tidak perlu rekap manual lagi."</p>
          <div class="testimonial-author">
            <div class="avatar">A</div>
            <div class="info">
              <h6>Andi Pratama, S.Pd</h6><span>Guru Matematika</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="testimonial-card fade-in"><i class="fas fa-quote-left quote-icon"></i>
          <p>"Sebagai wali murid, saya lebih tenang karena progres belajar anak bisa dipantau kapan saja."</p>
          <div class="testimonial-author">
            <div class="avatar">M</div>
            <div class="info">
              <h6>Maya Lestari</h6><span>Wali Murid</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 cta-section">
  <div class="container text-center py-5 px-4">
    <h2 class="fw-bold mb-3 title-xl">Mulai gunakan Bimbel Orion sekarang</h2>
    <p class="mb-4 subtitle-md">Bangun manajemen bimbel yang lebih terstruktur untuk tim pengajar, siswa, dan wali murid.</p>
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
        <p class="opacity-light">Sistem manajemen bimbel modern untuk operasional akademik yang lebih tertata dan profesional.</p>
        <div class="social-links">
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div class="col-lg-2">
        <h5>Navigasi</h5>
        <ul class="footer-links">
          <li><a href="#fitur">Fitur</a></li>
          <li><a href="#keunggulan">Keunggulan</a></li>
          <li><a href="#testimoni">Testimoni</a></li>
          <li><a href="index.php?page=login">Login</a></li>
        </ul>
      </div>
      <div class="col-lg-3">
        <h5>Kontak</h5>
        <ul class="footer-links">
          <li><a href="#"><i class="fas fa-map-marker-alt me-2"></i>Jember, Indonesia</a></li>
          <li><a href="#"><i class="fas fa-phone me-2"></i>+62 812-3456-7890</a></li>
          <li><a href="#"><i class="fas fa-envelope me-2"></i>hello@bimbelorion.id</a></li>
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
