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
// ID USER
// ======================================================

$id_user = (int) $_SESSION["user_id"];


// ======================================================
// AMBIL DATA PELANGGAN
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_pelanggan,
        kode_pelanggan,
        nama_pelanggan,
        no_telepon,
        email,
        alamat
    FROM pelanggan
    WHERE id_user = ?
    LIMIT 1
");


if (!$stmt) {
    die(
        "Gagal menyiapkan data pelanggan: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_user
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    die("Data pelanggan tidak ditemukan.");
}


$pelanggan = $result->fetch_assoc();

$stmt->close();


// ======================================================
// AMBIL DATA KERANJANG
// ======================================================

$sql = "
    SELECT
        k.id_keranjang,
        k.id_produk,
        k.jumlah,
        p.kode_produk,
        p.nama_produk,
        p.satuan,
        p.harga_jual,
        p.stok
    FROM keranjang k

    INNER JOIN produk p
        ON k.id_produk = p.id_produk

    WHERE k.id_pelanggan = ?

    ORDER BY k.created_at ASC
";


$stmt = $conn->prepare($sql);


if (!$stmt) {
    die(
        "Gagal menyiapkan data checkout: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $pelanggan["id_pelanggan"]
);

$stmt->execute();

$result = $stmt->get_result();


// ======================================================
// HITUNG TOTAL
// ======================================================

$items = [];

$subtotal = 0;

$jumlah_item = 0;


while ($item = $result->fetch_assoc()) {

    $item_subtotal =
        (float) $item["harga_jual"] *
        (int) $item["jumlah"];

    $item["subtotal"] =
        $item_subtotal;

    $items[] =
        $item;

    $subtotal +=
        $item_subtotal;

    $jumlah_item +=
        (int) $item["jumlah"];
}


$stmt->close();


// ======================================================
// CEK KERANJANG KOSONG
// ======================================================

if (empty($items)) {

    $conn->close();

    header(
        "Location: ../keranjang/index.php"
    );

    exit;
}


// ======================================================
// DISKON SEMENTARA
// ======================================================

$diskon = 0;

$total_harga =
    $subtotal - $diskon;

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Checkout | Griya Space</title>


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
           LAYOUT
        ================================================== */

        .checkout-grid {

            display: grid;

            grid-template-columns:
                1.5fr
                1fr;

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
           PELANGGAN
        ================================================== */

        .customer-row {

            margin-bottom: 15px;
        }


        .customer-label {

            display: block;

            color: #999;

            font-size: 10px;

            letter-spacing: 1px;

            margin-bottom: 5px;
        }


        .customer-value {

            font-size: 13px;

            line-height: 1.5;
        }


        .customer-row:last-child {

            margin-bottom: 0;
        }


        /* ==================================================
           PRODUK
        ================================================== */

        .item {

            padding: 18px 0;

            border-bottom: 1px solid #eeeeee;

            display: grid;

            grid-template-columns: 1fr auto;

            gap: 20px;

            align-items: center;
        }


        .item:first-child {

            padding-top: 0;
        }


        .item:last-child {

            border-bottom: none;

            padding-bottom: 0;
        }


        .item-name {

            font-size: 14px;

            font-weight: 600;
        }


        .item-code {

            margin-top: 4px;

            color: #999;

            font-size: 10px;
        }


        .item-meta {

            margin-top: 6px;

            color: #666;

            font-size: 11px;
        }


        .item-subtotal {

            text-align: right;

            font-size: 13px;

            font-weight: 600;

            white-space: nowrap;
        }


        /* ==================================================
           RINGKASAN
        ================================================== */

        .summary-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 10px 0;

            font-size: 12px;

            color: #666;
        }


        .summary-row.total {

            margin-top: 8px;

            padding-top: 18px;

            border-top: 1px solid #ddd;

            color: #1d1d1d;

            font-size: 16px;

            font-weight: 700;
        }


        .summary-value {

            white-space: nowrap;
        }


        /* ==================================================
           CATATAN
        ================================================== */

        textarea {

            width: 100%;

            min-height: 110px;

            padding: 13px;

            border: 1px solid #d8d8d5;

            background: #fafafa;

            border-radius: 6px;

            font-family: inherit;

            font-size: 12px;

            resize: vertical;

            outline: none;
        }


        textarea:focus {

            background: white;

            border-color: #555;
        }


        .hint {

            margin-top: 7px;

            color: #999;

            font-size: 10px;

            line-height: 1.5;
        }


        /* ==================================================
           BUTTON
        ================================================== */

        .checkout-button {

            width: 100%;

            margin-top: 20px;

            padding: 15px;

            border: none;

            background: #1d1d1d;

            color: white;

            font-size: 11px;

            font-weight: 600;

            letter-spacing: 1px;

            cursor: pointer;
        }


        .checkout-button:hover {

            background: #333;
        }


        .cancel-button {

            display: block;

            margin-top: 10px;

            padding: 13px;

            text-align: center;

            background: #f2f2f0;

            border: 1px solid #ddd;

            color: #555;

            text-decoration: none;

            font-size: 11px;

            letter-spacing: 1px;
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


            .checkout-grid {

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
            PELANGGAN
        </div>


        <a
            href="../keranjang/index.php"
            class="back-link"
        >
            KEMBALI
        </a>

    </div>

</header>


<main>


    <section class="page-header">

        <small>
            PEMESANAN
        </small>


        <h1>
            Checkout
        </h1>


        <p>
            Periksa data pelanggan dan rincian pesanan sebelum dikonfirmasi.
        </p>

    </section>


    <div class="checkout-grid">


        <!-- ==================================================
             KIRI
        ================================================== -->

        <div>


            <!-- DATA PELANGGAN -->

            <section class="card">

                <div class="card-title">
                    DATA PELANGGAN
                </div>


                <div class="card-body">


                    <div class="customer-row">

                        <span class="customer-label">
                            KODE PELANGGAN
                        </span>


                        <div class="customer-value">

                            <?= htmlspecialchars(
                                $pelanggan["kode_pelanggan"]
                            ); ?>

                        </div>

                    </div>


                    <div class="customer-row">

                        <span class="customer-label">
                            NAMA
                        </span>


                        <div class="customer-value">

                            <?= htmlspecialchars(
                                $pelanggan["nama_pelanggan"]
                            ); ?>

                        </div>

                    </div>


                    <div class="customer-row">

                        <span class="customer-label">
                            NO. TELEPON
                        </span>


                        <div class="customer-value">

                            <?= htmlspecialchars(
                                $pelanggan["no_telepon"] ?? "-"
                            ); ?>

                        </div>

                    </div>


                    <div class="customer-row">

                        <span class="customer-label">
                            EMAIL
                        </span>


                        <div class="customer-value">

                            <?= htmlspecialchars(
                                $pelanggan["email"] ?? "-"
                            ); ?>

                        </div>

                    </div>


                    <div class="customer-row">

                        <span class="customer-label">
                            ALAMAT
                        </span>


                        <div class="customer-value">

                            <?= nl2br(
                                htmlspecialchars(
                                    $pelanggan["alamat"] ?? "-"
                                )
                            ); ?>

                        </div>

                    </div>


                </div>

            </section>


            <!-- PESANAN -->

            <section
                class="card"
                style="margin-top:20px;"
            >

                <div class="card-title">
                    PRODUK YANG DIPESAN
                </div>


                <div class="card-body">


                    <?php foreach ($items as $item): ?>


                        <div class="item">


                            <div>

                                <div class="item-name">

                                    <?= htmlspecialchars(
                                        $item["nama_produk"]
                                    ); ?>

                                </div>


                                <div class="item-code">

                                    <?= htmlspecialchars(
                                        $item["kode_produk"]
                                    ); ?>

                                </div>


                                <div class="item-meta">

                                    <?= (int) $item["jumlah"]; ?>

                                    × Rp
                                    <?= number_format(
                                        $item["harga_jual"],
                                        0,
                                        ",",
                                        "."
                                    ); ?>

                                    /

                                    <?= htmlspecialchars(
                                        $item["satuan"]
                                    ); ?>

                                </div>

                            </div>


                            <div class="item-subtotal">

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


            <!-- CATATAN -->

            <form
                action="proses.php"
                method="POST"
                class="card"
                style="margin-top:20px;"
            >

                <div class="card-title">
                    CATATAN PESANAN
                </div>


                <div class="card-body">


                    <textarea
                        name="catatan"
                        maxlength="500"
                        placeholder="Tambahkan catatan untuk pesanan..."
                    ></textarea>


                    <div class="hint">
                        Catatan bersifat opsional.
                    </div>


                    <button
                        type="submit"
                        class="checkout-button"
                    >
                        KONFIRMASI PESANAN
                    </button>


                    <a
                        href="../keranjang/index.php"
                        class="cancel-button"
                    >
                        KEMBALI KE KERANJANG
                    </a>


                </div>

            </form>


        </div>


        <!-- ==================================================
             KANAN
        ================================================== -->

        <aside class="card">

            <div class="card-title">
                RINGKASAN PESANAN
            </div>


            <div class="card-body">


                <div class="summary-row">

                    <span>
                        Jumlah Item
                    </span>

                    <span class="summary-value">
                        <?= $jumlah_item; ?>
                    </span>

                </div>


                <div class="summary-row">

                    <span>
                        Subtotal
                    </span>

                    <span class="summary-value">

                        Rp <?= number_format(
                            $subtotal,
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
                            $diskon,
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

        </aside>


    </div>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>