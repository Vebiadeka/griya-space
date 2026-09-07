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
// AMBIL DATA SUPPLIER AKTIF
// ======================================================

$supplierQuery = mysqli_query(
    $conn,
    "
    SELECT
        id_supplier,
        kode_supplier,
        nama_supplier
    FROM supplier
    WHERE status = 'Aktif'
    ORDER BY nama_supplier ASC
    "
);

if (!$supplierQuery) {
    die("Gagal mengambil data supplier.");
}


// ======================================================
// AMBIL DATA PRODUK AKTIF
// ======================================================

$produkQuery = mysqli_query(
    $conn,
    "
    SELECT
        id_produk,
        kode_produk,
        nama_produk,
        satuan,
        harga_beli,
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

    <title>Tambah Barang Masuk | Griya Space</title>


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
            max-width: 950px;

            margin: 0 auto;

            padding: 45px 30px;
        }


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


        .form-grid {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 20px;
        }


        .form-group {
            margin-bottom: 2px;
        }


        .form-group.full {
            grid-column: 1 / -1;
        }


        .section-title {
            grid-column: 1 / -1;

            margin-top: 8px;

            padding-bottom: 10px;

            border-bottom: 1px solid #eeeeee;

            font-size: 11px;

            font-weight: 700;

            letter-spacing: 1.5px;

            color: #555;
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
            min-height: 110px;

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
        }


        .hint {
            margin-top: 6px;

            color: #999;

            font-size: 10px;

            line-height: 1.5;
        }


        /* ==================================================
           RINGKASAN
        ================================================== */

        .summary-box {
            grid-column: 1 / -1;

            background: #f7f7f5;

            border: 1px solid #e4e4e4;

            padding: 20px;
        }


        .summary-row {
            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 8px 0;

            font-size: 12px;

            color: #666;
        }


        .summary-row.total {
            margin-top: 8px;

            padding-top: 15px;

            border-top: 1px solid #ddd;

            color: #1d1d1d;

            font-size: 15px;

            font-weight: 600;
        }


        #subtotalPreview {
            font-weight: 600;
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


            .form-grid {
                grid-template-columns: 1fr;
            }


            .form-group.full,
            .section-title,
            .summary-box {
                grid-column: auto;
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
                htmlspecialchars($_SESSION["role"])
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
            Tambah Barang Masuk
        </h1>

        <p>
            Catat penerimaan barang dari supplier dan jumlah barang yang masuk.
        </p>

    </section>


    <form
        action="proses_tambah.php"
        method="POST"
        class="form-card"
        id="barangMasukForm"
    >


        <div class="form-grid">


            <!-- ==================================================
                 INFORMASI TRANSAKSI
            ================================================== -->

            <div class="section-title">
                INFORMASI TRANSAKSI
            </div>


            <div class="form-group">

                <label for="nomor_masuk">
                    Nomor Barang Masuk
                    <span class="required">*</span>
                </label>


                <input
                    type="text"
                    id="nomor_masuk"
                    name="nomor_masuk"
                    value="AUTO"
                    readonly
                >


                <div class="hint">
                    Nomor transaksi akan dibuat otomatis oleh sistem.
                </div>

            </div>


            <div class="form-group">

                <label for="tanggal_masuk">
                    Tanggal Masuk
                    <span class="required">*</span>
                </label>


                <input
                    type="datetime-local"
                    id="tanggal_masuk"
                    name="tanggal_masuk"
                    value="<?= date('Y-m-d\TH:i'); ?>"
                    required
                >

            </div>


            <div class="form-group full">

                <label for="id_supplier">

                    Supplier

                    <span class="required">*</span>

                </label>


                <select
                    id="id_supplier"
                    name="id_supplier"
                    required
                >

                    <option value="">
                        Pilih supplier
                    </option>


                    <?php while (
                        $supplier =
                        mysqli_fetch_assoc($supplierQuery)
                    ): ?>

                        <option
                            value="<?= (int) $supplier["id_supplier"]; ?>"
                        >

                            <?= htmlspecialchars(
                                $supplier["kode_supplier"]
                            ); ?>

                            -
                            <?= htmlspecialchars(
                                $supplier["nama_supplier"]
                            ); ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- ==================================================
                 DETAIL BARANG
            ================================================== -->

            <div class="section-title">
                DETAIL BARANG
            </div>


            <div class="form-group full">

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
                            data-harga="<?= htmlspecialchars(
                                $produk["harga_beli"]
                            ); ?>"
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

                            (Stok:
                            <?= (int) $produk["stok"]; ?>
                            <?= htmlspecialchars(
                                $produk["satuan"]
                            ); ?>)

                        </option>

                    <?php endwhile; ?>

                </select>


                <div class="hint">
                    Harga beli akan mengikuti harga beli produk dan masih dapat disesuaikan.
                </div>

            </div>


            <div class="form-group">

                <label for="jumlah">

                    Jumlah Masuk

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

            </div>


            <div class="form-group">

                <label for="harga_beli">

                    Harga Beli per Satuan

                    <span class="required">*</span>

                </label>


                <input
                    type="number"
                    id="harga_beli"
                    name="harga_beli"
                    min="0"
                    step="0.01"
                    value="0"
                    required
                >

            </div>


            <div class="summary-box">

                <div class="summary-row">

                    <span>
                        Jumlah
                    </span>

                    <span id="jumlahPreview">
                        1
                    </span>

                </div>


                <div class="summary-row">

                    <span>
                        Harga Beli / Satuan
                    </span>

                    <span id="hargaPreview">
                        Rp 0
                    </span>

                </div>


                <div class="summary-row total">

                    <span>
                        Subtotal
                    </span>

                    <span id="subtotalPreview">
                        Rp 0
                    </span>

                </div>

            </div>


            <!-- ==================================================
                 KETERANGAN
            ================================================== -->

            <div class="section-title">
                KETERANGAN
            </div>


            <div class="form-group full">

                <label for="keterangan">
                    Keterangan
                </label>


                <textarea
                    id="keterangan"
                    name="keterangan"
                    maxlength="500"
                    placeholder="Contoh: Pembelian stok semen dari supplier."
                ></textarea>

            </div>


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
            >
                SIMPAN BARANG MASUK
            </button>

        </div>


    </form>


</main>


<script>

    const produkSelect =
        document.getElementById("id_produk");

    const jumlahInput =
        document.getElementById("jumlah");

    const hargaInput =
        document.getElementById("harga_beli");

    const jumlahPreview =
        document.getElementById("jumlahPreview");

    const hargaPreview =
        document.getElementById("hargaPreview");

    const subtotalPreview =
        document.getElementById("subtotalPreview");


    function formatRupiah(angka) {

        return new Intl.NumberFormat(
            "id-ID"
        ).format(angka);

    }


    function updateSubtotal() {

        const jumlah =
            Number(jumlahInput.value) || 0;

        const harga =
            Number(hargaInput.value) || 0;

        const subtotal =
            jumlah * harga;


        jumlahPreview.textContent =
            jumlah;


        hargaPreview.textContent =
            "Rp " + formatRupiah(harga);


        subtotalPreview.textContent =
            "Rp " + formatRupiah(subtotal);

    }


    produkSelect.addEventListener(
        "change",
        function () {

            const option =
                this.options[this.selectedIndex];

            const harga =
                option.dataset.harga || 0;

            hargaInput.value =
                harga;

            updateSubtotal();

        }
    );


    jumlahInput.addEventListener(
        "input",
        updateSubtotal
    );


    hargaInput.addEventListener(
        "input",
        updateSubtotal
    );


    updateSubtotal();

</script>


</body>

</html>


<?php

mysqli_close($conn);

?>