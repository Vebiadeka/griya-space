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
// CEK ROLE KASIR
// ======================================================

if ($_SESSION["role"] !== "kasir") {
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL ID TRANSAKSI
// ======================================================

$id_transaksi = (int) ($_GET["id"] ?? 0);

if ($id_transaksi <= 0) {
    header("Location: index.php");
    exit;
}


// ======================================================
// AMBIL DATA TRANSAKSI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        t.id_transaksi,
        t.nomor_transaksi,
        t.id_pelanggan,
        t.id_user,
        t.tanggal_transaksi,
        t.subtotal,
        t.diskon,
        t.total_harga,
        t.status,
        t.keterangan,

        p.kode_pelanggan,
        p.nama_pelanggan,
        p.no_telepon,
        p.email,
        p.alamat,

        u.nama_lengkap AS nama_kasir

    FROM transaksi t

    INNER JOIN pelanggan p
        ON t.id_pelanggan = p.id_pelanggan

    LEFT JOIN users u
        ON t.id_user = u.id_user

    WHERE t.id_transaksi = ?

    LIMIT 1
");


if (!$stmt) {
    die(
        "Gagal menyiapkan data transaksi: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_transaksi
);


$stmt->execute();


$result =
    $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit;
}


$transaksi =
    $result->fetch_assoc();


$stmt->close();


// ======================================================
// AMBIL DATA PEMBAYARAN
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_pembayaran,
        metode_pembayaran,
        jumlah_bayar,
        uang_diterima,
        kembalian,
        status,
        tanggal_pembayaran
    FROM pembayaran
    WHERE id_transaksi = ?
    ORDER BY id_pembayaran DESC
    LIMIT 1
");


if (!$stmt) {
    die(
        "Gagal menyiapkan data pembayaran: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_transaksi
);


$stmt->execute();


$resultPembayaran =
    $stmt->get_result();


$pembayaran = null;


if (
    $resultPembayaran->num_rows === 1
) {

    $pembayaran =
        $resultPembayaran->fetch_assoc();
}


$stmt->close();


// ======================================================
// AMBIL DETAIL TRANSAKSI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        dt.id_detail_transaksi,
        dt.id_produk,
        dt.jumlah,
        dt.harga_satuan,
        dt.diskon,
        dt.subtotal,

        pr.kode_produk,
        pr.nama_produk,
        pr.satuan

    FROM detail_transaksi dt

    INNER JOIN produk pr
        ON dt.id_produk = pr.id_produk

    WHERE dt.id_transaksi = ?

    ORDER BY dt.id_detail_transaksi ASC
");


if (!$stmt) {
    die(
        "Gagal menyiapkan detail transaksi: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_transaksi
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
// STATUS TRANSAKSI
// ======================================================

$statusClass =
    "status-default";


switch (
    $transaksi["status"]
) {

    case "Selesai":

        $statusClass =
            "status-selesai";

        break;


    case "Diproses":

        $statusClass =
            "status-diproses";

        break;


    case "Menunggu":

        $statusClass =
            "status-menunggu";

        break;


    case "Dibatalkan":

        $statusClass =
            "status-batal";

        break;

}


// ======================================================
// STATUS PEMBAYARAN
// ======================================================

$paymentStatusClass =
    "payment-belum";


if (
    $pembayaran &&
    $pembayaran["status"] === "Lunas"
) {
    $paymentStatusClass =
        "payment-lunas";
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
        Detail Transaksi | Griya Space
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

            max-width: 1150px;

            margin: 0 auto;

            padding: 45px 30px;
        }


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

            align-items: flex-start;

            gap: 20px;

            padding: 10px 0;

            border-bottom: 1px solid #eeeeee;

            font-size: 12px;
        }


        .info-row:first-child {

            padding-top: 0;
        }


        .info-row:last-child {

            padding-bottom: 0;

            border-bottom: none;
        }


        .label {

            color: #999;

            font-size: 10px;

            letter-spacing: 1px;
        }


        .value {

            text-align: right;

            font-weight: 600;

            line-height: 1.5;
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


        .status-selesai {

            background: #edf5f3;

            color: #347166;
        }


        .status-diproses {

            background: #eef2f6;

            color: #44627d;
        }


        .status-menunggu {

            background: #f4f0e7;

            color: #8a6d2f;
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
           CUSTOMER
        ================================================== */

        .customer-name {

            font-size: 16px;

            font-weight: 600;

            margin-bottom: 5px;
        }


        .customer-code {

            color: #999;

            font-size: 10px;

            margin-bottom: 12px;
        }


        .customer-contact {

            color: #666;

            font-size: 12px;

            line-height: 1.7;
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

            min-width: 750px;
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

            margin-top: 4px;

            color: #999;

            font-size: 10px;
        }


        .number {

            text-align: right;

            white-space: nowrap;
        }


        /* ==================================================
           PAYMENT
        ================================================== */

        .payment-status {

            display: inline-block;

            padding: 6px 10px;

            font-size: 9px;

            border: 1px solid #ddd;

            border-radius: 4px;

            white-space: nowrap;
        }


        .payment-lunas {

            color: #3d6c43;

            background: #eef5ef;

            border-color: #d8e8da;
        }


        .payment-belum {

            color: #8a6d2f;

            background: #f8f3e9;

            border-color: #eadfc7;
        }


        /* ==================================================
           BOTTOM GRID
        ================================================== */

        .bottom-grid {

            display: grid;

            grid-template-columns:
                1fr
                360px;

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

            color: #666;

            font-size: 12px;
        }


        .summary-row.total {

            margin-top: 8px;

            padding-top: 17px;

            border-top: 1px solid #ddd;

            color: #1d1d1d;

            font-size: 18px;

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

        .empty {

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


            .info-grid,
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
            KASIR
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


    <!-- ==================================================
         HEADER
    ================================================== -->

    <section class="page-header">

        <small>
            MANAJEMEN PENJUALAN
        </small>


        <h1>
            Detail Transaksi
        </h1>


        <p>
            Informasi lengkap transaksi penjualan dan pembayaran.
        </p>

    </section>


    <!-- ==================================================
         INFORMASI UTAMA
    ================================================== -->

    <section class="info-grid">


        <!-- TRANSAKSI -->

        <div class="card">

            <div class="card-title">
                INFORMASI TRANSAKSI
            </div>


            <div class="card-body">


                <div class="info-row">

                    <span class="label">
                        NOMOR TRANSAKSI
                    </span>


                    <span class="value">

                        <?= htmlspecialchars(
                            $transaksi["nomor_transaksi"]
                        ); ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="label">
                        TANGGAL
                    </span>


                    <span class="value">

                        <?= htmlspecialchars(
                            $transaksi["tanggal_transaksi"]
                        ); ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="label">
                        KASIR
                    </span>


                    <span class="value">

                        <?= htmlspecialchars(
                            $transaksi["nama_kasir"]
                            ?? "-"
                        ); ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="label">
                        STATUS
                    </span>


                    <span class="value">

                        <span
                            class="status <?= $statusClass; ?>"
                        >

                            <?= htmlspecialchars(
                                $transaksi["status"]
                            ); ?>

                        </span>

                    </span>

                </div>


            </div>

        </div>


        <!-- PELANGGAN -->

        <div class="card">

            <div class="card-title">
                DATA PELANGGAN
            </div>


            <div class="card-body">


                <div class="customer-name">

                    <?= htmlspecialchars(
                        $transaksi["nama_pelanggan"]
                    ); ?>

                </div>


                <div class="customer-code">

                    <?= htmlspecialchars(
                        $transaksi["kode_pelanggan"]
                    ); ?>

                </div>


                <div class="customer-contact">

                    Telepon:
                    <?= htmlspecialchars(
                        $transaksi["no_telepon"]
                        ?? "-"
                    ); ?>

                    <br>

                    Email:
                    <?= htmlspecialchars(
                        $transaksi["email"]
                        ?? "-"
                    ); ?>

                    <br>

                    Alamat:
                    <?= nl2br(
                        htmlspecialchars(
                            $transaksi["alamat"]
                            ?? "-"
                        )
                    ); ?>

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
                                DISKON
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
                                        $detail["diskon"],
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


            <div class="empty">

                Tidak ada detail produk pada transaksi ini.

            </div>


        <?php endif; ?>


    </section>


    <!-- ==================================================
         PEMBAYARAN + RINGKASAN
    ================================================== -->

    <div class="bottom-grid">


        <!-- PEMBAYARAN -->

        <section class="card">


            <div class="card-title">
                INFORMASI PEMBAYARAN
            </div>


            <div class="card-body">


                <?php if ($pembayaran): ?>


                    <div class="info-row">

                        <span class="label">
                            METODE PEMBAYARAN
                        </span>


                        <span class="value">

                            <?= htmlspecialchars(
                                $pembayaran[
                                    "metode_pembayaran"
                                ]
                            ); ?>

                        </span>

                    </div>


                    <div class="info-row">

                        <span class="label">
                            JUMLAH BAYAR
                        </span>


                        <span class="value">

                            Rp <?= number_format(
                                $pembayaran[
                                    "jumlah_bayar"
                                ],
                                0,
                                ",",
                                "."
                            ); ?>

                        </span>

                    </div>


                    <?php if (
                        $pembayaran[
                            "metode_pembayaran"
                        ] === "Cash"
                    ): ?>


                        <div class="info-row">

                            <span class="label">
                                UANG DITERIMA
                            </span>


                            <span class="value">

                                Rp <?= number_format(
                                    $pembayaran[
                                        "uang_diterima"
                                    ],
                                    0,
                                    ",",
                                    "."
                                ); ?>

                            </span>

                        </div>


                        <div class="info-row">

                            <span class="label">
                                KEMBALIAN
                            </span>


                            <span class="value">

                                Rp <?= number_format(
                                    $pembayaran[
                                        "kembalian"
                                    ],
                                    0,
                                    ",",
                                    "."
                                ); ?>

                            </span>

                        </div>


                    <?php endif; ?>


                    <div class="info-row">

                        <span class="label">
                            STATUS PEMBAYARAN
                        </span>


                        <span class="value">

                            <span
                                class="payment-status <?= $paymentStatusClass; ?>"
                            >

                                <?= htmlspecialchars(
                                    $pembayaran[
                                        "status"
                                    ]
                                ); ?>

                            </span>

                        </span>

                    </div>


                    <div class="info-row">

                        <span class="label">
                            TANGGAL PEMBAYARAN
                        </span>


                        <span class="value">

                            <?= htmlspecialchars(
                                $pembayaran[
                                    "tanggal_pembayaran"
                                ]
                            ); ?>

                        </span>

                    </div>


                <?php else: ?>


                    <div class="note">

                        Belum ada data pembayaran
                        untuk transaksi ini.

                    </div>


                <?php endif; ?>


            </div>


        </section>


        <!-- RINGKASAN -->

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
                            $transaksi[
                                "subtotal"
                            ],
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
                            $transaksi[
                                "diskon"
                            ],
                            0,
                            ",",
                            "."
                        ); ?>

                    </span>

                </div>


                <div class="summary-row total">

                    <span>
                        TOTAL
                    </span>


                    <span class="summary-value">

                        Rp <?= number_format(
                            $transaksi[
                                "total_harga"
                            ],
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
         KETERANGAN
    ================================================== -->

    <section
        class="card"
        style="margin-top:20px;"
    >


        <div class="card-title">
            KETERANGAN
        </div>


        <div class="card-body">


            <div class="note">

                <?php if (
                    trim(
                        $transaksi["keterangan"]
                        ?? ""
                    ) !== ""
                ): ?>

                    <?= nl2br(
                        htmlspecialchars(
                            $transaksi["keterangan"]
                        )
                    ); ?>

                <?php else: ?>

                    Tidak ada keterangan.

                <?php endif; ?>

            </div>


        </div>


    </section>


    <!-- ==================================================
         ACTION
    ================================================== -->

    <div class="actions">


        <a
            href="index.php"
            class="button button-secondary"
        >
            KEMBALI KE RIWAYAT
        </a>


        <a
            href="../pesanan/index.php"
            class="button button-primary"
        >
            LIHAT PESANAN MASUK
        </a>


    </div>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>