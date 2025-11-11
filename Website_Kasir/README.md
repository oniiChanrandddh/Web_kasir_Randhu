# WEBSITE_KASIR

Website Kasir adalah aplikasi berbasis web yang dirancang untuk membantu pengelolaan data penjualan, stok barang, dan manajemen pengguna di sebuah toko atau usaha kecil.
Proyek ini dibangun menggunakan PHP dan MySQL, dengan tampilan yang responsif serta sistem role-based access (admin dan kasir).

## Deskripsi Proyek

Aplikasi ini memiliki dua jenis pengguna utama:

* Admin: bertugas mengelola data pengguna, data barang, serta melihat laporan penjualan.
* Kasir: bertugas melakukan transaksi dan mencatat penjualan harian.

Proyek ini mendukung fitur autentikasi login, CRUD data barang, data user, dan data member, serta menampilkan informasi diskon berdasarkan level member (Bronze, Silver, Gold, Platinum).

## Teknologi yang Digunakan

* HTML, CSS, JavaScript
* PHP (Native, tanpa framework)
* MySQL
* XAMPP (sebagai server lokal)

## Struktur Folder

WEBSITE_KASIR/
├── config/
│   └── db.php
│
├── includes/
│   └── auth.php
│
├── public/
│   ├── edit_delete/
│   │   ├── edit_barang.php
│   │   ├── edit_member.php
│   │   ├── edit_user.php
│   │   ├── hapus_barang.php
│   │   ├── hapus_member.php
│   │   └── hapus_user.php
│   │
│   ├── register_user/
│   │   ├── addMember.php
│   │   └── addUser.php
│   │
│   ├── styles/
│   │   ├── adminStyle.css
│   │   ├── kasirStyle.css
│   │   └── loginStyle.css
│   │
│   ├── tambah_barang/
│   │   └── tambah_barang.php
│   │
│   ├── dashboardAdmin.php
│   ├── dashboardKasir.php
│   ├── index.php
│   └── logout.php
│
├── seeds/
│   └── seed.php
│
└── README.md


## Instalasi dan Penggunaan

1. Pastikan kamu sudah menginstal XAMPP.
2. Pindahkan folder proyek ini ke dalam direktori `htdocs` (biasanya: `C:\xampp\htdocs\`).
3. Buka phpMyAdmin di browser melalui `http://localhost/phpmyadmin`.
4. Buat database baru dengan nama sesuai konfigurasi di file `config/db.php`.
5. Import file database hasil ekspor dari proyek aslimu (biasanya berekstensi `.sql`).
6. Jalankan website dengan membuka:

   ```
   http://localhost/Website_Kasir/public/
   ```
7. Login dengan akun yang sudah tersedia di tabel user, atau tambahkan data baru langsung melalui phpMyAdmin.

## Fitur Utama

* Login dan Logout
* Manajemen Barang (Tambah, Edit, Hapus)
* Manajemen User (Tambah, Edit, Hapus)
* Manajemen Member (Tambah, Edit, Hapus)
* Penentuan diskon otomatis berdasarkan level member
* Tampilan dashboard untuk Admin dan Kasir

## Catatan Penting

Proyek ini dikerjakan secara pribadi oleh Randhu Paksi Membumi,
kelas XI PPLG 2, sebagai bagian dari latihan pembuatan aplikasi berbasis web dengan integrasi backend dan frontend.
Seluruh kode ditulis sendiri tanpa menggunakan template atau framework eksternal.


