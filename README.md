# Bimbel Orion

Sistem Manajemen Bimbel berbasis web untuk membantu operasional lembaga bimbingan belajar.  
Project ini mendukung alur kerja multi-role: admin, guru, siswa, dan wali murid dalam satu aplikasi.

## 1. Deskripsi Singkat

Bimbel Orion adalah aplikasi manajemen bimbel dengan arsitektur **PHP native MVC** (tanpa framework).  
Fitur utamanya meliputi pengelolaan user, jadwal, nilai, absensi, relasi pengajar, dan monitoring pembelajaran.

## 2. Teknologi yang Digunakan

- PHP native MVC (tanpa framework)
- MySQL / MariaDB
- PDO (PHP Data Objects) untuk akses database
- HTML, CSS, JavaScript

## 3. Struktur Folder Singkat

```text
ManajemenBimbel/
|-- index.php                # Entry point aplikasi
|-- .env.example             # Contoh konfigurasi environment
|-- config/                  # Konfigurasi (DB, env loader, session, rate limiter)
|-- core/                    # Komponen inti (Router)
|-- controllers/             # Controller per role/fitur
|-- models/                  # Model/Repository/Service
|-- views/                   # Halaman dan template tampilan
|-- public/                  # Asset statis (css, js, svg, images)
|-- database/                # Script migrasi database
|-- helpers/                 # Helper umum
|-- storage/                 # Penyimpanan runtime (mis. rate limit)
|-- docs/                    # Dokumentasi tambahan project
```

## 4. Cara Setup Lokal (XAMPP)

1. Install XAMPP (minimal PHP 8+ dan MySQL aktif).
2. Letakkan folder project ke:
   - `C:\xampp\htdocs\ManajemenBimbel`
3. Jalankan:
   - Apache
   - MySQL
4. Pastikan folder project bisa diakses dari browser.

## 5. Cara Membuat Database

1. Buka `http://localhost/phpmyadmin`.
2. Buat database baru, contoh: `bimbel_orion`.
3. Sesuaikan nama database pada file `.env` (lihat bagian berikutnya).
4. Jalankan migrasi (jika diperlukan oleh project):
   - lewat terminal di root project:
   ```bash
   php database/migrate.php
   ```

Catatan:
- Jika project Anda menggunakan file SQL terpisah di folder `database/migrations`, import/jalankan file tersebut sesuai urutan.

## 6. Cara Mengatur `.env`

1. Copy file contoh:
   - `.env.example` menjadi `.env`
2. Isi variabel sesuai lingkungan lokal Anda.

Contoh isi `.env`:

```env
DB_HOST=localhost
DB_NAME=bimbel_orion
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

APP_NAME="Bimbel Orion"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/ManajemenBimbel
```

## 7. Cara Menjalankan Project di Browser

Setelah Apache dan MySQL aktif, buka:

```text
http://localhost/ManajemenBimbel
```

Atau langsung:

```text
http://localhost/ManajemenBimbel/index.php
```

## 8. Daftar Role User

- `admin`
- `guru`
- `siswa`
- `wali_murid` (wali murid)

Setiap role memiliki hak akses halaman dan fitur yang berbeda melalui sistem routing dan validasi role.

## 9. Fitur Utama Sistem

- Autentikasi login/logout
- Dashboard per role
- Kelola user (admin)
- Kelola data guru, siswa, wali murid (admin)
- Kelola mapel dan relasi pengajar (admin)
- Kelola jadwal bimbel (admin)
- Input dan monitoring nilai (guru/admin/siswa/wali murid)
- Input dan monitoring absensi (guru/admin/siswa/wali murid)
- Halaman profil per role
- Proteksi CSRF, session management, dan rate limiting

## 10. Catatan Pengembangan Berikutnya

- Tambahkan automated testing (unit/integration) untuk modul kritis (auth, nilai, absensi, role access).
- Rapikan konsistensi pemisahan concern agar query selalu berada di model/repository/service (bukan controller/view).
- Tingkatkan dokumentasi API/action AJAX per modul agar onboarding developer baru lebih cepat.
- Tambahkan logging terstruktur untuk error dan audit aktivitas penting.
- Siapkan pipeline quality check (lint, syntax check, test) sebelum deployment.

---

Jika Anda mahasiswa yang baru pertama kali mengerjakan project MVC native:
- Mulailah dari `index.php` -> `core/Router.php` -> controller -> model -> view.
- Ubah satu hal kecil dulu, lalu uji hasilnya di browser sebelum lanjut ke perubahan berikutnya.
