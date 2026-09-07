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
// AMBIL ID PESANAN
// ======================================================

$id_pesanan = (int) ($_GET["id"] ?? 0);

if ($id_pesanan <= 0) {
    header("Location: index.php");
    exit;
}


// ======================================================
// AMBIL DATA PESANAN
// ======================================================

$stmt = $conn->prepare("
    SELECT
        p.id_pesanan,
        p.nomor_pesanan,
        p.id_pelanggan,
        p.tanggal_pesanan,
        p.subtotal,
        p.diskon,
        p.total_harga,
        p.status,
        p.catatan,

        pl.kode_pelanggan,
        pl.nama_pelanggan,
        pl.no_telepon,
        pl.email,
        pl.alamat

    FROM pesanan p

    INNER JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan

    WHERE p.id_pesanan = ?

    LIMIT 1
");

if (!$stmt) {
    die(
        "Gagal mengambil data pesanan: " .
        $conn->error
    );
}

$stmt->bind_param(
    "i",
    $id_pesanan
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

$pesanan =
    $result->fetch_assoc();

$stmt->close();


// ======================================================
// AMBIL DETAIL PRODUK
// ======================================================

$stmt = $conn->prepare("
    SELECT
        dp.id_detail_pesanan,
        dp.id_produk,
        dp.jumlah,
        dp.harga_satuan,
        dp.subtotal,

        pr.kode_produk,
        pr.nama_produk,
        pr.satuan,
        pr.stok

    FROM detail_pesanan dp

    INNER JOIN produk pr
        ON dp.id_produk = pr.id_produk

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
        }


        .back-link:hover {

            background: white;

            color: #1d1d1d;
        }


        /* ==================================================
           MAIN
        ================================================== */

        main {

            max-width: 1100px;

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
           GRID
        ================================================== */

        .top-grid {

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


        .label {

            color: #999;

            font-size: 10px;

            letter-spacing: 1px;
        }


        .value {

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

            margin-bottom: 15px;
        }


        .customer-info {

            color: #666;

            font-size: 12px;

            line-height: 1.6;
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


        .product-name {

            font-weight: 600;
        }


        .product-code {

            margin-top: 4px;

            color: #999;

            font-size: 10px;
        }


        .stock {

            color: #666;

            white-space: nowrap;
        }


        .number {

            white-space: nowrap;

            text-align: right;
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
        }


        .note {

            color: #777;

            line-height: 1.6;

            font-size: 12px;
        }


        .summary-row {

            display: flex;

            justify-content: space-between;

            gap: 15px;

            padding: 10px 0;

            color: #666;

            font-size: 12px;
        }


        .summary-row.total {

            margin-top: 8px;

            padding-top: 17px;

            border-top: 1px solid #ddd;

            font-size: 17px;

            font-weight: 700;

            color: #1d1d1d;
        }


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


            .top-grid,
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


    <section class="page-header">

        <small>
            MANAJEMEN PENJUALAN
        </small>


        <h1>
            Detail Pesanan
        </h1>


        <p>
            Periksa rincian pesanan pelanggan sebelum memproses transaksi.
        </p>

    </section>


    <!-- ==================================================
         INFORMASI UTAMA
    ================================================== -->

    <div class="top-grid">


        <section class="card">

            <div class="card-title">
                INFORMASI PESANAN
            </div>


            <div class="card-body">


                <div class="info-row">

                    <span class="label">
                        NOMOR PESANAN
                    </span>


                    <span class="value">

                        <?= htmlspecialchars(
                            $pesanan["nomor_pesanan"]
                        ); ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="label">
                        TANGGAL
                    </span>


                    <span class="value">

                        <?= htmlspecialchars(
                            $pesanan["tanggal_pesanan"]
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
                                $pesanan["status"]
                            ); ?>

                        </span>

                    </span>

                </div>


            </div>

        </section>


        <section class="card">

            <div class="card-title">
                DATA PELANGGAN
            </div>


            <div class="card-body">


                <div class="customer-name">

                    <?= htmlspecialchars(
                        $pesanan["nama_pelanggan"]
                    ); ?>

                </div>


                <div class="customer-code">

                    <?= htmlspecialchars(
                        $pesanan["kode_pelanggan"]
                    ); ?>

                </div>


                <div class="customer-info">

                    Telepon:
                    <?= htmlspecialchars(
                        $pesanan["no_telepon"] ?? "-"
                    ); ?>

                    <br>

                    Email:
                    <?= htmlspecialchars(
                        $pesanan["email"] ?? "-"
                    ); ?>

                    <br>

                    Alamat:
                    <?= nl2br(
                        htmlspecialchars(
                            $pesanan["alamat"] ?? "-"
                        )
                    ); ?>

                </div>


            </div>

        </section>


    </div>


    <!-- ==================================================
         DETAIL PRODUK
    ================================================== -->

    <section class="card">


        <div class="card-title">
            DETAIL PRODUK
        </div>


        <div class="card-body">


            <div class="table-container">


                <?php if (!empty($detail_items)): ?>


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
                                    STOK SAAT INI
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


                                    <td class="stock">

                                        <?= (int) $detail["stok"]; ?>

                                        <?= htmlspecialchars(
                                            $detail["satuan"]
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


                <?php else: ?>


                    <div class="note">

                        Tidak ada detail produk pada pesanan ini.

                    </div>


                <?php endif; ?>


            </div>


        </div>


    </section>


    <!-- ==================================================
         RINGKASAN
    ================================================== -->

    <div class="bottom-grid">


        <section class="card">

            <div class="card-title">
                CATATAN
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

                        Tidak ada catatan.

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


                    <span>

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


                    <span>

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
                        TOTAL
                    </span>


                    <span>

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


    <div class="actions">


        <a
            href="index.php"
            class="button button-secondary"
        >
            KEMBALI KE PESANAN
        </a>


        <a
    href="../transaksi/proses.php?id=<?= (int) $pesanan["id_pesanan"]; ?>"
    class="button button-primary"
>
    PROSES TRANSAKSI
</a>

    </div>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>