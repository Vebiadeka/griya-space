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
// CEK ROLE ADMIN
// ======================================================

if ($_SESSION["role"] !== "admin") {
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
        p.harga_beli,
        p.harga_jual,
        p.stok,
        p.stok_minimum,
        p.status,
        k.nama_kategori,
        s.nama_supplier
    FROM produk p

    LEFT JOIN kategori k
        ON p.id_kategori = k.id_kategori

    LEFT JOIN supplier s
        ON p.id_supplier = s.id_supplier

    ORDER BY p.id_produk DESC
";


$result = mysqli_query($conn, $sql);


if (!$result) {
    die("Gagal mengambil data produk: " . mysqli_error($conn));
}


$jumlah_produk = mysqli_num_rows($result);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Data Produk | Griya Space</title>


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

            background: white;

            border: 1px solid #e4e4e4;

            padding: 15px 20px;

            min-width: 160px;

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
           TOOLBAR
        ================================================== */

        .toolbar {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 16px 20px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 18px;

        }


        .toolbar-info {

            color: #777;

            font-size: 12px;

        }


        .add-button {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            background: #1d1d1d;

            color: white;

            padding: 11px 18px;

            font-size: 11px;

            letter-spacing: 1px;

        }


        .add-button:hover {

            background: #333;

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

            min-width: 1000px;

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

            font-weight: 600;

            white-space: nowrap;

        }


        .product-name {

            font-weight: 600;

            min-width: 180px;

        }


        .category,

        .supplier {

            color: #777;

        }


        .money {

            white-space: nowrap;

            font-weight: 600;

        }


        .stock {

            font-weight: 600;

        }


        .stock-low {

            color: #b23b3b;

        }


        .stock-safe {

            color: #444;

        }


        .status {

            display: inline-block;

            padding: 5px 9px;

            font-size: 9px;

            font-weight: 600;

            letter-spacing: .8px;

            border-radius: 20px;

        }


        .status-active {

            background: #eef5ef;

            color: #3d6c43;

        }


        .status-inactive {

            background: #f5eeee;

            color: #8a4444;

        }


        .empty {

            padding: 60px 20px;

            text-align: center;

            color: #777;

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


            .back-link {

                padding: 8px 12px;

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


            .toolbar {

                flex-direction: column;

                align-items: stretch;

            }


            .add-button {

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
            ADMIN
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
         HEADER HALAMAN
    ================================================== -->

    <section class="page-header">


        <div class="page-title">

            <small>
                MANAJEMEN PRODUK
            </small>

            <h1>
                Data Produk
            </h1>

            <p>
                Kelola daftar produk dan persediaan Griya Space.
            </p>

        </div>


        <div class="summary">

            <span class="summary-label">
                TOTAL PRODUK
            </span>

            <span class="summary-value">
                <?= $jumlah_produk; ?>
            </span>

        </div>


    </section>



    <!-- ==================================================
         TOOLBAR
    ================================================== -->

    <section class="toolbar">

        <div class="toolbar-info">

            Menampilkan
            <?= $jumlah_produk; ?>
            produk dari database.

        </div>


        <a
            href="tambah.php"
            class="add-button"
        >
            + TAMBAH PRODUK
        </a>

    </section>



    <!-- ==================================================
         TABLE
    ================================================== -->

    <section class="table-container">


        <?php if ($jumlah_produk > 0): ?>


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
                            SUPPLIER
                        </th>

                        <th>
                            SATUAN
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
                            STATUS
                        </th>

                        <th>
                            AKSI
                        </th>
                    </tr>

                </thead>


                <tbody>


                    <?php while ($produk = mysqli_fetch_assoc($result)): ?>


                        <?php

                        $stok = (int) $produk["stok"];

                        $stok_minimum =
                            (int) $produk["stok_minimum"];

                        $stok_class =
                            ($stok <= $stok_minimum)
                            ? "stock-low"
                            : "stock-safe";

                        ?>


                        <tr>


                            <td class="code">

                                <?= htmlspecialchars(
                                    $produk["kode_produk"]
                                ); ?>

                            </td>


                            <td class="product-name">

                                <?= htmlspecialchars(
                                    $produk["nama_produk"]
                                ); ?>

                            </td>


                            <td class="category">

                                <?= htmlspecialchars(
                                    $produk["nama_kategori"]
                                    ?? "-"
                                ); ?>

                            </td>


                            <td class="supplier">

                                <?= htmlspecialchars(
                                    $produk["nama_supplier"]
                                    ?? "-"
                                ); ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $produk["satuan"]
                                ); ?>

                            </td>


                            <td class="money">

                                Rp <?= number_format(
                                    $produk["harga_beli"],
                                    0,
                                    ",",
                                    "."
                                ); ?>

                            </td>


                            <td class="money">

                                Rp <?= number_format(
                                    $produk["harga_jual"],
                                    0,
                                    ",",
                                    "."
                                ); ?>

                            </td>


                            <td class="stock <?= $stok_class; ?>">

                                <?= $stok; ?>

                                <?php if ($stok <= $stok_minimum): ?>

                                    <br>

                                    <small>
                                        Stok menipis
                                    </small>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?php if ($produk["status"] === "Aktif"): ?>

                                    <span class="status status-active">
                                        AKTIF
                                    </span>

                                <?php else: ?>

                                    <span class="status status-inactive">
                                        NONAKTIF
                                    </span>

                                <?php endif; ?>

                            </td>
<td>

    <div style="
        display:flex;
        align-items:center;
        gap:8px;
    ">

        <a
            href="edit.php?id=<?= (int) $produk["id_produk"]; ?>"
            style="
                display:inline-block;
                padding:7px 10px;
                background:#f0f0ee;
                border:1px solid #d9d9d6;
                color:#333;
                text-decoration:none;
                font-size:10px;
                border-radius:4px;
            "
        >
            EDIT
        </a>


        <form
            action="hapus.php"
            method="POST"
            style="margin:0;"
            onsubmit="return confirm(
                'Yakin ingin menghapus produk ini?'
            );"
        >

            <input
                type="hidden"
                name="id_produk"
                value="<?= (int) $produk["id_produk"]; ?>"
            >

            <button
                type="submit"
                style="
                    padding:7px 10px;
                    background:#1d1d1d;
                    border:1px solid #1d1d1d;
                    color:white;
                    font-size:10px;
                    border-radius:4px;
                    cursor:pointer;
                "
            >
                HAPUS
            </button>

        </form>

    </div>

</td>

                        </tr>


                    <?php endwhile; ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="empty">

                Belum ada produk di database.

            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>