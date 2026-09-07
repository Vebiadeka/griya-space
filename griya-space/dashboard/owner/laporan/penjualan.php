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
// CEK ROLE OWNER
// ======================================================

if ($_SESSION["role"] !== "owner") {
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// DEFAULT PERIODE
// ======================================================

$tanggal_awal =
    $_GET["tanggal_awal"]
    ?? date("Y-m-01");

$tanggal_akhir =
    $_GET["tanggal_akhir"]
    ?? date("Y-m-d");


// ======================================================
// VALIDASI FORMAT TANGGAL
// ======================================================

function validTanggal($tanggal)
{
    $date = DateTime::createFromFormat(
        "Y-m-d",
        $tanggal
    );

    return
        $date &&
        $date->format("Y-m-d") === $tanggal;
}


if (!validTanggal($tanggal_awal)) {
    $tanggal_awal = date("Y-m-01");
}


if (!validTanggal($tanggal_akhir)) {
    $tanggal_akhir = date("Y-m-d");
}


// ======================================================
// VALIDASI RENTANG
// ======================================================

if ($tanggal_awal > $tanggal_akhir) {

    $sementara = $tanggal_awal;

    $tanggal_awal =
        $tanggal_akhir;

    $tanggal_akhir =
        $sementara;
}


// ======================================================
// RENTANG DATABASE
// ======================================================
//
// tanggal_transaksi memiliki waktu,
// maka akhir periode dibuat sampai 23:59:59.
//

$mulai =
    $tanggal_awal . " 00:00:00";

$akhir =
    $tanggal_akhir . " 23:59:59";


// ======================================================
// RINGKASAN LAPORAN
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_transaksi,
        COALESCE(
            SUM(total_harga),
            0
        ) AS total_omzet
    FROM transaksi
    WHERE status = 'Selesai'
      AND tanggal_transaksi
          BETWEEN ? AND ?
");


if (!$stmt) {
    die(
        "Gagal mengambil ringkasan laporan: " .
        $conn->error
    );
}


$stmt->bind_param(
    "ss",
    $mulai,
    $akhir
);


$stmt->execute();


$resultRingkasan =
    $stmt->get_result();


$ringkasan =
    $resultRingkasan->fetch_assoc();


$stmt->close();


$total_transaksi =
    (int) (
        $ringkasan["total_transaksi"]
        ?? 0
    );


$total_omzet =
    (float) (
        $ringkasan["total_omzet"]
        ?? 0
    );


// ======================================================
// TOTAL ITEM TERJUAL
// ======================================================

$stmt = $conn->prepare("
    SELECT
        COALESCE(
            SUM(dt.jumlah),
            0
        ) AS total_item
    FROM detail_transaksi dt

    INNER JOIN transaksi t
        ON dt.id_transaksi = t.id_transaksi

    WHERE t.status = 'Selesai'
      AND t.tanggal_transaksi
          BETWEEN ? AND ?
");


if (!$stmt) {
    die(
        "Gagal mengambil total item: " .
        $conn->error
    );
}


$stmt->bind_param(
    "ss",
    $mulai,
    $akhir
);


$stmt->execute();


$resultItem =
    $stmt->get_result();


$dataItem =
    $resultItem->fetch_assoc();


$stmt->close();


$total_item =
    (int) (
        $dataItem["total_item"]
        ?? 0
    );


// ======================================================
// DAFTAR TRANSAKSI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        t.id_transaksi,
        t.nomor_transaksi,
        t.tanggal_transaksi,
        t.subtotal,
        t.diskon,
        t.total_harga,
        t.status,

        pl.kode_pelanggan,
        pl.nama_pelanggan,

        u.nama_lengkap AS nama_kasir

    FROM transaksi t

    INNER JOIN pelanggan pl
        ON t.id_pelanggan = pl.id_pelanggan

    INNER JOIN users u
        ON t.id_user = u.id_user

    WHERE t.status = 'Selesai'
      AND t.tanggal_transaksi
          BETWEEN ? AND ?

    ORDER BY
        t.tanggal_transaksi DESC,
        t.id_transaksi DESC
");


if (!$stmt) {
    die(
        "Gagal mengambil daftar transaksi: " .
        $conn->error
    );
}


$stmt->bind_param(
    "ss",
    $mulai,
    $akhir
);


$stmt->execute();


$resultTransaksi =
    $stmt->get_result();


$transaksi_list = [];


while (
    $transaksi =
    $resultTransaksi->fetch_assoc()
) {

    $transaksi_list[] =
        $transaksi;
}


$stmt->close();


// ======================================================
// TOP PRODUK PERIODE
// ======================================================

$stmt = $conn->prepare("
    SELECT
        p.kode_produk,
        p.nama_produk,
        p.satuan,
        SUM(dt.jumlah) AS total_terjual,
        SUM(dt.subtotal) AS total_nilai

    FROM detail_transaksi dt

    INNER JOIN transaksi t
        ON dt.id_transaksi = t.id_transaksi

    INNER JOIN produk p
        ON dt.id_produk = p.id_produk

    WHERE t.status = 'Selesai'
      AND t.tanggal_transaksi
          BETWEEN ? AND ?

    GROUP BY
        p.id_produk,
        p.kode_produk,
        p.nama_produk,
        p.satuan

    ORDER BY
        total_terjual DESC,
        total_nilai DESC

    LIMIT 5
");


if (!$stmt) {
    die(
        "Gagal mengambil produk terlaris: " .
        $conn->error
    );
}


$stmt->bind_param(
    "ss",
    $mulai,
    $akhir
);


$stmt->execute();


$resultTopProduk =
    $stmt->get_result();


$top_produk = [];


while (
    $produk =
    $resultTopProduk->fetch_assoc()
) {

    $top_produk[] =
        $produk;
}


$stmt->close();


$conn->close();

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
        Laporan Penjualan | Griya Space
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

            gap: 15px;
        }


        .role {

            color: #aaa;

            font-size: 10px;

            letter-spacing: 2px;
        }


        .back-link {

            color: white;

            text-decoration: none;

            border: 1px solid #555;

            padding: 9px 15px;

            font-size: 10px;

            letter-spacing: 1px;
        }


        .back-link:hover {

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


        .page-header {

            margin-bottom: 25px;
        }


        .page-header small {

            display: block;

            margin-bottom: 8px;

            color: #777;

            font-size: 10px;

            letter-spacing: 3px;
        }


        .page-header h1 {

            font-size: 32px;

            font-weight: 500;
        }


        .page-header p {

            margin-top: 8px;

            color: #777;

            font-size: 13px;
        }


        /* ==================================================
           FILTER
        ================================================== */

        .filter-card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 20px;

            margin-bottom: 20px;
        }


        .filter-title {

            margin-bottom: 15px;

            color: #777;

            font-size: 10px;

            letter-spacing: 2px;
        }


        .filter-form {

            display: grid;

            grid-template-columns:
                1fr
                1fr
                auto
                auto;

            align-items: end;

            gap: 12px;
        }


        .form-group {

            display: flex;

            flex-direction: column;
        }


        .form-group label {

            margin-bottom: 6px;

            font-size: 10px;

            font-weight: 600;
        }


        input[type="date"] {

            min-height: 40px;

            border: 1px solid #d8d8d5;

            background: #fafafa;

            padding: 9px 10px;

            font-family: inherit;

            font-size: 11px;

            outline: none;
        }


        input[type="date"]:focus {

            background: white;

            border-color: #777;
        }


        .button {

            min-height: 40px;

            padding: 0 16px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            border: 1px solid #ccc;

            font-size: 10px;

            letter-spacing: 1px;

            cursor: pointer;
        }


        .button-primary {

            background: #1d1d1d;

            color: white;

            border-color: #1d1d1d;
        }


        .button-primary:hover {

            background: #333;
        }


        .button-secondary {

            background: #f1f1ef;

            color: #555;
        }


        /* ==================================================
           SUMMARY
        ================================================== */

        .summary-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-bottom: 20px;
        }


        .summary-card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 20px;
        }


        .summary-label {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1.5px;
        }


        .summary-value {

            display: block;

            margin-top: 7px;

            font-size: 25px;

            font-weight: 600;
        }


        .summary-note {

            margin-top: 6px;

            color: #999;

            font-size: 10px;
        }


        /* ==================================================
           CONTENT GRID
        ================================================== */

        .content-grid {

            display: grid;

            grid-template-columns:
                1.6fr
                .8fr;

            gap: 20px;

            margin-bottom: 20px;
        }


        .card {

            background: white;

            border: 1px solid #e4e4e4;
        }


        .card-title {

            padding: 17px 20px;

            border-bottom: 1px solid #eeeeee;

            color: #777;

            font-size: 10px;

            letter-spacing: 2px;
        }


        .card-body {

            padding: 20px;
        }


        /* ==================================================
           TABLE
        ================================================== */

        .table-container {

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 800px;
        }


        th {

            padding: 13px;

            background: #1d1d1d;

            color: white;

            text-align: left;

            font-size: 9px;

            letter-spacing: 1px;

            white-space: nowrap;
        }


        td {

            padding: 13px;

            border-bottom: 1px solid #eeeeee;

            font-size: 11px;

            vertical-align: middle;
        }


        tbody tr:hover {

            background: #fafafa;
        }


        .transaction-number {

            font-weight: 600;

            white-space: nowrap;
        }


        .transaction-date {

            color: #666;

            white-space: nowrap;
        }


        .money {

            font-weight: 600;

            white-space: nowrap;

            text-align: right;
        }


        .status {

            display: inline-block;

            padding: 5px 8px;

            border-radius: 20px;

            background: #edf5f3;

            color: #347166;

            font-size: 8px;

            font-weight: 600;

            letter-spacing: .7px;
        }


        /* ==================================================
           TOP PRODUK
        ================================================== */

        .product-item {

            display: flex;

            justify-content: space-between;

            gap: 15px;

            padding: 13px 0;

            border-bottom: 1px solid #eeeeee;
        }


        .product-item:first-child {

            padding-top: 0;
        }


        .product-item:last-child {

            padding-bottom: 0;

            border-bottom: none;
        }


        .product-name {

            font-size: 11px;

            font-weight: 600;

            line-height: 1.4;
        }


        .product-code {

            margin-top: 3px;

            color: #999;

            font-size: 9px;
        }


        .product-value {

            text-align: right;

            white-space: nowrap;

            font-size: 11px;

            font-weight: 600;
        }


        .product-meta {

            margin-top: 3px;

            color: #999;

            font-size: 9px;

            font-weight: 400;
        }


        .empty {

            padding: 35px 10px;

            text-align: center;

            color: #777;

            font-size: 11px;

            line-height: 1.5;
        }


        /* ==================================================
           PRINT
        ================================================== */

        .print-only {

            display: none;
        }


        @media print {

            body {

                background: white;

                padding: 0;
            }


            header,
            .filter-card,
            .actions {

                display: none !important;
            }


            main {

                max-width: none;

                padding: 20px;
            }


            .print-only {

                display: block;
            }


            .summary-card,
            .card {

                break-inside: avoid;
            }


            .summary-grid,
            .content-grid {

                gap: 10px;
            }


            .table-container {

                overflow: visible;
            }


            table {

                min-width: 0;
            }

        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 900px) {

            .filter-form {

                grid-template-columns:
                    1fr
                    1fr;
            }


            .content-grid {

                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 650px) {

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


            .filter-form {

                grid-template-columns: 1fr;
            }


            .page-header h1 {

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
            PEMILIK
        </div>


        <a
            href="../index.php"
            class="back-link"
        >
            KEMBALI
        </a>

    </div>

</header>


<main>


    <div class="print-only">

        <h2>
            GRIYA SPACE
        </h2>

        <p>
            LAPORAN PENJUALAN
        </p>

    </div>


    <!-- ==================================================
         HEADER
    ================================================== -->

    <section class="page-header">

        <small>
            LAPORAN & ANALISIS
        </small>


        <h1>
            Laporan Penjualan
        </h1>


        <p>
            Pantau penjualan berdasarkan periode transaksi.
        </p>

    </section>


    <!-- ==================================================
         FILTER
    ================================================== -->

    <section class="filter-card">


        <div class="filter-title">
            PERIODE LAPORAN
        </div>


        <form
            action=""
            method="GET"
            class="filter-form"
        >


            <div class="form-group">

                <label for="tanggal_awal">
                    Tanggal Awal
                </label>


                <input
                    type="date"
                    id="tanggal_awal"
                    name="tanggal_awal"
                    value="<?= htmlspecialchars(
                        $tanggal_awal
                    ); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="tanggal_akhir">
                    Tanggal Akhir
                </label>


                <input
                    type="date"
                    id="tanggal_akhir"
                    name="tanggal_akhir"
                    value="<?= htmlspecialchars(
                        $tanggal_akhir
                    ); ?>"
                    required
                >

            </div>


            <button
                type="submit"
                class="button button-primary"
            >
                TAMPILKAN
            </button>


            <button
                type="button"
                class="button button-secondary"
                onclick="window.print();"
            >
                CETAK LAPORAN
            </button>


        </form>


    </section>


    <!-- ==================================================
         SUMMARY
    ================================================== -->

    <section class="summary-grid">


        <div class="summary-card">

            <span class="summary-label">
                TOTAL OMZET
            </span>


            <span class="summary-value">

                Rp <?= number_format(
                    $total_omzet,
                    0,
                    ",",
                    "."
                ); ?>

            </span>


            <div class="summary-note">

                Transaksi selesai pada periode terpilih.

            </div>

        </div>


        <div class="summary-card">

            <span class="summary-label">
                TOTAL TRANSAKSI
            </span>


            <span class="summary-value">
                <?= $total_transaksi; ?>
            </span>


            <div class="summary-note">

                Transaksi dengan status Selesai.

            </div>

        </div>


        <div class="summary-card">

            <span class="summary-label">
                ITEM TERJUAL
            </span>


            <span class="summary-value">
                <?= $total_item; ?>
            </span>


            <div class="summary-note">

                Total kuantitas produk terjual.

            </div>

        </div>


    </section>


    <!-- ==================================================
         TRANSAKSI + TOP PRODUK
    ================================================== -->

    <section class="content-grid">


        <section class="card">


            <div class="card-title">
                DAFTAR TRANSAKSI
            </div>


            <div class="table-container">


                <?php if (
                    !empty(
                        $transaksi_list
                    )
                ): ?>


                    <table>


                        <thead>

                            <tr>

                                <th>
                                    NOMOR
                                </th>

                                <th>
                                    TANGGAL
                                </th>

                                <th>
                                    PELANGGAN
                                </th>

                                <th>
                                    KASIR
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
                                $transaksi_list
                                as $transaksi
                            ): ?>


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


                                    <td>

                                        <?= htmlspecialchars(
                                            $transaksi[
                                                "nama_kasir"
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

                                        <span class="status">

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

                        Tidak ada transaksi selesai
                        pada periode yang dipilih.

                    </div>


                <?php endif; ?>


            </div>


        </section>


        <!-- ==================================================
             TOP PRODUK
        ================================================== -->

        <section class="card">


            <div class="card-title">
                PRODUK TERLARIS
            </div>


            <div class="card-body">


                <?php if (
                    !empty($top_produk)
                ): ?>


                    <?php foreach (
                        $top_produk
                        as $produk
                    ): ?>


                        <div class="product-item">


                            <div>

                                <div class="product-name">

                                    <?= htmlspecialchars(
                                        $produk[
                                            "nama_produk"
                                        ]
                                    ); ?>

                                </div>


                                <div class="product-code">

                                    <?= htmlspecialchars(
                                        $produk[
                                            "kode_produk"
                                        ]
                                    ); ?>

                                </div>

                            </div>


                            <div class="product-value">

                                <?= (int) $produk[
                                    "total_terjual"
                                ]; ?>

                                <?= htmlspecialchars(
                                    $produk["satuan"]
                                ); ?>


                                <div class="product-meta">

                                    Rp <?= number_format(
                                        $produk[
                                            "total_nilai"
                                        ],
                                        0,
                                        ",",
                                        "."
                                    ); ?>

                                </div>

                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty">

                        Belum ada produk terjual
                        pada periode ini.

                    </div>


                <?php endif; ?>


            </div>


        </section>


    </section>


    <!-- ==================================================
         ACTION
    ================================================== -->

    <div class="actions"
         style="
            display:flex;
            justify-content:flex-start;
            margin-top:20px;
         "
    >

        <a
            href="../index.php"
            class="button button-secondary"
        >
            KEMBALI KE DASHBOARD
        </a>

    </div>


</main>


</body>

</html>