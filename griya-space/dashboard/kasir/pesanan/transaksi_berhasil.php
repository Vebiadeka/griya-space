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
// CEK DATA TRANSAKSI
// ======================================================

if (!isset($_SESSION["transaksi_berhasil"])) {
    header("Location: index.php");
    exit;
}


$transaksi =
    $_SESSION["transaksi_berhasil"];


// Ambil data sekali lalu hapus dari session
unset($_SESSION["transaksi_berhasil"]);


// ======================================================
// FORMAT DATA
// ======================================================

$id_transaksi =
    (int) $transaksi["id_transaksi"];

$nomor_transaksi =
    $transaksi["nomor_transaksi"];

$id_pesanan =
    (int) $transaksi["id_pesanan"];

$total_harga =
    (float) $transaksi["total_harga"];

$metode_pembayaran =
    $transaksi["metode_pembayaran"];

$uang_diterima =
    (float) $transaksi["uang_diterima"];

$kembalian =
    (float) $transaksi["kembalian"];

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
        Transaksi Berhasil | Griya Space
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

            min-height: 100vh;

            display: flex;

            flex-direction: column;
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

            flex: 1;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 50px 20px;
        }


        .success-card {

            width: 100%;

            max-width: 680px;

            background: white;

            border: 1px solid #e4e4e4;

            padding: 45px;
        }


        /* ==================================================
           ICON
        ================================================== */

        .success-icon {

            width: 70px;

            height: 70px;

            border: 1px solid #333;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 24px;

            font-size: 28px;

            font-weight: 600;
        }


        .eyebrow {

            text-align: center;

            color: #777;

            font-size: 10px;

            letter-spacing: 3px;

            margin-bottom: 10px;
        }


        h1 {

            text-align: center;

            font-size: 30px;

            font-weight: 500;
        }


        .description {

            margin-top: 10px;

            text-align: center;

            color: #777;

            font-size: 13px;

            line-height: 1.6;
        }


        /* ==================================================
           INFO
        ================================================== */

        .info-box {

            margin-top: 30px;

            border: 1px solid #e4e4e4;
        }


        .info-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 15px 18px;

            border-bottom: 1px solid #eeeeee;

            font-size: 12px;
        }


        .info-row:last-child {

            border-bottom: none;
        }


        .label {

            color: #888;

            font-size: 10px;

            letter-spacing: 1px;
        }


        .value {

            font-weight: 600;

            text-align: right;
        }


        .total-row {

            background: #f7f7f5;

        }


        .total-row .value {

            font-size: 17px;
        }


        /* ==================================================
           ACTION
        ================================================== */

        .actions {

            margin-top: 30px;

            display: flex;

            justify-content: center;

            gap: 10px;

            flex-wrap: wrap;
        }


        .button {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 180px;

            padding: 13px 18px;

            text-decoration: none;

            font-size: 11px;

            letter-spacing: 1px;
        }


        .button-primary {

            background: #1d1d1d;

            color: white;
        }


        .button-primary:hover {

            background: #333;
        }


        .button-secondary {

            background: #f2f2f0;

            color: #555;

            border: 1px solid #ddd;
        }


        /* ==================================================
           NOTE
        ================================================== */

        .note {

            margin-top: 20px;

            text-align: center;

            color: #999;

            font-size: 10px;

            line-height: 1.6;
        }


        /* ==================================================
           MOBILE
        ================================================== */

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


            .success-card {

                padding: 30px 20px;
            }


            .info-row {

                flex-direction: column;

                gap: 5px;
            }


            .value {

                text-align: left;
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


    <section class="success-card">


        <div class="success-icon">
            ✓
        </div>


        <div class="eyebrow">
            TRANSAKSI BERHASIL
        </div>


        <h1>
            Pembayaran Dikonfirmasi
        </h1>


        <p class="description">
            Pembayaran pelanggan berhasil diproses.
            Stok dan mutasi stok juga telah diperbarui.
        </p>


        <div class="info-box">


            <div class="info-row">

                <span class="label">
                    NOMOR TRANSAKSI
                </span>


                <span class="value">

                    <?= htmlspecialchars(
                        $nomor_transaksi
                    ); ?>

                </span>

            </div>


            <div class="info-row">

                <span class="label">
                    ID PESANAN
                </span>


                <span class="value">

                    #<?= $id_pesanan; ?>

                </span>

            </div>


            <div class="info-row">

                <span class="label">
                    ID TRANSAKSI
                </span>


                <span class="value">

                    #<?= $id_transaksi; ?>

                </span>

            </div>


            <div class="info-row">

                <span class="label">
                    METODE PEMBAYARAN
                </span>


                <span class="value">

                    <?= htmlspecialchars(
                        $metode_pembayaran
                    ); ?>

                </span>

            </div>


            <div class="info-row">

                <span class="label">
                    TOTAL PEMBAYARAN
                </span>


                <span class="value">

                    Rp <?= number_format(
                        $total_harga,
                        0,
                        ",",
                        "."
                    ); ?>

                </span>

            </div>


            <?php if (
                $metode_pembayaran === "Cash"
            ): ?>


                <div class="info-row">

                    <span class="label">
                        UANG DITERIMA
                    </span>


                    <span class="value">

                        Rp <?= number_format(
                            $uang_diterima,
                            0,
                            ",",
                            "."
                        ); ?>

                    </span>

                </div>


                <div class="info-row total-row">

                    <span class="label">
                        KEMBALIAN
                    </span>


                    <span class="value">

                        Rp <?= number_format(
                            $kembalian,
                            0,
                            ",",
                            "."
                        ); ?>

                    </span>

                </div>


            <?php endif; ?>


        </div>


        <div class="actions">


            <a
                href="index.php"
                class="button button-primary"
            >
                KEMBALI KE PESANAN
            </a>


            <a
                href="../index.php"
                class="button button-secondary"
            >
                KEMBALI KE DASHBOARD
            </a>


        </div>


        <div class="note">

            Pesanan pelanggan telah berstatus
            <strong>Selesai</strong> dan stok produk telah dikurangi
            sesuai jumlah penjualan.

        </div>


    </section>


</main>


</body>

</html>