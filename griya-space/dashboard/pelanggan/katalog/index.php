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

$nama = $_SESSION["nama"] ?? "Pelanggan";

$id_user = (int) $_SESSION["user_id"];


// ======================================================
// AMBIL PESAN
// ======================================================

$cart_success = $_SESSION["cart_success"] ?? "";
$cart_error = $_SESSION["cart_error"] ?? "";

unset($_SESSION["cart_success"]);
unset($_SESSION["cart_error"]);


// ======================================================
// AMBIL DATA PRODUK
// ======================================================

$sql = "
    SELECT
        p.id_produk,
        p.kode_produk,
        p.nama_produk,
        p.satuan,
        p.harga_jual,
        p.stok,
        p.deskripsi,
        p.foto_produk,
        k.nama_kategori
    FROM produk p

    LEFT JOIN kategori k
        ON p.id_kategori = k.id_kategori

    WHERE p.status = 'Aktif'

    ORDER BY p.nama_produk ASC
";


$result = mysqli_query($conn, $sql);


if (!$result) {
    die(
        "Gagal mengambil data produk: " .
        mysqli_error($conn)
    );
}


// ======================================================
// HITUNG JUMLAH ITEM KERANJANG DARI DATABASE
// ======================================================

$stmt_cart = $conn->prepare("
    SELECT
        COALESCE(SUM(k.jumlah), 0) AS jumlah_item
    FROM keranjang k

    INNER JOIN pelanggan p
        ON k.id_pelanggan = p.id_pelanggan

    WHERE p.id_user = ?
");


if (!$stmt_cart) {
    die(
        "Gagal mengambil jumlah keranjang: " .
        $conn->error
    );
}


$stmt_cart->bind_param(
    "i",
    $id_user
);


$stmt_cart->execute();


$result_cart =
    $stmt_cart->get_result();


$data_cart =
    $result_cart->fetch_assoc();


$jumlah_keranjang =
    (int) ($data_cart["jumlah_item"] ?? 0);


$stmt_cart->close();

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
        Katalog Produk | Griya Space
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

            padding: 22px 40px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .brand {

            letter-spacing: 5px;

            font-size: 18px;

        }


        .header-right {

            display: flex;

            align-items: center;

            gap: 25px;

        }


        .role {

            font-size: 12px;

            letter-spacing: 2px;

            opacity: .7;

        }


        .cart-link {

            color: white;

            text-decoration: none;

            border: 1px solid #666;

            padding: 9px 16px;

            font-size: 12px;

            transition: .2s;

        }


        .cart-link:hover {

            background: white;

            color: #1d1d1d;

        }


        .back-link {

            color: white;

            text-decoration: none;

            border: 1px solid #666;

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

            max-width: 1200px;

            margin: 0 auto;

            padding: 50px 25px;

        }


        .page-header {

            margin-bottom: 35px;

        }


        .page-header small {

            color: #777;

            letter-spacing: 3px;

            font-size: 11px;

        }


        .page-header h1 {

            font-size: 34px;

            font-weight: 400;

            margin-top: 10px;

        }


        .page-header p {

            color: #777;

            margin-top: 10px;

            line-height: 1.5;

        }


        /* ==================================================
           PESAN
        ================================================== */

        .message {

            padding: 14px 18px;

            margin-bottom: 25px;

            background: white;

            border: 1px solid #e4e4e4;

            font-size: 13px;

        }


        .success {

            border-left: 4px solid #333;

        }


        .error {

            border-left: 4px solid #999;

        }


        /* ==================================================
           PRODUCT GRID
        ================================================== */

        .product-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

        }


        .product-card {

            background: white;

            border: 1px solid #e4e4e4;

            overflow: hidden;

        }


        .product-image {

            height: 210px;

            background: #eeeeec;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #999;

            font-size: 12px;

        }


        .product-image img {

            width: 100%;

            height: 100%;

            object-fit: cover;

        }


        .product-content {

            padding: 22px;

        }


        .category {

            color: #888;

            font-size: 11px;

            letter-spacing: 1.5px;

            text-transform: uppercase;

        }


        .product-name {

            font-size: 19px;

            margin-top: 8px;

            line-height: 1.3;

            font-weight: 500;

        }


        .description {

            color: #777;

            font-size: 13px;

            line-height: 1.5;

            margin-top: 10px;

            min-height: 40px;

        }


        .product-bottom {

            margin-top: 20px;

            display: flex;

            justify-content: space-between;

            align-items: end;

        }


        .price {

            font-size: 18px;

            font-weight: bold;

        }


        .unit {

            color: #888;

            font-size: 11px;

            margin-top: 4px;

        }


        .stock {

            font-size: 11px;

            color: #777;

            text-align: right;

        }


        /* ==================================================
           BUTTON TAMBAH
        ================================================== */

        .add-cart-form {

            margin-top: 18px;

        }


        .add-cart-button {

            width: 100%;

            border: none;

            background: #1d1d1d;

            color: white;

            padding: 13px 15px;

            font-size: 11px;

            letter-spacing: 1px;

            cursor: pointer;

            transition: .2s;

        }


        .add-cart-button:hover {

            background: #333;

        }


        .add-cart-button:disabled {

            background: #aaa;

            cursor: not-allowed;

        }


        .empty {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 50px;

            text-align: center;

            color: #777;

        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 1000px) {

            .product-grid {

                grid-template-columns:
                    repeat(3, 1fr);

            }

        }


        @media (max-width: 900px) {

            .product-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 650px) {

            header {

                padding: 20px;

            }


            .header-right {

                gap: 8px;

            }


            .role {

                display: none;

            }


            .cart-link,
            .back-link {

                padding: 8px 10px;

                font-size: 10px;

            }


            main {

                padding: 35px 18px;

            }


            .page-header h1 {

                font-size: 28px;

            }


            .product-grid {

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
            class="cart-link"
        >
            KERANJANG
            (<?= $jumlah_keranjang; ?>)
        </a>


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


        <small>
            KATALOG PRODUK
        </small>


        <h1>
            Material Bangunan
        </h1>


        <p>
            Temukan berbagai material bangunan
            yang tersedia di Griya Space.
        </p>


    </section>


    <?php if ($cart_success !== ""): ?>

        <div class="message success">

            <?= htmlspecialchars(
                $cart_success
            ); ?>

        </div>

    <?php endif; ?>


    <?php if ($cart_error !== ""): ?>

        <div class="message error">

            <?= htmlspecialchars(
                $cart_error
            ); ?>

        </div>

    <?php endif; ?>


    <?php if (mysqli_num_rows($result) > 0): ?>


        <section class="product-grid">


            <?php while (
                $produk =
                mysqli_fetch_assoc($result)
            ): ?>


                <article class="product-card">


                    <div class="product-image">


                        <?php if (
                            !empty(
                                $produk["foto_produk"]
                            )
                        ): ?>


                            <img
                                src="../../../uploads/produk/<?= htmlspecialchars(
                                    $produk["foto_produk"]
                                ); ?>"
                                alt="<?= htmlspecialchars(
                                    $produk["nama_produk"]
                                ); ?>"
                            >


                        <?php else: ?>


                            Tidak ada foto


                        <?php endif; ?>


                    </div>


                    <div class="product-content">


                        <div class="category">

                            <?= htmlspecialchars(
                                $produk["nama_kategori"]
                                ?? "Tanpa kategori"
                            ); ?>

                        </div>


                        <h2 class="product-name">

                            <?= htmlspecialchars(
                                $produk["nama_produk"]
                            ); ?>

                        </h2>


                        <p class="description">

                            <?= htmlspecialchars(
                                $produk["deskripsi"]
                                ?? ""
                            ); ?>

                        </p>


                        <div class="product-bottom">


                            <div>


                                <div class="price">

                                    Rp <?= number_format(
                                        $produk["harga_jual"],
                                        0,
                                        ",",
                                        "."
                                    ); ?>

                                </div>


                                <div class="unit">

                                    per

                                    <?= htmlspecialchars(
                                        $produk["satuan"]
                                    ); ?>

                                </div>


                            </div>


                            <div class="stock">

                                Stok

                                <br>

                                <?= (int) $produk["stok"]; ?>

                            </div>


                        </div>


                        <?php if (
                            (int) $produk["stok"] > 0
                        ): ?>


                            <form
                                action="../keranjang/tambah.php"
                                method="POST"
                                class="add-cart-form"
                            >


                                <input
                                    type="hidden"
                                    name="id_produk"
                                    value="<?= (int) $produk["id_produk"]; ?>"
                                >


                                <button
                                    type="submit"
                                    class="add-cart-button"
                                >
                                    TAMBAH KE KERANJANG
                                </button>


                            </form>


                        <?php else: ?>


                            <form
                                class="add-cart-form"
                            >


                                <button
                                    type="button"
                                    class="add-cart-button"
                                    disabled
                                >
                                    STOK HABIS
                                </button>


                            </form>


                        <?php endif; ?>


                    </div>


                </article>


            <?php endwhile; ?>


        </section>


    <?php else: ?>


        <div class="empty">

            Belum ada produk yang tersedia.

        </div>


    <?php endif; ?>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>