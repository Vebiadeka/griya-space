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
// CEK DATA PESANAN
// ======================================================

if (!isset($_SESSION["pesanan_berhasil"])) {
    header("Location: ../keranjang/index.php");
    exit;
}


$pesanan =
    $_SESSION["pesanan_berhasil"];


// Ambil data lalu hapus dari session
// agar halaman ini tidak terus muncul
// sebagai hasil transaksi yang sama.

unset($_SESSION["pesanan_berhasil"]);


// ======================================================
// FORMAT DATA
// ======================================================

$nomor_pesanan =
    $pesanan["nomor_pesanan"];

$total_harga =
    (float) $pesanan["total_harga"];

$jumlah_item =
    (int) $pesanan["jumlah_item"];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pesanan Berhasil | Griya Space</title>


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

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 18px 45px;
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

            font-size: 12px;

            border: 1px solid #555;

            padding: 9px 16px;

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

            flex: 1;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 50px 20px;
        }


        .success-card {

            width: 100%;

            max-width: 650px;

            background: white;

            border: 1px solid #e4e4e4;

            padding: 50px 45px;

            text-align: center;
        }


        /* ==================================================
           ICON
        ================================================== */

        .success-icon {

            width: 70px;

            height: 70px;

            margin: 0 auto 25px;

            border: 1px solid #333;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 28px;
        }


        /* ==================================================
           TEXT
        ================================================== */

        .eyebrow {

            color: #777;

            font-size: 10px;

            letter-spacing: 3px;

            margin-bottom: 10px;
        }


        h1 {

            font-size: 32px;

            font-weight: 500;
        }


        .description {

            margin-top: 12px;

            color: #777;

            font-size: 13px;

            line-height: 1.6;
        }


        /* ==================================================
           ORDER INFO
        ================================================== */

        .order-info {

            margin-top: 30px;

            border: 1px solid #e4e4e4;

            text-align: left;
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


        .info-label {

            color: #888;

            letter-spacing: 1px;

            font-size: 10px;
        }


        .info-value {

            font-weight: 600;

            text-align: right;
        }


        /* ==================================================
           STATUS
        ================================================== */

        .status {

            display: inline-block;

            margin-top: 25px;

            padding: 7px 13px;

            background: #f1f1ef;

            border: 1px solid #ddd;

            font-size: 10px;

            letter-spacing: 1px;

            font-weight: 600;
        }


        /* ==================================================
           BUTTON
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

            min-width: 170px;

            padding: 13px 18px;

            font-size: 11px;

            letter-spacing: 1px;

            text-decoration: none;
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

                padding: 35px 22px;
            }


            h1 {

                font-size: 28px;
            }


            .info-row {

                flex-direction: column;

                gap: 5px;
            }


            .info-value {

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


    <section class="success-card">


        <div class="success-icon">
            ✓
        </div>


        <div class="eyebrow">
            PESANAN BERHASIL
        </div>


        <h1>
            Terima Kasih
        </h1>


        <p class="description">
            Pesanan Anda berhasil dibuat dan sedang menunggu proses dari Griya Space.
        </p>


        <div class="order-info">


            <div class="info-row">

                <span class="info-label">
                    NOMOR PESANAN
                </span>


                <span class="info-value">

                    <?= htmlspecialchars(
                        $nomor_pesanan
                    ); ?>

                </span>

            </div>


            <div class="info-row">

                <span class="info-label">
                    JUMLAH ITEM
                </span>


                <span class="info-value">

                    <?= $jumlah_item; ?>

                </span>

            </div>


            <div class="info-row">

                <span class="info-label">
                    TOTAL PESANAN
                </span>


                <span class="info-value">

                    Rp <?= number_format(
                        $total_harga,
                        0,
                        ",",
                        "."
                    ); ?>

                </span>

            </div>

        </div>


        <div class="status">
            MENUNGGU
        </div>


        <div class="actions">


            <a
                href="../index.php"
                class="button button-primary"
            >
                KEMBALI KE DASHBOARD
            </a>


            <a
                href="#"
                class="button button-secondary"
            >
                LIHAT PESANAN
            </a>


        </div>


    </section>


</main>


</body>

</html>