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

if (
    $_SESSION["role"] !== "kasir" &&
    $_SESSION["role"] !== "admin"
) {
    header("Location: ../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL DATA PESANAN
// ======================================================

$sql = "
    SELECT
        p.id_pesanan,
        p.nomor_pesanan,
        p.tanggal_pesanan,
        p.subtotal,
        p.diskon,
        p.total_harga,
        p.status,
        p.catatan,

        pl.kode_pelanggan,
        pl.nama_pelanggan,
        pl.no_telepon

    FROM pesanan p

    INNER JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan

    ORDER BY
        p.id_pesanan DESC
";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {
    die(
        "Gagal mengambil data pesanan: " .
        mysqli_error($conn)
    );
}


$jumlah_pesanan =
    mysqli_num_rows($result);

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
        Pesanan Masuk | Griya Space
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

            max-width: 1350px;

            margin: 0 auto;

            padding: 45px 30px;
        }


        /* ==================================================
           PAGE HEADER
        ================================================== */

        .page-header {

            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 30px;
        }


        .page-title small {

            display: block;

            color: #777;

            font-size: 10px;

            letter-spacing: 3px;

            margin-bottom: 9px;
        }


        .page-title h1 {

            font-size: 32px;

            font-weight: 500;
        }


        .page-title p {

            margin-top: 9px;

            color: #777;

            font-size: 13px;
        }


        .summary {

            min-width: 170px;

            background: white;

            border: 1px solid #e4e4e4;

            padding: 15px 20px;
        }


        .summary-label {

            display: block;

            color: #888;

            font-size: 10px;

            letter-spacing: 1.5px;
        }


        .summary-value {

            display: block;

            margin-top: 6px;

            font-size: 24px;

            font-weight: 600;
        }


        /* ==================================================
           TABLE
        ================================================== */

        .table-container {

            background: white;

            border: 1px solid #e4e4e4;

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 1150px;
        }


        thead {

            background: #1d1d1d;

            color: white;
        }


        th {

            padding: 15px 14px;

            text-align: left;

            font-size: 10px;

            font-weight: 600;

            letter-spacing: 1px;

            white-space: nowrap;
        }


        td {

            padding: 16px 14px;

            border-bottom: 1px solid #eeeeee;

            font-size: 12px;

            vertical-align: middle;
        }


        tbody tr:hover {

            background: #fafafa;
        }


        .order-number {

            font-weight: 600;

            white-space: nowrap;
        }


        .order-date {

            color: #666;

            white-space: nowrap;
        }


        .customer {

            min-width: 180px;
        }


        .customer-name {

            font-weight: 600;
        }


        .customer-code {

            margin-top: 4px;

            color: #999;

            font-size: 10px;
        }


        .phone {

            margin-top: 4px;

            color: #777;

            font-size: 10px;
        }


        .total {

            white-space: nowrap;

            font-weight: 600;
        }


        .note {

            max-width: 220px;

            color: #777;

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
           ACTION
        ================================================== */

        .action-button {

            display: inline-block;

            padding: 8px 12px;

            background: #1d1d1d;

            color: white;

            text-decoration: none;

            font-size: 10px;

            letter-spacing: .8px;

            border-radius: 4px;

            white-space: nowrap;
        }


        .action-button:hover {

            background: #333;
        }


        /* ==================================================
           EMPTY
        ================================================== */

        .empty {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 70px 25px;

            text-align: center;

            color: #777;
        }


        .empty h2 {

            font-size: 24px;

            font-weight: 400;

            color: #1d1d1d;
        }


        .empty p {

            margin-top: 10px;

            line-height: 1.5;
        }


        /* ==================================================
           MOBILE
        ================================================== */

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


            .page-header {

                flex-direction: column;

                align-items: flex-start;
            }


            .page-title h1 {

                font-size: 28px;
            }


            .summary {

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
            href="../index.php"
            class="back-link"
        >
            KEMBALI
        </a>

    </div>

</header>


<main>


    <!-- ==================================================
         PAGE HEADER
    ================================================== -->

    <section class="page-header">


        <div class="page-title">

            <small>
                MANAJEMEN PENJUALAN
            </small>


            <h1>
                Pesanan Masuk
            </h1>


            <p>
                Kelola pesanan pelanggan dan proses transaksi pembayaran.
            </p>

        </div>


        <div class="summary">

            <span class="summary-label">
                TOTAL PESANAN
            </span>


            <span class="summary-value">
                <?= $jumlah_pesanan; ?>
            </span>

        </div>


    </section>


    <!-- ==================================================
         TABLE
    ================================================== -->

    <?php if ($jumlah_pesanan > 0): ?>


        <section class="table-container">


            <table>


                <thead>

                    <tr>

                        <th>
                            NOMOR PESANAN
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

                        <th>
                            CATATAN
                        </th>

                        <th>
                            AKSI
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php while (
                        $pesanan =
                        mysqli_fetch_assoc($result)
                    ): ?>


                        <?php

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


                        <tr>


                            <td class="order-number">

                                <?= htmlspecialchars(
                                    $pesanan["nomor_pesanan"]
                                ); ?>

                            </td>


                            <td class="order-date">

                                <?= htmlspecialchars(
                                    $pesanan["tanggal_pesanan"]
                                ); ?>

                            </td>


                            <td class="customer">

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


                                <div class="phone">

                                    <?= htmlspecialchars(
                                        $pesanan["no_telepon"] ?? "-"
                                    ); ?>

                                </div>

                            </td>


                            <td class="total">

                                Rp <?= number_format(
                                    $pesanan["total_harga"],
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
                                        $pesanan["status"]
                                    ); ?>

                                </span>

                            </td>


                            <td class="note">

                                <?= htmlspecialchars(
                                    $pesanan["catatan"]
                                    ?? "-"
                                ); ?>

                            </td>


                            <td>

                                <a
                                    href="detail.php?id=<?= (int) $pesanan["id_pesanan"]; ?>"
                                    class="action-button"
                                >
                                    DETAIL
                                </a>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                </tbody>


            </table>


        </section>


    <?php else: ?>


        <section class="empty">


            <h2>
                Belum Ada Pesanan
            </h2>


            <p>
                Belum ada pesanan pelanggan yang masuk.
            </p>


        </section>


    <?php endif; ?>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>