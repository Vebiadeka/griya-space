# Griya Space – Sistem Informasi Manajemen Toko Bangunan

Griya Space adalah sistem informasi manajemen toko bangunan berbasis web yang dikembangkan untuk membantu mengelola aktivitas operasional toko secara terintegrasi.

Sistem ini menyediakan beberapa hak akses pengguna, yaitu Pemilik, Admin, Kasir, Gudang, dan Pelanggan, dengan fitur yang disesuaikan berdasarkan kebutuhan masing-masing pengguna.

---

## Fitur Utama

### Multi-Role Access

Sistem memiliki hak akses berdasarkan peran pengguna:

- Pemilik – Memantau informasi dan aktivitas toko.
- Admin – Mengelola data dan pengguna sistem.
- Kasir – Mengelola proses transaksi penjualan.
- Gudang – Mengelola dan memantau persediaan barang.
- Pelanggan – Melihat produk dan melakukan transaksi.

### Manajemen Produk

- Menambahkan, mengubah, dan menghapus data produk.
- Mengelola informasi harga dan stok produk.
- Mengelola kategori bahan bangunan.

### Manajemen Kategori

- Mengelola kategori produk.
- Mengelompokkan produk berdasarkan jenis bahan bangunan.

### Manajemen Supplier

- Mengelola data supplier.
- Menyimpan informasi supplier yang berkaitan dengan produk toko.

### Manajemen Stok

- Memantau ketersediaan barang.
- Mengelola data persediaan produk.

### Transaksi

- Mengelola proses transaksi penjualan.
- Menyimpan data transaksi ke dalam database.
- Mendukung proses transaksi berdasarkan hak akses pengguna.

### Informasi dan Laporan

- Menampilkan informasi terkait aktivitas dan transaksi toko.
- Membantu pemilik dan pengelola dalam memantau operasional toko.

---

## Teknologi yang Digunakan

### Backend

- PHP Native

### Frontend

- HTML5
- CSS3
- JavaScript

### Database

- MySQL

### Development Tools

- Visual Studio Code
- Laragon
- phpMyAdmin

---

## Struktur Repository

```text
griya-space/
├── database/
│   └── *.sql
│
├── griya-space/
│   ├── assets/
│   ├── auth/
│   ├── config/
│   ├── dashboard/
│   │   ├── admin/
│   │   ├── kasir/
│   │   ├── gudang/
│   │   ├── pemilik/
│   │   └── pelanggan/
│   ├── cek_logo.php
│   └── index.php
│
└── README.md
