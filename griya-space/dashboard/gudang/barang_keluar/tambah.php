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

if (
    $_SESSION["role"] !== "admin" &&
    $_SESSION["role"] !== "gudang"
) {
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL PRODUK AKTIF
// ======================================================

$produkQuery = mysqli_query(
    $conn,
    "
    SELECT
        id_produk,
        kode_produk,
        nama_produk,
        satuan,
        stok
    FROM produk
    WHERE status = 'Aktif'
    ORDER BY nama_produk ASC
    "
);

if (!$produkQuery) {
    die("Gagal mengambil data produk.");
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

    <title>Tambah Barang Keluar | Griya Space</title>


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
            max-width: 900px;

            margin: 0 auto;

            padding: 45px 30px;
        }


        /* ==================================================
           PAGE HEADER
        ================================================== */

        .page-header {
            margin-bottom: 28px;
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

            line-height: 1.6;
        }


        /* ==================================================
           FORM CARD
        ================================================== */

        .form-card {
            background: white;

            border: 1px solid #e4e4e4;

            padding: 32px;
        }


        .form-group {
            margin-bottom: 22px;
        }


        .form-group label {
            display: block;

            margin-bottom: 8px;

            font-size: 11px;

            font-weight: 600;

            letter-spacing: .5px;
        }


        .required {
            color: #a33;
        }


        input,
        select,
        textarea {
            width: 100%;

            border: 1px solid #d8d8d5;

            background: #fafafa;

            color: #222;

            border-radius: 6px;

            outline: none;

            font-family: inherit;

            font-size: 12px;

            transition:
                border-color .2s ease,
                box-shadow .2s ease;
        }


        input,
        select {
            height: 46px;

            padding: 0 13px;
        }


        textarea {
            min-height: 120px;

            padding: 13px;

            resize: vertical;
        }


        input:focus,
        select:focus,
        textarea:focus {
            background: #fff;

            border-color: #555;

            box-shadow:
                0 0 0 3px rgba(0,0,0,.035);
        }


        input[readonly] {
            background: #f0f0ee;

            color: #777;

            cursor: not-allowed;
        }


        .hint {
            margin-top: 6px;

            color: #999;

            font-size: 10px;

            line-height: 1.5;
        }


        /* ==================================================
           INFO PRODUK
        ================================================== */

        .product-info {
            display: grid;

            grid-template-columns: repeat(3, 1fr);

            gap: 12px;

            margin-top: 10px;
        }


        .info-box {
            background: #f7f7f5;

            border: 1px solid #e4e4e4;

            padding: 15px;
        }


        .info-label {
            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1px;

            margin-bottom: 6px;
        }


        .info-value {
            font-size: 14px;

            font-weight: 600;
        }


        .stock-warning {
            color: #9a5a18;
        }


        .stock-safe {
            color: #3d6c43;
        }


        /* ==================================================
           WARNING
        ================================================== */

        .warning {
            display: none;

            margin-top: 14px;

            padding: 12px 14px;

            background: #faf0f0;

            border: 1px solid #ead0d0;

            border-left: 4px solid #8b3030;

            color: #8b3030;

            font-size: 11px;

            line-height: 1.5;
        }


        /* ==================================================
           ACTION
        ================================================== */

        .form-actions {
            display: flex;

            justify-content: flex-end;

            align-items: center;

            gap: 10px;

            margin-top: 30px;

            padding-top: 20px;

            border-top: 1px solid #eeeeee;
        }


        .button {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 140px;

            height: 44px;

            padding: 0 18px;

            border-radius: 6px;

            font-size: 11px;

            font-weight: 600;

            letter-spacing: 1px;

            text-decoration: none;

            cursor: pointer;
        }


        .button-secondary {
            background: #f2f2f0;

            color: #555;

            border: 1px solid #ddd;
        }


        .button-primary {
            background: #1d1d1d;

            color: #fff;

            border: 1px solid #1d1d1d;
        }


        .button-primary:hover {
            background: #333;
        }


        .button-primary:disabled {
            background: #aaa;

            border-color: #aaa;

            cursor: not-allowed;
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


            main {
                padding: 30px 18px;
            }


            .page-header h1 {
                font-size: 28px;
            }


            .form-card {
                padding: 22px 18px;
            }


            .product-info {
                grid-template-columns: 1fr;
            }


            .form-actions {
                flex-direction: column-reverse;

                align-items: stretch;
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

            <?= strtoupper(
                htmlspecialchars(
                    $_SESSION["role"]
                )
            ); ?>

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
            MANAJEMEN PERSEDIAAN
        </small>

        <h1>
            Tambah Barang Keluar
        </h1>

        <p>
            Catat barang yang keluar dari persediaan Griya Space.
        </p>

    </section>


    <form
        action="proses_tambah.php"
        method="POST"
        class="form-card"
        id="barangKeluarForm"
    >


        <!-- ==================================================
             NOMOR TRANSAKSI
        ================================================== -->

        <div class="form-group">

            <label for="nomor_keluar">
                Nomor Barang Keluar
                <span class="required">*</span>
            </label>


            <input
                type="text"
                id="nomor_keluar"
                name="nomor_keluar"
                value="AUTO"
                readonly
            >


            <div class="hint">
                Nomor transaksi akan dibuat otomatis oleh sistem.
            </div>

        </div>


        <!-- ==================================================
             TANGGAL
        ================================================== -->

        <div class="form-group">

            <label for="tanggal_keluar">

                Tanggal Keluar

                <span class="required">*</span>

            </label>


            <input
                type="datetime-local"
                id="tanggal_keluar"
                name="tanggal_keluar"
                value="<?= date('Y-m-d\TH:i'); ?>"
                required
            >

        </div>


        <!-- ==================================================
             PRODUK
        ================================================== -->

        <div class="form-group">

            <label for="id_produk">

                Produk

                <span class="required">*</span>

            </label>


            <select
                id="id_produk"
                name="id_produk"
                required
            >

                <option value="">
                    Pilih produk
                </option>


                <?php while (
                    $produk =
                    mysqli_fetch_assoc($produkQuery)
                ): ?>

                    <option
                        value="<?= (int) $produk["id_produk"]; ?>"
                        data-stok="<?= (int) $produk["stok"]; ?>"
                        data-satuan="<?= htmlspecialchars(
                            $produk["satuan"]
                        ); ?>"
                    >

                        <?= htmlspecialchars(
                            $produk["kode_produk"]
                        ); ?>

                        -
                        <?= htmlspecialchars(
                            $produk["nama_produk"]
                        ); ?>

                    </option>

                <?php endwhile; ?>

            </select>


            <div class="hint">
                Pilih produk yang akan dikeluarkan dari stok.
            </div>


            <div class="product-info">


                <div class="info-box">

                    <span class="info-label">
                        STOK SAAT INI
                    </span>

                    <span
                        class="info-value"
                        id="stokPreview"
                    >
                        -
                    </span>

                </div>


                <div class="info-box">

                    <span class="info-label">
                        SATUAN
                    </span>

                    <span
                        class="info-value"
                        id="satuanPreview"
                    >
                        -
                    </span>

                </div>


                <div class="info-box">

                    <span class="info-label">
                        JUMLAH KELUAR
                    </span>

                    <span
                        class="info-value"
                        id="jumlahPreview"
                    >
                        0
                    </span>

                </div>


            </div>


            <div
                class="warning"
                id="stockWarning"
            >
                Jumlah barang keluar tidak boleh melebihi stok yang tersedia.
            </div>

        </div>


        <!-- ==================================================
             JUMLAH
        ================================================== -->

        <div class="form-group">

            <label for="jumlah">

                Jumlah Keluar

                <span class="required">*</span>

            </label>


            <input
                type="number"
                id="jumlah"
                name="jumlah"
                min="1"
                value="1"
                required
            >


            <div class="hint">
                Masukkan jumlah barang yang benar-benar keluar dari persediaan.
            </div>

        </div>


        <!-- ==================================================
             KETERANGAN
        ================================================== -->

        <div class="form-group">

            <label for="keterangan">
                Keterangan
            </label>


            <textarea
                id="keterangan"
                name="keterangan"
                maxlength="500"
                placeholder="Contoh: Barang keluar untuk kebutuhan proyek."
            ></textarea>

        </div>


        <!-- ==================================================
             ACTION
        ================================================== -->

        <div class="form-actions">


            <a
                href="index.php"
                class="button button-secondary"
            >
                BATAL
            </a>


            <button
                type="submit"
                class="button button-primary"
                id="submitButton"
            >
                SIMPAN BARANG KELUAR
            </button>


        </div>


    </form>


</main>


<script>

    const produkSelect =
        document.getElementById("id_produk");

    const jumlahInput =
        document.getElementById("jumlah");

    const stokPreview =
        document.getElementById("stokPreview");

    const satuanPreview =
        document.getElementById("satuanPreview");

    const jumlahPreview =
        document.getElementById("jumlahPreview");

    const stockWarning =
        document.getElementById("stockWarning");

    const submitButton =
        document.getElementById("submitButton");


    let stokSaatIni = 0;


    function updateInfo() {

        const option =
            produkSelect.options[
                produkSelect.selectedIndex
            ];


        stokSaatIni =
            Number(
                option.dataset.stok || 0
            );


        const satuan =
            option.dataset.satuan || "-";


        const jumlah =
            Number(
                jumlahInput.value || 0
            );


        stokPreview.textContent =
            stokSaatIni > 0
                ? stokSaatIni
                : "-";


        satuanPreview.textContent =
            satuan;


        jumlahPreview.textContent =
            jumlah;


        if (
            produkSelect.value !== "" &&
            jumlah > stokSaatIni
        ) {

            stockWarning.style.display =
                "block";

            submitButton.disabled =
                true;

        } else {

            stockWarning.style.display =
                "none";

            submitButton.disabled =
                false;

        }

    }


    produkSelect.addEventListener(
        "change",
        updateInfo
    );


    jumlahInput.addEventListener(
        "input",
        updateInfo
    );


    updateInfo();

</script>


</body>

</html>


<?php

mysqli_close($conn);

?>