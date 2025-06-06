# Aplikasi Peminjaman Sarana

Aplikasi web untuk mengelola peminjaman sarana dan prasarana. Aplikasi ini memungkinkan pengguna untuk melakukan peminjaman barang dan admin untuk mengelola inventaris serta menyetujui peminjaman.

## Fitur

### Admin
1. CRUD sarana/barang yang dapat dipinjam
2. Approval peminjaman
3. Approval pengembalian
4. Dashboard dengan ringkasan data peminjaman dan pengembalian
5. Laporan peminjaman dan pengembalian dengan fitur export

### User
1. Melakukan peminjaman barang
2. Melakukan pengembalian barang
3. Dashboard dengan ringkasan peminjaman pribadi

## Persyaratan Aplikasi

- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB
- Web Server (Apache/Nginx)
- XAMPP (rekomendasi untuk pengembangan lokal)

## Instalasi

1. Clone atau download repository ini ke direktori web server Anda (misalnya: `htdocs` untuk XAMPP)
2. Import file database `pjb_muhamad_alee_alghifari.sql` ke MySQL/MariaDB
3. Sesuaikan konfigurasi database di file `config/database.php` jika diperlukan
4. Akses aplikasi melalui web browser

## Akun Default

### Admin
- Username: Alee
- Password: muhamadaleealghifari

### User
- Username: Jea
- Password: jeaxkiko123

## Struktur Direktori

```
├── auth/
│   ├── login.php
│   └── logout.php
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   └── footer.php
├── approval.php
├── dashboard.php
├── index.php
├── peminjaman.php
├── report.php
├── sarana.php
├── pjb_muhamad_alee_alghifari.sql
└── README.md
```

## Penggunaan

1. Login sebagai admin atau user menggunakan akun yang tersedia
2. Admin dapat:
   - Mengelola data sarana di menu "Kelola Sarana"
   - Menyetujui peminjaman dan pengembalian di menu "Approval"
   - Melihat laporan di menu "Laporan"
3. User dapat:
   - Melakukan peminjaman di menu "Peminjaman"
   - Melihat status peminjaman di Dashboard

## Keamanan

- Semua input telah divalidasi dan dibersihkan untuk mencegah SQL injection
- Menggunakan prepared statements untuk query database
- Pemisahan hak akses antara admin dan user
- Enkripsi session untuk keamanan

## Pengembangan

Aplikasi ini dikembangkan menggunakan:
- PHP untuk backend
- MySQL untuk database
- Bootstrap 5 untuk frontend
- DataTables untuk tabel interaktif
- XLSX.js untuk export data ke Excel #
