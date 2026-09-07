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
// CEK ROLE
// ======================================================

if ($_SESSION["role"] !== "gudang") {
    header("Location: ../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../config/database.php";


$nama =
    $_SESSION["nama"] ?? "Staff Gudang";


// ======================================================
// TOTAL PRODUK AKTIF
// ======================================================

$sql =
    "
    SELECT COUNT(*) AS total
    FROM produk
    WHERE status = 'Aktif'
    ";


$result =
    mysqli_query(
        $conn,
        $sql
    );


if (!$result) {
    die(
        "Gagal mengambil total produk: " .
        mysqli_error($conn)
    );
}


$data =
    mysqli_fetch_assoc($result);


$total_produk =
    (int) $data["total"];


// ======================================================
// STOK MENIPIS
// ======================================================
//
// Menggunakan stok <= stok_minimum
//

$sql =
    "
    SELECT COUNT(*) AS total
    FROM produk
    WHERE status = 'Aktif'
      AND stok <= stok_minimum
    ";


$result =
    mysqli_query(
        $conn,
        $sql
    );


if (!$result) {
    die(
        "Gagal mengambil stok menipis: " .
        mysqli_error($conn)
    );
}


$data =
    mysqli_fetch_assoc($result);


$stok_menipis =
    (int) $data["total"];


// ======================================================
// TOTAL BARANG MASUK
// ======================================================

$sql =
    "
    SELECT COUNT(*) AS total
    FROM barang_masuk
    ";


$result =
    mysqli_query(
        $conn,
        $sql
    );


if (!$result) {
    die(
        "Gagal mengambil barang masuk: " .
        mysqli_error($conn)
    );
}


$data =
    mysqli_fetch_assoc($result);


$total_barang_masuk =
    (int) $data["total"];


// ======================================================
// TOTAL BARANG KELUAR
// ======================================================

$sql =
    "
    SELECT COUNT(*) AS total
    FROM barang_keluar
    ";


$result =
    mysqli_query(
        $conn,
        $sql
    );


if (!$result) {
    die(
        "Gagal mengambil barang keluar: " .
        mysqli_error($conn)
    );
}


$data =
    mysqli_fetch_assoc($result);


$total_barang_keluar =
    (int) $data["total"];


// ======================================================
// PRODUK DENGAN STOK MENIPIS
// ======================================================

$sql =
    "
    SELECT
        id_produk,
        kode_produk,
        nama_produk,
        stok,
        stok_minimum,
        satuan
    FROM produk
    WHERE status = 'Aktif'
      AND stok <= stok_minimum
    ORDER BY stok ASC
    LIMIT 8
    ";


$result_stok =
    mysqli_query(
        $conn,
        $sql
    );


if (!$result_stok) {
    die(
        "Gagal mengambil daftar stok menipis: " .
        mysqli_error($conn)
    );
}


$produk_menipis = [];


while (
    $produk =
    mysqli_fetch_assoc($result_stok)
) {

    $produk_menipis[] =
        $produk;
}


// ======================================================
// MUTASI STOK TERBARU
// ======================================================

$sql =
    "
    SELECT
        m.id_mutasi,
        m.id_produk,
        m.jenis_mutasi,
        m.jumlah,
        m.stok_sebelum,
        m.stok_sesudah,
        m.referensi,
        m.tanggal_mutasi,

        p.nama_produk,
        p.satuan

    FROM mutasi_stok m

    INNER JOIN produk p
        ON m.id_produk = p.id_produk

    ORDER BY
        m.id_mutasi DESC

    LIMIT 6
    ";


$result_mutasi =
    mysqli_query(
        $conn,
        $sql
    );


if (!$result_mutasi) {
    die(
        "Gagal mengambil mutasi stok: " .
        mysqli_error($conn)
    );
}


$mutasi_terbaru = [];


while (
    $mutasi =
    mysqli_fetch_assoc($result_mutasi)
) {

    $mutasi_terbaru[] =
        $mutasi;
}

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
        Gudang | Griya Space
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

            justify-content: space-between;

            align-items: center;
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

            max-width: 1250px;

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
           SUMMARY
        ================================================== */

        .summary-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 30px;
        }


        .summary-card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 22px;
        }


        .summary-label {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1.5px;
        }


        .summary-value {

            display: block;

            margin-top: 9px;

            font-size: 28px;

            font-weight: 600;
        }


        .summary-note {

            margin-top: 7px;

            color: #999;

            font-size: 10px;
        }


        .warning-card {

            background: #f8f3e9;

            border-color: #eadfc7;
        }


        .warning-card .summary-value {

            color: #8a6d2f;
        }


        /* ==================================================
           MENU
        ================================================== */

        .section-title {

            margin-bottom: 15px;

            color: #777;

            font-size: 10px;

            letter-spacing: 2px;
        }


        .menu-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 30px;
        }


        .menu-card {

            display: block;

            background: white;

            border: 1px solid #e4e4e4;

            padding: 24px;

            color: #1d1d1d;

            text-decoration: none;

            transition: .2s;
        }


        .menu-card:hover {

            border-color: #aaa;

            transform: translateY(-2px);
        }


        .menu-number {

            display: block;

            color: #999;

            font-size: 10px;

            letter-spacing: 2px;

            margin-bottom: 12px;
        }


        .menu-title {

            display: block;

            font-size: 16px;

            font-weight: 600;
        }


        .menu-description {

            display: block;

            margin-top: 8px;

            color: #777;

            font-size: 11px;

            line-height: 1.5;
        }


        /* ==================================================
           TWO COLUMN
        ================================================== */

        .content-grid {

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            gap: 20px;
        }


        .card {

            background: white;

            border: 1px solid #e4e4e4;
        }


        .card-title {

            padding: 18px 22px;

            border-bottom: 1px solid #e4e4e4;

            font-size: 11px;

            letter-spacing: 2px;

            color: #777;
        }


        .card-body {

            padding: 20px;
        }


        /* ==================================================
           STOK MENIPIS
        ================================================== */

        .stock-item {

            padding: 14px 0;

            border-bottom: 1px solid #eeeeee;

            display: flex;

            justify-content: space-between;

            gap: 15px;
        }


        .stock-item:first-child {

            padding-top: 0;
        }


        .stock-item:last-child {

            border-bottom: none;

            padding-bottom: 0;
        }


        .product-name {

            font-size: 13px;

            font-weight: 600;
        }


        .product-code {

            margin-top: 4px;

            color: #999;

            font-size: 10px;
        }


        .stock-number {

            text-align: right;

            white-space: nowrap;
        }


        .stock-current {

            font-size: 16px;

            font-weight: 700;

            color: #8a6d2f;
        }


        .stock-minimum {

            margin-top: 3px;

            color: #999;

            font-size: 9px;
        }


        /* ==================================================
           MUTASI
        ================================================== */

        .mutation-item {

            padding: 14px 0;

            border-bottom: 1px solid #eeeeee;

            display: flex;

            justify-content: space-between;

            gap: 15px;
        }


        .mutation-item:first-child {

            padding-top: 0;
        }


        .mutation-item:last-child {

            border-bottom: none;

            padding-bottom: 0;
        }


        .mutation-name {

            font-size: 13px;

            font-weight: 600;
        }


        .mutation-meta {

            margin-top: 5px;

            color: #999;

            font-size: 10px;

            line-height: 1.5;
        }


        .mutation-number {

            text-align: right;

            white-space: nowrap;

            font-weight: 600;
        }


        .mutation-in {

            color: #3d6c43;
        }


        .mutation-out {

            color: #8a4444;
        }


        .mutation-default {

            color: #555;
        }


        /* ==================================================
           EMPTY
        ================================================== */

        .empty {

            padding: 35px 15px;

            text-align: center;

            color: #777;

            font-size: 12px;

            line-height: 1.5;
        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 1000px) {

            .summary-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .menu-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 750px) {

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


            .summary-grid,
            .menu-grid,
            .content-grid {

                grid-template-columns: 1fr;
            }


            .welcome h1 {

                font-size: 28px;
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
            STAFF GUDANG
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
            DASHBOARD GUDANG
        </small>


        <h1>
            Selamat datang,
            <?= htmlspecialchars($nama); ?>
        </h1>


        <p>
            Pantau persediaan, penerimaan, pengeluaran,
            dan mutasi stok Griya Space.
        </p>

    </section>


    <!-- ==================================================
         SUMMARY
    ================================================== -->

    <section class="summary-grid">


        <div class="summary-card">

            <span class="summary-label">
                TOTAL PRODUK AKTIF
            </span>


            <span class="summary-value">
                <?= $total_produk; ?>
            </span>


            <div class="summary-note">
                Produk yang tersedia di sistem
            </div>

        </div>


        <div class="summary-card warning-card">

            <span class="summary-label">
                STOK MENIPIS
            </span>


            <span class="summary-value">
                <?= $stok_menipis; ?>
            </span>


            <div class="summary-note">
                Mencapai atau di bawah stok minimum
            </div>

        </div>


        <div class="summary-card">

            <span class="summary-label">
                BARANG MASUK
            </span>


            <span class="summary-value">
                <?= $total_barang_masuk; ?>
            </span>


            <div class="summary-note">
                Total transaksi penerimaan
            </div>

        </div>


        <div class="summary-card">

            <span class="summary-label">
                BARANG KELUAR
            </span>


            <span class="summary-value">
                <?= $total_barang_keluar; ?>
            </span>


            <div class="summary-note">
                Total transaksi pengeluaran
            </div>

        </div>


    </section>


    <!-- ==================================================
         MENU
    ================================================== -->

    <div class="section-title">
        MENU GUDANG
    </div>


    <section class="menu-grid">


        <a
            href="barang_masuk/"
            class="menu-card"
        >

            <span class="menu-number">
                01
            </span>


            <span class="menu-title">
                Barang Masuk
            </span>


            <span class="menu-description">
                Catat dan pantau penerimaan barang dari supplier.
            </span>

        </a>


        <a
            href="barang_keluar/"
            class="menu-card"
        >

            <span class="menu-number">
                02
            </span>


            <span class="menu-title">
                Barang Keluar
            </span>


            <span class="menu-description">
                Catat pengeluaran barang dari persediaan gudang.
            </span>

        </a>


        <a
            href="produk/"
            class="menu-card"
        >

            <span class="menu-number">
                03
            </span>


            <span class="menu-title">
                Produk & Stok
            </span>


            <span class="menu-description">
                Pantau stok produk dan kondisi persediaan.
            </span>

        </a>


        <a
            href="mutasi/"
            class="menu-card"
        >

            <span class="menu-number">
                04
            </span>


            <span class="menu-title">
                Mutasi Stok
            </span>


            <span class="menu-description">
                Lihat seluruh riwayat perubahan stok barang.
            </span>

        </a>


    </section>


    <!-- ==================================================
         CONTENT
    ================================================== -->

    <section class="content-grid">


        <!-- ==================================================
             STOK MENIPIS
        ================================================== -->

        <section class="card">


            <div class="card-title">
                STOK MENIPIS
            </div>


            <div class="card-body">


                <?php if (
                    !empty($produk_menipis)
                ): ?>


                    <?php foreach (
                        $produk_menipis
                        as $produk
                    ): ?>


                        <div class="stock-item">


                            <div>

                                <div class="product-name">

                                    <?= htmlspecialchars(
                                        $produk["nama_produk"]
                                    ); ?>

                                </div>


                                <div class="product-code">

                                    <?= htmlspecialchars(
                                        $produk["kode_produk"]
                                    ); ?>

                                </div>

                            </div>


                            <div class="stock-number">


                                <div class="stock-current">

                                    <?= (int) $produk["stok"]; ?>

                                    <?= htmlspecialchars(
                                        $produk["satuan"]
                                    ); ?>

                                </div>


                                <div class="stock-minimum">

                                    Minimum:
                                    <?= (int) $produk["stok_minimum"]; ?>

                                </div>


                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty">

                        Tidak ada produk yang stoknya menipis.

                    </div>


                <?php endif; ?>


            </div>


        </section>


        <!-- ==================================================
             MUTASI TERBARU
        ================================================== -->

        <section class="card">


            <div class="card-title">
                MUTASI STOK TERBARU
            </div>


            <div class="card-body">


                <?php if (
                    !empty($mutasi_terbaru)
                ): ?>


                    <?php foreach (
                        $mutasi_terbaru
                        as $mutasi
                    ): ?>


                        <?php

                        $mutationClass =
                            "mutation-default";


                        $jenis =
                            strtolower(
                                $mutasi["jenis_mutasi"]
                            );


                        if (
                            $jenis ===
                            "barang masuk"
                        ) {

                            $mutationClass =
                                "mutation-in";

                        } elseif (
                            $jenis ===
                            "barang keluar"
                            ||
                            $jenis ===
                            "penjualan"
                        ) {

                            $mutationClass =
                                "mutation-out";
                        }

                        ?>


                        <div class="mutation-item">


                            <div>

                                <div class="mutation-name">

                                    <?= htmlspecialchars(
                                        $mutasi[
                                            "nama_produk"
                                        ]
                                    ); ?>

                                </div>


                                <div class="mutation-meta">

                                    <?= htmlspecialchars(
                                        $mutasi[
                                            "jenis_mutasi"
                                        ]
                                    ); ?>

                                    ·

                                    <?= htmlspecialchars(
                                        $mutasi[
                                            "referensi"
                                        ]
                                    ); ?>

                                    <br>

                                    <?= htmlspecialchars(
                                        $mutasi[
                                            "tanggal_mutasi"
                                        ]
                                    ); ?>

                                </div>

                            </div>


                            <div
                                class="
                                    mutation-number
                                    <?= $mutationClass; ?>
                                "
                            >

                                <?php if (
                                    $jenis ===
                                    "barang masuk"
                                ): ?>

                                    +

                                <?php else: ?>

                                    -

                                <?php endif; ?>


                                <?= (int) $mutasi["jumlah"]; ?>

                                <?= htmlspecialchars(
                                    $mutasi["satuan"]
                                ); ?>

                                <br>

                                <?= (int) $mutasi["stok_sebelum"]; ?>

                                →
                                <?= (int) $mutasi["stok_sesudah"]; ?>

                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty">

                        Belum ada mutasi stok.

                    </div>


                <?php endif; ?>


            </div>


        </section>


    </section>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>