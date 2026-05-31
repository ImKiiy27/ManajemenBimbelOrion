# AGENTS.md

## Aturan Kerja Project Bimbel Orion

1. Project ini adalah Sistem Manajemen Bimbel bernama **Bimbel Orion**.
2. Stack yang digunakan adalah **PHP native MVC tanpa framework**.
3. Jangan mengubah struktur besar project tanpa alasan kuat.
4. Jangan menghapus fitur yang sudah ada.
5. Setiap perubahan harus menjaga role **admin, guru, siswa, dan wali murid** tetap berjalan.
6. Gunakan **PDO** untuk query database.
7. Gunakan **prepared statement** untuk semua input user.
8. Jangan menaruh query database langsung di view.
9. Controller hanya mengatur request, validasi ringan, dan memanggil model/service/repository.
10. View hanya untuk tampilan HTML.
11. Setelah perubahan, pastikan tidak ada error syntax PHP.
12. Jika mengubah banyak file, jelaskan file apa saja yang berubah dan alasannya.
