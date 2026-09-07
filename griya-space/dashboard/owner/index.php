<?php

session_start();


// ======================================================
// CEK LOGIN
// ======================================================

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}


// ======================================================
// CEK ROLE OWNER
// ======================================================

if ($_SESSION["role"] !== "owner") {
    header("Location: ../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../config/database.php";


$nama =
    $_SESSION["nama"] ?? "Pemilik Griya Space";


// ======================================================
// TOTAL PENJUALAN SELESAI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COALESCE(
            SUM(total_harga),
            0
        ) AS total
    FROM transaksi
    WHERE status = 'Selesai'
");


if (!$stmt) {
    die(
        "Gagal mengambil total penjualan: " .
        $conn->error
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$data =
    $result->fetch_assoc();


$total_penjualan =
    (float) $data["total"];


$stmt->close();


// ======================================================
// TRANSAKSI SELESAI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM transaksi
    WHERE status = 'Selesai'
");


if (!$stmt) {
    die(
        "Gagal mengambil jumlah transaksi: " .
        $conn->error
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$data =
    $result->fetch_assoc();


$total_transaksi =
    (int) $data["total"];


$stmt->close();


// ======================================================
// TOTAL PELANGGAN
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM pelanggan
");


if (!$stmt) {
    die(
        "Gagal mengambil pelanggan: " .
        $conn->error
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$data =
    $result->fetch_assoc();


$total_pelanggan =
    (int) $data["total"];


$stmt->close();


// ======================================================
// TOTAL PRODUK AKTIF
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM produk
    WHERE status = 'Aktif'
");


if (!$stmt) {
    die(
        "Gagal mengambil produk: " .
        $conn->error
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$data =
    $result->fetch_assoc();


$total_produk =
    (int) $data["total"];


$stmt->close();


// ======================================================
// PRODUK STOK MENIPIS
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM produk
    WHERE status = 'Aktif'
      AND stok <= stok_minimum
");


if (!$stmt) {
    die(
        "Gagal mengambil stok menipis: " .
        $conn->error
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$data =
    $result->fetch_assoc();


$stok_menipis =
    (int) $data["total"];


$stmt->close();


// ======================================================
// PESANAN MENUNGGU
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM pesanan
    WHERE status = 'Menunggu'
");


if (!$stmt) {
    die(
        "Gagal mengambil pesanan menunggu: " .
        $conn->error
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$data =
    $result->fetch_assoc();


$pesanan_menunggu =
    (int) $data["total"];


$stmt->close();


// ======================================================
// PENJUALAN HARI INI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COALESCE(
            SUM(total_harga),
            0
        ) AS total
    FROM transaksi
    WHERE DATE(tanggal_transaksi) = CURDATE()
      AND status = 'Selesai'
");


if (!$stmt) {
    die(
        "Gagal mengambil penjualan hari ini: " .
        $conn->error
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$data =
    $result->fetch_assoc();


$penjualan_hari_ini =
    (float) $data["total"];


$stmt->close();


// ======================================================
// TRANSAKSI HARI INI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM transaksi
    WHERE DATE(tanggal_transaksi) = CURDATE()
      AND status = 'Selesai'
");


if (!$stmt) {
    die(
        "Gagal mengambil transaksi hari ini: " .
        $conn->error
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$data =
    $result->fetch_assoc();


$transaksi_hari_ini =
    (int) $data["total"];


$stmt->close();


// ======================================================
// TOP PRODUK BERDASARKAN JUMLAH TERJUAL
// ======================================================

$stmt = $conn->prepare("
    SELECT
        p.nama_produk,
        p.kode_produk,
        p.satuan,
        SUM(dt.jumlah) AS total_terjual,
        SUM(dt.subtotal) AS total_nilai

    FROM detail_transaksi dt

    INNER JOIN transaksi t
        ON dt.id_transaksi = t.id_transaksi

    INNER JOIN produk p
        ON dt.id_produk = p.id_produk

    WHERE t.status = 'Selesai'

    GROUP BY
        p.id_produk,
        p.nama_produk,
        p.kode_produk,
        p.satuan

    ORDER BY
        total_terjual DESC

    LIMIT 5
");


if (!$stmt) {
    die(
        "Gagal mengambil produk terlaris: " .
        $conn->error
    );
}


$stmt->execute();


$resultTopProduk =
    $stmt->get_result();


$produk_terlaris = [];


while (
    $row =
    $resultTopProduk->fetch_assoc()
) {

    $produk_terlaris[] =
        $row;
}


$stmt->close();


// ======================================================
// PRODUK STOK TERENDAH
// ======================================================

$stmt = $conn->prepare("
    SELECT
        kode_produk,
        nama_produk,
        stok,
        stok_minimum,
        satuan

    FROM produk

    WHERE status = 'Aktif'

    ORDER BY
        stok ASC

    LIMIT 5
");


if (!$stmt) {
    die(
        "Gagal mengambil stok terendah: " .
        $conn->error
    );
}


$stmt->execute();


$resultStok =
    $stmt->get_result();


$produk_stok_terendah = [];


while (
    $row =
    $resultStok->fetch_assoc()
) {

    $produk_stok_terendah[] =
        $row;
}


$stmt->close();


// ======================================================
// TRANSAKSI TERBARU
// ======================================================

$stmt = $conn->prepare("
    SELECT
        t.id_transaksi,
        t.nomor_transaksi,
        t.tanggal_transaksi,
        t.total_harga,
        t.status,
        p.nama_pelanggan

    FROM transaksi t

    INNER JOIN pelanggan p
        ON t.id_pelanggan = p.id_pelanggan

    ORDER BY
        t.id_transaksi DESC

    LIMIT 6
");


if (!$stmt) {
    die(
        "Gagal mengambil transaksi terbaru: " .
        $conn->error
    );
}


$stmt->execute();


$resultTransaksi =
    $stmt->get_result();


$transaksi_terbaru = [];


while (
    $row =
    $resultTransaksi->fetch_assoc()
) {

    $transaksi_terbaru[] =
        $row;
}


$stmt->close();


// ======================================================
// PENJUALAN BULAN INI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COALESCE(
            SUM(total_harga),
            0
        ) AS total
    FROM transaksi

    WHERE status = 'Selesai'

      AND YEAR(tanggal_transaksi)
          = YEAR(CURDATE())

      AND MONTH(tanggal_transaksi)
          = MONTH(CURDATE())
");


if (!$stmt) {
    die(
        "Gagal mengambil penjualan bulan ini: " .
        $conn->error
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$data =
    $result->fetch_assoc();


$penjualan_bulan_ini =
    (float) $data["total"];


$stmt->close();


// ======================================================
// TRANSAKSI BULAN INI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM transaksi

    WHERE status = 'Selesai'

      AND YEAR(tanggal_transaksi)
          = YEAR(CURDATE())

      AND MONTH(tanggal_transaksi)
          = MONTH(CURDATE())
");


if (!$stmt) {
    die(
        "Gagal mengambil transaksi bulan ini: " .
        $conn->error
    );
}


$stmt->execute();


$result =
    $stmt->get_result();


$data =
    $result->fetch_assoc();


$transaksi_bulan_ini =
    (int) $data["total"];


$stmt->close();

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard Owner | Griya Space
    </title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f5f5f3;

            color: #1d1d1d;
        }


        /* ==================================================
           HEADER
        ================================================== */

        header {

            min-height: 76px;

            background: #1d1d1d;

            color: white;

            padding: 18px 45px;

            display: flex;

            align-items: center;

            justify-content: space-between;
        }


        .brand {

            font-size: 18px;

            letter-spacing: 6px;

            font-weight: 500;
        }


        .header-right {

            display: flex;

            align-items: center;

            gap: 20px;
        }


        .role {

            font-size: 11px;

            letter-spacing: 2px;

            color: #aaa;
        }


        .logout {

            color: white;

            text-decoration: none;

            border: 1px solid #555;

            padding: 9px 15px;

            font-size: 11px;

            transition: .2s;
        }


        .logout:hover {

            background: white;

            color: #1d1d1d;
        }


        /* ==================================================
           MAIN
        ================================================== */

        main {

            max-width: 1350px;

            margin: 0 auto;

            padding: 45px 30px;
        }


        /* ==================================================
           WELCOME
        ================================================== */

        .welcome {

            margin-bottom: 30px;
        }


        .welcome small {

            display: block;

            color: #777;

            font-size: 10px;

            letter-spacing: 3px;

            margin-bottom: 9px;
        }


        .welcome h1 {

            font-size: 32px;

            font-weight: 500;
        }


        .welcome p {

            margin-top: 9px;

            color: #777;

            font-size: 13px;

            line-height: 1.5;
        }


        /* ==================================================
           KPI UTAMA
        ================================================== */

        .kpi-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 15px;
        }


        .kpi-card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 22px;
        }


        .kpi-label {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1.5px;
        }


        .kpi-value {

            display: block;

            margin-top: 8px;

            font-size: 27px;

            font-weight: 600;
        }


        .kpi-note {

            margin-top: 7px;

            color: #999;

            font-size: 10px;

            line-height: 1.4;
        }


        .warning {

            color: #8a6d2f;
        }


        .success {

            color: #3d6c43;
        }


        /* ==================================================
           KPI OPERASIONAL
        ================================================== */

        .secondary-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 30px;
        }


        .secondary-card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 19px 22px;
        }


        .secondary-label {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1.4px;
        }


        .secondary-value {

            display: block;

            margin-top: 7px;

            font-size: 22px;

            font-weight: 600;
        }


        .secondary-note {

            margin-top: 6px;

            color: #999;

            font-size: 10px;
        }


        /* ==================================================
           SECTION
        ================================================== */

        .section-title {

            margin-bottom: 15px;

            color: #777;

            font-size: 10px;

            letter-spacing: 2px;
        }


        /* ==================================================
           CONTENT GRID
        ================================================== */

        .content-grid {

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            gap: 20px;

            margin-bottom: 20px;
        }


        .card {

            background: white;

            border: 1px solid #e4e4e4;
        }


        .card-title {

            padding: 18px 22px;

            border-bottom: 1px solid #eeeeee;

            color: #777;

            font-size: 11px;

            letter-spacing: 2px;
        }


        .card-body {

            padding: 20px;
        }


        /* ==================================================
           LIST
        ================================================== */

        .list-item {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding: 14px 0;

            border-bottom: 1px solid #eeeeee;
        }


        .list-item:first-child {

            padding-top: 0;
        }


        .list-item:last-child {

            border-bottom: none;

            padding-bottom: 0;
        }


        .item-name {

            font-size: 13px;

            font-weight: 600;
        }


        .item-meta {

            margin-top: 4px;

            color: #999;

            font-size: 10px;

            line-height: 1.5;
        }


        .item-value {

            text-align: right;

            white-space: nowrap;

            font-size: 13px;

            font-weight: 600;
        }


        .item-value-small {

            margin-top: 3px;

            color: #999;

            font-size: 9px;

            font-weight: 400;
        }


        .stock-low {

            color: #8a4444;
        }


        .empty {

            padding: 35px 15px;

            text-align: center;

            color: #777;

            font-size: 12px;

            line-height: 1.5;
        }


        /* ==================================================
           TRANSAKSI
        ================================================== */

        .table-container {

            overflow-x: auto;
        }


        table {

            width: 100%;

            min-width: 850px;

            border-collapse: collapse;
        }


        thead {

            background: #1d1d1d;

            color: white;
        }


        th {

            padding: 14px;

            text-align: left;

            font-size: 10px;

            letter-spacing: 1px;

            white-space: nowrap;
        }


        td {

            padding: 14px;

            border-bottom: 1px solid #eeeeee;

            font-size: 12px;
        }


        .transaction-number {

            font-weight: 600;

            white-space: nowrap;
        }


        .transaction-date {

            color: #666;

            white-space: nowrap;
        }


        .transaction-money {

            font-weight: 600;

            white-space: nowrap;
        }


        .status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 9px;

            font-weight: 600;

            letter-spacing: .7px;

            white-space: nowrap;
        }


        .status-selesai {

            background: #edf5f3;

            color: #347166;
        }


        .status-menunggu {

            background: #f4f0e7;

            color: #8a6d2f;
        }


        .status-diproses {

            background: #eef2f6;

            color: #44627d;
        }


        .status-batal {

            background: #f5eeee;

            color: #8a4444;
        }


        .status-default {

            background: #f1f1ef;

            color: #555;
        }


        /* ==================================================
           FOOTER NOTE
        ================================================== */

        .owner-note {

            margin-top: 20px;

            padding: 18px 20px;

            background: #fafafa;

            border: 1px solid #e4e4e4;

            color: #777;

            font-size: 11px;

            line-height: 1.6;
        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 1050px) {

            .kpi-grid,
            .secondary-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 800px) {

            header {

                padding: 18px 20px;
            }


            .brand {

                font-size: 15px;

                letter-spacing: 4px;
            }


            .role {

                display: none;
            }


            main {

                padding: 30px 18px;
            }


            .content-grid {

                grid-template-columns: 1fr;
            }


            .welcome h1 {

                font-size: 28px;
            }

        }


        @media (max-width: 550px) {

            .kpi-grid,
            .secondary-grid {

                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<header>

    <div class="brand">
        GRIYA SPACE
    </div>


    <div class="header-right">

        <div class="role">
            PEMILIK
        </div>


        <a
            href="../../auth/logout.php"
            class="logout"
        >
            LOGOUT
        </a>

    </div>

</header>


<main>


    <!-- ==================================================
         WELCOME
    ================================================== -->

    <section class="welcome">

        <small>
            DASHBOARD PEMILIK
        </small>


        <h1>
            Selamat datang,
            <?= htmlspecialchars($nama); ?>
        </h1>


        <p>
            Pantau performa penjualan, pelanggan,
            produk, dan kondisi persediaan Griya Space.
        </p>

    </section>


    <!-- ==================================================
         KPI UTAMA
    ================================================== -->

    <section class="kpi-grid">


        <div class="kpi-card">

            <span class="kpi-label">
                TOTAL PENJUALAN
            </span>


            <span class="kpi-value">

                Rp <?= number_format(
                    $total_penjualan,
                    0,
                    ",",
                    "."
                ); ?>

            </span>


            <div class="kpi-note">
                Akumulasi seluruh transaksi selesai
            </div>

        </div>


        <div class="kpi-card">

            <span class="kpi-label">
                TRANSAKSI SELESAI
            </span>


            <span class="kpi-value">

                <?= $total_transaksi; ?>

            </span>


            <div class="kpi-note">
                Total transaksi yang berhasil diselesaikan
            </div>

        </div>


        <div class="kpi-card">

            <span class="kpi-label">
                PELANGGAN
            </span>


            <span class="kpi-value">

                <?= $total_pelanggan; ?>

            </span>


            <div class="kpi-note">
                Pelanggan terdaftar dalam sistem
            </div>

        </div>


        <div class="kpi-card">

            <span class="kpi-label">
                PRODUK AKTIF
            </span>


            <span class="kpi-value">

                <?= $total_produk; ?>

            </span>


            <div class="kpi-note">
                Produk yang masih tersedia di katalog
            </div>

        </div>


    </section>


    <!-- ==================================================
         KPI OPERASIONAL
    ================================================== -->

    <section class="secondary-grid">


        <div class="secondary-card">

            <span class="secondary-label">
                PENJUALAN HARI INI
            </span>


            <span class="secondary-value">

                Rp <?= number_format(
                    $penjualan_hari_ini,
                    0,
                    ",",
                    "."
                ); ?>

            </span>


            <div class="secondary-note">
                Transaksi selesai hari ini
            </div>

        </div>


        <div class="secondary-card">

            <span class="secondary-label">
                TRANSAKSI HARI INI
            </span>


            <span class="secondary-value">

                <?= $transaksi_hari_ini; ?>

            </span>


            <div class="secondary-note">
                Transaksi selesai hari ini
            </div>

        </div>


        <div class="secondary-card">

            <span class="secondary-label">
                PENJUALAN BULAN INI
            </span>


            <span class="secondary-value">

                Rp <?= number_format(
                    $penjualan_bulan_ini,
                    0,
                    ",",
                    "."
                ); ?>

            </span>


            <div class="secondary-note">
                Bulan berjalan
            </div>

        </div>


        <div class="secondary-card">

            <span class="secondary-label">
                TRANSAKSI BULAN INI
            </span>


            <span class="secondary-value">

                <?= $transaksi_bulan_ini; ?>

            </span>


            <div class="secondary-note">
                Transaksi selesai bulan berjalan
            </div>

        </div>


    </section>


    <!-- ==================================================
         ANALISIS
    ================================================== -->

    <div class="section-title">
        RINGKASAN BISNIS
    </div>


    <section class="content-grid">


        <!-- ==================================================
             PRODUK TERLARIS
        ================================================== -->

        <section class="card">


            <div class="card-title">
                PRODUK TERLARIS
            </div>


            <div class="card-body">


                <?php if (
                    !empty(
                        $produk_terlaris
                    )
                ): ?>


                    <?php foreach (
                        $produk_terlaris
                        as $produk
                    ): ?>


                        <div class="list-item">


                            <div>

                                <div class="item-name">

                                    <?= htmlspecialchars(
                                        $produk[
                                            "nama_produk"
                                        ]
                                    ); ?>

                                </div>


                                <div class="item-meta">

                                    <?= htmlspecialchars(
                                        $produk[
                                            "kode_produk"
                                        ]
                                    ); ?>

                                    ·

                                    <?= (int) $produk[
                                        "total_terjual"
                                    ]; ?>

                                    <?= htmlspecialchars(
                                        $produk["satuan"]
                                    ); ?>

                                    terjual

                                </div>

                            </div>


                            <div class="item-value">

                                Rp <?= number_format(
                                    $produk[
                                        "total_nilai"
                                    ],
                                    0,
                                    ",",
                                    "."
                                ); ?>

                                <div
                                    class="
                                        item-value-small
                                    "
                                >
                                    Nilai penjualan
                                </div>

                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty">

                        Belum ada data produk terlaris.

                    </div>


                <?php endif; ?>


            </div>


        </section>


        <!-- ==================================================
             STOK TERENDAH
        ================================================== -->

        <section class="card">


            <div class="card-title">
                PERHATIAN STOK
            </div>


            <div class="card-body">


                <?php if (
                    !empty(
                        $produk_stok_terendah
                    )
                ): ?>


                    <?php foreach (
                        $produk_stok_terendah
                        as $produk
                    ): ?>


                        <?php

                        $stok =
                            (int) $produk["stok"];

                        $stok_minimum =
                            (int) $produk[
                                "stok_minimum"
                            ];

                        $stokClass =
                            (
                                $stok <=
                                $stok_minimum
                            )
                                ? "stock-low"
                                : "";

                        ?>


                        <div class="list-item">


                            <div>

                                <div class="item-name">

                                    <?= htmlspecialchars(
                                        $produk[
                                            "nama_produk"
                                        ]
                                    ); ?>

                                </div>


                                <div class="item-meta">

                                    <?= htmlspecialchars(
                                        $produk[
                                            "kode_produk"
                                        ]
                                    ); ?>

                                    · Minimum
                                    <?= $stok_minimum; ?>

                                    <?= htmlspecialchars(
                                        $produk["satuan"]
                                    ); ?>

                                </div>

                            </div>


                            <div class="item-value <?= $stokClass; ?>">

                                <?= $stok; ?>

                                <?= htmlspecialchars(
                                    $produk["satuan"]
                                ); ?>


                                <div
                                    class="
                                        item-value-small
                                    "
                                >
                                    Stok saat ini
                                </div>

                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty">

                        Belum ada data stok.

                    </div>


                <?php endif; ?>


            </div>


        </section>


    </section>


    <!-- ==================================================
         STATUS OPERASIONAL
    ================================================== -->

    <div class="section-title">
        STATUS OPERASIONAL
    </div>


    <section class="secondary-grid">


        <div class="secondary-card">

            <span class="secondary-label">
                PESANAN MENUNGGU
            </span>


            <span
                class="
                    secondary-value
                    <?= (
                        $pesanan_menunggu > 0
                    )
                        ? "warning"
                        : "success"
                    ?>
                "
            >

                <?= $pesanan_menunggu; ?>

            </span>


            <div class="secondary-note">
                Pesanan pelanggan yang belum selesai
            </div>

        </div>


        <div class="secondary-card">

            <span class="secondary-label">
                STOK MENIPIS
            </span>


            <span
                class="
                    secondary-value
                    <?= (
                        $stok_menipis > 0
                    )
                        ? "warning"
                        : "success"
                    ?>
                "
            >

                <?= $stok_menipis; ?>

            </span>


            <div class="secondary-note">
                Produk mencapai batas stok minimum
            </div>

        </div>


        <div class="secondary-card">

            <span class="secondary-label">
                PROFIL PELANGGAN
            </span>


            <span class="secondary-value">

                <?= $total_pelanggan; ?>

            </span>


            <div class="secondary-note">
                Total pelanggan terdaftar
            </div>

        </div>


        <div class="secondary-card">

            <span class="secondary-label">
                PRODUK AKTIF
            </span>


            <span class="secondary-value">

                <?= $total_produk; ?>

            </span>


            <div class="secondary-note">
                Produk yang tersedia untuk dijual
            </div>

        </div>


    </section>


    <!-- ==================================================
         TRANSAKSI TERBARU
    ================================================== -->

    <div class="section-title">
        TRANSAKSI TERBARU
    </div>


    <section class="card">


        <?php if (
            !empty(
                $transaksi_terbaru
            )
        ): ?>


            <div class="table-container">


                <table>


                    <thead>

                        <tr>

                            <th>
                                NOMOR TRANSAKSI
                            </th>

                            <th>
                                TANGGAL
                            </th>

                            <th>
                                PELANGGAN
                            </th>

                            <th>
                                TOTAL
                            </th>

                            <th>
                                STATUS
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach (
                            $transaksi_terbaru
                            as $transaksi
                        ): ?>


                            <?php

                            $statusClass =
                                "status-default";


                            switch (
                                $transaksi[
                                    "status"
                                ]
                            ) {

                                case "Selesai":

                                    $statusClass =
                                        "status-selesai";

                                    break;


                                case "Menunggu":

                                    $statusClass =
                                        "status-menunggu";

                                    break;


                                case "Diproses":

                                    $statusClass =
                                        "status-diproses";

                                    break;


                                case "Dibatalkan":

                                    $statusClass =
                                        "status-batal";

                                    break;

                            }

                            ?>


                            <tr>


                                <td class="transaction-number">

                                    <?= htmlspecialchars(
                                        $transaksi[
                                            "nomor_transaksi"
                                        ]
                                    ); ?>

                                </td>


                                <td class="transaction-date">

                                    <?= htmlspecialchars(
                                        $transaksi[
                                            "tanggal_transaksi"
                                        ]
                                    ); ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $transaksi[
                                            "nama_pelanggan"
                                        ]
                                    ); ?>

                                </td>


                                <td class="transaction-money">

                                    Rp <?= number_format(
                                        $transaksi[
                                            "total_harga"
                                        ],
                                        0,
                                        ",",
                                        "."
                                    ); ?>

                                </td>


                                <td>

                                    <span
                                        class="
                                            status
                                            <?= $statusClass; ?>
                                        "
                                    >

                                        <?= htmlspecialchars(
                                            $transaksi[
                                                "status"
                                            ]
                                        ); ?>

                                    </span>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>


                </table>


            </div>


        <?php else: ?>


            <div class="empty">

                Belum ada transaksi yang tercatat.

            </div>


        <?php endif; ?>


    </section>


    <!-- ==================================================
         CATATAN OWNER
    ================================================== -->

    <div class="owner-note">

        Dashboard ini bersifat
        <strong>monitoring</strong>.
        Pengelolaan produk, pengguna, supplier,
        stok, pesanan, dan transaksi dilakukan
        melalui modul Admin, Kasir, dan Gudang.

    </div>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>