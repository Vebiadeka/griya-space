<?php

session_start();


// ======================================================
// CEK LOGIN
// ======================================================

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// CEK ROLE
// ======================================================

if ($_SESSION["role"] !== "pelanggan") {
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL ID USER
// ======================================================

$id_user = (int) $_SESSION["user_id"];


// ======================================================
// AMBIL ID PESANAN DARI URL
// ======================================================

$id_pesanan = (int) ($_GET["id"] ?? 0);

if ($id_pesanan <= 0) {
    header("Location: index.php");
    exit;
}


// ======================================================
// CARI ID PELANGGAN
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_pelanggan,
        kode_pelanggan,
        nama_pelanggan
    FROM pelanggan
    WHERE id_user = ?
    LIMIT 1
");

if (!$stmt) {
    die(
        "Gagal mengambil data pelanggan: " .
        $conn->error
    );
}

$stmt->bind_param(
    "i",
    $id_user
);

$stmt->execute();

$resultPelanggan =
    $stmt->get_result();

if ($resultPelanggan->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    die("Data pelanggan tidak ditemukan.");
}

$pelanggan =
    $resultPelanggan->fetch_assoc();

$id_pelanggan =
    (int) $pelanggan["id_pelanggan"];

$stmt->close();


// ======================================================
// AMBIL HEADER PESANAN
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_pesanan,
        nomor_pesanan,
        tanggal_pesanan,
        subtotal,
        diskon,
        total_harga,
        status,
        catatan
    FROM pesanan
    WHERE id_pesanan = ?
      AND id_pelanggan = ?
    LIMIT 1
");

if (!$stmt) {
    die(
        "Gagal mengambil data pesanan: " .
        $conn->error
    );
}

$stmt->bind_param(
    "ii",
    $id_pesanan,
    $id_pelanggan
);

$stmt->execute();

$resultPesanan =
    $stmt->get_result();

if ($resultPesanan->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit;
}

$pesanan =
    $resultPesanan->fetch_assoc();

$stmt->close();


// ======================================================
// AMBIL DETAIL PESANAN
// ======================================================

$stmt = $conn->prepare("
    SELECT
        dp.id_detail_pesanan,
        dp.id_produk,
        dp.jumlah,
        dp.harga_satuan,
        dp.subtotal,
        p.kode_produk,
        p.nama_produk,
        p.satuan
    FROM detail_pesanan dp

    INNER JOIN produk p
        ON dp.id_produk = p.id_produk

    WHERE dp.id_pesanan = ?

    ORDER BY dp.id_detail_pesanan ASC
");

if (!$stmt) {
    die(
        "Gagal mengambil detail pesanan: " .
        $conn->error
    );
}

$stmt->bind_param(
    "i",
    $id_pesanan
);

$stmt->execute();

$resultDetail =
    $stmt->get_result();

$detail_items = [];

while (
    $detail =
    $resultDetail->fetch_assoc()
) {

    $detail_items[] =
        $detail;
}

$stmt->close();


// ======================================================
// STATUS CLASS
// ======================================================

$statusClass =
    "status-default";


switch (
    $pesanan["status"]
) {

    case "Menunggu":

        $statusClass =
            "status-menunggu";

        break;


    case "Diproses":

        $statusClass =
            "status-diproses";

        break;


    case "Siap Diambil":

        $statusClass =
            "status-siap";

        break;


    case "Selesai":

        $statusClass =
            "status-selesai";

        break;


    case "Dibatalkan":

        $statusClass =
            "status-batal";

        break;
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
        Detail Pesanan | Griya Space
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


        .back-link {

            color: white;

            text-decoration: none;

            border: 1px solid #555;

            padding: 9px 16px;

            font-size: 12px;

            transition: .2s;
        }


        .back-link:hover {

            background: white;

            color: #1d1d1d;
        }


        /* ==================================================
           MAIN
        ================================================== */

        main {

            max-width: 1050px;

            margin: 0 auto;

            padding: 45px 30px;
        }


        /* ==================================================
           PAGE HEADER
        ================================================== */

        .page-header {

            margin-bottom: 30px;
        }


        .page-header small {

            display: block;

            color: #777;

            font-size: 10px;

            letter-spacing: 3px;

            margin-bottom: 9px;
        }


        .page-header h1 {

            font-size: 32px;

            font-weight: 500;
        }


        .page-header p {

            margin-top: 9px;

            color: #777;

            font-size: 13px;

            line-height: 1.6;
        }


        /* ==================================================
           INFO GRID
        ================================================== */

        .info-grid {

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

            border-bottom: 1px solid #e4e4e4;

            font-size: 11px;

            letter-spacing: 2px;

            color: #777;
        }


        .card-body {

            padding: 22px;
        }


        .info-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 10px 0;

            border-bottom: 1px solid #eeeeee;

            font-size: 12px;
        }


        .info-row:first-child {

            padding-top: 0;
        }


        .info-row:last-child {

            border-bottom: none;

            padding-bottom: 0;
        }


        .info-label {

            color: #999;

            font-size: 10px;

            letter-spacing: 1px;
        }


        .info-value {

            text-align: right;

            font-weight: 600;
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

            letter-spacing: .8px;

            white-space: nowrap;
        }


        .status-menunggu {

            background: #f4f0e7;

            color: #8a6d2f;
        }


        .status-diproses {

            background: #eef2f6;

            color: #44627d;
        }


        .status-siap {

            background: #eef5ef;

            color: #3d6c43;
        }


        .status-selesai {

            background: #edf5f3;

            color: #347166;
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
           TABLE PRODUK
        ================================================== */

        .table-container {

            background: white;

            border: 1px solid #e4e4e4;

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 700px;
        }


        thead {

            background: #1d1d1d;

            color: white;
        }


        th {

            padding: 15px 14px;

            text-align: left;

            font-size: 10px;

            letter-spacing: 1px;

            white-space: nowrap;
        }


        td {

            padding: 16px 14px;

            border-bottom: 1px solid #eeeeee;

            font-size: 12px;

            vertical-align: middle;
        }


        tbody tr:last-child td {

            border-bottom: none;
        }


        .product-name {

            font-weight: 600;
        }


        .product-code {

            color: #999;

            font-size: 10px;

            margin-top: 4px;
        }


        .number {

            text-align: right;

            white-space: nowrap;
        }


        /* ==================================================
           SUMMARY
        ================================================== */

        .bottom-grid {

            display: grid;

            grid-template-columns:
                1fr
                330px;

            gap: 20px;

            margin-top: 20px;

            align-items: start;
        }


        .note {

            color: #777;

            font-size: 12px;

            line-height: 1.6;
        }


        .summary-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 10px 0;

            font-size: 12px;

            color: #666;
        }


        .summary-row.total {

            margin-top: 8px;

            padding-top: 17px;

            border-top: 1px solid #ddd;

            font-size: 17px;

            color: #1d1d1d;

            font-weight: 700;
        }


        .summary-value {

            white-space: nowrap;
        }


        /* ==================================================
           ACTION
        ================================================== */

        .actions {

            margin-top: 20px;

            display: flex;

            justify-content: space-between;

            gap: 10px;
        }


        .button {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 12px 18px;

            font-size: 11px;

            letter-spacing: 1px;

            text-decoration: none;
        }


        .button-primary {

            background: #1d1d1d;

            color: white;
        }


        .button-secondary {

            background: #f2f2f0;

            color: #555;

            border: 1px solid #ddd;
        }


        /* ==================================================
           EMPTY DETAIL
        ================================================== */

        .empty-detail {

            padding: 50px 20px;

            text-align: center;

            color: #777;
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


            .info-grid {

                grid-template-columns: 1fr;
            }


            .bottom-grid {

                grid-template-columns: 1fr;
            }


            .actions {

                flex-direction: column;
            }


            .button {

                width: 100%;
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
            PELANGGAN
        </div>


        <a
            href="index.php"
            class="back-link"
        >
            KEMBALI
        </a>

    </div>

</header>


<main>


    <section class="page-header">


        <small>
            RIWAYAT TRANSAKSI
        </small>


        <h1>
            Detail Pesanan
        </h1>


        <p>
            Rincian lengkap pesanan Anda di Griya Space.
        </p>

    </section>


    <!-- ==================================================
         INFORMASI PESANAN & PELANGGAN
    ================================================== -->

    <section class="info-grid">


        <div class="card">


            <div class="card-title">
                INFORMASI PESANAN
            </div>


            <div class="card-body">


                <div class="info-row">

                    <span class="info-label">
                        NOMOR PESANAN
                    </span>


                    <span class="info-value">

                        <?= htmlspecialchars(
                            $pesanan["nomor_pesanan"]
                        ); ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        TANGGAL
                    </span>


                    <span class="info-value">

                        <?= htmlspecialchars(
                            $pesanan["tanggal_pesanan"]
                        ); ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        STATUS
                    </span>


                    <span class="info-value">

                        <span
                            class="status <?= $statusClass; ?>"
                        >

                            <?= htmlspecialchars(
                                $pesanan["status"]
                            ); ?>

                        </span>

                    </span>

                </div>


            </div>


        </div>


        <div class="card">


            <div class="card-title">
                DATA PELANGGAN
            </div>


            <div class="card-body">


                <div class="info-row">

                    <span class="info-label">
                        KODE
                    </span>


                    <span class="info-value">

                        <?= htmlspecialchars(
                            $pelanggan["kode_pelanggan"]
                        ); ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        NAMA
                    </span>


                    <span class="info-value">

                        <?= htmlspecialchars(
                            $pelanggan["nama_pelanggan"]
                        ); ?>

                    </span>

                </div>


            </div>


        </div>


    </section>


    <!-- ==================================================
         DETAIL PRODUK
    ================================================== -->

    <section class="card">


        <div class="card-title">
            DETAIL PRODUK
        </div>


        <?php if (!empty($detail_items)): ?>


            <div class="table-container">


                <table>


                    <thead>

                        <tr>

                            <th>
                                PRODUK
                            </th>

                            <th>
                                JUMLAH
                            </th>

                            <th>
                                HARGA SATUAN
                            </th>

                            <th>
                                SUBTOTAL
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach (
                            $detail_items
                            as $detail
                        ): ?>


                            <tr>


                                <td>

                                    <div class="product-name">

                                        <?= htmlspecialchars(
                                            $detail["nama_produk"]
                                        ); ?>

                                    </div>


                                    <div class="product-code">

                                        <?= htmlspecialchars(
                                            $detail["kode_produk"]
                                        ); ?>

                                    </div>

                                </td>


                                <td>

                                    <?= (int) $detail["jumlah"]; ?>

                                    ×

                                    <?= htmlspecialchars(
                                        $detail["satuan"]
                                    ); ?>

                                </td>


                                <td class="number">

                                    Rp <?= number_format(
                                        $detail["harga_satuan"],
                                        0,
                                        ",",
                                        "."
                                    ); ?>

                                </td>


                                <td class="number">

                                    Rp <?= number_format(
                                        $detail["subtotal"],
                                        0,
                                        ",",
                                        "."
                                    ); ?>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>


                </table>


            </div>


        <?php else: ?>


            <div class="empty-detail">

                Tidak ada detail produk pada pesanan ini.

            </div>


        <?php endif; ?>


    </section>


    <!-- ==================================================
         TOTAL & CATATAN
    ================================================== -->

    <div class="bottom-grid">


        <section class="card">


            <div class="card-title">
                CATATAN PESANAN
            </div>


            <div class="card-body">


                <div class="note">

                    <?php if (
                        trim(
                            $pesanan["catatan"] ?? ""
                        ) !== ""
                    ): ?>

                        <?= nl2br(
                            htmlspecialchars(
                                $pesanan["catatan"]
                            )
                        ); ?>

                    <?php else: ?>

                        Tidak ada catatan untuk pesanan ini.

                    <?php endif; ?>

                </div>


            </div>


        </section>


        <section class="card">


            <div class="card-title">
                RINGKASAN
            </div>


            <div class="card-body">


                <div class="summary-row">

                    <span>
                        Subtotal
                    </span>


                    <span class="summary-value">

                        Rp <?= number_format(
                            $pesanan["subtotal"],
                            0,
                            ",",
                            "."
                        ); ?>

                    </span>

                </div>


                <div class="summary-row">

                    <span>
                        Diskon
                    </span>


                    <span class="summary-value">

                        Rp <?= number_format(
                            $pesanan["diskon"],
                            0,
                            ",",
                            "."
                        ); ?>

                    </span>

                </div>


                <div class="summary-row total">

                    <span>
                        Total
                    </span>


                    <span class="summary-value">

                        Rp <?= number_format(
                            $pesanan["total_harga"],
                            0,
                            ",",
                            "."
                        ); ?>

                    </span>

                </div>


            </div>


        </section>


    </div>


    <!-- ==================================================
         ACTION
    ================================================== -->

    <div class="actions">


        <a
            href="index.php"
            class="button button-secondary"
        >
            KEMBALI KE PESANAN
        </a>


        <a
            href="../index.php"
            class="button button-primary"
        >
            KEMBALI KE DASHBOARD
        </a>


    </div>


</main>


</body>

</html>


<?php

$conn->close();

?>