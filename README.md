# 🏪 Griya Space - E-Commerce Bahan Bangunan

**Griya Space** adalah platform e-commerce dan sistem manajemen toko bangunan berbasis web yang dirancang mirip dengan Shopee, namun dikhususkan untuk kebutuhan bahan bangunan. Aplikasi ini mempermudah pengelolaan produk, stok, transaksi, hingga multi-vendor (supplier) secara terintegrasi.

---

## 🚀 Fitur Utama
* **Multi-Role Access**: Manajemen akses untuk Pembeli, Penjual/Supplier, dan Admin Toko.
* **Manajemen Produk & Stok**: Pembaruan stok bahan bangunan secara *real-time*.
* **Sistem Keranjang & Checkout**: Proses belanja yang intuitif seperti *marketplace* besar.
* **Laporan Penjualan**: Ringkasan transaksi terintegrasi untuk kebutuhan operasional toko.

## 🛠️ Teknologi yang Digunakan
* **Backend:** PHP Native / Framework
* **Database:** MySQL
* **Frontend:** HTML5, CSS3, JavaScript

## 📂 Struktur Repositori
* `/griya-space` : Berisi seluruh file source code aplikasi web.
* `/database` : Berisi file `.sql` untuk skema database aplikasi.

---

## 💻 Cara Menjalankan Proyek Secara Lokal
Jika Anda ingin menguji coba aplikasi ini di komputer Anda, ikuti langkah berikut:

1. **Download / Clone Repositori:**
   Unduh zip proyek ini atau gunakan perintah git clone.
2. **Siapkan Database:**
   * Buka `localhost/phpmyadmin`.
   * Buat database baru.
   * *Import* file database `.sql` yang ada di dalam folder `/database`.
3. **Konfigurasi Koneksi:**
   * Sesuaikan *username* dan *password* database Anda pada file koneksi database Anda di folder proyek.
4. **Jalankan Aplikasi:**
   * Pindahkan folder proyek ke dalam direktori server lokal Anda (misal: `htdocs` untuk XAMPP).
   * Akses melalui browser di alamat `http://localhost/griya-space`.
