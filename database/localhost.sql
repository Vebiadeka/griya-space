-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 07, 2026 at 03:28 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_griya_space`
--
CREATE DATABASE IF NOT EXISTS `db_griya_space` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `db_griya_space`;

-- --------------------------------------------------------

--
-- Table structure for table `barang_keluar`
--

CREATE TABLE `barang_keluar` (
  `id_barang_keluar` int NOT NULL,
  `nomor_keluar` varchar(30) NOT NULL,
  `id_user` int NOT NULL,
  `tanggal_keluar` datetime DEFAULT CURRENT_TIMESTAMP,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang_keluar`
--

INSERT INTO `barang_keluar` (`id_barang_keluar`, `nomor_keluar`, `id_user`, `tanggal_keluar`, `keterangan`) VALUES
(1, 'BK-20260813025233', 2, '2026-08-13 02:51:00', 'Pengeluaran stok untuk kebutuhan proyek.');

-- --------------------------------------------------------

--
-- Table structure for table `barang_masuk`
--

CREATE TABLE `barang_masuk` (
  `id_barang_masuk` int NOT NULL,
  `nomor_masuk` varchar(30) NOT NULL,
  `id_supplier` int DEFAULT NULL,
  `id_user` int NOT NULL,
  `tanggal_masuk` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_harga` decimal(15,2) DEFAULT '0.00',
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang_masuk`
--

INSERT INTO `barang_masuk` (`id_barang_masuk`, `nomor_masuk`, `id_supplier`, `id_user`, `tanggal_masuk`, `total_harga`, `keterangan`) VALUES
(1, 'BM-20260813023444', 1, 2, '2026-08-13 02:32:00', 900000.00, 'Penambahan stok semen');

-- --------------------------------------------------------

--
-- Table structure for table `detail_barang_keluar`
--

CREATE TABLE `detail_barang_keluar` (
  `id_detail_keluar` int NOT NULL,
  `id_barang_keluar` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah` int NOT NULL,
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_barang_keluar`
--

INSERT INTO `detail_barang_keluar` (`id_detail_keluar`, `id_barang_keluar`, `id_produk`, `jumlah`, `keterangan`) VALUES
(1, 1, 1, 10, 'Pengeluaran stok untuk kebutuhan proyek.');

-- --------------------------------------------------------

--
-- Table structure for table `detail_barang_masuk`
--

CREATE TABLE `detail_barang_masuk` (
  `id_detail_masuk` int NOT NULL,
  `id_barang_masuk` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah` int NOT NULL,
  `harga_beli` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_barang_masuk`
--

INSERT INTO `detail_barang_masuk` (`id_detail_masuk`, `id_barang_masuk`, `id_produk`, `jumlah`, `harga_beli`, `subtotal`) VALUES
(1, 1, 1, 20, 45000.00, 900000.00);

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail_pesanan` int NOT NULL,
  `id_pesanan` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah` int NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail_pesanan`, `id_pesanan`, `id_produk`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(3, 2, 2, 1, 180000.00, 180000.00),
(4, 2, 1, 9, 52000.00, 468000.00),
(5, 3, 2, 1, 180000.00, 180000.00),
(6, 4, 2, 3, 180000.00, 540000.00),
(7, 4, 1, 1, 52000.00, 52000.00);

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail_transaksi` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah` int NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `diskon` decimal(15,2) DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail_transaksi`, `id_transaksi`, `id_produk`, `jumlah`, `harga_satuan`, `diskon`, `subtotal`) VALUES
(1, 1, 2, 1, 180000.00, 0.00, 180000.00),
(2, 1, 1, 9, 52000.00, 0.00, 468000.00),
(3, 2, 2, 1, 180000.00, 0.00, 180000.00);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`, `created_at`) VALUES
(1, 'Semen', 'Berbagai jenis semen untuk konstruksi', '2026-08-11 14:51:05'),
(2, 'Pasir', 'Berbagai jenis pasir bangunan', '2026-08-11 14:51:05'),
(3, 'Batu', 'Batu dan material bangunan', '2026-08-11 14:51:05'),
(4, 'Besi', 'Besi untuk kebutuhan konstruksi', '2026-08-11 14:51:05'),
(5, 'Cat', 'Cat tembok dan kebutuhan pengecatan', '2026-08-11 14:51:05'),
(6, 'Keramik', 'Keramik lantai dan dinding', '2026-08-11 14:51:05'),
(7, 'Kayu', 'Kayu untuk kebutuhan konstruksi', '2026-08-11 14:51:05'),
(8, 'Pipa', 'Pipa dan perlengkapan plumbing', '2026-08-11 14:51:05'),
(9, 'Atap', 'Genteng dan material atap', '2026-08-11 14:51:05'),
(10, 'Peralatan', 'Peralatan dan perlengkapan bangunan', '2026-08-11 14:51:05');

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int NOT NULL,
  `id_pelanggan` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mutasi_stok`
--

CREATE TABLE `mutasi_stok` (
  `id_mutasi` int NOT NULL,
  `id_produk` int NOT NULL,
  `id_user` int NOT NULL,
  `jenis_mutasi` enum('Barang Masuk','Barang Keluar','Penjualan','Penyesuaian') NOT NULL,
  `jumlah` int NOT NULL,
  `stok_sebelum` int NOT NULL,
  `stok_sesudah` int NOT NULL,
  `referensi` varchar(50) DEFAULT NULL,
  `keterangan` text,
  `tanggal_mutasi` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mutasi_stok`
--

INSERT INTO `mutasi_stok` (`id_mutasi`, `id_produk`, `id_user`, `jenis_mutasi`, `jumlah`, `stok_sebelum`, `stok_sesudah`, `referensi`, `keterangan`, `tanggal_mutasi`) VALUES
(1, 1, 2, 'Barang Masuk', 20, 100, 120, 'BM-20260813023444', 'Barang masuk dari supplier', '2026-08-13 09:34:44'),
(2, 1, 2, 'Barang Keluar', 10, 120, 110, 'BK-20260813025233', 'Barang keluar dari persediaan', '2026-08-13 09:52:33'),
(3, 2, 3, 'Penjualan', 1, 50, 49, 'TRX-20260815030723', 'Penjualan dari pesanan 1', '2026-08-15 10:07:23'),
(4, 1, 3, 'Penjualan', 9, 110, 101, 'TRX-20260815030723', 'Penjualan dari pesanan 1', '2026-08-15 10:07:23'),
(5, 2, 3, 'Penjualan', 1, 49, 48, 'TRX-20260815091140', 'Penjualan dari pesanan PS-20260815085528-1', '2026-08-15 16:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `kode_pelanggan` varchar(30) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `id_user`, `kode_pelanggan`, `nama_pelanggan`, `no_telepon`, `email`, `alamat`, `created_at`) VALUES
(1, 6, 'PLG001', 'Budi Santoso', '081234567890', 'budi@gmail.com', 'Bandar Lampung', '2026-08-14 16:30:23');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `metode_pembayaran` enum('Cash','Transfer','QRIS','Debit','Kredit') NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `uang_diterima` decimal(15,2) DEFAULT '0.00',
  `kembalian` decimal(15,2) DEFAULT '0.00',
  `status` enum('Belum Lunas','Lunas') DEFAULT 'Lunas',
  `tanggal_pembayaran` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_transaksi`, `metode_pembayaran`, `jumlah_bayar`, `uang_diterima`, `kembalian`, `status`, `tanggal_pembayaran`) VALUES
(1, 1, 'Cash', 648000.00, 700000.00, 52000.00, 'Lunas', '2026-08-15 10:07:23'),
(2, 2, 'Cash', 180000.00, 200000.00, 20000.00, 'Lunas', '2026-08-15 16:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int NOT NULL,
  `nomor_pesanan` varchar(30) NOT NULL,
  `id_pelanggan` int NOT NULL,
  `tanggal_pesanan` datetime DEFAULT CURRENT_TIMESTAMP,
  `subtotal` decimal(15,2) DEFAULT '0.00',
  `diskon` decimal(15,2) DEFAULT '0.00',
  `total_harga` decimal(15,2) DEFAULT '0.00',
  `status` enum('Menunggu','Diproses','Siap Diambil','Selesai','Dibatalkan') DEFAULT 'Menunggu',
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `nomor_pesanan`, `id_pelanggan`, `tanggal_pesanan`, `subtotal`, `diskon`, `total_harga`, `status`, `catatan`) VALUES
(2, 'PS-20260814203207-1', 1, '2026-08-14 20:32:07', 648000.00, 0.00, 648000.00, 'Selesai', ''),
(3, 'PS-20260815085528-1', 1, '2026-08-15 08:55:28', 180000.00, 0.00, 180000.00, 'Selesai', ''),
(4, 'PS-20260829185208-1', 1, '2026-08-29 18:52:08', 592000.00, 0.00, 592000.00, 'Menunggu', 'GAAG');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int NOT NULL,
  `kode_produk` varchar(30) NOT NULL,
  `id_kategori` int NOT NULL,
  `id_supplier` int DEFAULT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `satuan` varchar(30) NOT NULL,
  `harga_beli` decimal(15,2) NOT NULL DEFAULT '0.00',
  `harga_jual` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stok` int NOT NULL DEFAULT '0',
  `stok_minimum` int NOT NULL DEFAULT '0',
  `deskripsi` text,
  `foto_produk` varchar(255) DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `kode_produk`, `id_kategori`, `id_supplier`, `nama_produk`, `satuan`, `harga_beli`, `harga_jual`, `stok`, `stok_minimum`, `deskripsi`, `foto_produk`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PRD001', 1, 1, 'Semen Portland 50 Kg', 'sak', 45000.00, 52000.00, 101, 20, 'Semen Portland untuk kebutuhan konstruksi dan pembangunan.', NULL, 'Aktif', '2026-08-11 22:11:33', '2026-08-15 03:07:23'),
(2, 'PRD002', 2, 2, 'Pasir Bangunan', 'm3', 149999.98, 180000.00, 48, 10, 'Pasir bangunan untuk kebutuhan konstruksi.', NULL, 'Aktif', '2026-08-12 21:33:55', '2026-08-15 09:11:40'),
(3, 'PRD003', 3, 2, 'Batu Bata Merah', 'pcs', 1200.00, 1600.00, 500, 50, '0', NULL, 'Nonaktif', '2026-08-15 05:49:12', '2026-08-15 05:57:25');

-- --------------------------------------------------------

--
-- Table structure for table `promo`
--

CREATE TABLE `promo` (
  `id_promo` int NOT NULL,
  `kode_promo` varchar(30) NOT NULL,
  `nama_promo` varchar(100) NOT NULL,
  `jenis_diskon` enum('Persentase','Nominal') NOT NULL,
  `nilai_diskon` decimal(15,2) NOT NULL,
  `minimal_pembelian` decimal(15,2) DEFAULT '0.00',
  `maksimal_diskon` decimal(15,2) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_produk`
--

CREATE TABLE `promo_produk` (
  `id_promo_produk` int NOT NULL,
  `id_promo` int NOT NULL,
  `id_produk` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_role` int NOT NULL,
  `nama_role` varchar(50) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_role`, `nama_role`, `deskripsi`, `created_at`) VALUES
(1, 'Pemilik Toko', 'Mengawasi dan mengambil keputusan bisnis', '2026-08-11 14:51:04'),
(2, 'Admin', 'Mengelola data dan sistem', '2026-08-11 14:51:04'),
(3, 'Kasir', 'Mengelola transaksi penjualan dan pembayaran', '2026-08-11 14:51:04'),
(4, 'Staff Gudang', 'Mengelola persediaan dan stok barang', '2026-08-11 14:51:04'),
(5, 'Pelanggan', 'Melihat produk dan melakukan pemesanan', '2026-08-11 14:51:04');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int NOT NULL,
  `kode_supplier` varchar(30) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `nama_kontak` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `kode_supplier`, `nama_supplier`, `nama_kontak`, `no_telepon`, `email`, `alamat`, `status`, `created_at`) VALUES
(1, 'SUP001', 'PT Semen Indonesia', 'Budi Santoso', '081234567801', 'semen@supplier.com', 'Bandar Lampung', 'Aktif', '2026-08-11 21:38:12'),
(2, 'SUP002', 'CV Sumber Bangunan', 'Andi Wijaya', '081234567802', 'sumber@supplier.com', 'Bandar Lampung', 'Aktif', '2026-08-11 21:40:43'),
(3, 'SUP003', 'PT Baja Nusantara', 'Rizky Pratama', '081234567803', 'baja@supplier.com', 'Bandar Lampung', 'Aktif', '2026-08-11 21:42:32'),
(4, 'SUP004', 'CV Makmur Jaya', 'Dedi Kurniawan', '081234567804', 'makmur@supplier.com', 'Bandar Lampung', 'Aktif', '2026-08-11 21:43:59'),
(5, 'SUP005', 'PT Keramik Indonesia', 'Fajar Hidayat', '081234567805', 'keramik@supplier.com', 'Bandar Lampung', 'Aktif', '2026-08-11 21:45:09');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `nomor_transaksi` varchar(30) NOT NULL,
  `id_pelanggan` int DEFAULT NULL,
  `id_user` int NOT NULL,
  `id_promo` int DEFAULT NULL,
  `tanggal_transaksi` datetime DEFAULT CURRENT_TIMESTAMP,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_harga` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('Menunggu','Diproses','Selesai','Dibatalkan') DEFAULT 'Selesai',
  `keterangan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `nomor_transaksi`, `id_pelanggan`, `id_user`, `id_promo`, `tanggal_transaksi`, `subtotal`, `diskon`, `total_harga`, `status`, `keterangan`) VALUES
(1, 'TRX-20260815030723', 1, 3, NULL, '2026-08-15 03:07:23', 648000.00, 0.00, 648000.00, 'Selesai', 'Pembayaran pesanan 2'),
(2, 'TRX-20260815091140', 1, 3, NULL, '2026-08-15 16:11:40', 180000.00, 0.00, 180000.00, 'Selesai', 'Pembayaran pesanan PS-20260815085528-1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `id_role` int NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat` text,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `id_role`, `nama_lengkap`, `username`, `password`, `email`, `no_telepon`, `alamat`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pemilik Griya Space', 'owner', 'password', 'owner@griyaspaces.com', NULL, NULL, 'Aktif', '2026-08-11 14:51:05', '2026-08-11 14:51:05'),
(2, 2, 'Administrator Griya Space', 'admin', 'password', 'admin@griyaspaces.com', NULL, NULL, 'Aktif', '2026-08-11 14:51:05', '2026-08-11 14:51:05'),
(3, 3, 'Kasir Griya Space', 'kasir', 'password', 'kasir@griyaspaces.com', NULL, NULL, 'Aktif', '2026-08-11 14:51:05', '2026-08-11 14:51:05'),
(4, 4, 'Staff Gudang Griya Space', 'gudang', 'password', 'gudang@griyaspaces.com', NULL, NULL, 'Aktif', '2026-08-11 14:51:05', '2026-08-11 14:51:05'),
(5, 5, 'Pelanggan Griya Space', 'pelanggan', 'password', 'pelanggan@griyaspaces.com', NULL, NULL, 'Aktif', '2026-08-11 14:51:05', '2026-08-11 14:51:05'),
(6, 5, 'Budi Santoso', 'budi123', '$2y$10$LrdNTfjK2ozaMHH177C5NuUicVVPWcbqI99oYqtntfkUNMYEdW7uK', 'budi@gmail.com', '081234567890', 'Bandar Lampung', 'Aktif', '2026-08-11 21:14:54', '2026-08-11 21:14:54'),
(7, 3, 'Kasir Testing 2', 'kasir2', '$2y$10$8fwlaPH9NjbNWf6SC2zfZu7v5..Gp5yODxawnah3MGY2PmZkYmmGi', 'kasir2@griyaspaces.com', '081234567891', 'Bandar Lampung', 'Nonaktif', '2026-08-15 08:28:47', '2026-08-15 08:37:20'),
(8, 5, 'doni', 'doni', '$2y$10$uo/DHk4r7qi6UZ./cuWZNexFNerPrKDVJHvMYP.Eu8JoDlPRUy8yG', 'doni1212@gmail.com', '087713282923', 'Gedung harapan', 'Aktif', '2026-08-29 18:55:20', '2026-08-29 18:55:20');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_laporan_penjualan`
-- (See below for the actual view)
--
CREATE TABLE `view_laporan_penjualan` (
`id_transaksi` int
,`nomor_transaksi` varchar(30)
,`tanggal_transaksi` datetime
,`pelanggan` varchar(100)
,`kasir` varchar(100)
,`subtotal` decimal(15,2)
,`diskon` decimal(15,2)
,`total_harga` decimal(15,2)
,`status` enum('Menunggu','Diproses','Selesai','Dibatalkan')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_stok_produk`
-- (See below for the actual view)
--
CREATE TABLE `view_stok_produk` (
`id_produk` int
,`kode_produk` varchar(30)
,`nama_produk` varchar(150)
,`nama_kategori` varchar(100)
,`satuan` varchar(30)
,`harga_beli` decimal(15,2)
,`harga_jual` decimal(15,2)
,`stok` int
,`stok_minimum` int
,`status_stok` varchar(12)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD PRIMARY KEY (`id_barang_keluar`),
  ADD UNIQUE KEY `nomor_keluar` (`nomor_keluar`),
  ADD KEY `fk_barang_keluar_user` (`id_user`);

--
-- Indexes for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD PRIMARY KEY (`id_barang_masuk`),
  ADD UNIQUE KEY `nomor_masuk` (`nomor_masuk`),
  ADD KEY `fk_barang_masuk_supplier` (`id_supplier`),
  ADD KEY `fk_barang_masuk_user` (`id_user`);

--
-- Indexes for table `detail_barang_keluar`
--
ALTER TABLE `detail_barang_keluar`
  ADD PRIMARY KEY (`id_detail_keluar`),
  ADD KEY `fk_detail_keluar_header` (`id_barang_keluar`),
  ADD KEY `fk_detail_keluar_produk` (`id_produk`);

--
-- Indexes for table `detail_barang_masuk`
--
ALTER TABLE `detail_barang_masuk`
  ADD PRIMARY KEY (`id_detail_masuk`),
  ADD KEY `fk_detail_masuk_header` (`id_barang_masuk`),
  ADD KEY `fk_detail_masuk_produk` (`id_produk`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail_pesanan`),
  ADD KEY `fk_detail_pesanan_header` (`id_pesanan`),
  ADD KEY `fk_detail_pesanan_produk` (`id_produk`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail_transaksi`),
  ADD KEY `fk_detail_transaksi_header` (`id_transaksi`),
  ADD KEY `fk_detail_transaksi_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD UNIQUE KEY `id_pelanggan` (`id_pelanggan`,`id_produk`),
  ADD KEY `fk_keranjang_produk` (`id_produk`);

--
-- Indexes for table `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  ADD PRIMARY KEY (`id_mutasi`),
  ADD KEY `fk_mutasi_produk` (`id_produk`),
  ADD KEY `fk_mutasi_user` (`id_user`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `kode_pelanggan` (`kode_pelanggan`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `fk_pembayaran_transaksi` (`id_transaksi`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`),
  ADD KEY `fk_pesanan_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD KEY `fk_produk_kategori` (`id_kategori`),
  ADD KEY `fk_produk_supplier` (`id_supplier`);

--
-- Indexes for table `promo`
--
ALTER TABLE `promo`
  ADD PRIMARY KEY (`id_promo`),
  ADD UNIQUE KEY `kode_promo` (`kode_promo`);

--
-- Indexes for table `promo_produk`
--
ALTER TABLE `promo_produk`
  ADD PRIMARY KEY (`id_promo_produk`),
  ADD UNIQUE KEY `id_promo` (`id_promo`,`id_produk`),
  ADD KEY `fk_promo_produk_produk` (`id_produk`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `nama_role` (`nama_role`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`),
  ADD UNIQUE KEY `kode_supplier` (`kode_supplier`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD UNIQUE KEY `nomor_transaksi` (`nomor_transaksi`),
  ADD KEY `fk_transaksi_pelanggan` (`id_pelanggan`),
  ADD KEY `fk_transaksi_user` (`id_user`),
  ADD KEY `fk_transaksi_promo` (`id_promo`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_users_role` (`id_role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `id_barang_keluar` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  MODIFY `id_barang_masuk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail_barang_keluar`
--
ALTER TABLE `detail_barang_keluar`
  MODIFY `id_detail_keluar` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail_barang_masuk`
--
ALTER TABLE `detail_barang_masuk`
  MODIFY `id_detail_masuk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail_pesanan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  MODIFY `id_mutasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `promo`
--
ALTER TABLE `promo`
  MODIFY `id_promo` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_produk`
--
ALTER TABLE `promo_produk`
  MODIFY `id_promo_produk` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

-- --------------------------------------------------------

--
-- Structure for view `view_laporan_penjualan`
--
DROP TABLE IF EXISTS `view_laporan_penjualan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_laporan_penjualan`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `t`.`nomor_transaksi` AS `nomor_transaksi`, `t`.`tanggal_transaksi` AS `tanggal_transaksi`, coalesce(`p`.`nama_pelanggan`,'Umum') AS `pelanggan`, `u`.`nama_lengkap` AS `kasir`, `t`.`subtotal` AS `subtotal`, `t`.`diskon` AS `diskon`, `t`.`total_harga` AS `total_harga`, `t`.`status` AS `status` FROM ((`transaksi` `t` left join `pelanggan` `p` on((`t`.`id_pelanggan` = `p`.`id_pelanggan`))) join `users` `u` on((`t`.`id_user` = `u`.`id_user`))) ;

-- --------------------------------------------------------

--
-- Structure for view `view_stok_produk`
--
DROP TABLE IF EXISTS `view_stok_produk`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_stok_produk`  AS SELECT `p`.`id_produk` AS `id_produk`, `p`.`kode_produk` AS `kode_produk`, `p`.`nama_produk` AS `nama_produk`, `k`.`nama_kategori` AS `nama_kategori`, `p`.`satuan` AS `satuan`, `p`.`harga_beli` AS `harga_beli`, `p`.`harga_jual` AS `harga_jual`, `p`.`stok` AS `stok`, `p`.`stok_minimum` AS `stok_minimum`, (case when (`p`.`stok` <= 0) then 'Habis' when (`p`.`stok` <= `p`.`stok_minimum`) then 'Stok Menipis' else 'Aman' end) AS `status_stok` FROM (`produk` `p` join `kategori` `k` on((`p`.`id_kategori` = `k`.`id_kategori`))) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD CONSTRAINT `fk_barang_keluar_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD CONSTRAINT `fk_barang_masuk_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_barang_masuk_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `detail_barang_keluar`
--
ALTER TABLE `detail_barang_keluar`
  ADD CONSTRAINT `fk_detail_keluar_header` FOREIGN KEY (`id_barang_keluar`) REFERENCES `barang_keluar` (`id_barang_keluar`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_keluar_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `detail_barang_masuk`
--
ALTER TABLE `detail_barang_masuk`
  ADD CONSTRAINT `fk_detail_masuk_header` FOREIGN KEY (`id_barang_masuk`) REFERENCES `barang_masuk` (`id_barang_masuk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_masuk_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_pesanan_header` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_pesanan_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `fk_detail_transaksi_header` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_transaksi_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `fk_keranjang_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_keranjang_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  ADD CONSTRAINT `fk_mutasi_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mutasi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD CONSTRAINT `fk_pelanggan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_pembayaran_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produk_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `promo_produk`
--
ALTER TABLE `promo_produk`
  ADD CONSTRAINT `fk_promo_produk_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_promo_produk_promo` FOREIGN KEY (`id_promo`) REFERENCES `promo` (`id_promo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_promo` FOREIGN KEY (`id_promo`) REFERENCES `promo` (`id_promo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON DELETE RESTRICT ON UPDATE CASCADE;
--
-- Database: `db_toko_bangunan`
--
CREATE DATABASE IF NOT EXISTS `db_toko_bangunan` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_toko_bangunan`;

-- --------------------------------------------------------

--
-- Table structure for table `detail_pembelian`
--

CREATE TABLE `detail_pembelian` (
  `id_detail_pembelian` int UNSIGNED NOT NULL,
  `id_pembelian` int UNSIGNED NOT NULL,
  `id_produk` int UNSIGNED NOT NULL,
  `jumlah` decimal(15,2) NOT NULL DEFAULT '0.00',
  `harga_beli` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_pembelian`
--

INSERT INTO `detail_pembelian` (`id_detail_pembelian`, `id_pembelian`, `id_produk`, `jumlah`, `harga_beli`, `subtotal`) VALUES
(1, 1, 1, 20.00, 58000.00, 1160000.00),
(2, 1, 2, 20.00, 57000.00, 1140000.00),
(3, 1, 6, 20.00, 20000.00, 400000.00),
(4, 1, 7, 10.00, 20000.00, 200000.00),
(5, 2, 8, 10.00, 95000.00, 950000.00),
(6, 2, 10, 10.00, 80000.00, 800000.00),
(7, 2, 12, 10.00, 8000.00, 80000.00),
(8, 2, 13, 10.00, 15000.00, 150000.00),
(9, 3, 14, 20.00, 18000.00, 360000.00),
(10, 3, 15, 20.00, 22000.00, 440000.00),
(11, 3, 19, 50.00, 8500.00, 425000.00),
(12, 3, 21, 10.00, 18000.00, 180000.00),
(13, 3, 22, 5.00, 12000.00, 60000.00);

-- --------------------------------------------------------

--
-- Table structure for table `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `id_detail_penjualan` int UNSIGNED NOT NULL,
  `id_penjualan` int UNSIGNED NOT NULL,
  `id_produk` int UNSIGNED NOT NULL,
  `jumlah` decimal(15,2) NOT NULL DEFAULT '0.00',
  `harga_jual` decimal(15,2) NOT NULL DEFAULT '0.00',
  `diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_penjualan`
--

INSERT INTO `detail_penjualan` (`id_detail_penjualan`, `id_penjualan`, `id_produk`, `jumlah`, `harga_jual`, `diskon`, `subtotal`) VALUES
(1, 1, 1, 5.00, 65000.00, 0.00, 325000.00),
(2, 2, 8, 2.00, 115000.00, 0.00, 230000.00),
(3, 3, 14, 5.00, 25000.00, 0.00, 125000.00),
(4, 3, 15, 5.00, 30000.00, 0.00, 150000.00),
(5, 3, 19, 20.00, 12000.00, 0.00, 240000.00),
(6, 4, 21, 5.00, 28000.00, 0.00, 140000.00),
(7, 4, 22, 5.00, 20000.00, 0.00, 100000.00),
(8, 4, 12, 10.00, 12000.00, 0.00, 120000.00),
(9, 4, 13, 3.00, 20000.00, 0.00, 60000.00);

-- --------------------------------------------------------

--
-- Table structure for table `detail_retur_penjualan`
--

CREATE TABLE `detail_retur_penjualan` (
  `id_detail_retur` int UNSIGNED NOT NULL,
  `id_retur_penjualan` int UNSIGNED NOT NULL,
  `id_produk` int UNSIGNED NOT NULL,
  `jumlah` decimal(15,2) NOT NULL DEFAULT '0.00',
  `harga` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `kondisi_barang` enum('baik','rusak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'baik',
  `keterangan` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_retur_penjualan`
--

INSERT INTO `detail_retur_penjualan` (`id_detail_retur`, `id_retur_penjualan`, `id_produk`, `jumlah`, `harga`, `subtotal`, `kondisi_barang`, `keterangan`) VALUES
(1, 1, 12, 1.00, 12000.00, 12000.00, 'rusak', 'Bulu kuas rusak');

-- --------------------------------------------------------

--
-- Table structure for table `detail_stok_opname`
--

CREATE TABLE `detail_stok_opname` (
  `id_detail_opname` int UNSIGNED NOT NULL,
  `id_opname` int UNSIGNED NOT NULL,
  `id_produk` int UNSIGNED NOT NULL,
  `stok_sistem` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stok_fisik` decimal(15,2) NOT NULL DEFAULT '0.00',
  `selisih` decimal(15,2) NOT NULL DEFAULT '0.00',
  `keterangan` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_stok_opname`
--

INSERT INTO `detail_stok_opname` (`id_detail_opname`, `id_opname`, `id_produk`, `stok_sistem`, `stok_fisik`, `selisih`, `keterangan`) VALUES
(1, 1, 1, 45.00, 45.00, 0.00, 'Stok sesuai'),
(2, 1, 2, 45.00, 44.00, -1.00, 'Selisih satu sak'),
(3, 1, 8, 23.00, 23.00, 0.00, 'Stok sesuai'),
(4, 1, 14, 75.00, 75.00, 0.00, 'Stok sesuai'),
(5, 1, 19, 280.00, 278.00, -2.00, 'Selisih kabel');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Semen', 'Produk semen untuk kebutuhan konstruksi', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(2, 'Besi', 'Produk besi dan baja', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(3, 'Cat', 'Cat tembok dan cat kayu/besi', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(4, 'Pipa', 'Pipa PVC dan perlengkapannya', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(5, 'Keramik', 'Keramik lantai dan dinding', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(6, 'Kelistrikan', 'Kabel dan perlengkapan listrik', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(7, 'Peralatan', 'Peralatan pertukangan', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(8, 'Bahan Bangunan', 'Berbagai bahan bangunan lainnya', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `merek`
--

CREATE TABLE `merek` (
  `id_merek` int UNSIGNED NOT NULL,
  `nama_merek` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `merek`
--

INSERT INTO `merek` (`id_merek`, `nama_merek`, `deskripsi`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tiga Roda', 'Merek semen', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(2, 'Gresik', 'Merek semen', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(3, 'Dulux', 'Merek cat', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(4, 'Avian', 'Merek cat', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(5, 'Nippon Paint', 'Merek cat', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(6, 'Eterna', 'Merek kabel dan kelistrikan', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(7, 'Tanpa Merek', 'Produk tanpa merek tertentu', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int UNSIGNED NOT NULL,
  `kode_pelanggan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pelanggan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `kode_pelanggan`, `nama_pelanggan`, `no_telepon`, `alamat`, `email`, `created_at`, `updated_at`) VALUES
(1, 'PLG001', 'Andi Setiawan', '081211111111', 'Bandar Lampung', 'andi@example.com', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(2, 'PLG002', 'Budi Santoso', '081222222222', 'Kedaton', 'budi@example.com', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(3, 'PLG003', 'Citra Lestari', '081233333333', 'Rajabasa', 'citra@example.com', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(4, 'PLG004', 'Dedi Irawan', '081244444444', 'Sukarame', 'dedi@example.com', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(5, 'PLG005', 'Eko Prasetyo', '081255555555', 'Way Halim', 'eko@example.com', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(6, 'PLG006', 'Fajar Nugroho', '081266666666', 'Kemiling', 'fajar@example.com', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(7, 'PLG007', 'Gilang Ramadhan', '081277777777', 'Tanjung Karang', 'gilang@example.com', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(8, 'PLG008', 'Hendra Wijaya', '081288888888', 'Teluk Betung', 'hendra@example.com', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(9, 'PLG009', 'Indra Saputra', '081299999999', 'Sukabumi', 'indra@example.com', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(10, 'PLG010', 'Joko Susanto', '081200000000', 'Labuhan Ratu', 'joko@example.com', '2026-08-11 02:19:23', '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `pembelian`
--

CREATE TABLE `pembelian` (
  `id_pembelian` int UNSIGNED NOT NULL,
  `nomor_pembelian` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_pembelian` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_supplier` int UNSIGNED NOT NULL,
  `id_user` int UNSIGNED NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','selesai','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'selesai',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pembelian`
--

INSERT INTO `pembelian` (`id_pembelian`, `nomor_pembelian`, `tanggal_pembelian`, `id_supplier`, `id_user`, `subtotal`, `diskon`, `grand_total`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PB-20260801-001', '2026-08-01 09:00:00', 1, 3, 2900000.00, 0.00, 2900000.00, 'selesai', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(2, 'PB-20260803-002', '2026-08-03 10:30:00', 2, 3, 2100000.00, 100000.00, 2000000.00, 'selesai', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(3, 'PB-20260805-003', '2026-08-05 14:00:00', 3, 3, 1500000.00, 0.00, 1500000.00, 'selesai', '2026-08-11 02:19:23', '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` int UNSIGNED NOT NULL,
  `nomor_transaksi` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_penjualan` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_pelanggan` int UNSIGNED DEFAULT NULL,
  `id_user` int UNSIGNED NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pembayaran` decimal(15,2) NOT NULL DEFAULT '0.00',
  `kembalian` decimal(15,2) NOT NULL DEFAULT '0.00',
  `metode_pembayaran` enum('cash','debit','transfer','qris') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `status` enum('selesai','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'selesai',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`id_penjualan`, `nomor_transaksi`, `tanggal_penjualan`, `id_pelanggan`, `id_user`, `subtotal`, `diskon`, `grand_total`, `pembayaran`, `kembalian`, `metode_pembayaran`, `status`, `created_at`, `updated_at`) VALUES
(1, 'TRX-20260806-001', '2026-08-06 09:30:00', 1, 2, 325000.00, 0.00, 325000.00, 350000.00, 25000.00, 'cash', 'selesai', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(2, 'TRX-20260806-002', '2026-08-06 11:15:00', 2, 2, 240000.00, 10000.00, 230000.00, 230000.00, 0.00, 'qris', 'selesai', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(3, 'TRX-20260807-003', '2026-08-07 13:45:00', 3, 2, 515000.00, 15000.00, 500000.00, 500000.00, 0.00, 'transfer', 'selesai', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(4, 'TRX-20260808-004', '2026-08-08 15:20:00', 4, 2, 420000.00, 20000.00, 400000.00, 400000.00, 0.00, 'debit', 'selesai', '2026-08-11 02:19:23', '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int UNSIGNED NOT NULL,
  `kode_produk` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_produk` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kategori` int UNSIGNED NOT NULL,
  `id_merek` int UNSIGNED DEFAULT NULL,
  `id_satuan` int UNSIGNED NOT NULL,
  `harga_beli` decimal(15,2) NOT NULL DEFAULT '0.00',
  `harga_jual` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stok` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stok_minimum` decimal(15,2) NOT NULL DEFAULT '0.00',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `kode_produk`, `barcode`, `nama_produk`, `id_kategori`, `id_merek`, `id_satuan`, `harga_beli`, `harga_jual`, `stok`, `stok_minimum`, `deskripsi`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PRD001', '899000000001', 'Semen Tiga Roda 50 Kg', 1, 1, 4, 58000.00, 65000.00, 50.00, 10.00, 'Semen Portland 50 Kg', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(2, 'PRD002', '899000000002', 'Semen Gresik 50 Kg', 1, 2, 4, 57000.00, 64000.00, 45.00, 10.00, 'Semen 50 Kg', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(3, 'PRD003', '899000000003', 'Besi Beton 8 mm', 2, 7, 6, 45000.00, 52000.00, 100.00, 20.00, 'Besi beton ukuran 8 mm', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(4, 'PRD004', '899000000004', 'Besi Beton 10 mm', 2, 7, 6, 68000.00, 78000.00, 80.00, 15.00, 'Besi beton ukuran 10 mm', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(5, 'PRD005', '899000000005', 'Besi Beton 12 mm', 2, 7, 6, 95000.00, 108000.00, 60.00, 10.00, 'Besi beton ukuran 12 mm', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(6, 'PRD006', '899000000006', 'Paku 2 Inch', 2, 7, 2, 20000.00, 28000.00, 40.00, 10.00, 'Paku ukuran 2 inch', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(7, 'PRD007', '899000000007', 'Paku 3 Inch', 2, 7, 2, 21000.00, 30000.00, 35.00, 10.00, 'Paku ukuran 3 inch', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(8, 'PRD008', '899000000008', 'Cat Tembok Dulux 5 Kg', 3, 3, 7, 95000.00, 115000.00, 25.00, 5.00, 'Cat tembok interior', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(9, 'PRD009', '899000000009', 'Cat Tembok Dulux 20 Kg', 3, 3, 7, 350000.00, 395000.00, 15.00, 3.00, 'Cat tembok ukuran besar', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(10, 'PRD010', '899000000010', 'Cat Tembok Avian 5 Kg', 3, 4, 7, 80000.00, 98000.00, 30.00, 5.00, 'Cat tembok', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(11, 'PRD011', '899000000011', 'Cat Tembok Nippon Paint 5 Kg', 3, 5, 7, 90000.00, 110000.00, 20.00, 5.00, 'Cat tembok', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(12, 'PRD012', '899000000012', 'Kuas 2 Inch', 7, 7, 1, 8000.00, 12000.00, 50.00, 10.00, 'Kuas cat 2 inch', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(13, 'PRD013', '899000000013', 'Kuas 4 Inch', 7, 7, 1, 15000.00, 22000.00, 45.00, 10.00, 'Kuas cat 4 inch', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(14, 'PRD014', '899000000014', 'Pipa PVC 1/2 Inch', 4, 7, 6, 18000.00, 25000.00, 80.00, 15.00, 'Pipa PVC 1/2 inch', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(15, 'PRD015', '899000000015', 'Pipa PVC 3/4 Inch', 4, 7, 6, 22000.00, 30000.00, 70.00, 15.00, 'Pipa PVC 3/4 inch', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(16, 'PRD016', '899000000016', 'Pipa PVC 1 Inch', 4, 7, 6, 30000.00, 40000.00, 65.00, 10.00, 'Pipa PVC 1 inch', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(17, 'PRD017', '899000000017', 'Knee PVC 1/2 Inch', 4, 7, 1, 2500.00, 4000.00, 100.00, 20.00, 'Sambungan pipa', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(18, 'PRD018', '899000000018', 'Knee PVC 3/4 Inch', 4, 7, 1, 3000.00, 5000.00, 90.00, 20.00, 'Sambungan pipa', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(19, 'PRD019', '899000000019', 'Kabel NYM 2x1.5', 6, 6, 3, 8500.00, 12000.00, 300.00, 50.00, 'Kabel listrik', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(20, 'PRD020', '899000000020', 'Kabel NYM 2x2.5', 6, 6, 3, 12000.00, 16500.00, 250.00, 50.00, 'Kabel listrik', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(21, 'PRD021', '899000000021', 'Stop Kontak 3 Lubang', 6, 6, 1, 18000.00, 28000.00, 35.00, 10.00, 'Stop kontak', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(22, 'PRD022', '899000000022', 'Saklar Tunggal', 6, 6, 1, 12000.00, 20000.00, 40.00, 10.00, 'Saklar listrik', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(23, 'PRD023', '899000000023', 'Keramik 40x40 Motif A', 5, 7, 5, 55000.00, 70000.00, 40.00, 10.00, 'Keramik lantai', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(24, 'PRD024', '899000000024', 'Keramik 40x40 Motif B', 5, 7, 5, 58000.00, 73000.00, 35.00, 10.00, 'Keramik lantai', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(25, 'PRD025', '899000000025', 'Keramik 60x60 Motif A', 5, 7, 5, 85000.00, 105000.00, 30.00, 5.00, 'Keramik lantai', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(26, 'PRD026', '899000000026', 'Sekop Pasir', 7, 7, 1, 35000.00, 50000.00, 20.00, 5.00, 'Sekop untuk pekerjaan bangunan', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(28, 'PRD028', '899000000028', 'Ember Bangunan', 7, 7, 1, 20000.00, 30000.00, 30.00, 5.00, 'Ember untuk kebutuhan bangunan', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(29, 'PRD029', '899000000029', 'Triplek 6 mm', 8, 7, 1, 65000.00, 85000.00, 25.00, 5.00, 'Triplek 6 mm', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(30, 'PRD030', '899000000030', 'Triplek 9 mm', 8, 7, 1, 85000.00, 110000.00, 20.00, 5.00, 'Triplek 9 mm', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `retur_penjualan`
--

CREATE TABLE `retur_penjualan` (
  `id_retur_penjualan` int UNSIGNED NOT NULL,
  `nomor_retur` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_penjualan` int UNSIGNED NOT NULL,
  `tanggal_retur` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_user` int UNSIGNED NOT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci,
  `total_retur` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('diproses','selesai','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'selesai',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `retur_penjualan`
--

INSERT INTO `retur_penjualan` (`id_retur_penjualan`, `nomor_retur`, `id_penjualan`, `tanggal_retur`, `id_user`, `alasan`, `total_retur`, `status`, `created_at`, `updated_at`) VALUES
(1, 'RET-20260809-001', 4, '2026-08-09 10:00:00', 2, 'Produk kuas mengalami kerusakan', 12000.00, 'selesai', '2026-08-11 02:19:23', '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_role` int NOT NULL,
  `nama_role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_role`, `nama_role`, `deskripsi`, `created_at`) VALUES
(1, 'Pemilik Toko', 'Mengawasi dan mengambil keputusan bisnis', '2026-08-11 14:49:40'),
(2, 'Admin', 'Mengelola data dan sistem', '2026-08-11 14:49:40'),
(3, 'Kasir', 'Mengelola transaksi penjualan dan pembayaran', '2026-08-11 14:49:40'),
(4, 'Staff Gudang', 'Mengelola persediaan dan stok barang', '2026-08-11 14:49:40'),
(5, 'Pelanggan', 'Melihat produk dan melakukan pemesanan', '2026-08-11 14:49:40');

-- --------------------------------------------------------

--
-- Table structure for table `satuan`
--

CREATE TABLE `satuan` (
  `id_satuan` int UNSIGNED NOT NULL,
  `nama_satuan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `satuan`
--

INSERT INTO `satuan` (`id_satuan`, `nama_satuan`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 'pcs', 'Satuan per buah', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(2, 'kg', 'Kilogram', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(3, 'meter', 'Meter', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(4, 'sak', 'Satuan per sak', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(5, 'dus', 'Satuan per dus', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(6, 'batang', 'Satuan per batang', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(7, 'kaleng', 'Satuan per kaleng', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(8, 'roll', 'Satuan per roll', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(9, 'unit', 'Satuan unit', '2026-08-11 02:19:23', '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `stok_mutasi`
--

CREATE TABLE `stok_mutasi` (
  `id_mutasi` bigint UNSIGNED NOT NULL,
  `id_produk` int UNSIGNED NOT NULL,
  `tanggal_mutasi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jenis_mutasi` enum('pembelian','penjualan','retur_penjualan','penyesuaian','stok_opname') COLLATE utf8mb4_unicode_ci NOT NULL,
  `referensi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jumlah` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stok_sebelum` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stok_sesudah` decimal(15,2) NOT NULL DEFAULT '0.00',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `id_user` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stok_mutasi`
--

INSERT INTO `stok_mutasi` (`id_mutasi`, `id_produk`, `tanggal_mutasi`, `jenis_mutasi`, `referensi`, `jumlah`, `stok_sebelum`, `stok_sesudah`, `keterangan`, `id_user`, `created_at`) VALUES
(1, 1, '2026-08-01 09:05:00', 'pembelian', 'PB-20260801-001', 20.00, 30.00, 50.00, 'Pembelian semen', 3, '2026-08-11 02:19:23'),
(2, 2, '2026-08-01 09:05:00', 'pembelian', 'PB-20260801-001', 20.00, 25.00, 45.00, 'Pembelian semen', 3, '2026-08-11 02:19:23'),
(3, 6, '2026-08-01 09:05:00', 'pembelian', 'PB-20260801-001', 20.00, 20.00, 40.00, 'Pembelian paku', 3, '2026-08-11 02:19:23'),
(4, 7, '2026-08-01 09:05:00', 'pembelian', 'PB-20260801-001', 10.00, 25.00, 35.00, 'Pembelian paku', 3, '2026-08-11 02:19:23'),
(5, 8, '2026-08-03 10:35:00', 'pembelian', 'PB-20260803-002', 10.00, 15.00, 25.00, 'Pembelian cat', 3, '2026-08-11 02:19:23'),
(6, 10, '2026-08-03 10:35:00', 'pembelian', 'PB-20260803-002', 10.00, 20.00, 30.00, 'Pembelian cat', 3, '2026-08-11 02:19:23'),
(7, 12, '2026-08-03 10:35:00', 'pembelian', 'PB-20260803-002', 10.00, 40.00, 50.00, 'Pembelian kuas', 3, '2026-08-11 02:19:23'),
(8, 13, '2026-08-03 10:35:00', 'pembelian', 'PB-20260803-002', 10.00, 35.00, 45.00, 'Pembelian kuas', 3, '2026-08-11 02:19:23'),
(9, 14, '2026-08-05 14:05:00', 'pembelian', 'PB-20260805-003', 20.00, 60.00, 80.00, 'Pembelian pipa', 3, '2026-08-11 02:19:23'),
(10, 15, '2026-08-05 14:05:00', 'pembelian', 'PB-20260805-003', 20.00, 50.00, 70.00, 'Pembelian pipa', 3, '2026-08-11 02:19:23'),
(11, 19, '2026-08-05 14:05:00', 'pembelian', 'PB-20260805-003', 50.00, 250.00, 300.00, 'Pembelian kabel', 3, '2026-08-11 02:19:23'),
(12, 21, '2026-08-05 14:05:00', 'pembelian', 'PB-20260805-003', 10.00, 25.00, 35.00, 'Pembelian stop kontak', 3, '2026-08-11 02:19:23'),
(13, 22, '2026-08-05 14:05:00', 'pembelian', 'PB-20260805-003', 5.00, 35.00, 40.00, 'Pembelian saklar', 3, '2026-08-11 02:19:23'),
(14, 1, '2026-08-06 09:35:00', 'penjualan', 'TRX-20260806-001', 5.00, 50.00, 45.00, 'Penjualan semen', 2, '2026-08-11 02:19:23'),
(15, 8, '2026-08-06 11:20:00', 'penjualan', 'TRX-20260806-002', 2.00, 25.00, 23.00, 'Penjualan cat', 2, '2026-08-11 02:19:23'),
(16, 14, '2026-08-07 13:50:00', 'penjualan', 'TRX-20260807-003', 5.00, 80.00, 75.00, 'Penjualan pipa', 2, '2026-08-11 02:19:23'),
(17, 15, '2026-08-07 13:50:00', 'penjualan', 'TRX-20260807-003', 5.00, 70.00, 65.00, 'Penjualan pipa', 2, '2026-08-11 02:19:23'),
(18, 19, '2026-08-07 13:50:00', 'penjualan', 'TRX-20260807-003', 20.00, 300.00, 280.00, 'Penjualan kabel', 2, '2026-08-11 02:19:23'),
(19, 21, '2026-08-08 15:25:00', 'penjualan', 'TRX-20260808-004', 5.00, 35.00, 30.00, 'Penjualan stop kontak', 2, '2026-08-11 02:19:23'),
(20, 22, '2026-08-08 15:25:00', 'penjualan', 'TRX-20260808-004', 5.00, 40.00, 35.00, 'Penjualan saklar', 2, '2026-08-11 02:19:23'),
(21, 12, '2026-08-08 15:25:00', 'penjualan', 'TRX-20260808-004', 10.00, 50.00, 40.00, 'Penjualan kuas', 2, '2026-08-11 02:19:23'),
(22, 13, '2026-08-08 15:25:00', 'penjualan', 'TRX-20260808-004', 3.00, 45.00, 42.00, 'Penjualan kuas', 2, '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `stok_opname`
--

CREATE TABLE `stok_opname` (
  `id_opname` int UNSIGNED NOT NULL,
  `nomor_opname` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_opname` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_user` int UNSIGNED NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','selesai','dibatalkan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stok_opname`
--

INSERT INTO `stok_opname` (`id_opname`, `nomor_opname`, `tanggal_opname`, `id_user`, `keterangan`, `status`, `created_at`, `updated_at`) VALUES
(1, 'OPN-20260808-001', '2026-08-08 17:00:00', 3, 'Pemeriksaan stok mingguan', 'selesai', '2026-08-11 02:19:23', '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int UNSIGNED NOT NULL,
  `kode_supplier` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_supplier` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `no_telepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kontak_person` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `kode_supplier`, `nama_supplier`, `alamat`, `no_telepon`, `email`, `kontak_person`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SUP001', 'PT Sumber Bangunan Jaya', 'Jl. Raya Industri No. 10', '081234567801', 'supplier1@example.com', 'Budi', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(2, 'SUP002', 'CV Makmur Material', 'Jl. Pembangunan No. 25', '081234567802', 'supplier2@example.com', 'Andi', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(3, 'SUP003', 'PT Mitra Konstruksi', 'Jl. Veteran No. 15', '081234567803', 'supplier3@example.com', 'Rudi', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(4, 'SUP004', 'CV Sinar Jaya', 'Jl. Industri No. 30', '081234567804', 'supplier4@example.com', 'Dedi', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23'),
(5, 'SUP005', 'PT Bangun Persada', 'Jl. Raya Utama No. 50', '081234567805', 'supplier5@example.com', 'Agus', 'aktif', '2026-08-11 02:19:23', '2026-08-11 02:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int UNSIGNED NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','kasir','gudang','owner') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kasir',
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', '$2y$12$MbSFecZogNtz4WuCsIpIauWTE2Gkd7THxiHu3GgEwegBrQR4UAjQm', 'admin', 'aktif', '2026-08-11 02:19:22', '2026-08-11 02:19:22'),
(2, 'Kasir Toko', 'kasir', '$2y$12$MbSFecZogNtz4WuCsIpIauWTE2Gkd7THxiHu3GgEwegBrQR4UAjQm', 'kasir', 'aktif', '2026-08-11 02:19:22', '2026-08-11 02:19:22'),
(3, 'Petugas Gudang', 'gudang', '$2y$12$MbSFecZogNtz4WuCsIpIauWTE2Gkd7THxiHu3GgEwegBrQR4UAjQm', 'gudang', 'aktif', '2026-08-11 02:19:22', '2026-08-11 02:19:22'),
(4, 'Pemilik Toko', 'owner', '$2y$12$MbSFecZogNtz4WuCsIpIauWTE2Gkd7THxiHu3GgEwegBrQR4UAjQm', 'owner', 'aktif', '2026-08-11 02:19:22', '2026-08-11 02:19:22');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_laporan_penjualan_produk`
-- (See below for the actual view)
--
CREATE TABLE `v_laporan_penjualan_produk` (
`id_produk` int unsigned
,`kode_produk` varchar(30)
,`nama_produk` varchar(150)
,`nama_kategori` varchar(100)
,`total_terjual` decimal(37,2)
,`total_penjualan` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_penjualan`
-- (See below for the actual view)
--
CREATE TABLE `v_penjualan` (
`id_penjualan` int unsigned
,`nomor_transaksi` varchar(30)
,`tanggal_penjualan` datetime
,`pelanggan` varchar(150)
,`kasir` varchar(100)
,`subtotal` decimal(15,2)
,`diskon` decimal(15,2)
,`grand_total` decimal(15,2)
,`pembayaran` decimal(15,2)
,`kembalian` decimal(15,2)
,`metode_pembayaran` enum('cash','debit','transfer','qris')
,`status` enum('selesai','dibatalkan')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_produk`
-- (See below for the actual view)
--
CREATE TABLE `v_produk` (
`id_produk` int unsigned
,`kode_produk` varchar(30)
,`barcode` varchar(50)
,`nama_produk` varchar(150)
,`nama_kategori` varchar(100)
,`nama_merek` varchar(100)
,`nama_satuan` varchar(50)
,`harga_beli` decimal(15,2)
,`harga_jual` decimal(15,2)
,`stok` decimal(15,2)
,`stok_minimum` decimal(15,2)
,`status_stok` varchar(7)
,`status` enum('aktif','nonaktif')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_stok_menipis`
-- (See below for the actual view)
--
CREATE TABLE `v_stok_menipis` (
`id_produk` int unsigned
,`kode_produk` varchar(30)
,`nama_produk` varchar(150)
,`nama_kategori` varchar(100)
,`nama_satuan` varchar(50)
,`stok` decimal(15,2)
,`stok_minimum` decimal(15,2)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD PRIMARY KEY (`id_detail_pembelian`),
  ADD KEY `idx_detail_pembelian` (`id_pembelian`),
  ADD KEY `idx_detail_produk` (`id_produk`);

--
-- Indexes for table `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD PRIMARY KEY (`id_detail_penjualan`),
  ADD KEY `idx_detail_penjualan` (`id_penjualan`),
  ADD KEY `idx_detail_penjualan_produk` (`id_produk`);

--
-- Indexes for table `detail_retur_penjualan`
--
ALTER TABLE `detail_retur_penjualan`
  ADD PRIMARY KEY (`id_detail_retur`),
  ADD KEY `idx_detail_retur` (`id_retur_penjualan`),
  ADD KEY `idx_detail_retur_produk` (`id_produk`);

--
-- Indexes for table `detail_stok_opname`
--
ALTER TABLE `detail_stok_opname`
  ADD PRIMARY KEY (`id_detail_opname`),
  ADD KEY `idx_detail_opname` (`id_opname`),
  ADD KEY `idx_detail_opname_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `merek`
--
ALTER TABLE `merek`
  ADD PRIMARY KEY (`id_merek`),
  ADD UNIQUE KEY `nama_merek` (`nama_merek`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `kode_pelanggan` (`kode_pelanggan`);

--
-- Indexes for table `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD UNIQUE KEY `nomor_pembelian` (`nomor_pembelian`),
  ADD KEY `idx_pembelian_supplier` (`id_supplier`),
  ADD KEY `idx_pembelian_user` (`id_user`),
  ADD KEY `idx_pembelian_tanggal` (`tanggal_pembelian`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD UNIQUE KEY `nomor_transaksi` (`nomor_transaksi`),
  ADD KEY `idx_penjualan_pelanggan` (`id_pelanggan`),
  ADD KEY `idx_penjualan_user` (`id_user`),
  ADD KEY `idx_penjualan_tanggal` (`tanggal_penjualan`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `idx_produk_kategori` (`id_kategori`),
  ADD KEY `idx_produk_merek` (`id_merek`),
  ADD KEY `idx_produk_satuan` (`id_satuan`),
  ADD KEY `idx_produk_nama` (`nama_produk`);

--
-- Indexes for table `retur_penjualan`
--
ALTER TABLE `retur_penjualan`
  ADD PRIMARY KEY (`id_retur_penjualan`),
  ADD UNIQUE KEY `nomor_retur` (`nomor_retur`),
  ADD KEY `idx_retur_penjualan` (`id_penjualan`),
  ADD KEY `idx_retur_user` (`id_user`),
  ADD KEY `idx_retur_tanggal` (`tanggal_retur`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `nama_role` (`nama_role`);

--
-- Indexes for table `satuan`
--
ALTER TABLE `satuan`
  ADD PRIMARY KEY (`id_satuan`),
  ADD UNIQUE KEY `nama_satuan` (`nama_satuan`);

--
-- Indexes for table `stok_mutasi`
--
ALTER TABLE `stok_mutasi`
  ADD PRIMARY KEY (`id_mutasi`),
  ADD KEY `idx_mutasi_produk` (`id_produk`),
  ADD KEY `idx_mutasi_user` (`id_user`),
  ADD KEY `idx_mutasi_tanggal` (`tanggal_mutasi`),
  ADD KEY `idx_mutasi_jenis` (`jenis_mutasi`);

--
-- Indexes for table `stok_opname`
--
ALTER TABLE `stok_opname`
  ADD PRIMARY KEY (`id_opname`),
  ADD UNIQUE KEY `nomor_opname` (`nomor_opname`),
  ADD KEY `idx_opname_user` (`id_user`),
  ADD KEY `idx_opname_tanggal` (`tanggal_opname`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`),
  ADD UNIQUE KEY `kode_supplier` (`kode_supplier`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  MODIFY `id_detail_pembelian` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  MODIFY `id_detail_penjualan` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `detail_retur_penjualan`
--
ALTER TABLE `detail_retur_penjualan`
  MODIFY `id_detail_retur` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail_stok_opname`
--
ALTER TABLE `detail_stok_opname`
  MODIFY `id_detail_opname` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `merek`
--
ALTER TABLE `merek`
  MODIFY `id_merek` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id_pembelian` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `retur_penjualan`
--
ALTER TABLE `retur_penjualan`
  MODIFY `id_retur_penjualan` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `satuan`
--
ALTER TABLE `satuan`
  MODIFY `id_satuan` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `stok_mutasi`
--
ALTER TABLE `stok_mutasi`
  MODIFY `id_mutasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `stok_opname`
--
ALTER TABLE `stok_opname`
  MODIFY `id_opname` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- --------------------------------------------------------

--
-- Structure for view `v_laporan_penjualan_produk`
--
DROP TABLE IF EXISTS `v_laporan_penjualan_produk`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_laporan_penjualan_produk`  AS SELECT `p`.`id_produk` AS `id_produk`, `p`.`kode_produk` AS `kode_produk`, `p`.`nama_produk` AS `nama_produk`, `k`.`nama_kategori` AS `nama_kategori`, sum(`dp`.`jumlah`) AS `total_terjual`, sum(`dp`.`subtotal`) AS `total_penjualan` FROM (((`detail_penjualan` `dp` join `produk` `p` on((`dp`.`id_produk` = `p`.`id_produk`))) join `kategori` `k` on((`p`.`id_kategori` = `k`.`id_kategori`))) join `penjualan` `pj` on((`dp`.`id_penjualan` = `pj`.`id_penjualan`))) WHERE (`pj`.`status` = 'selesai') GROUP BY `p`.`id_produk`, `p`.`kode_produk`, `p`.`nama_produk`, `k`.`nama_kategori` ;

-- --------------------------------------------------------

--
-- Structure for view `v_penjualan`
--
DROP TABLE IF EXISTS `v_penjualan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_penjualan`  AS SELECT `p`.`id_penjualan` AS `id_penjualan`, `p`.`nomor_transaksi` AS `nomor_transaksi`, `p`.`tanggal_penjualan` AS `tanggal_penjualan`, coalesce(`pl`.`nama_pelanggan`,'Umum') AS `pelanggan`, `u`.`nama` AS `kasir`, `p`.`subtotal` AS `subtotal`, `p`.`diskon` AS `diskon`, `p`.`grand_total` AS `grand_total`, `p`.`pembayaran` AS `pembayaran`, `p`.`kembalian` AS `kembalian`, `p`.`metode_pembayaran` AS `metode_pembayaran`, `p`.`status` AS `status` FROM ((`penjualan` `p` left join `pelanggan` `pl` on((`p`.`id_pelanggan` = `pl`.`id_pelanggan`))) join `users` `u` on((`p`.`id_user` = `u`.`id_user`))) ;

-- --------------------------------------------------------

--
-- Structure for view `v_produk`
--
DROP TABLE IF EXISTS `v_produk`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_produk`  AS SELECT `p`.`id_produk` AS `id_produk`, `p`.`kode_produk` AS `kode_produk`, `p`.`barcode` AS `barcode`, `p`.`nama_produk` AS `nama_produk`, `k`.`nama_kategori` AS `nama_kategori`, `m`.`nama_merek` AS `nama_merek`, `s`.`nama_satuan` AS `nama_satuan`, `p`.`harga_beli` AS `harga_beli`, `p`.`harga_jual` AS `harga_jual`, `p`.`stok` AS `stok`, `p`.`stok_minimum` AS `stok_minimum`, (case when (`p`.`stok` <= 0) then 'Habis' when (`p`.`stok` <= `p`.`stok_minimum`) then 'Menipis' else 'Aman' end) AS `status_stok`, `p`.`status` AS `status` FROM (((`produk` `p` join `kategori` `k` on((`p`.`id_kategori` = `k`.`id_kategori`))) left join `merek` `m` on((`p`.`id_merek` = `m`.`id_merek`))) join `satuan` `s` on((`p`.`id_satuan` = `s`.`id_satuan`))) ;

-- --------------------------------------------------------

--
-- Structure for view `v_stok_menipis`
--
DROP TABLE IF EXISTS `v_stok_menipis`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stok_menipis`  AS SELECT `p`.`id_produk` AS `id_produk`, `p`.`kode_produk` AS `kode_produk`, `p`.`nama_produk` AS `nama_produk`, `k`.`nama_kategori` AS `nama_kategori`, `s`.`nama_satuan` AS `nama_satuan`, `p`.`stok` AS `stok`, `p`.`stok_minimum` AS `stok_minimum` FROM ((`produk` `p` join `kategori` `k` on((`p`.`id_kategori` = `k`.`id_kategori`))) join `satuan` `s` on((`p`.`id_satuan` = `s`.`id_satuan`))) WHERE ((`p`.`stok` <= `p`.`stok_minimum`) AND (`p`.`status` = 'aktif')) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pembelian`
--
ALTER TABLE `detail_pembelian`
  ADD CONSTRAINT `fk_detail_pembelian` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian` (`id_pembelian`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_pembelian_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `fk_detail_penjualan` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id_penjualan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_penjualan_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `detail_retur_penjualan`
--
ALTER TABLE `detail_retur_penjualan`
  ADD CONSTRAINT `fk_detail_retur` FOREIGN KEY (`id_retur_penjualan`) REFERENCES `retur_penjualan` (`id_retur_penjualan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_retur_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `detail_stok_opname`
--
ALTER TABLE `detail_stok_opname`
  ADD CONSTRAINT `fk_detail_opname` FOREIGN KEY (`id_opname`) REFERENCES `stok_opname` (`id_opname`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_opname_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `pembelian`
--
ALTER TABLE `pembelian`
  ADD CONSTRAINT `fk_pembelian_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pembelian_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `fk_penjualan_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penjualan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produk_merek` FOREIGN KEY (`id_merek`) REFERENCES `merek` (`id_merek`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produk_satuan` FOREIGN KEY (`id_satuan`) REFERENCES `satuan` (`id_satuan`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `retur_penjualan`
--
ALTER TABLE `retur_penjualan`
  ADD CONSTRAINT `fk_retur_penjualan` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id_penjualan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_retur_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `stok_mutasi`
--
ALTER TABLE `stok_mutasi`
  ADD CONSTRAINT `fk_mutasi_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mutasi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `stok_opname`
--
ALTER TABLE `stok_opname`
  ADD CONSTRAINT `fk_opname_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
