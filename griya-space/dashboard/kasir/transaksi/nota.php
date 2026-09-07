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

$id_transaksi = (int) (
    $_GET["id"] ?? 0
);


if ($id_transaksi <= 0) {
    header("Location: index.php");
    exit;
}


// ======================================================
// AMBIL DATA TRANSAKSI
// ======================================================

$sql = "
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

        pl.kode_pelanggan,
        pl.nama_pelanggan,
        pl.no_telepon,
        pl.email,

        u.nama_lengkap AS nama_kasir

    FROM transaksi t

    INNER JOIN pelanggan pl
        ON t.id_pelanggan = pl.id_pelanggan

    INNER JOIN users u
        ON t.id_user = u.id_user

    WHERE t.id_transaksi = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);


if (!$stmt) {
    die(
        "Gagal mengambil transaksi: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_transaksi
);


$stmt->execute();


$result = $stmt->get_result();


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
// AMBIL DETAIL TRANSAKSI
// ======================================================

$sql_detail = "
    SELECT
        dt.id_detail_transaksi,
        dt.id_produk,
        dt.jumlah,
        dt.harga_satuan,
        dt.diskon,
        dt.subtotal,

        p.kode_produk,
        p.nama_produk,
        p.satuan

    FROM detail_transaksi dt

    INNER JOIN produk p
        ON dt.id_produk = p.id_produk

    WHERE dt.id_transaksi = ?

    ORDER BY
        dt.id_detail_transaksi ASC
";


$stmt = $conn->prepare($sql_detail);


if (!$stmt) {
    die(
        "Gagal mengambil detail transaksi: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_transaksi
);


$stmt->execute();


$result_detail =
    $stmt->get_result();


$detail_list = [];


while (
    $detail =
    $result_detail->fetch_assoc()
) {

    $detail_list[] =
        $detail;
}


$stmt->close();


// ======================================================
// CEK DETAIL
// ======================================================

if (empty($detail_list)) {

    $conn->close();

    die(
        "Detail transaksi tidak ditemukan."
    );
}


// ======================================================
// AMBIL DATA PEMBAYARAN
// ======================================================

$sql_pembayaran = "
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

    ORDER BY
        id_pembayaran DESC

    LIMIT 1
";


$stmt =
    $conn->prepare(
        $sql_pembayaran
    );


if (!$stmt) {
    die(
        "Gagal mengambil data pembayaran: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_transaksi
);


$stmt->execute();


$result_pembayaran =
    $stmt->get_result();


$pembayaran = null;


if (
    $result_pembayaran->num_rows === 1
) {

    $pembayaran =
        $result_pembayaran->fetch_assoc();
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
        Nota
        <?= htmlspecialchars(
            $transaksi["nomor_transaksi"]
        ); ?>
        | Griya Space
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

            background: #eeeeec;

            color: #1d1d1d;

            padding: 30px;
        }


        .receipt {

            width: 100%;

            max-width: 760px;

            margin: 0 auto;

            background: white;

            padding: 35px 40px;

            border: 1px solid #ddd;
        }


        .top {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            padding-bottom: 20px;

            border-bottom: 2px solid #1d1d1d;
        }


        .brand {

            font-size: 22px;

            font-weight: 600;

            letter-spacing: 5px;
        }


        .brand-subtitle {

            margin-top: 6px;

            color: #777;

            font-size: 10px;

            letter-spacing: 1.5px;
        }


        .receipt-title {

            text-align: right;

            font-size: 15px;

            font-weight: 700;

            letter-spacing: 1px;
        }


        .receipt-status {

            margin-top: 6px;

            color: #347166;

            font-size: 9px;

            font-weight: 700;

            letter-spacing: 1px;
        }


        .meta {

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            gap: 15px;

            padding: 20px 0;

            border-bottom: 1px solid #ddd;
        }


        .meta-group {

            line-height: 1.6;
        }


        .meta-label {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1px;
        }


        .meta-value {

            display: block;

            margin-top: 4px;

            font-size: 12px;

            font-weight: 600;
        }


        .customer {

            margin-top: 20px;

            padding-bottom: 20px;

            border-bottom: 1px solid #ddd;
        }


        .section-title {

            margin-bottom: 10px;

            font-size: 9px;

            color: #777;

            letter-spacing: 2px;

            font-weight: 600;
        }


        .customer-name {

            font-size: 13px;

            font-weight: 600;
        }


        .customer-info {

            margin-top: 4px;

            color: #666;

            font-size: 10px;

            line-height: 1.6;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 20px;
        }


        th {

            padding: 10px 6px;

            border-bottom: 1px solid #1d1d1d;

            text-align: left;

            font-size: 9px;

            letter-spacing: .7px;
        }


        td {

            padding: 11px 6px;

            border-bottom: 1px solid #eeeeee;

            font-size: 10px;

            vertical-align: top;
        }


        .text-right {

            text-align: right;
        }


        .product-name {

            font-weight: 600;

            font-size: 11px;
        }


        .product-code {

            margin-top: 3px;

            color: #999;

            font-size: 9px;
        }


        .summary {

            width: 360px;

            max-width: 100%;

            margin: 20px 0 0 auto;
        }


        .summary-row {

            display: flex;

            justify-content: space-between;

            gap: 15px;

            padding: 7px 0;

            font-size: 11px;
        }


        .summary-row.total {

            padding-top: 12px;

            margin-top: 5px;

            border-top: 2px solid #1d1d1d;

            font-size: 15px;

            font-weight: 700;
        }


        .payment {

            margin-top: 20px;

            padding-top: 18px;

            border-top: 1px solid #ddd;
        }


        .payment-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 10px;
        }


        .payment-box {

            padding: 12px;

            background: #f7f7f5;

            border: 1px solid #e5e5e1;
        }


        .payment-box-label {

            display: block;

            color: #888;

            font-size: 8px;

            letter-spacing: 1px;
        }


        .payment-box-value {

            display: block;

            margin-top: 5px;

            font-size: 11px;

            font-weight: 700;
        }


        .footer {

            margin-top: 30px;

            padding-top: 18px;

            border-top: 1px dashed #bbb;

            text-align: center;

            color: #777;

            font-size: 10px;

            line-height: 1.7;
        }


        .actions {

            max-width: 760px;

            margin: 15px auto 0;

            display: flex;

            justify-content: center;

            gap: 10px;
        }


        .button {

            min-height: 40px;

            padding: 0 18px;

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


        .button-secondary {

            background: white;

            color: #555;
        }


        @media print {

            body {

                background: white;

                padding: 0;
            }


            .receipt {

                max-width: none;

                border: none;

                padding: 20px;
            }


            .actions {

                display: none;
            }

        }


        @media (max-width: 600px) {

            body {

                padding: 10px;
            }


            .receipt {

                padding: 25px 18px;
            }


            .top {

                flex-direction: column;
            }


            .receipt-title {

                text-align: left;
            }


            .meta {

                grid-template-columns: 1fr;
            }


            .payment-grid {

                grid-template-columns: 1fr;
            }


            .summary {

                width: 100%;
            }

        }

    </style>

</head>


<body>


<section class="receipt">


    <div class="top">

        <div>

            <div class="brand">
                GRIYA SPACE
            </div>


            <div class="brand-subtitle">
                TOKO BANGUNAN
            </div>

        </div>


        <div>

            <div class="receipt-title">
                NOTA PENJUALAN
            </div>


            <div class="receipt-status">

                <?= htmlspecialchars(
                    $transaksi["status"]
                ); ?>

            </div>

        </div>

    </div>


    <!-- ==================================================
         META
    ================================================== -->

    <div class="meta">


        <div class="meta-group">

            <span class="meta-label">
                NOMOR TRANSAKSI
            </span>


            <span class="meta-value">

                <?= htmlspecialchars(
                    $transaksi["nomor_transaksi"]
                ); ?>

            </span>

        </div>


        <div class="meta-group">

            <span class="meta-label">
                TANGGAL
            </span>


            <span class="meta-value">

                <?= htmlspecialchars(
                    $transaksi["tanggal_transaksi"]
                ); ?>

            </span>

        </div>


        <div class="meta-group">

            <span class="meta-label">
                KASIR
            </span>


            <span class="meta-value">

                <?= htmlspecialchars(
                    $transaksi["nama_kasir"]
                ); ?>

            </span>

        </div>


        <div class="meta-group">

            <span class="meta-label">
                ID TRANSAKSI
            </span>


            <span class="meta-value">

                #<?= (int) $transaksi[
                    "id_transaksi"
                ]; ?>

            </span>

        </div>


    </div>


    <!-- ==================================================
         PELANGGAN
    ================================================== -->

    <section class="customer">


        <div class="section-title">
            PELANGGAN
        </div>


        <div class="customer-name">

            <?= htmlspecialchars(
                $transaksi["nama_pelanggan"]
            ); ?>

        </div>


        <div class="customer-info">

            <?= htmlspecialchars(
                $transaksi["kode_pelanggan"]
            ); ?>


            <?php if (
                !empty(
                    $transaksi["no_telepon"]
                )
            ): ?>

                ·
                <?= htmlspecialchars(
                    $transaksi["no_telepon"]
                ); ?>

            <?php endif; ?>


            <?php if (
                !empty(
                    $transaksi["email"]
                )
            ): ?>

                ·
                <?= htmlspecialchars(
                    $transaksi["email"]
                ); ?>

            <?php endif; ?>

        </div>

    </section>


    <!-- ==================================================
         PRODUK
    ================================================== -->

    <table>

        <thead>

            <tr>

                <th>
                    PRODUK
                </th>

                <th class="text-right">
                    JUMLAH
                </th>

                <th class="text-right">
                    HARGA
                </th>

                <th class="text-right">
                    SUBTOTAL
                </th>

            </tr>

        </thead>


        <tbody>


            <?php foreach (
                $detail_list
                as $detail
            ): ?>

                <tr>

                    <td>

                        <div class="product-name">

                            <?= htmlspecialchars(
                                $detail[
                                    "nama_produk"
                                ]
                            ); ?>

                        </div>


                        <div class="product-code">

                            <?= htmlspecialchars(
                                $detail[
                                    "kode_produk"
                                ]
                            ); ?>

                        </div>

                    </td>


                    <td class="text-right">

                        <?= (int) $detail[
                            "jumlah"
                        ]; ?>

                        <?= htmlspecialchars(
                            $detail["satuan"]
                        ); ?>

                    </td>


                    <td class="text-right">

                        Rp <?= number_format(
                            $detail[
                                "harga_satuan"
                            ],
                            0,
                            ",",
                            "."
                        ); ?>

                    </td>


                    <td class="text-right">

                        Rp <?= number_format(
                            $detail[
                                "subtotal"
                            ],
                            0,
                            ",",
                            "."
                        ); ?>

                    </td>

                </tr>

            <?php endforeach; ?>


        </tbody>

    </table>


    <!-- ==================================================
         RINGKASAN
    ================================================== -->

    <div class="summary">


        <div class="summary-row">

            <span>
                Subtotal
            </span>


            <span>

                Rp <?= number_format(
                    $transaksi["subtotal"],
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
                    $transaksi["diskon"],
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
                    $transaksi["total_harga"],
                    0,
                    ",",
                    "."
                ); ?>

            </span>

        </div>


    </div>


    <!-- ==================================================
         PEMBAYARAN
    ================================================== -->

    <section class="payment">


        <div class="section-title">
            PEMBAYARAN
        </div>


        <?php if (
            $pembayaran !== null
        ): ?>

            <div class="payment-grid">


                <div class="payment-box">

                    <span class="payment-box-label">
                        METODE
                    </span>


                    <span class="payment-box-value">

                        <?= htmlspecialchars(
                            $pembayaran[
                                "metode_pembayaran"
                            ]
                        ); ?>

                    </span>

                </div>


                <div class="payment-box">

                    <span class="payment-box-label">
                        UANG DITERIMA
                    </span>


                    <span class="payment-box-value">

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


                <div class="payment-box">

                    <span class="payment-box-label">
                        KEMBALIAN
                    </span>


                    <span class="payment-box-value">

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


            </div>


        <?php else: ?>

            <div
                style="
                    color:#8a4444;
                    font-size:11px;
                "
            >
                Data pembayaran tidak ditemukan.
            </div>

        <?php endif; ?>


    </section>


    <!-- ==================================================
         FOOTER
    ================================================== -->

    <div class="footer">

        Terima kasih telah berbelanja di
        <strong>Griya Space</strong>.

        <br>

        Simpan nota ini sebagai bukti transaksi.

    </div>


</section>


<div class="actions">


    <button
        type="button"
        class="button button-primary"
        onclick="window.print();"
    >
        CETAK NOTA
    </button>


    <a
        href="index.php"
        class="button button-secondary"
    >
        KEMBALI
    </a>


</div>


</body>

</html>