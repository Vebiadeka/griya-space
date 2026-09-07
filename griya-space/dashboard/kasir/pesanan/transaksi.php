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
        pl.no_telepon

    FROM pesanan p

    INNER JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan

    WHERE p.id_pesanan = ?

    LIMIT 1
");


if (!$stmt) {
    die(
        "Gagal menyiapkan data pesanan: " .
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
// CEK STATUS PESANAN
// ======================================================

if ($pesanan["status"] === "Dibatalkan") {

    $conn->close();

    die("Pesanan ini sudah dibatalkan.");
}


// ======================================================
// AMBIL DETAIL PESANAN
// ======================================================

$stmt = $conn->prepare("
    SELECT
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
        "Gagal menyiapkan detail pesanan: " .
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
// CEK DETAIL
// ======================================================

if (empty($detail_items)) {

    $conn->close();

    die("Detail produk pada pesanan tidak ditemukan.");
}


// ======================================================
// TOTAL
// ======================================================

$total_harga =
    (float) $pesanan["total_harga"];


// ======================================================
// FORMAT STATUS
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
        Proses Transaksi | Griya Space
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

        .grid {

            display: grid;

            grid-template-columns:
                1.35fr
                .85fr;

            gap: 20px;

            align-items: start;
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


        /* ==================================================
           ORDER INFO
        ================================================== */

        .order-number {

            font-size: 17px;

            font-weight: 600;

            margin-bottom: 8px;
        }


        .order-date {

            color: #777;

            font-size: 12px;

            line-height: 1.5;
        }


        .customer {

            margin-top: 20px;

            padding-top: 20px;

            border-top: 1px solid #eeeeee;
        }


        .customer-name {

            font-size: 16px;

            font-weight: 600;

            margin-bottom: 5px;
        }


        .customer-code {

            color: #999;

            font-size: 10px;

            margin-bottom: 10px;
        }


        .customer-phone {

            color: #666;

            font-size: 12px;
        }


        /* ==================================================
           STATUS
        ================================================== */

        .status {

            display: inline-block;

            margin-top: 18px;

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
           PRODUK
        ================================================== */

        .product-item {

            padding: 17px 0;

            border-bottom: 1px solid #eeeeee;
        }


        .product-item:first-child {

            padding-top: 0;
        }


        .product-item:last-child {

            border-bottom: none;

            padding-bottom: 0;
        }


        .product-name {

            font-size: 14px;

            font-weight: 600;
        }


        .product-code {

            margin-top: 4px;

            color: #999;

            font-size: 10px;
        }


        .product-meta {

            margin-top: 7px;

            color: #666;

            font-size: 11px;

            line-height: 1.5;
        }


        .product-total {

            margin-top: 6px;

            font-size: 13px;

            font-weight: 600;
        }


        /* ==================================================
           SUMMARY
        ================================================== */

        .summary-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 10px 0;

            font-size: 12px;

            color: #666;
        }


        .summary-row:first-child {

            padding-top: 0;
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
           PAYMENT FORM
        ================================================== */

        .form-group {

            margin-bottom: 20px;
        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 11px;

            font-weight: 600;

            letter-spacing: .5px;
        }


        select,
        input {

            width: 100%;

            height: 46px;

            padding: 0 13px;

            border: 1px solid #d8d8d5;

            border-radius: 6px;

            background: #fafafa;

            color: #222;

            outline: none;

            font-family: inherit;

            font-size: 12px;
        }


        select:focus,
        input:focus {

            background: white;

            border-color: #555;

            box-shadow:
                0 0 0 3px rgba(0,0,0,.035);
        }


        .payment-info {

            display: none;

            margin-top: 10px;

            padding: 12px 14px;

            background: #f7f7f5;

            border: 1px solid #e4e4e4;

            color: #777;

            font-size: 10px;

            line-height: 1.5;
        }


        .cash-section {

            display: none;
        }


        .change-box {

            margin-top: 15px;

            padding: 15px;

            background: #f7f7f5;

            border: 1px solid #e4e4e4;
        }


        .change-label {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1px;

            margin-bottom: 5px;
        }


        .change-value {

            font-size: 20px;

            font-weight: 700;
        }


        .warning {

            display: none;

            margin-top: 10px;

            padding: 10px 12px;

            background: #faf0f0;

            border: 1px solid #ead0d0;

            border-left: 4px solid #8b3030;

            color: #8b3030;

            font-size: 10px;

            line-height: 1.5;
        }


        /* ==================================================
           BUTTON
        ================================================== */

        .submit-button {

            width: 100%;

            height: 46px;

            margin-top: 10px;

            border: none;

            background: #1d1d1d;

            color: white;

            font-size: 11px;

            font-weight: 600;

            letter-spacing: 1px;

            cursor: pointer;
        }


        .submit-button:hover {

            background: #333;
        }


        .submit-button:disabled {

            background: #aaa;

            cursor: not-allowed;
        }


        .cancel-button {

            display: block;

            width: 100%;

            margin-top: 10px;

            padding: 13px;

            text-align: center;

            text-decoration: none;

            background: #f2f2f0;

            border: 1px solid #ddd;

            color: #555;

            font-size: 11px;

            letter-spacing: 1px;
        }


        /* ==================================================
           NOTE
        ================================================== */

        .note {

            margin-top: 15px;

            color: #999;

            font-size: 10px;

            line-height: 1.6;
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


            .grid {

                grid-template-columns: 1fr;
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
            Proses Transaksi
        </h1>


        <p>
            Konfirmasi pembayaran pelanggan dan selesaikan transaksi.
        </p>

    </section>


    <div class="grid">


        <!-- ==================================================
             KIRI
        ================================================== -->

        <div>


            <!-- INFORMASI PESANAN -->

            <section class="card">


                <div class="card-title">
                    INFORMASI PESANAN
                </div>


                <div class="card-body">


                    <div class="order-number">

                        <?= htmlspecialchars(
                            $pesanan["nomor_pesanan"]
                        ); ?>

                    </div>


                    <div class="order-date">

                        Tanggal:
                        <?= htmlspecialchars(
                            $pesanan["tanggal_pesanan"]
                        ); ?>

                    </div>


                    <span
                        class="status <?= $statusClass; ?>"
                    >

                        <?= htmlspecialchars(
                            $pesanan["status"]
                        ); ?>

                    </span>


                    <div class="customer">


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


                        <div class="customer-phone">

                            <?= htmlspecialchars(
                                $pesanan["no_telepon"] ?? "-"
                            ); ?>

                        </div>


                    </div>


                </div>


            </section>


            <!-- PRODUK -->

            <section
                class="card"
                style="margin-top:20px;"
            >


                <div class="card-title">
                    PRODUK PESANAN
                </div>


                <div class="card-body">


                    <?php foreach (
                        $detail_items
                        as $item
                    ): ?>


                        <div class="product-item">


                            <div class="product-name">

                                <?= htmlspecialchars(
                                    $item["nama_produk"]
                                ); ?>

                            </div>


                            <div class="product-code">

                                <?= htmlspecialchars(
                                    $item["kode_produk"]
                                ); ?>

                            </div>


                            <div class="product-meta">

                                <?= (int) $item["jumlah"]; ?>

                                × Rp

                                <?= number_format(
                                    $item["harga_satuan"],
                                    0,
                                    ",",
                                    "."
                                ); ?>

                                /

                                <?= htmlspecialchars(
                                    $item["satuan"]
                                ); ?>

                            </div>


                            <div class="product-total">

                                Rp <?= number_format(
                                    $item["subtotal"],
                                    0,
                                    ",",
                                    "."
                                ); ?>

                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>


            </section>


        </div>


        <!-- ==================================================
             KANAN
        ================================================== -->

        <div>


            <!-- RINGKASAN -->

            <section class="card">


                <div class="card-title">
                    RINGKASAN PEMBAYARAN
                </div>


                <div class="card-body">


                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>


                        <span class="summary-value">

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


                        <span class="summary-value">

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


                        <span class="summary-value">

                            Rp <?= number_format(
                                $total_harga,
                                0,
                                ",",
                                "."
                            ); ?>

                        </span>

                    </div>


                </div>


            </section>


            <!-- PEMBAYARAN -->

            <form
                action="proses_transaksi.php"
                method="POST"
                class="card"
                style="margin-top:20px;"
                id="paymentForm"
            >


                <div class="card-title">
                    PEMBAYARAN
                </div>


                <div class="card-body">


                    <input
                        type="hidden"
                        name="id_pesanan"
                        value="<?= (int) $id_pesanan; ?>"
                    >


                    <div class="form-group">


                        <label for="metode_pembayaran">

                            Metode Pembayaran

                        </label>


                        <select
                            id="metode_pembayaran"
                            name="metode_pembayaran"
                            required
                        >

                            <option value="">
                                Pilih metode pembayaran
                            </option>


                            <option value="Cash">
                                Cash
                            </option>


                            <option value="Transfer">
                                Transfer
                            </option>


                            <option value="QRIS">
                                QRIS
                            </option>


                            <option value="Debit">
                                Debit
                            </option>


                            <option value="Kredit">
                                Kredit
                            </option>

                        </select>


                        <div
                            class="payment-info"
                            id="paymentInfo"
                        ></div>

                    </div>


                    <div
                        class="cash-section"
                        id="cashSection"
                    >


                        <div class="form-group">


                            <label for="uang_diterima">

                                Uang Diterima

                            </label>


                            <input
                                type="number"
                                id="uang_diterima"
                                name="uang_diterima"
                                min="<?= $total_harga; ?>"
                                step="0.01"
                                placeholder="Masukkan nominal uang"
                            >


                            <div
                                class="warning"
                                id="cashWarning"
                            >
                                Uang diterima belum mencukupi total pembayaran.
                            </div>


                            <div class="change-box">


                                <span class="change-label">

                                    KEMBALIAN

                                </span>


                                <span
                                    class="change-value"
                                    id="changeValue"
                                >
                                    Rp 0
                                </span>


                            </div>


                        </div>


                    </div>


                    <button
                        type="submit"
                        class="submit-button"
                        id="submitButton"
                    >
                        KONFIRMASI PEMBAYARAN
                    </button>


                    <a
                        href="detail.php?id=<?= (int) $id_pesanan; ?>"
                        class="cancel-button"
                    >
                        KEMBALI KE DETAIL
                    </a>


                    <div class="note">

                        Setelah pembayaran berhasil, sistem akan membuat
                        transaksi, mencatat pembayaran, mengurangi stok,
                        dan mencatat mutasi stok.

                    </div>


                </div>


            </form>


        </div>


    </div>


</main>


<script>

    const totalHarga =
        <?= json_encode($total_harga); ?>;


    const metodeSelect =
        document.getElementById(
            "metode_pembayaran"
        );


    const cashSection =
        document.getElementById(
            "cashSection"
        );


    const uangInput =
        document.getElementById(
            "uang_diterima"
        );


    const changeValue =
        document.getElementById(
            "changeValue"
        );


    const cashWarning =
        document.getElementById(
            "cashWarning"
        );


    const paymentInfo =
        document.getElementById(
            "paymentInfo"
        );


    const submitButton =
        document.getElementById(
            "submitButton"
        );


    metodeSelect.addEventListener(
        "change",
        function () {

            const metode =
                this.value;


            cashSection.style.display =
                metode === "Cash"
                    ? "block"
                    : "none";


            if (metode === "Cash") {

                paymentInfo.style.display =
                    "none";

                uangInput.required =
                    true;

            } else {

                uangInput.required =
                    false;

                uangInput.value =
                    "";

                cashWarning.style.display =
                    "none";

                changeValue.textContent =
                    "Rp 0";


                if (metode !== "") {

                    paymentInfo.style.display =
                        "block";


                    paymentInfo.textContent =
                        "Pastikan pembayaran " +
                        metode +
                        " sudah diterima sebelum dikonfirmasi.";

                } else {

                    paymentInfo.style.display =
                        "none";

                }

            }

        }
    );


    uangInput.addEventListener(
        "input",
        function () {

            const uang =
                Number(
                    this.value || 0
                );


            const kembalian =
                uang - totalHarga;


            if (uang < totalHarga) {

                cashWarning.style.display =
                    "block";

                changeValue.textContent =
                    "Rp 0";

                submitButton.disabled =
                    true;

            } else {

                cashWarning.style.display =
                    "none";

                changeValue.textContent =
                    "Rp " +
                    new Intl.NumberFormat(
                        "id-ID"
                    ).format(
                        kembalian
                    );

                submitButton.disabled =
                    false;

            }

        }
    );

</script>


</body>

</html>


<?php

mysqli_close($conn);

?>