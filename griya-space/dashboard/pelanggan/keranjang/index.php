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
        id_pelanggan
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


if (
    $resultPelanggan->num_rows !== 1
) {

    $stmt->close();
    $conn->close();

    die(
        "Data pelanggan untuk akun ini tidak ditemukan."
    );
}


$dataPelanggan =
    $resultPelanggan->fetch_assoc();


$id_pelanggan =
    (int) $dataPelanggan["id_pelanggan"];


$stmt->close();


// ======================================================
// PROSES PERUBAHAN JUMLAH
// ======================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    $id_keranjang =
        (int) (
            $_POST["id_keranjang"]
            ?? 0
        );


    $jumlah_input =
        (int) (
            $_POST["jumlah"]
            ?? 1
        );


    $aksi =
        $_POST["aksi"]
        ?? "set";


    if (
        $id_keranjang > 0
    ) {

        // ----------------------------------------------
        // AMBIL DATA KERANJANG + STOK
        // ----------------------------------------------

        $stmt =
            $conn->prepare("
                SELECT
                    k.id_keranjang,
                    k.id_produk,
                    k.jumlah,
                    p.stok,
                    p.status
                FROM keranjang k

                INNER JOIN produk p
                    ON k.id_produk = p.id_produk

                WHERE k.id_keranjang = ?
                  AND k.id_pelanggan = ?

                LIMIT 1
            ");


        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $id_keranjang,
                $id_pelanggan
            );


            $stmt->execute();


            $resultItem =
                $stmt->get_result();


            if (
                $resultItem->num_rows === 1
            ) {

                $item =
                    $resultItem->fetch_assoc();


                $jumlah_sekarang =
                    (int) $item["jumlah"];


                $stok =
                    (int) $item["stok"];


                // --------------------------------------
                // TENTUKAN JUMLAH BARU
                // --------------------------------------

                if (
                    $aksi === "increase"
                ) {

                    $jumlah_baru =
                        $jumlah_sekarang + 1;

                } elseif (
                    $aksi === "decrease"
                ) {

                    $jumlah_baru =
                        $jumlah_sekarang - 1;

                } else {

                    $jumlah_baru =
                        $jumlah_input;
                }


                // --------------------------------------
                // BATAS MINIMUM
                // --------------------------------------

                if (
                    $jumlah_baru < 1
                ) {

                    $jumlah_baru = 1;
                }


                // --------------------------------------
                // BATAS MAKSIMUM STOK
                // --------------------------------------

                if (
                    $stok > 0 &&
                    $jumlah_baru > $stok
                ) {

                    $jumlah_baru = $stok;
                }


                // --------------------------------------
                // UPDATE
                // --------------------------------------

                if (
                    $stok > 0 &&
                    $jumlah_baru >= 1 &&
                    strtolower(
                        $item["status"]
                    ) === "aktif"
                ) {

                    $update =
                        $conn->prepare("
                            UPDATE keranjang
                            SET
                                jumlah = ?
                            WHERE id_keranjang = ?
                              AND id_pelanggan = ?
                        ");


                    if ($update) {

                        $update->bind_param(
                            "iii",
                            $jumlah_baru,
                            $id_keranjang,
                            $id_pelanggan
                        );


                        $update->execute();


                        $update->close();
                    }
                }
            }


            $stmt->close();
        }
    }


    // ----------------------------------------------
    // KEMBALI KE KERANJANG
    // ----------------------------------------------

    header(
        "Location: index.php"
    );

    exit;
}


// ======================================================
// AMBIL DATA KERANJANG
// ======================================================

$stmt = $conn->prepare("
    SELECT
        k.id_keranjang,
        k.id_produk,
        k.jumlah,

        p.kode_produk,
        p.nama_produk,
        p.satuan,
        p.harga_jual,
        p.stok,
        p.status

    FROM keranjang k

    INNER JOIN produk p
        ON k.id_produk = p.id_produk

    WHERE k.id_pelanggan = ?

    ORDER BY
        k.id_keranjang ASC
");


if (!$stmt) {
    die(
        "Gagal mengambil data keranjang: " .
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


// ======================================================
// SIAPKAN DATA
// ======================================================

$items = [];

$total = 0;

$jumlah_item = 0;


while (
    $item =
    $result->fetch_assoc()
) {

    $jumlah =
        (int) $item["jumlah"];


    $harga =
        (float) $item["harga_jual"];


    $stok =
        (int) $item["stok"];


    // ----------------------------------------------
    // JAGA AGAR JUMLAH TIDAK MELEBIHI STOK
    // ----------------------------------------------

    if (
        $stok > 0 &&
        $jumlah > $stok
    ) {

        $jumlah =
            $stok;
    }


    if (
        $jumlah < 1 &&
        $stok > 0
    ) {

        $jumlah =
            1;
    }


    $subtotal =
        $jumlah * $harga;


    $item["jumlah"] =
        $jumlah;


    $item["subtotal"] =
        $subtotal;


    $items[] =
        $item;


    $total +=
        $subtotal;


    $jumlah_item +=
        $jumlah;
}


$stmt->close();

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
        Keranjang | Griya Space
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

            gap: 15px;
        }


        .role {

            font-size: 12px;

            letter-spacing: 2px;

            opacity: .7;
        }


        .back-link {

            color: white;

            text-decoration: none;

            border: 1px solid #666;

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
        }


        /* ==================================================
           CART
        ================================================== */

        .cart-container {

            background: white;

            border: 1px solid #e4e4e4;
        }


        .cart-header {

            padding: 20px 25px;

            border-bottom: 1px solid #e4e4e4;

            display: flex;

            justify-content: space-between;

            align-items: center;
        }


        .cart-header-title {

            font-size: 12px;

            letter-spacing: 2px;

            color: #777;
        }


        .item-count {

            font-size: 12px;

            color: #777;
        }


        .cart-item {

            padding: 25px;

            display: grid;

            grid-template-columns:
                1fr
                auto
                auto;

            gap: 30px;

            align-items: center;

            border-bottom: 1px solid #eeeeee;
        }


        .product-name {

            font-size: 18px;
        }


        .product-code {

            font-size: 11px;

            color: #999;

            margin-top: 6px;
        }


        .price {

            color: #555;

            font-size: 13px;

            margin-top: 8px;
        }


        /* ==================================================
           QUANTITY CONTROL
        ================================================== */

        .quantity-control {

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 0;

            border: 1px solid #d8d8d5;

            background: white;

            width: fit-content;
        }


        .quantity-form {

            margin: 0;
        }


        .quantity-button {

            width: 38px;

            height: 38px;

            border: none;

            background: #f4f4f2;

            color: #1d1d1d;

            font-size: 18px;

            cursor: pointer;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: .15s;
        }


        .quantity-button:hover {

            background: #e7e7e4;
        }


        .quantity-button:disabled {

            opacity: .35;

            cursor: not-allowed;
        }


        .quantity-input {

            width: 58px;

            height: 38px;

            border: none;

            border-left: 1px solid #d8d8d5;

            border-right: 1px solid #d8d8d5;

            background: white;

            text-align: center;

            font-size: 14px;

            font-weight: 600;

            outline: none;
        }


        .quantity-input:focus {

            background: #fafafa;
        }


        .stock-info {

            margin-top: 7px;

            color: #999;

            font-size: 9px;

            text-align: center;

            white-space: nowrap;
        }


        .stock-danger {

            color: #8a4444;
        }


        .subtotal {

            font-weight: bold;

            min-width: 150px;

            text-align: right;

            white-space: nowrap;
        }


        /* ==================================================
           FOOTER
        ================================================== */

        .cart-footer {

            padding: 25px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;
        }


        .total-label {

            color: #777;

            font-size: 12px;

            letter-spacing: 2px;
        }


        .total-price {

            font-size: 28px;

            font-weight: bold;

            margin-top: 5px;
        }


        .checkout-button {

            display: inline-block;

            background: #1d1d1d;

            color: white;

            text-decoration: none;

            padding: 14px 22px;

            font-size: 12px;

            letter-spacing: 1px;
        }


        .checkout-button:hover {

            background: #333;
        }


        .checkout-disabled {

            opacity: .45;

            pointer-events: none;
        }


        /* ==================================================
           EMPTY
        ================================================== */

        .empty-cart {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 70px 25px;

            text-align: center;
        }


        .empty-cart h2 {

            font-size: 24px;

            font-weight: 400;
        }


        .empty-cart p {

            color: #777;

            margin-top: 10px;

            line-height: 1.5;
        }


        .catalog-button {

            display: inline-block;

            margin-top: 25px;

            background: #1d1d1d;

            color: white;

            text-decoration: none;

            padding: 13px 22px;

            font-size: 12px;

            letter-spacing: 1px;
        }


        /* ==================================================
           MOBILE
        ================================================== */

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


            main {

                padding: 35px 18px;
            }


            .page-header h1 {

                font-size: 28px;
            }


            .cart-item {

                grid-template-columns: 1fr;

                gap: 16px;
            }


            .quantity-control {

                justify-content: flex-start;
            }


            .subtotal {

                text-align: left;

                min-width: auto;
            }


            .cart-footer {

                flex-direction: column;

                align-items: flex-start;
            }


            .checkout-button {

                width: 100%;

                text-align: center;
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

        <small>
            KERANJANG BELANJA
        </small>


        <h1>
            Keranjang Anda
        </h1>


        <p>
            Periksa kembali produk yang ingin Anda beli.
        </p>

    </section>


    <?php if (empty($items)): ?>


        <div class="empty-cart">

            <h2>
                Keranjang masih kosong
            </h2>


            <p>
                Belum ada produk yang ditambahkan ke keranjang.
            </p>


            <a
                href="../katalog/index.php"
                class="catalog-button"
            >
                LIHAT KATALOG
            </a>

        </div>


    <?php else: ?>


        <section class="cart-container">


            <div class="cart-header">

                <span class="cart-header-title">
                    DAFTAR PRODUK
                </span>


                <span class="item-count">

                    <?= $jumlah_item; ?>

                    item

                </span>

            </div>


            <?php foreach (
                $items
                as $item
            ): ?>


                <?php

                $stok =
                    (int) $item["stok"];

                $jumlah =
                    (int) $item["jumlah"];

                $stok_habis =
                    $stok <= 0;

                $stok_maks =
                    (
                        $jumlah >= $stok
                        && !$stok_habis
                    );

                ?>


                <div class="cart-item">


                    <div>

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


                        <div class="price">

                            Rp <?= number_format(
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


                    <!-- ==================================================
                         QUANTITY
                    ================================================== -->

                    <div>


                        <div class="quantity-control">


                            <!-- KURANG -->

                            <form
                                action="index.php"
                                method="POST"
                                class="quantity-form"
                            >

                                <input
                                    type="hidden"
                                    name="id_keranjang"
                                    value="<?= (int) $item[
                                        "id_keranjang"
                                    ]; ?>"
                                >


                                <input
                                    type="hidden"
                                    name="aksi"
                                    value="decrease"
                                >


                                <button
                                    type="submit"
                                    class="quantity-button"
                                    <?= (
                                        $jumlah <= 1 ||
                                        $stok_habis
                                    )
                                        ? "disabled"
                                        : ""
                                    ?>
                                >
                                    −
                                </button>

                            </form>


                            <!-- INPUT JUMLAH -->

                            <form
                                action="index.php"
                                method="POST"
                                class="quantity-form"
                                id="form-<?= (int) $item[
                                    "id_keranjang"
                                ]; ?>"
                            >

                                <input
                                    type="hidden"
                                    name="id_keranjang"
                                    value="<?= (int) $item[
                                        "id_keranjang"
                                    ]; ?>"
                                >


                                <input
                                    type="hidden"
                                    name="aksi"
                                    value="set"
                                >


                                <input
                                    type="number"
                                    name="jumlah"
                                    class="quantity-input"
                                    value="<?= $jumlah; ?>"
                                    min="1"
                                    max="<?= max(
                                        1,
                                        $stok
                                    ); ?>"
                                    inputmode="numeric"
                                    onchange="
                                        this.form.submit();
                                    "
                                    oninput="
                                        const max = <?= max(
                                            1,
                                            $stok
                                        ); ?>;
                                        if (
                                            this.value !== '' &&
                                            Number(this.value) > max
                                        ) {
                                            this.value = max;
                                        }
                                        if (
                                            this.value !== '' &&
                                            Number(this.value) < 1
                                        ) {
                                            this.value = 1;
                                        }
                                    "
                                    aria-label="Jumlah produk"
                                >

                            </form>


                            <!-- TAMBAH -->

                            <form
                                action="index.php"
                                method="POST"
                                class="quantity-form"
                            >

                                <input
                                    type="hidden"
                                    name="id_keranjang"
                                    value="<?= (int) $item[
                                        "id_keranjang"
                                    ]; ?>"
                                >


                                <input
                                    type="hidden"
                                    name="aksi"
                                    value="increase"
                                >


                                <button
                                    type="submit"
                                    class="quantity-button"
                                    <?= (
                                        $stok_habis ||
                                        $stok_maks
                                    )
                                        ? "disabled"
                                        : ""
                                    ?>
                                >
                                    +
                                </button>

                            </form>


                        </div>


                        <div
                            class="
                                stock-info
                                <?= (
                                    $stok_maks
                                    || $stok_habis
                                )
                                    ? "stock-danger"
                                    : ""
                                ?>
                            "
                        >

                            Stok:
                            <?= $stok; ?>

                            <?= htmlspecialchars(
                                $item["satuan"]
                            ); ?>

                            <?php if (
                                $stok_maks
                            ): ?>

                                · Maksimal

                            <?php endif; ?>


                            <?php if (
                                $stok_habis
                            ): ?>

                                · Habis

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- ==================================================
                         SUBTOTAL
                    ================================================== -->

                    <div class="subtotal">

                        Rp <?= number_format(
                            $item["subtotal"],
                            0,
                            ",",
                            "."
                        ); ?>

                    </div>


                </div>


            <?php endforeach; ?>


            <?php

            $ada_stok_habis =
                false;


            $ada_jumlah_invalid =
                false;


            foreach (
                $items
                as $checkItem
            ) {

                $checkStok =
                    (int) $checkItem["stok"];

                $checkJumlah =
                    (int) $checkItem["jumlah"];


                if (
                    $checkStok <= 0
                ) {

                    $ada_stok_habis =
                        true;

                }


                if (
                    $checkStok > 0 &&
                    $checkJumlah > $checkStok
                ) {

                    $ada_jumlah_invalid =
                        true;
                }
            }


            $checkout_disabled =
                (
                    $ada_stok_habis ||
                    $ada_jumlah_invalid
                );

            ?>


            <div class="cart-footer">


                <div>

                    <div class="total-label">
                        TOTAL BELANJA
                    </div>


                    <div class="total-price">

                        Rp <?= number_format(
                            $total,
                            0,
                            ",",
                            "."
                        ); ?>

                    </div>

                </div>


                <?php if (
                    !$checkout_disabled
                ): ?>

                    <a
                        href="../checkout/index.php"
                        class="checkout-button"
                    >
                        LANJUT KE CHECKOUT
                    </a>

                <?php else: ?>

                    <div
                        class="
                            checkout-button
                            checkout-disabled
                        "
                    >
                        STOK TIDAK TERSEDIA
                    </div>

                <?php endif; ?>


            </div>


        </section>


    <?php endif; ?>


</main>


</body>

</html>

<?php

mysqli_close($conn);

?>