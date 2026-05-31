# Bimbel Orion - Sistem Manajemen Bimbel (PHP MVC)

## Gambaran Umum
Bimbel Orion adalah aplikasi manajemen bimbingan belajar berbasis PHP MVC dengan 3 role utama:
- `admin`: kelola user, data siswa/guru, relasi mapel, jadwal, dan monitoring nilai.
- `guru`: melihat jadwal mengajar dan menginput nilai siswa.
- `siswa`: melihat jadwal belajar dan nilai.

Arsitektur saat ini sudah dipisah per tanggung jawab: `controller -> action handler -> repository/service -> view`.

## Teknologi
- PHP 8+ (tanpa framework)
- MySQL/MariaDB via PDO
- Session-based auth + role-based access control
- CSRF token untuk semua aksi POST
- HTML/CSS/JS statis (`public/css/main.css`, `public/js/main.js`)

## Struktur Proyek Saat Ini
```text
bimbel-orion/
|-- .gitignore
|-- index.php
|-- README.md
|-- config/
|   |-- database.php
|   `-- session.php
|-- controllers/
|   |-- SiswaController.php
|   |-- auth/
|   |   |-- AuthController.php
|   |   `-- actions/
|   |       |-- AuthLoginActionHandler.php
|   |       |-- AuthLogoutActionHandler.php
|   |       `-- AuthPendaftaranActionHandler.php
|   |-- admin/
|   |   |-- BaseAdminController.php
|   |   |-- AdminDashboardController.php
|   |   |-- AdminSiswaController.php
|   |   |-- AdminGuruController.php
|   |   |-- AdminJadwalController.php
|   |   |-- AdminAbsensiController.php
|   |   |-- AdminNilaiController.php
|   |   |-- AdminUserController.php
|   |   |-- AdminPageController.php
|   |   `-- actions/
|   |       |-- AdminUserActionHandler.php
|   |       |-- AdminSiswaActionHandler.php
|   |       |-- AdminJadwalActionHandler.php
|   |       `-- AdminNilaiActionHandler.php
|   |-- guru/
|   |   |-- BaseGuruController.php
|   |   |-- GuruDashboardController.php
|   |   |-- GuruJadwalController.php
|   |   |-- GuruAbsensiController.php
|   |   |-- GuruNilaiController.php
|   |   |-- GuruProfilController.php
|   |   |-- GuruController.php
|   |   `-- actions/
|   |       `-- GuruNilaiActionHandler.php
|   |-- siswa/
|   |   |-- BaseSiswaController.php
|   |   |-- SiswaDashboardController.php
|   |   |-- SiswaJadwalController.php
|   |   |-- SiswaAbsensiController.php
|   |   |-- SiswaNilaiController.php
|   |   `-- SiswaProfilController.php
|   `-- wali_murid/
|       |-- BaseWaliMuridController.php
|       |-- WaliMuridDashboardController.php
|       |-- WaliMuridNilaiController.php
|       `-- WaliMuridAbsensiController.php
|-- core/
|   `-- Router.php
|-- models/
|   |-- IdCounterModel.php
|   |-- absensi/
|   |   |-- AbsensiQueryService.php
|   |   `-- AbsensiCommandService.php
|   |-- admin/
|   |   |-- AdminGuruRepository.php
|   |   |-- AdminUserModel.php
|   |   |-- AdminSiswaRepository.php
|   |   `-- AdminUserRepository.php
|   |-- auth/
|   |   `-- AuthModel.php
|   |-- guru/
|   |   |-- GuruDashboardRepository.php
|   |   |-- GuruAbsensiRepository.php
|   |   `-- GuruProfilRepository.php
|   |-- jadwal/
|   |   |-- JadwalCommandService.php
|   |   |-- JadwalModel.php
|   |   `-- JadwalQueryService.php
|   |-- nilai/
|   |   |-- NilaiCommandService.php
|   |   |-- NilaiModel.php
|   |   `-- NilaiQueryService.php
|   |-- siswa/
|   |   |-- SiswaDashboardRepository.php
|   |   |-- SiswaNilaiRepository.php
|   |   |-- SiswaAbsensiRepository.php
|   |   `-- SiswaProfilRepository.php
|   |-- pendaftaran/
|   |   `-- PendaftaranModel.php
|   `-- wali_murid/
|       `-- WaliMuridRepository.php
|-- public/
|   |-- css/
|   |   `-- main.css
|   |-- js/
|   |   `-- main.js
|   `-- svg/
|       |-- hero-shape-1.svg
|       `-- hero-shape-2.svg
|-- scripts/
|   `-- migrate_to_erd.php
`-- views/
    |-- admin/
    |   |-- absensi.php
    |   |-- dashboard.php
    |   |-- guru.php
    |   |-- jadwal.php
    |   |-- nilai.php
    |   |-- siswa.php
    |   `-- user.php
    |-- auth/
    |   |-- index.php
    |   |-- login.php
    |   `-- pendaftaran.php
    |-- guru/
    |   |-- absensi.php
    |   |-- dashboard.php
    |   |-- jadwal.php
    |   |-- nilai.php
    |   `-- profil.php
    |-- layouts/
    |   |-- dashboard-navbar.php
    |   |-- footer.php
    |   |-- header.php
    |   `-- sidebar.php
    `-- siswa/
        |-- absensi.php
        |-- dashboard.php
        |-- jadwal.php
        |-- nilai.php
        `-- profil.php
```

## Arsitektur Sistem
### 1. Entry Point dan Routing
- Semua request masuk dari `index.php`.
- Routing berbasis query string: `index.php?page=...`.
- `core/Router.php` menangani:
  - whitelist halaman publik,
  - validasi login,
  - validasi role (`admin`, `guru`, `siswa`),
  - dispatch ke controller terkait.

### 2. Layer Controller
- `controllers/auth/AuthController.php`: orkestrasi halaman auth.
- `controllers/*/actions/*`: handler aksi POST per role/fitur.
- `controllers/admin/*Controller.php`: controller admin dipisah per fitur halaman.
- `controllers/guru/*Controller.php`: controller guru dipisah per fitur halaman.
- `controllers/siswa/*Controller.php`: controller siswa dipisah per fitur halaman.
- `AdminPageController`, `GuruController`, `SiswaController`: compatibility facade selama masa transisi.

### 3. Layer Model/Service/Repository
- `models/auth/AuthModel.php`: model autentikasi login.
- `models/pendaftaran/PendaftaranModel.php`: model pendaftaran calon siswa.
- `models/admin/*Repository`: data access khusus area admin.
- `models/admin/AdminUserModel.php`: compatibility facade area admin.
- `models/jadwal/*Service`: pemisahan query dan command jadwal.
- `models/jadwal/JadwalModel.php`: compatibility facade modul jadwal.
- `models/nilai/*Service`: pemisahan query dan command nilai.
- `models/nilai/NilaiModel.php`: compatibility facade modul nilai.
- `models/absensi/*`: fondasi service absensi (roadmap).
- `models/wali_murid/*`: fondasi role wali murid (roadmap).
- `IdCounterModel`: generator ID berprefix lintas tabel (contoh `JDL`, `NLI`, `SMP`).

### 4. Layer View
- `views/layouts`: komponen layout bersama (header/footer/sidebar/navbar).
- `views/auth`: landing page, login, pendaftaran.
- `views/admin`, `views/guru`, `views/siswa`: halaman per role.

## Fitur Berdasarkan Role
### Public
- Landing page (`index`)
- Login (`login`)
- Pendaftaran calon siswa multi-mapel (`pendaftaran`)
- Logout (`logout`)

### Admin
- Dashboard ringkas pendaftar dan statistik jadwal
- Kelola user (`create`, `update`, `unlock`, `delete`, `delete-force`)
- Kelola data siswa dan relasi mapel siswa
- Monitoring data guru
- Kelola jadwal dengan validasi bentrok guru/siswa
- Monitoring nilai + hapus nilai

### Guru
- Dashboard
- Lihat jadwal mengajar
- Input/ubah/hapus nilai siswa
- Halaman absensi dan profil (masih placeholder UI)

### Siswa
- Dashboard
- Lihat jadwal les
- Lihat nilai dari guru
- Halaman absensi dan profil (masih placeholder UI)

## Daftar Route
Semua route melalui `index.php?page=...`

### Public
- `index`
- `login`
- `pendaftaran`
- `logout`

### Admin
- `admin-dashboard`
- `admin-siswa`
- `admin-guru`
- `admin-jadwal`
- `admin-absensi`
- `admin-nilai`
- `admin-user`

### Guru
- `guru-dashboard`
- `guru-jadwal`
- `guru-absensi`
- `guru-nilai`
- `guru-profil`

### Siswa
- `siswa-dashboard`
- `siswa-jadwal`
- `siswa-absensi`
- `siswa-nilai`
- `siswa-profil`

## Keamanan yang Diimplementasikan
- PDO prepared statements (anti SQL injection)
- Password hashing: `password_hash` / `password_verify`
- Login attempt limiter + lock account (`users.attempts`, `users.is_locked`)
- Session hardening di `config/session.php`:
  - idle timeout 30 menit,
  - max session lifetime 8 jam,
  - periodic session ID regeneration,
  - secure cookie flags (`httponly`, `samesite`)
- CSRF token validation di seluruh aksi POST sensitif
- Role-based access control di router
- Output escaping pada view (`htmlspecialchars`)

## Skema Data Inti
Tabel utama yang dipakai saat ini:
- `users`
- `guru`
- `siswa`
- `mapel`
- `siswa_mapel`
- `kelas`
- `jadwal`
- `nilai`
- `pendaftaran`
- `pendaftaran_mapel`
- `id_counter`
- `wali_murid` (sudah disiapkan pada skema migrasi)

## SRS (Software Requirements Specification)
### 1. Pendahuluan
Dokumen ini mendefinisikan kebutuhan perangkat lunak untuk sistem Bimbel Orion.
Ruang lingkup SRS meliputi modul autentikasi, manajemen user, manajemen relasi belajar, jadwal, nilai, dan pendaftaran calon siswa.
Dokumen ini digunakan sebagai acuan pengembangan, pengujian, dan validasi penerimaan sistem.

### 2. Deskripsi Umum Sistem
Sistem adalah aplikasi manajemen bimbel berbasis web dengan 4 aktor:
- Admin
- Guru
- Siswa
- Calon siswa (belum login, melalui pendaftaran publik)

Ringkasan proses bisnis:
- Calon siswa mendaftar melalui halaman publik.
- Admin mengelola akun, data siswa/guru, relasi mapel, dan jadwal.
- Guru mengelola nilai berdasarkan jadwal mengajar.
- Siswa melihat jadwal dan nilai miliknya.

Batasan sistem saat ini:
- Modul absensi dan profil (guru/siswa/admin) masih placeholder tampilan.
- Integrasi pihak ketiga (payment, WhatsApp gateway, LMS eksternal) belum tersedia.

### 3. Kebutuhan Fungsional
- `FR-01`: Sistem harus menyediakan login berbasis email dan password.
- `FR-02`: Sistem harus mengarahkan user ke dashboard sesuai role.
- `FR-03`: Admin harus dapat menambah, mengubah, unlock, dan menghapus user.
- `FR-04`: Sistem harus melarang perubahan role langsung pada user yang sudah ada.
- `FR-05`: Admin harus dapat mengelola data siswa (profil dan relasi mapel siswa).
- `FR-06`: Admin harus dapat melihat data guru.
- `FR-07`: Admin harus dapat membuat, mengubah, dan menghapus jadwal.
- `FR-08`: Sistem harus menolak jadwal yang bentrok pada guru/siswa di hari dan rentang jam yang sama.
- `FR-09`: Guru harus dapat menambah, mengubah, dan menghapus nilai siswa.
- `FR-10`: Siswa harus dapat melihat nilai miliknya.
- `FR-11`: Siswa harus dapat melihat jadwal belajarnya.
- `FR-12`: Calon siswa harus dapat mendaftar dengan minimal satu mapel.
- `FR-13`: Sistem harus menyimpan relasi mapel pendaftaran pada tabel `pendaftaran_mapel`.
- `FR-14`: Sistem harus menerapkan cooldown pendaftaran ulang berbasis email.
- `FR-15`: Semua aksi POST sensitif harus tervalidasi CSRF token.

### 4. Kebutuhan Non-Fungsional
- `NFR-01`: Sistem berjalan pada PHP 8+ dan MySQL/MariaDB.
- `NFR-02`: Query database harus menggunakan prepared statement PDO.
- `NFR-03`: Password disimpan dalam bentuk hash.
- `NFR-04`: Session harus memiliki timeout idle dan batas masa hidup maksimum.
- `NFR-05`: Sistem menerapkan role-based access control pada routing.
- `NFR-06`: UI utama harus dapat digunakan pada desktop dan mobile.
- `NFR-07`: Operasi CRUD utama pada data skala kecil-menengah ditargetkan responsif (sekitar < 2 detik pada environment lokal/staging normal).
- `NFR-08`: Struktur kode harus modular agar mudah dirawat dan dikembangkan.

### 5. Kebutuhan Antarmuka
Antarmuka pengguna:
- Halaman publik: landing page, login, pendaftaran.
- Halaman dashboard berbasis role: admin, guru, siswa.
- Form utama: kelola user, kelola relasi mapel, kelola jadwal, input nilai.

Antarmuka perangkat lunak:
- HTTP request melalui `index.php?page=...`.
- Database interface melalui PDO (`config/database.php`).
- Session dan CSRF helper melalui `config/session.php`.

Antarmuka data:
- Input form utama: email, password, role, data siswa/guru, jadwal, nilai, dan pendaftaran mapel.
- Output utama: tabel data user/siswa/guru/jadwal/nilai dan pesan flash status operasi.

### 6. Diagram Pendukung (Opsional)
Diagram yang disarankan untuk dilampirkan pada dokumen terpisah:
- Use Case Diagram (aktor: Admin, Guru, Siswa, Calon Siswa)
- ERD (tabel: `users`, `guru`, `siswa`, `mapel`, `siswa_mapel`, `kelas`, `jadwal`, `nilai`, `pendaftaran`, `pendaftaran_mapel`)
- Activity Diagram untuk alur login, pendaftaran, dan input nilai

### 7. Acceptance Criteria
- `AC-01`: Login valid harus mengarahkan user ke dashboard sesuai role.
- `AC-02`: Login gagal berulang harus menaikkan `attempts` dan mengunci akun saat melewati batas.
- `AC-03`: Admin berhasil membuat user baru dengan role valid dan data tersimpan di tabel terkait.
- `AC-04`: Perubahan role langsung pada user existing ditolak oleh sistem.
- `AC-05`: Admin dapat menambah relasi mapel siswa tanpa duplikasi relasi yang sama.
- `AC-06`: Sistem menolak pembuatan/ubah jadwal yang bentrok.
- `AC-07`: Guru dapat menyimpan dan mengubah nilai untuk jadwal yang dia ajar.
- `AC-08`: Siswa hanya melihat nilai miliknya sendiri.
- `AC-09`: Pendaftaran publik gagal jika mapel kosong atau email masih dalam masa cooldown.
- `AC-10`: Request POST tanpa CSRF token valid harus ditolak.

## Menjalankan Proyek Secara Lokal
1. Pastikan PHP 8+ dan MySQL/MariaDB aktif (contoh: XAMPP).
2. Letakkan project di web root (contoh: `htdocs/bimbel-orion`).
3. Buat database sesuai `config/database.php` (default: `bimbelku`).
4. Sesuaikan kredensial DB di `config/database.php` jika diperlukan.
5. Jalankan migrasi skema:
   ```bash
   php scripts/migrate_to_erd.php
   ```
6. Akses aplikasi:
   - Apache/XAMPP: `http://localhost/bimbel-orion/index.php`
   - Atau PHP built-in server dari root project:
     ```bash
     php -S localhost:8000
     ```
     lalu buka `http://localhost:8000/index.php`

## Catatan Pengembangan
- File `_tmp_*.php` di root (jika ada) hanya untuk utilitas debug lokal.
- Saat ini modul absensi dan profil pada area guru/siswa/admin masih berupa placeholder tampilan.
- Proyek belum memakai Composer, jadi tidak ada langkah install dependency tambahan.
