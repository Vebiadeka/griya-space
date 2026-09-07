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
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL DATA RIWAYAT TRANSAKSI
// ======================================================

$sql = "
    SELECT
        t.id_transaksi,
        t.nomor_transaksi,
        t.id_pelanggan,
        t.tanggal_transaksi,
        t.subtotal,
        t.diskon,
        t.total_harga,
        t.status,
        t.keterangan,

        p.kode_pelanggan,
        p.nama_pelanggan,

        u.nama_lengkap AS nama_kasir,

        COALESCE(
            pb.metode_pembayaran,
            '-'
        ) AS metode_pembayaran,

        COALESCE(
            pb.status,
            'Belum Lunas'
        ) AS status_pembayaran

    FROM transaksi t

    INNER JOIN pelanggan p
        ON t.id_pelanggan = p.id_pelanggan

    LEFT JOIN users u
        ON t.id_user = u.id_user

    LEFT JOIN pembayaran pb
        ON t.id_transaksi = pb.id_transaksi

    ORDER BY
        t.id_transaksi DESC
";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {
    die(
        "Gagal mengambil riwayat transaksi: " .
        mysqli_error($conn)
    );
}


$jumlah_transaksi =
    mysqli_num_rows($result);


// ======================================================
// HITUNG TOTAL NILAI TRANSAKSI
// ======================================================

$total_nilai = 0;


$data_transaksi = [];


while (
    $transaksi =
    mysqli_fetch_assoc($result)
) {

    $total_nilai +=
        (float) $transaksi["total_harga"];

    $data_transaksi[] =
        $transaksi;
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
        Riwayat Transaksi | Griya Space
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
           HEADER HALAMAN
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


        .summary-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 10px;

            min-width: 360px;
        }


        .summary-card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 15px 18px;
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

            font-size: 21px;

            font-weight: 600;
        }


        /* ==================================================
           FILTER
        ================================================== */

        .filter-card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 16px 20px;

            margin-bottom: 18px;

            color: #777;

            font-size: 12px;
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

            min-width: 1250px;
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

            padding: 15px 14px;

            border-bottom: 1px solid #eeeeee;

            font-size: 12px;

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


        .customer-name {

            font-weight: 600;
        }


        .customer-code {

            color: #999;

            margin-top: 4px;

            font-size: 10px;
        }


        .cashier {

            color: #666;
        }


        .money {

            white-space: nowrap;

            font-weight: 600;
        }


        .method {

            white-space: nowrap;

            color: #555;
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
           PEMBAYARAN STATUS
        ================================================== */

        .payment-status {

            display: inline-block;

            padding: 5px 8px;

            font-size: 9px;

            border: 1px solid #ddd;

            background: #f7f7f5;

            color: #666;

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
           AKSI
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

            padding: 70px 25px;

            text-align: center;

            color: #777;
        }


        .empty h2 {

            color: #1d1d1d;

            font-size: 24px;

            font-weight: 400;
        }


        .empty p {

            margin-top: 10px;

            line-height: 1.5;
        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 850px) {

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


            .summary-grid {

                width: 100%;

                min-width: 0;
            }


            .page-title h1 {

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
                Riwayat Transaksi
            </h1>


            <p>
                Lihat seluruh transaksi penjualan dan pembayaran Griya Space.
            </p>

        </div>


        <div class="summary-grid">


            <div class="summary-card">

                <span class="summary-label">
                    TOTAL TRANSAKSI
                </span>


                <span class="summary-value">
                    <?= $jumlah_transaksi; ?>
                </span>

            </div>


            <div class="summary-card">

                <span class="summary-label">
                    TOTAL PENJUALAN
                </span>


                <span class="summary-value">

                    Rp <?= number_format(
                        $total_nilai,
                        0,
                        ",",
                        "."
                    ); ?>

                </span>

            </div>


        </div>


    </section>


    <!-- ==================================================
         INFO
    ================================================== -->

    <section class="filter-card">

        Menampilkan
        <?= $jumlah_transaksi; ?>
        transaksi yang tercatat dalam sistem.

    </section>


    <!-- ==================================================
         TABLE
    ================================================== -->

    <?php if (
        !empty($data_transaksi)
    ): ?>


        <section class="table-container">


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
                            KASIR
                        </th>

                        <th>
                            METODE
                        </th>

                        <th>
                            TOTAL
                        </th>

                        <th>
                            PEMBAYARAN
                        </th>

                        <th>
                            STATUS
                        </th>

                        <th>
                            AKSI
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach (
                        $data_transaksi
                        as $transaksi
                    ): ?>


                        <?php

                        // ----------------------------------
                        // CLASS STATUS TRANSAKSI
                        // ----------------------------------

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


                        // ----------------------------------
                        // CLASS STATUS PEMBAYARAN
                        // ----------------------------------

                        $paymentClass =
                            $transaksi[
                                "status_pembayaran"
                            ] === "Lunas"

                                ? "payment-lunas"

                                : "payment-belum";

                        ?>


                        <tr>


                            <!-- NOMOR -->

                            <td class="transaction-number">

                                <?= htmlspecialchars(
                                    $transaksi[
                                        "nomor_transaksi"
                                    ]
                                ); ?>

                            </td>


                            <!-- TANGGAL -->

                            <td class="transaction-date">

                                <?= htmlspecialchars(
                                    $transaksi[
                                        "tanggal_transaksi"
                                    ]
                                ); ?>

                            </td>


                            <!-- PELANGGAN -->

                            <td>

                                <div class="customer-name">

                                    <?= htmlspecialchars(
                                        $transaksi[
                                            "nama_pelanggan"
                                        ]
                                    ); ?>

                                </div>


                                <div class="customer-code">

                                    <?= htmlspecialchars(
                                        $transaksi[
                                            "kode_pelanggan"
                                        ]
                                    ); ?>

                                </div>

                            </td>


                            <!-- KASIR -->

                            <td class="cashier">

                                <?= htmlspecialchars(
                                    $transaksi[
                                        "nama_kasir"
                                    ] ?? "-"
                                ); ?>

                            </td>


                            <!-- METODE -->

                            <td class="method">

                                <?= htmlspecialchars(
                                    $transaksi[
                                        "metode_pembayaran"
                                    ]
                                ); ?>

                            </td>


                            <!-- TOTAL -->

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


                            <!-- PEMBAYARAN -->

                            <td>

                                <span
                                    class="payment-status <?= $paymentClass; ?>"
                                >

                                    <?= htmlspecialchars(
                                        $transaksi[
                                            "status_pembayaran"
                                        ]
                                    ); ?>

                                </span>

                            </td>


                            <!-- STATUS -->

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


                            <!-- AKSI -->

                            <td>

                                <a
                                    href="detail.php?id=<?= (int) $transaksi["id_transaksi"]; ?>"
                                    class="action-button"
                                >
                                    DETAIL
                                </a>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>


            </table>


        </section>


    <?php else: ?>


        <section class="table-container">


            <div class="empty">

                <h2>
                    Belum Ada Transaksi
                </h2>


                <p>
                    Belum ada transaksi penjualan yang tercatat.
                </p>

            </div>


        </section>


    <?php endif; ?>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>