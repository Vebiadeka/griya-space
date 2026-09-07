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
// AMBIL DATA TRANSAKSI DARI SESSION
// ======================================================

if (
    !isset(
        $_SESSION["transaksi_berhasil"]
    )
) {

    header(
        "Location: ../pesanan/"
    );

    exit;
}


$data =
    $_SESSION[
        "transaksi_berhasil"
    ];


// ======================================================
// AMBIL DATA
// ======================================================

$id_transaksi =
    (int) (
        $data["id_transaksi"]
        ?? 0
    );


$nomor_transaksi =
    $data["nomor_transaksi"]
    ?? "-";


$nomor_pesanan =
    $data["nomor_pesanan"]
    ?? "-";


$total_harga =
    (float) (
        $data["total_harga"]
        ?? 0
    );


$metode_pembayaran =
    $data["metode_pembayaran"]
    ?? "-";


$uang_diterima =
    (float) (
        $data["uang_diterima"]
        ?? 0
    );


$kembalian =
    (float) (
        $data["kembalian"]
        ?? 0
    );


// ======================================================
// HAPUS DATA FLASH SESSION
// ======================================================

unset(
    $_SESSION["transaksi_berhasil"]
);

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

            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f5f5f3;

            color: #1d1d1d;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 25px;
        }


        .page {

            width: 100%;

            max-width: 650px;
        }


        .brand {

            text-align: center;

            margin-bottom: 22px;

            font-size: 17px;

            letter-spacing: 6px;

            font-weight: 500;
        }


        .card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 40px;
        }


        .success-icon {

            width: 58px;

            height: 58px;

            margin: 0 auto 20px;

            border-radius: 50%;

            background: #eef5ef;

            color: #3d6c43;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 28px;

            font-weight: 700;
        }


        .eyebrow {

            text-align: center;

            color: #3d6c43;

            font-size: 10px;

            letter-spacing: 3px;

            margin-bottom: 10px;
        }


        h1 {

            text-align: center;

            font-size: 30px;

            font-weight: 500;
        }


        .subtitle {

            margin: 10px auto 30px;

            max-width: 470px;

            text-align: center;

            color: #777;

            font-size: 12px;

            line-height: 1.6;
        }


        .transaction-number {

            padding: 18px;

            background: #f7f7f5;

            border: 1px solid #e4e4e1;

            text-align: center;

            margin-bottom: 20px;
        }


        .transaction-number span {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1.5px;
        }


        .transaction-number strong {

            display: block;

            margin-top: 7px;

            font-size: 20px;

            letter-spacing: .5px;
        }


        .details {

            border-top: 1px solid #eeeeee;

            border-bottom: 1px solid #eeeeee;

        }


        .detail-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 14px 0;

            border-bottom: 1px solid #eeeeee;

            font-size: 12px;
        }


        .detail-row:last-child {

            border-bottom: none;
        }


        .label {

            color: #888;
        }


        .value {

            text-align: right;

            font-weight: 600;
        }


        .total {

            font-size: 16px;

            font-weight: 700;
        }


        .status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            background: #edf5f3;

            color: #347166;

            font-size: 9px;

            font-weight: 600;

            letter-spacing: .8px;
        }


        .actions {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 10px;

    margin-top: 25px;
}


        .button {

            min-height: 43px;

            display: flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            font-size: 10px;

            letter-spacing: 1px;

            border: 1px solid #ddd;
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


        .button-secondary:hover {

            background: #e7e7e4;
        }


        .note {

            margin-top: 20px;

            color: #999;

            font-size: 10px;

            text-align: center;

            line-height: 1.5;
        }


        @media (max-width: 600px) {

            body {

                padding: 15px;
            }


            .card {

                padding: 25px 20px;
            }


            h1 {

                font-size: 26px;
            }


            .transaction-number strong {

                font-size: 17px;
            }


            .actions {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<div class="page">


    <div class="brand">
        GRIYA SPACE
    </div>


    <section class="card">


        <div class="success-icon">
            ✓
        </div>


        <div class="eyebrow">
            TRANSAKSI SELESAI
        </div>


        <h1>
            Pembayaran Berhasil
        </h1>


        <p class="subtitle">
            Transaksi telah berhasil dicatat,
            pembayaran diterima, dan stok produk
            telah diperbarui.
        </p>


        <div class="transaction-number">

            <span>
                NOMOR TRANSAKSI
            </span>


            <strong>
                <?= htmlspecialchars(
                    $nomor_transaksi
                ); ?>
            </strong>

        </div>


        <div class="details">


            <div class="detail-row">

                <span class="label">
                    Nomor Pesanan
                </span>


                <span class="value">
                    <?= htmlspecialchars(
                        $nomor_pesanan
                    ); ?>
                </span>

            </div>


            <div class="detail-row">

                <span class="label">
                    Total
                </span>


                <span class="value total">

                    Rp <?= number_format(
                        $total_harga,
                        0,
                        ",",
                        "."
                    ); ?>

                </span>

            </div>


            <div class="detail-row">

                <span class="label">
                    Metode Pembayaran
                </span>


                <span class="value">
                    <?= htmlspecialchars(
                        $metode_pembayaran
                    ); ?>
                </span>

            </div>


            <div class="detail-row">

                <span class="label">
                    Uang Diterima
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


            <div class="detail-row">

                <span class="label">
                    Kembalian
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


            <div class="detail-row">

                <span class="label">
                    Status
                </span>


                <span class="value">

                    <span class="status">
                        LUNAS
                    </span>

                </span>

            </div>


        </div>


        <div class="actions">


            <a
                href="index.php"
                class="button button-primary"
            >
                RIWAYAT TRANSAKSI
            </a>


            <a
                href="../pesanan/"
                class="button button-secondary"
            >
                KEMBALI KE PESANAN
            </a>


        </div>


        <div class="note">

            ID transaksi:
            <?= $id_transaksi; ?>

            ·
            Data pembayaran telah tersimpan di sistem.

        </div>


    </section>


</div>


</body>

</html>