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

if ($_SESSION["role"] !== "kasir") {
    header("Location: ../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../config/database.php";


$nama =
    $_SESSION["nama"] ?? "Kasir";

$id_user =
    (int) $_SESSION["user_id"];


// ======================================================
// PESANAN MENUNGGU
// ======================================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS jumlah
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
    (int) $data["jumlah"];

$stmt->close();


// ======================================================
// TRANSAKSI HARI INI
// ======================================================

$stmt = $conn->prepare("
    SELECT COUNT(*) AS jumlah
    FROM transaksi
    WHERE DATE(tanggal_transaksi) = CURDATE()
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
    (int) $data["jumlah"];

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
        t.nomor_transaksi,
        t.tanggal_transaksi,
        t.total_harga,
        t.status,
        p.nama_pelanggan
    FROM transaksi t

    INNER JOIN pelanggan p
        ON t.id_pelanggan = p.id_pelanggan

    ORDER BY t.id_transaksi DESC

    LIMIT 5
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
        Kasir | Griya Space
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
        }


        /* ==================================================
           SUMMARY
        ================================================== */

        .summary-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

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

            font-size: 10px;

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
                repeat(3, 1fr);

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
           TRANSAKSI TERBARU
        ================================================== */

        .recent-card {

            background: white;

            border: 1px solid #e4e4e4;

            overflow-x: auto;
        }


        table {

            width: 100%;

            min-width: 800px;

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


        .empty {

            padding: 50px;

            text-align: center;

            color: #777;

            font-size: 12px;
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


            .summary-grid {

                grid-template-columns: 1fr;
            }


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
            KASIR
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
            HALAMAN KASIR
        </small>


        <h1>
            Selamat datang,
            <?= htmlspecialchars($nama); ?>
        </h1>


        <p>
            Kelola pesanan, pembayaran, dan transaksi penjualan Griya Space.
        </p>

    </section>


    <!-- ==================================================
         SUMMARY
    ================================================== -->

    <section class="summary-grid">


        <div class="summary-card">

            <span class="summary-label">
                PESANAN MENUNGGU
            </span>


            <span class="summary-value">
                <?= $pesanan_menunggu; ?>
            </span>


            <div class="summary-note">
                Pesanan yang belum diproses
            </div>

        </div>


        <div class="summary-card">

            <span class="summary-label">
                TRANSAKSI HARI INI
            </span>


            <span class="summary-value">
                <?= $transaksi_hari_ini; ?>
            </span>


            <div class="summary-note">
                Transaksi tercatat hari ini
            </div>

        </div>


        <div class="summary-card">

            <span class="summary-label">
                PENJUALAN HARI INI
            </span>


            <span class="summary-value">

                Rp <?= number_format(
                    $penjualan_hari_ini,
                    0,
                    ",",
                    "."
                ); ?>

            </span>


            <div class="summary-note">
                Total transaksi selesai
            </div>

        </div>


    </section>


    <!-- ==================================================
         MENU
    ================================================== -->

    <div class="section-title">
        MENU KASIR
    </div>


    <section class="menu-grid">


        <a
            href="pesanan/"
            class="menu-card"
        >

            <span class="menu-number">
                01
            </span>


            <span class="menu-title">
                Pesanan Masuk
            </span>


            <span class="menu-description">
                Lihat pesanan pelanggan yang menunggu proses.
            </span>

        </a>


        <a
            href="transaksi/"
            class="menu-card"
        >

            <span class="menu-number">
                02
            </span>


            <span class="menu-title">
                Riwayat Transaksi
            </span>


            <span class="menu-description">
                Lihat seluruh transaksi penjualan yang telah diproses.
            </span>

        </a>


        <a
            href="transaksi/"
            class="menu-card"
        >

            <span class="menu-number">
                03
            </span>


            <span class="menu-title">
                Pembayaran
            </span>


            <span class="menu-description">
                Akses transaksi dan konfirmasi pembayaran pelanggan.
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


            <div class="empty">

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