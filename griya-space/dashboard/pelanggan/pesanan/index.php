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
// DATA SESSION
// ======================================================

$id_user = (int) $_SESSION["user_id"];


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
// AMBIL DATA PESANAN
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
    WHERE id_pelanggan = ?
    ORDER BY id_pesanan DESC
");


if (!$stmt) {
    die(
        "Gagal mengambil data pesanan: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_pelanggan
);


$stmt->execute();


$result =
    $stmt->get_result();


$jumlah_pesanan =
    $result->num_rows;


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
        Pesanan Saya | Griya Space
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

            background: #1d1d1d;

            color: white;

            min-height: 76px;

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

            min-width: 900px;
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


        .total {

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
           DETAIL BUTTON
        ================================================== */

        .detail-button {

            display: inline-block;

            padding: 8px 12px;

            border: 1px solid #d8d8d5;

            background: #f5f5f3;

            color: #333;

            text-decoration: none;

            font-size: 10px;

            letter-spacing: .8px;

            white-space: nowrap;

            border-radius: 4px;
        }


        .detail-button:hover {

            background: #1d1d1d;

            color: white;

            border-color: #1d1d1d;
        }


        /* ==================================================
           EMPTY
        ================================================== */

        .empty {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 70px 25px;

            text-align: center;
        }


        .empty h2 {

            font-size: 24px;

            font-weight: 400;
        }


        .empty p {

            margin-top: 10px;

            color: #777;

            line-height: 1.5;
        }


        .catalog-button {

            display: inline-block;

            margin-top: 25px;

            background: #1d1d1d;

            color: white;

            text-decoration: none;

            padding: 13px 22px;

            font-size: 11px;

            letter-spacing: 1px;
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

                align-items: flex-start;

                flex-direction: column;
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
            PELANGGAN
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


    <section class="page-header">


        <div class="page-title">

            <small>
                RIWAYAT TRANSAKSI
            </small>


            <h1>
                Pesanan Saya
            </h1>


            <p>
                Lihat status dan riwayat pesanan Anda di Griya Space.
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
                            SUBTOTAL
                        </th>

                        <th>
                            DISKON
                        </th>

                        <th>
                            TOTAL
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


                    <?php while (
                        $pesanan =
                        $result->fetch_assoc()
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


                            <td>

                                Rp <?= number_format(
                                    $pesanan["subtotal"],
                                    0,
                                    ",",
                                    "."
                                ); ?>

                            </td>


                            <td>

                                Rp <?= number_format(
                                    $pesanan["diskon"],
                                    0,
                                    ",",
                                    "."
                                ); ?>

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


                            <td>

                                <a
                                    href="detail.php?id=<?= (int) $pesanan["id_pesanan"]; ?>"
                                    class="detail-button"
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
                Anda belum memiliki riwayat pesanan.
            </p>


            <a
                href="../katalog/index.php"
                class="catalog-button"
            >
                LIHAT KATALOG
            </a>


        </section>


    <?php endif; ?>


</main>


</body>

</html>


<?php

$stmt->close();

$conn->close();

?>