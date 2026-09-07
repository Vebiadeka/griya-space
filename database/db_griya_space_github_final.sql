-- =========================================================
-- Griya Space - Database
-- File SQL untuk Repository GitHub
-- Database: db_griya_space
-- =========================================================

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
