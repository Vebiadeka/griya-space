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

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../config/database.php";


$nama =
    $_SESSION["nama"] ?? "Administrator";


// ======================================================
// TOTAL PRODUK AKTIF
// ======================================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM produk
    WHERE status = 'Aktif'
");

if (!$stmt) {
    die(
        "Gagal mengambil total produk: " .
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
// TOTAL PELANGGAN
// ======================================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM pelanggan
");

if (!$stmt) {
    die(
        "Gagal mengambil total pelanggan: " .
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
// TOTAL USER
// ======================================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM users
    WHERE status = 'Aktif'
");

if (!$stmt) {
    die(
        "Gagal mengambil total pengguna: " .
        $conn->error
    );
}

$stmt->execute();

$result =
    $stmt->get_result();

$data =
    $result->fetch_assoc();

$total_user =
    (int) $data["total"];

$stmt->close();


// ======================================================
// TOTAL SUPPLIER
// ======================================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM supplier
");

if (!$stmt) {
    die(
        "Gagal mengambil total supplier: " .
        $conn->error
    );
}

$stmt->execute();

$result =
    $stmt->get_result();

$data =
    $result->fetch_assoc();

$total_supplier =
    (int) $data["total"];

$stmt->close();


// ======================================================
// PESANAN MENUNGGU
// ======================================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM pesanan
    WHERE status = 'Menunggu'
");

if (!$stmt) {
    die(
        "Gagal mengambil jumlah pesanan: " .
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
// TRANSAKSI SELESAI
// ======================================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM transaksi
    WHERE status = 'Selesai'
");

if (!$stmt) {
    die(
        "Gagal mengambil transaksi selesai: " .
        $conn->error
    );
}

$stmt->execute();

$result =
    $stmt->get_result();

$data =
    $result->fetch_assoc();

$transaksi_selesai =
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

    LIMIT 5
");

if (!$stmt) {
    die(
        "Gagal mengambil transaksi terbaru: " .
        $conn->error
    );
}

$stmt->execute();

$result =
    $stmt->get_result();

$transaksi_terbaru = [];

while (
    $row =
    $result->fetch_assoc()
) {
    $transaksi_terbaru[] =
        $row;
}

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
        Dashboard Admin | Griya Space
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

            max-width: 1300px;

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
           SUMMARY MASTER
        ================================================== */

        .summary-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 25px;
        }


        .summary-card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 21px;
        }


        .summary-label {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1.5px;
        }


        .summary-value {

            display: block;

            margin-top: 8px;

            font-size: 28px;

            font-weight: 600;
        }


        .summary-note {

            margin-top: 7px;

            color: #999;

            font-size: 10px;
        }


        /* ==================================================
           OPERASIONAL
        ================================================== */

        .operational-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-bottom: 30px;
        }


        .operational-card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 20px;
        }


        .operational-label {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1.5px;
        }


        .operational-value {

            display: block;

            margin-top: 8px;

            font-size: 25px;

            font-weight: 600;
        }


        .operational-note {

            margin-top: 7px;

            color: #999;

            font-size: 10px;
        }


        .warning {

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

            padding: 23px;

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

            font-size: 15px;

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
           TRANSAKSI TERBARU
        ================================================== */

        .recent-card {

            background: white;

            border: 1px solid #e4e4e4;

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


        .number {

            font-weight: 600;

            white-space: nowrap;
        }


        .date {

            color: #666;

            white-space: nowrap;
        }


        .money {

            font-weight: 600;

            white-space: nowrap;
        }


        /* ==================================================
           STATUS
        ================================================== */

        .status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 9px;

            font-weight: 600;

            letter-spacing: .7px;
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


            .operational-grid {

                grid-template-columns:
                    1fr;
            }

        }


        @media (max-width: 700px) {

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
            .menu-grid {

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
            ADMIN
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
            DASHBOARD ADMIN
        </small>


        <h1>

            Selamat datang,
            <?= htmlspecialchars(
                $nama
            ); ?>

        </h1>


        <p>

            Kelola data master dan pantau aktivitas operasional
            Griya Space dari satu dashboard.

        </p>

    </section>


    <!-- ==================================================
         MASTER SUMMARY
    ================================================== -->

    <section class="summary-grid">


        <div class="summary-card">

            <span class="summary-label">
                PRODUK AKTIF
            </span>


            <span class="summary-value">
                <?= $total_produk; ?>
            </span>


            <div class="summary-note">
                Data produk aktif
            </div>

        </div>


        <div class="summary-card">

            <span class="summary-label">
                PELANGGAN
            </span>


            <span class="summary-value">
                <?= $total_pelanggan; ?>
            </span>


            <div class="summary-note">
                Pelanggan terdaftar
            </div>

        </div>


        <div class="summary-card">

            <span class="summary-label">
                PENGGUNA AKTIF
            </span>


            <span class="summary-value">
                <?= $total_user; ?>
            </span>


            <div class="summary-note">
                Semua user aktif
            </div>

        </div>


        <div class="summary-card">

            <span class="summary-label">
                SUPPLIER
            </span>


            <span class="summary-value">
                <?= $total_supplier; ?>
            </span>


            <div class="summary-note">
                Supplier terdaftar
            </div>

        </div>


    </section>


    <!-- ==================================================
         OPERASIONAL
    ================================================== -->

    <section class="operational-grid">


        <div class="operational-card">

            <span class="operational-label">
                PESANAN MENUNGGU
            </span>


            <span class="operational-value warning">
                <?= $pesanan_menunggu; ?>
            </span>


            <div class="operational-note">
                Pesanan belum selesai diproses
            </div>

        </div>


        <div class="operational-card">

            <span class="operational-label">
                TRANSAKSI SELESAI
            </span>


            <span class="operational-value">
                <?= $transaksi_selesai; ?>
            </span>


            <div class="operational-note">
                Total transaksi sukses
            </div>

        </div>


        <div class="operational-card">

            <span class="operational-label">
                PENJUALAN HARI INI
            </span>


            <span class="operational-value">

                Rp <?= number_format(
                    $penjualan_hari_ini,
                    0,
                    ",",
                    "."
                ); ?>

            </span>


            <div class="operational-note">
                Total transaksi selesai hari ini
            </div>

        </div>


    </section>


    <!-- ==================================================
         MENU
    ================================================== -->

    <div class="section-title">
        MENU ADMIN
    </div>


    <section class="menu-grid">


        <a
            href="produk/"
            class="menu-card"
        >

            <span class="menu-number">
                01
            </span>


            <span class="menu-title">
                Produk
            </span>


            <span class="menu-description">
                Kelola data produk, harga, stok minimum,
                dan status produk.
            </span>

        </a>


        <a
            href="kategori/"
            class="menu-card"
        >

            <span class="menu-number">
                02
            </span>


            <span class="menu-title">
                Kategori
            </span>


            <span class="menu-description">
                Kelola kategori material bangunan.
            </span>

        </a>


        <a
            href="supplier/"
            class="menu-card"
        >

            <span class="menu-number">
                03
            </span>


            <span class="menu-title">
                Supplier
            </span>


            <span class="menu-description">
                Kelola data supplier dan informasi kontak.
            </span>

        </a>


        <a
            href="pelanggan/"
            class="menu-card"
        >

            <span class="menu-number">
                04
            </span>


            <span class="menu-title">
                Pelanggan
            </span>


            <span class="menu-description">
                Lihat dan kelola data pelanggan.
            </span>

        </a>


        <a
            href="users/"
            class="menu-card"
        >

            <span class="menu-number">
                05
            </span>


            <span class="menu-title">
                Pengguna
            </span>


            <span class="menu-description">
                Kelola akun Admin, Kasir, Gudang,
                dan Pelanggan.
            </span>

        </a>


        <a
            href="../kasir/pesanan/"
            class="menu-card"
        >

            <span class="menu-number">
                06
            </span>


            <span class="menu-title">
                Pesanan
            </span>


            <span class="menu-description">
                Pantau pesanan pelanggan yang masuk.
            </span>

        </a>


        <a
            href="../kasir/transaksi/"
            class="menu-card"
        >

            <span class="menu-number">
                07
            </span>


            <span class="menu-title">
                Transaksi
            </span>


            <span class="menu-description">
                Lihat riwayat transaksi dan pembayaran.
            </span>

        </a>


    </section>


    <!-- ==================================================
         TRANSAKSI TERBARU
    ================================================== -->

    <div class="section-title">
        TRANSAKSI TERBARU
    </div>


    <section class="recent-card">


        <?php if (
            !empty($transaksi_terbaru)
        ): ?>


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
                            $transaksi["status"]
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


                            <td class="number">

                                <?= htmlspecialchars(
                                    $transaksi[
                                        "nomor_transaksi"
                                    ]
                                ); ?>

                            </td>


                            <td class="date">

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


                            <td class="money">

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
                                    class="status <?= $statusClass; ?>"
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


        <?php else: ?>


            <div
                style="
                    padding:45px;
                    text-align:center;
                    color:#777;
                    font-size:12px;
                "
            >

                Belum ada transaksi.

            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>