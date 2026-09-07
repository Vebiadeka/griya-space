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
// AMBIL ID PRODUK
// ======================================================

$id_produk = (int) (
    $_GET["id"] ??
    $_POST["id_produk"] ??
    0
);


if ($id_produk <= 0) {
    header("Location: index.php");
    exit;
}


// ======================================================
// PESAN ERROR
// ======================================================

$error = "";


// ======================================================
// AMBIL KATEGORI
// ======================================================

$kategori_list = [];


$stmt = $conn->prepare("
    SELECT
        id_kategori,
        nama_kategori
    FROM kategori
    ORDER BY nama_kategori ASC
");


if (!$stmt) {
    die(
        "Gagal mengambil kategori: " .
        $conn->error
    );
}


$stmt->execute();


$resultKategori =
    $stmt->get_result();


while (
    $kategori =
    $resultKategori->fetch_assoc()
) {

    $kategori_list[] =
        $kategori;
}


$stmt->close();


// ======================================================
// AMBIL SUPPLIER
// ======================================================

$supplier_list = [];


$stmt = $conn->prepare("
    SELECT
        id_supplier,
        nama_supplier
    FROM supplier
    ORDER BY nama_supplier ASC
");


if (!$stmt) {
    die(
        "Gagal mengambil supplier: " .
        $conn->error
    );
}


$stmt->execute();


$resultSupplier =
    $stmt->get_result();


while (
    $supplier =
    $resultSupplier->fetch_assoc()
) {

    $supplier_list[] =
        $supplier;
}


$stmt->close();


// ======================================================
// AMBIL DATA PRODUK
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_produk,
        kode_produk,
        nama_produk,
        id_kategori,
        id_supplier,
        satuan,
        harga_beli,
        harga_jual,
        stok,
        stok_minimum,
        deskripsi,
        foto_produk,
        status
    FROM produk
    WHERE id_produk = ?
    LIMIT 1
");


if (!$stmt) {
    die(
        "Gagal mengambil data produk: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_produk
);


$stmt->execute();


$resultProduk =
    $stmt->get_result();


if ($resultProduk->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit;
}


$produk =
    $resultProduk->fetch_assoc();


$stmt->close();


// ======================================================
// PROSES UPDATE
// ======================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {


    // ==================================================
    // AMBIL DATA FORM
    // ==================================================

    $kode_produk =
        trim(
            $_POST["kode_produk"] ?? ""
        );


    $nama_produk =
        trim(
            $_POST["nama_produk"] ?? ""
        );


    $id_kategori_baru =
        (int) (
            $_POST["id_kategori"] ?? 0
        );


    $id_supplier_baru =
        (int) (
            $_POST["id_supplier"] ?? 0
        );


    $satuan =
        trim(
            $_POST["satuan"] ?? ""
        );


    $harga_beli =
        (float) (
            $_POST["harga_beli"] ?? 0
        );


    $harga_jual =
        (float) (
            $_POST["harga_jual"] ?? 0
        );


    $stok_minimum =
        (int) (
            $_POST["stok_minimum"] ?? 0
        );


    $deskripsi =
        trim(
            $_POST["deskripsi"] ?? ""
        );


    $status =
        $_POST["status"] ?? "Aktif";


    // ==================================================
    // VALIDASI
    // ==================================================

    if (
        $kode_produk === "" ||
        $nama_produk === "" ||
        $id_kategori_baru <= 0 ||
        $id_supplier_baru <= 0 ||
        $satuan === ""
    ) {

        $error =
            "Data wajib belum lengkap.";

    } elseif (
        $harga_beli < 0 ||
        $harga_jual < 0
    ) {

        $error =
            "Harga tidak boleh bernilai negatif.";

    } elseif (
        $harga_jual < $harga_beli
    ) {

        $error =
            "Harga jual tidak boleh lebih kecil dari harga beli.";

    } elseif (
        $stok_minimum < 0
    ) {

        $error =
            "Stok minimum tidak boleh bernilai negatif.";

    } elseif (
        !in_array(
            $status,
            [
                "Aktif",
                "Nonaktif"
            ],
            true
        )
    ) {

        $error =
            "Status produk tidak valid.";
    }


    // ==================================================
    // CEK KODE PRODUK
    // ==================================================

    if (
        $error === ""
    ) {

        $stmt =
            $conn->prepare("
                SELECT
                    id_produk
                FROM produk
                WHERE kode_produk = ?
                  AND id_produk <> ?
                LIMIT 1
            ");


        if (!$stmt) {

            $error =
                "Gagal memeriksa kode produk: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "si",
                $kode_produk,
                $id_produk
            );


            $stmt->execute();


            $resultKode =
                $stmt->get_result();


            if (
                $resultKode->num_rows > 0
            ) {

                $error =
                    "Kode produk sudah digunakan oleh produk lain.";
            }


            $stmt->close();
        }
    }


    // ==================================================
    // FOTO BARU
    // ==================================================

    $nama_foto_baru =
        $produk["foto_produk"];


    $foto_lama =
        $produk["foto_produk"];


    $upload_foto_baru =
        false;


    if (
        $error === "" &&
        isset(
            $_FILES["foto_produk"]
        ) &&
        $_FILES["foto_produk"]["error"]
        !== UPLOAD_ERR_NO_FILE
    ) {

        $file =
            $_FILES["foto_produk"];


        if (
            $file["error"] !==
            UPLOAD_ERR_OK
        ) {

            $error =
                "Gagal mengunggah foto produk.";

        } elseif (
            $file["size"] >
            5 * 1024 * 1024
        ) {

            $error =
                "Ukuran foto maksimal 5 MB.";

        } else {

            $extension =
                strtolower(
                    pathinfo(
                        $file["name"],
                        PATHINFO_EXTENSION
                    )
                );


            $extension_valid = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];


            if (
                !in_array(
                    $extension,
                    $extension_valid,
                    true
                )
            ) {

                $error =
                    "Format foto harus JPG, JPEG, PNG, atau WEBP.";

            } else {

                $finfo =
                    finfo_open(
                        FILEINFO_MIME_TYPE
                    );


                $mime =
                    finfo_file(
                        $finfo,
                        $file["tmp_name"]
                    );


                finfo_close($finfo);


                $mime_valid = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];


                if (
                    !in_array(
                        $mime,
                        $mime_valid,
                        true
                    )
                ) {

                    $error =
                        "File yang diunggah bukan gambar yang valid.";

                } else {

                    $upload_dir =
                        "../../../uploads/produk/";


                    if (
                        !is_dir(
                            $upload_dir
                        )
                    ) {

                        if (
                            !mkdir(
                                $upload_dir,
                                0755,
                                true
                            )
                        ) {

                            $error =
                                "Folder upload produk tidak dapat dibuat.";
                        }
                    }


                    if (
                        $error === ""
                    ) {

                        $nama_foto_baru =
                            "produk_" .
                            date("YmdHis") .
                            "_" .
                            bin2hex(
                                random_bytes(4)
                            ) .
                            "." .
                            $extension;


                        $target_file =
                            $upload_dir .
                            $nama_foto_baru;


                        if (
                            move_uploaded_file(
                                $file["tmp_name"],
                                $target_file
                            )
                        ) {

                            $upload_foto_baru =
                                true;

                        } else {

                            $error =
                                "Foto baru gagal disimpan.";

                            $nama_foto_baru =
                                $foto_lama;
                        }
                    }
                }
            }
        }
    }


    // ==================================================
    // UPDATE DATA PRODUK
    // ==================================================

    if (
        $error === ""
    ) {

        /*
         * Stok TIDAK diubah di sini.
         *
         * Stok aktual hanya berubah melalui:
         * - Barang Masuk
         * - Barang Keluar
         * - Penjualan
         */

        $stmt =
            $conn->prepare("
                UPDATE produk
                SET
                    kode_produk = ?,
                    nama_produk = ?,
                    id_kategori = ?,
                    id_supplier = ?,
                    satuan = ?,
                    harga_beli = ?,
                    harga_jual = ?,
                    stok_minimum = ?,
                    deskripsi = ?,
                    foto_produk = ?,
                    status = ?
                WHERE id_produk = ?
            ");


        if (!$stmt) {

            if (
                $upload_foto_baru &&
                $nama_foto_baru !==
                $foto_lama
            ) {

                $file_path =
                    "../../../uploads/produk/" .
                    $nama_foto_baru;


                if (
                    file_exists(
                        $file_path
                    )
                ) {

                    unlink(
                        $file_path
                    );
                }
            }


            $error =
                "Gagal menyiapkan update produk: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "ssiisddiissi",
                $kode_produk,
                $nama_produk,
                $id_kategori_baru,
                $id_supplier_baru,
                $satuan,
                $harga_beli,
                $harga_jual,
                $stok_minimum,
                $deskripsi,
                $nama_foto_baru,
                $status,
                $id_produk
            );


            if (
                !$stmt->execute()
            ) {

                if (
                    $upload_foto_baru &&
                    $nama_foto_baru !==
                    $foto_lama
                ) {

                    $file_path =
                        "../../../uploads/produk/" .
                        $nama_foto_baru;


                    if (
                        file_exists(
                            $file_path
                        )
                    ) {

                        unlink(
                            $file_path
                        );
                    }
                }


                $error =
                    "Gagal memperbarui produk: " .
                    $stmt->error;

            } else {

                // --------------------------------------
                // HAPUS FOTO LAMA
                // --------------------------------------

                if (
                    $upload_foto_baru &&
                    !empty($foto_lama) &&
                    $foto_lama !==
                    $nama_foto_baru
                ) {

                    $old_file =
                        "../../../uploads/produk/" .
                        $foto_lama;


                    if (
                        file_exists(
                            $old_file
                        )
                    ) {

                        unlink(
                            $old_file
                        );
                    }
                }


                $stmt->close();


                $conn->close();


                header(
                    "Location: index.php"
                );

                exit;
            }


            $stmt->close();
        }
    }
}


// ======================================================
// DATA FORM UNTUK TAMPILAN
// ======================================================

$form_kode =
    $_POST["kode_produk"]
    ?? $produk["kode_produk"];


$form_nama =
    $_POST["nama_produk"]
    ?? $produk["nama_produk"];


$form_kategori =
    (int) (
        $_POST["id_kategori"]
        ?? $produk["id_kategori"]
    );


$form_supplier =
    (int) (
        $_POST["id_supplier"]
        ?? $produk["id_supplier"]
    );


$form_satuan =
    $_POST["satuan"]
    ?? $produk["satuan"];


$form_harga_beli =
    $_POST["harga_beli"]
    ?? $produk["harga_beli"];


$form_harga_jual =
    $_POST["harga_jual"]
    ?? $produk["harga_jual"];


$form_stok =
    (int) $produk["stok"];


$form_stok_minimum =
    $_POST["stok_minimum"]
    ?? $produk["stok_minimum"];


$form_deskripsi =
    $_POST["deskripsi"]
    ?? ($produk["deskripsi"] ?? "");


$form_status =
    $_POST["status"]
    ?? $produk["status"];

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
        Edit Produk | Griya Space
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


        main {

            max-width: 950px;

            margin: 0 auto;

            padding: 45px 30px;
        }


        .page-header {

            margin-bottom: 25px;
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

            line-height: 1.5;
        }


        .form-card {

            background: white;

            border: 1px solid #e4e4e4;
        }


        .section {

            padding: 25px;

            border-bottom: 1px solid #eeeeee;
        }


        .section:last-child {

            border-bottom: none;
        }


        .section-title {

            margin-bottom: 20px;

            color: #777;

            font-size: 11px;

            letter-spacing: 2px;
        }


        .form-grid {

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            gap: 18px;
        }


        .form-group {

            display: flex;

            flex-direction: column;
        }


        .full {

            grid-column: 1 / -1;
        }


        label {

            margin-bottom: 7px;

            font-size: 11px;

            font-weight: 600;
        }


        input,
        select,
        textarea {

            width: 100%;

            border: 1px solid #d8d8d5;

            background: #fafafa;

            padding: 11px 12px;

            font-family: inherit;

            font-size: 12px;

            color: #222;

            outline: none;

            border-radius: 4px;
        }


        input,
        select {

            min-height: 43px;
        }


        textarea {

            min-height: 110px;

            resize: vertical;
        }


        input:focus,
        select:focus,
        textarea:focus {

            background: white;

            border-color: #777;
        }


        input[readonly] {

            background: #eeeeec;

            color: #777;

            cursor: not-allowed;
        }


        .help {

            margin-top: 6px;

            color: #999;

            font-size: 10px;

            line-height: 1.5;
        }


        .error {

            margin-bottom: 20px;

            background: #faf0f0;

            border: 1px solid #ead0d0;

            border-left: 4px solid #8b3030;

            color: #8b3030;

            padding: 13px 15px;

            font-size: 12px;

            line-height: 1.5;
        }


        .current-photo {

            margin-top: 10px;
        }


        .current-photo img {

            display: block;

            width: 160px;

            height: 110px;

            object-fit: cover;

            border: 1px solid #ddd;

            background: #eee;

        }


        .no-photo {

            width: 160px;

            height: 110px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #eeeeec;

            color: #999;

            border: 1px solid #ddd;

            font-size: 10px;

        }


        .actions {

            padding: 20px 25px;

            display: flex;

            justify-content: space-between;

            gap: 10px;
        }


        .button {

            border: none;

            padding: 12px 20px;

            text-decoration: none;

            font-size: 11px;

            letter-spacing: 1px;

            cursor: pointer;

            display: inline-flex;

            align-items: center;

            justify-content: center;
        }


        .button-primary {

            background: #1d1d1d;

            color: white;
        }


        .button-primary:hover {

            background: #333;
        }


        .button-secondary {

            background: #f1f1ef;

            color: #555;

            border: 1px solid #ddd;
        }


        .required {

            color: #8b3030;
        }


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


            .form-grid {

                grid-template-columns: 1fr;
            }


            .full {

                grid-column: auto;
            }


            .actions {

                flex-direction: column-reverse;
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
            ADMIN
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
            MANAJEMEN PRODUK
        </small>


        <h1>
            Edit Produk
        </h1>


        <p>
            Perbarui informasi produk tanpa mengubah stok aktual.
        </p>

    </section>


    <?php if (
        $error !== ""
    ): ?>

        <div class="error">

            <?= htmlspecialchars(
                $error
            ); ?>

        </div>

    <?php endif; ?>


    <form
        action="edit.php?id=<?= $id_produk; ?>"
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >


        <input
            type="hidden"
            name="id_produk"
            value="<?= $id_produk; ?>"
        >


        <!-- ==================================================
             DATA PRODUK
        ================================================== -->

        <section class="section">


            <div class="section-title">
                DATA PRODUK
            </div>


            <div class="form-grid">


                <div class="form-group">

                    <label for="kode_produk">

                        Kode Produk
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="kode_produk"
                        name="kode_produk"
                        value="<?= htmlspecialchars(
                            $form_kode
                        ); ?>"
                        maxlength="50"
                        required
                    >


                    <div class="help">
                        Kode harus unik.
                    </div>

                </div>


                <div class="form-group">

                    <label for="nama_produk">

                        Nama Produk
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="nama_produk"
                        name="nama_produk"
                        value="<?= htmlspecialchars(
                            $form_nama
                        ); ?>"
                        maxlength="150"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="id_kategori">

                        Kategori
                        <span class="required">*</span>

                    </label>


                    <select
                        id="id_kategori"
                        name="id_kategori"
                        required
                    >

                        <option value="">
                            Pilih kategori
                        </option>


                        <?php foreach (
                            $kategori_list
                            as $kategori
                        ): ?>

                            <option
                                value="<?= (int) $kategori["id_kategori"]; ?>"
                                <?= (
                                    $form_kategori
                                    ===
                                    (int) $kategori["id_kategori"]
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $kategori["nama_kategori"]
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

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


                        <?php foreach (
                            $supplier_list
                            as $supplier
                        ): ?>

                            <option
                                value="<?= (int) $supplier["id_supplier"]; ?>"
                                <?= (
                                    $form_supplier
                                    ===
                                    (int) $supplier["id_supplier"]
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $supplier["nama_supplier"]
                                ); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label for="satuan">

                        Satuan
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="satuan"
                        name="satuan"
                        value="<?= htmlspecialchars(
                            $form_satuan
                        ); ?>"
                        maxlength="30"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="status">

                        Status
                        <span class="required">*</span>

                    </label>


                    <select
                        id="status"
                        name="status"
                        required
                    >

                        <option
                            value="Aktif"
                            <?= (
                                $form_status ===
                                "Aktif"
                            )
                                ? "selected"
                                : ""
                            ?>
                        >
                            Aktif
                        </option>


                        <option
                            value="Nonaktif"
                            <?= (
                                $form_status ===
                                "Nonaktif"
                            )
                                ? "selected"
                                : ""
                            ?>
                        >
                            Nonaktif
                        </option>

                    </select>

                </div>


            </div>


        </section>


        <!-- ==================================================
             HARGA & STOK
        ================================================== -->

        <section class="section">


            <div class="section-title">
                HARGA & PERSEDIAAN
            </div>


            <div class="form-grid">


                <div class="form-group">

                    <label for="harga_beli">

                        Harga Beli
                        <span class="required">*</span>

                    </label>


                    <input
                        type="number"
                        id="harga_beli"
                        name="harga_beli"
                        value="<?= htmlspecialchars(
                            $form_harga_beli
                        ); ?>"
                        min="0"
                        step="1"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="harga_jual">

                        Harga Jual
                        <span class="required">*</span>

                    </label>


                    <input
                        type="number"
                        id="harga_jual"
                        name="harga_jual"
                        value="<?= htmlspecialchars(
                            $form_harga_jual
                        ); ?>"
                        min="0"
                        step="1"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="stok">

                        Stok Saat Ini

                    </label>


                    <input
                        type="number"
                        id="stok"
                        name="stok"
                        value="<?= $form_stok; ?>"
                        readonly
                    >


                    <div class="help">
                        Stok tidak diedit di halaman ini.
                        Gunakan Barang Masuk, Barang Keluar,
                        atau Penjualan.
                    </div>

                </div>


                <div class="form-group">

                    <label for="stok_minimum">

                        Stok Minimum
                        <span class="required">*</span>

                    </label>


                    <input
                        type="number"
                        id="stok_minimum"
                        name="stok_minimum"
                        value="<?= htmlspecialchars(
                            $form_stok_minimum
                        ); ?>"
                        min="0"
                        step="1"
                        required
                    >


                </div>


                <div class="form-group full">

                    <label for="foto_produk">

                        Foto Baru

                    </label>


                    <input
                        type="file"
                        id="foto_produk"
                        name="foto_produk"
                        accept=".jpg,.jpeg,.png,.webp"
                    >


                    <div class="help">
                        Kosongkan jika ingin mempertahankan foto lama.
                        Maksimal 5 MB.
                    </div>


                    <div class="current-photo">


                        <?php if (
                            !empty(
                                $produk["foto_produk"]
                            )
                        ): ?>


                            <img
                                src="../../../uploads/produk/<?= htmlspecialchars(
                                    $produk["foto_produk"]
                                ); ?>"
                                alt="Foto produk"
                            >


                        <?php else: ?>


                            <div class="no-photo">

                                TIDAK ADA FOTO

                            </div>


                        <?php endif; ?>


                    </div>


                </div>


            </div>


        </section>


        <!-- ==================================================
             DESKRIPSI
        ================================================== -->

        <section class="section">


            <div class="section-title">
                DESKRIPSI
            </div>


            <div class="form-group">

                <label for="deskripsi">
                    Deskripsi Produk
                </label>


                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    placeholder="Masukkan deskripsi produk..."
                ><?= htmlspecialchars(
                    $form_deskripsi
                ); ?></textarea>

            </div>


        </section>


        <!-- ==================================================
             ACTION
        ================================================== -->

        <div class="actions">


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
                SIMPAN PERUBAHAN
            </button>


        </div>


    </form>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>