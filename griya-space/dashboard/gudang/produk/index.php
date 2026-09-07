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

if ($_SESSION["role"] !== "gudang") {
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL DATA PRODUK
// ======================================================

$sql = "
    SELECT
        p.id_produk,
        p.kode_produk,
        p.nama_produk,
        p.satuan,
        p.stok,
        p.stok_minimum,
        p.harga_beli,
        p.harga_jual,
        p.status,
        k.nama_kategori
    FROM produk p

    LEFT JOIN kategori k
        ON p.id_kategori = k.id_kategori

    ORDER BY
        p.nama_produk ASC
";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {
    die(
        "Gagal mengambil data produk: " .
        mysqli_error($conn)
    );
}


$produk_list = [];

$total_produk = 0;

$stok_menipis = 0;

$stok_habis = 0;


while (
    $produk =
    mysqli_fetch_assoc($result)
) {

    $produk_list[] =
        $produk;


    if (
        $produk["status"] === "Aktif"
    ) {

        $total_produk++;


        $stok =
            (int) $produk["stok"];

        $stok_minimum =
            (int) $produk["stok_minimum"];


        if ($stok <= 0) {

            $stok_habis++;

        } elseif (
            $stok <= $stok_minimum
        ) {

            $stok_menipis++;
        }
    }
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
        Produk & Stok | Griya Space
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

            max-width: 1250px;

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

            line-height: 1.5;
        }


        /* ==================================================
           SUMMARY
        ================================================== */

        .summary-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 10px;

            min-width: 430px;
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

            margin-top: 6px;

            font-size: 22px;

            font-weight: 600;
        }


        .summary-warning {

            color: #8a6d2f;
        }


        .summary-danger {

            color: #8a4444;
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

            min-width: 1050px;
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


        .code {

            color: #666;

            font-size: 11px;

            white-space: nowrap;
        }


        .product-name {

            font-weight: 600;
        }


        .category {

            color: #777;

            font-size: 11px;
        }


        .number {

            text-align: right;

            white-space: nowrap;
        }


        .stock-value {

            font-size: 16px;

            font-weight: 700;
        }


        .stock-minimum {

            margin-top: 3px;

            color: #999;

            font-size: 9px;
        }


        /* ==================================================
           STATUS STOK
        ================================================== */

        .stock-status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 9px;

            font-weight: 600;

            letter-spacing: .7px;

            white-space: nowrap;
        }


        .stock-aman {

            background: #edf5f3;

            color: #347166;
        }


        .stock-menipis {

            background: #f4f0e7;

            color: #8a6d2f;
        }


        .stock-habis {

            background: #f5eeee;

            color: #8a4444;
        }


        .stock-nonaktif {

            background: #f1f1ef;

            color: #777;
        }


        /* ==================================================
           STATUS PRODUK
        ================================================== */

        .product-status {

            display: inline-block;

            padding: 5px 9px;

            font-size: 9px;

            border-radius: 4px;

            border: 1px solid #ddd;
        }


        .product-active {

            background: #eef5ef;

            color: #3d6c43;

            border-color: #d8e8da;
        }


        .product-inactive {

            background: #f1f1ef;

            color: #777;

            border-color: #ddd;
        }


        /* ==================================================
           EMPTY
        ================================================== */

        .empty {

            padding: 60px 20px;

            text-align: center;

            color: #777;

            font-size: 12px;
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

                min-width: 0;

                width: 100%;
            }


            .page-title h1 {

                font-size: 28px;
            }

        }


        @media (max-width: 550px) {

            .summary-grid {

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
            STAFF GUDANG
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
                PERSEDIAAN
            </small>


            <h1>
                Produk & Stok
            </h1>


            <p>
                Pantau jumlah stok dan kondisi persediaan setiap produk.
            </p>

        </div>


        <div class="summary-grid">


            <div class="summary-card">

                <span class="summary-label">
                    PRODUK AKTIF
                </span>


                <span class="summary-value">
                    <?= $total_produk; ?>
                </span>

            </div>


            <div class="summary-card">

                <span class="summary-label">
                    STOK MENIPIS
                </span>


                <span class="summary-value summary-warning">
                    <?= $stok_menipis; ?>
                </span>

            </div>


            <div class="summary-card">

                <span class="summary-label">
                    STOK HABIS
                </span>


                <span class="summary-value summary-danger">
                    <?= $stok_habis; ?>
                </span>

            </div>


        </div>


    </section>


    <!-- ==================================================
         TABLE
    ================================================== -->

    <?php if (
        !empty($produk_list)
    ): ?>


        <section class="table-container">


            <table>


                <thead>

                    <tr>

                        <th>
                            KODE
                        </th>

                        <th>
                            PRODUK
                        </th>

                        <th>
                            KATEGORI
                        </th>

                        <th>
                            HARGA BELI
                        </th>

                        <th>
                            HARGA JUAL
                        </th>

                        <th>
                            STOK
                        </th>

                        <th>
                            KONDISI STOK
                        </th>

                        <th>
                            STATUS
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach (
                        $produk_list
                        as $produk
                    ): ?>


                        <?php

                        $stok =
                            (int) $produk["stok"];

                        $stok_minimum =
                            (int) $produk["stok_minimum"];


                        if (
                            $produk["status"] !== "Aktif"
                        ) {

                            $stockClass =
                                "stock-nonaktif";

                            $stockLabel =
                                "NONAKTIF";

                        } elseif (
                            $stok <= 0
                        ) {

                            $stockClass =
                                "stock-habis";

                            $stockLabel =
                                "HABIS";

                        } elseif (
                            $stok <= $stok_minimum
                        ) {

                            $stockClass =
                                "stock-menipis";

                            $stockLabel =
                                "MENIPIS";

                        } else {

                            $stockClass =
                                "stock-aman";

                            $stockLabel =
                                "AMAN";
                        }


                        $productStatusClass =
                            $produk["status"] === "Aktif"

                                ? "product-active"

                                : "product-inactive";

                        ?>


                        <tr>


                            <!-- KODE -->

                            <td class="code">

                                <?= htmlspecialchars(
                                    $produk["kode_produk"]
                                ); ?>

                            </td>


                            <!-- PRODUK -->

                            <td>

                                <div class="product-name">

                                    <?= htmlspecialchars(
                                        $produk["nama_produk"]
                                    ); ?>

                                </div>


                                <div
                                    class="category"
                                    style="margin-top:5px;"
                                >

                                    per
                                    <?= htmlspecialchars(
                                        $produk["satuan"]
                                    ); ?>

                                </div>

                            </td>


                            <!-- KATEGORI -->

                            <td class="category">

                                <?= htmlspecialchars(
                                    $produk["nama_kategori"]
                                    ?? "Tanpa kategori"
                                ); ?>

                            </td>


                            <!-- HARGA BELI -->

                            <td class="number">

                                Rp <?= number_format(
                                    $produk["harga_beli"],
                                    0,
                                    ",",
                                    "."
                                ); ?>

                            </td>


                            <!-- HARGA JUAL -->

                            <td class="number">

                                Rp <?= number_format(
                                    $produk["harga_jual"],
                                    0,
                                    ",",
                                    "."
                                ); ?>

                            </td>


                            <!-- STOK -->

                            <td class="number">


                                <div class="stock-value">

                                    <?= $stok; ?>

                                    <?= htmlspecialchars(
                                        $produk["satuan"]
                                    ); ?>

                                </div>


                                <div class="stock-minimum">

                                    Minimum:
                                    <?= $stok_minimum; ?>

                                </div>


                            </td>


                            <!-- KONDISI -->

                            <td>

                                <span
                                    class="stock-status <?= $stockClass; ?>"
                                >

                                    <?= $stockLabel; ?>

                                </span>

                            </td>


                            <!-- STATUS PRODUK -->

                            <td>

                                <span
                                    class="product-status <?= $productStatusClass; ?>"
                                >

                                    <?= htmlspecialchars(
                                        $produk["status"]
                                    ); ?>

                                </span>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>


            </table>


        </section>


    <?php else: ?>


        <section class="table-container">

            <div class="empty">

                Belum ada produk dalam sistem.

            </div>

        </section>


    <?php endif; ?>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>