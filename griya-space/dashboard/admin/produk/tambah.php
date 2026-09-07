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
// PESAN ERROR
// ======================================================

$error = "";


// ======================================================
// AMBIL KATEGORI
// ======================================================

$kategori_list = [];

$sql_kategori = "
    SELECT
        id_kategori,
        nama_kategori
    FROM kategori
    ORDER BY nama_kategori ASC
";

$result_kategori = mysqli_query(
    $conn,
    $sql_kategori
);

if (!$result_kategori) {
    die(
        "Gagal mengambil kategori: " .
        mysqli_error($conn)
    );
}

while (
    $kategori = mysqli_fetch_assoc(
        $result_kategori
    )
) {
    $kategori_list[] = $kategori;
}


// ======================================================
// AMBIL SUPPLIER
// ======================================================

$supplier_list = [];

$sql_supplier = "
    SELECT
        id_supplier,
        nama_supplier
    FROM supplier
    ORDER BY nama_supplier ASC
";

$result_supplier = mysqli_query(
    $conn,
    $sql_supplier
);

if (!$result_supplier) {
    die(
        "Gagal mengambil supplier: " .
        mysqli_error($conn)
    );
}

while (
    $supplier = mysqli_fetch_assoc(
        $result_supplier
    )
) {
    $supplier_list[] = $supplier;
}


// ======================================================
// PROSES FORM
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --------------------------------------------------
    // AMBIL DATA
    // --------------------------------------------------

    $kode_produk = trim(
        $_POST["kode_produk"] ?? ""
    );

    $nama_produk = trim(
        $_POST["nama_produk"] ?? ""
    );

    $id_kategori = (int) (
        $_POST["id_kategori"] ?? 0
    );

    $id_supplier = (int) (
        $_POST["id_supplier"] ?? 0
    );

    $satuan = trim(
        $_POST["satuan"] ?? ""
    );

    $harga_beli = (float) (
        $_POST["harga_beli"] ?? 0
    );

    $harga_jual = (float) (
        $_POST["harga_jual"] ?? 0
    );

    $stok = (int) (
        $_POST["stok"] ?? 0
    );

    $stok_minimum = (int) (
        $_POST["stok_minimum"] ?? 0
    );

    $deskripsi = trim(
        $_POST["deskripsi"] ?? ""
    );

    $status = $_POST["status"] ?? "Aktif";


    // --------------------------------------------------
    // VALIDASI
    // --------------------------------------------------

    if (
        $kode_produk === "" ||
        $nama_produk === "" ||
        $id_kategori <= 0 ||
        $id_supplier <= 0 ||
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
        $stok < 0 ||
        $stok_minimum < 0
    ) {

        $error =
            "Stok tidak boleh bernilai negatif.";

    } elseif (
        $harga_jual < $harga_beli
    ) {

        $error =
            "Harga jual tidak boleh lebih kecil dari harga beli.";

    } elseif (
        !in_array(
            $status,
            ["Aktif", "Nonaktif"],
            true
        )
    ) {

        $error =
            "Status produk tidak valid.";
    }


    // --------------------------------------------------
    // CEK KODE PRODUK
    // --------------------------------------------------

    if ($error === "") {

        $stmt = $conn->prepare("
            SELECT
                id_produk
            FROM produk
            WHERE kode_produk = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $error =
                "Gagal memeriksa kode produk: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "s",
                $kode_produk
            );

            $stmt->execute();

            $result =
                $stmt->get_result();

            if (
                $result->num_rows > 0
            ) {

                $error =
                    "Kode produk sudah digunakan.";
            }

            $stmt->close();
        }
    }


    // --------------------------------------------------
    // FOTO
    // --------------------------------------------------

    $nama_foto = null;


    if (
        $error === "" &&
        isset($_FILES["foto_produk"]) &&
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


                    if ($error === "") {

                        $nama_foto =
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
                            $nama_foto;


                        if (
                            !move_uploaded_file(
                                $file["tmp_name"],
                                $target_file
                            )
                        ) {

                            $error =
                                "Foto produk gagal disimpan.";

                            $nama_foto =
                                null;
                        }
                    }
                }
            }
        }
    }


    // --------------------------------------------------
    // SIMPAN PRODUK
    // --------------------------------------------------

    if ($error === "") {

        $stmt = $conn->prepare("
            INSERT INTO produk
            (
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
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");


        if (!$stmt) {

            if (
                $nama_foto !== null
            ) {

                $file_path =
                    "../../../uploads/produk/" .
                    $nama_foto;

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
                "Gagal menyiapkan penyimpanan produk: " .
                $conn->error;

        } else {

            /*
             * 12 variabel:
             *
             * 1  kode_produk   = s
             * 2  nama_produk   = s
             * 3  id_kategori   = i
             * 4  id_supplier   = i
             * 5  satuan        = s
             * 6  harga_beli    = d
             * 7  harga_jual    = d
             * 8  stok          = i
             * 9  stok_minimum  = i
             * 10 deskripsi    = s
             * 11 foto_produk   = s
             * 12 status       = s
             */

            $stmt->bind_param(
                "ssiisddiisss",
                $kode_produk,
                $nama_produk,
                $id_kategori,
                $id_supplier,
                $satuan,
                $harga_beli,
                $harga_jual,
                $stok,
                $stok_minimum,
                $deskripsi,
                $nama_foto,
                $status
            );


            // Hapus spasi pada type definition
            // agar menjadi:
            //
            // "ssiisddiisss"

            if (!$stmt->execute()) {

                if (
                    $nama_foto !== null
                ) {

                    $file_path =
                        "../../../uploads/produk/" .
                        $nama_foto;

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
                    "Gagal menyimpan produk: " .
                    $stmt->error;

                $stmt->close();

            } else {

                $stmt->close();

                $conn->close();

                header(
                    "Location: index.php"
                );

                exit;
            }
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
        Tambah Produk | Griya Space
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

            font-size: 11px;

            color: #777;

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
            Tambah Produk
        </h1>


        <p>
            Tambahkan produk baru ke dalam sistem Griya Space.
        </p>

    </section>


    <?php if ($error !== ""): ?>

        <div class="error">

            <?= htmlspecialchars(
                $error
            ); ?>

        </div>

    <?php endif; ?>


    <form
        action=""
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >


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
                            $_POST["kode_produk"]
                            ?? ""
                        ); ?>"
                        placeholder="Contoh: PRD003"
                        maxlength="50"
                        required
                    >

                    <div class="help">
                        Kode produk harus unik.
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
                            $_POST["nama_produk"]
                            ?? ""
                        ); ?>"
                        placeholder="Nama material bangunan"
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
                                    (int) (
                                        $_POST["id_kategori"]
                                        ?? 0
                                    )
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
                                    (int) (
                                        $_POST["id_supplier"]
                                        ?? 0
                                    )
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
                            $_POST["satuan"]
                            ?? ""
                        ); ?>"
                        placeholder="Contoh: pcs, sak, m3"
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
                                (
                                    $_POST["status"]
                                    ?? "Aktif"
                                )
                                ===
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
                                (
                                    $_POST["status"]
                                    ?? ""
                                )
                                ===
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
                            $_POST["harga_beli"]
                            ?? ""
                        ); ?>"
                        min="0"
                        step="1"
                        placeholder="Contoh: 45000"
                        required
                    >

                    <div class="help">
                        Masukkan angka tanpa titik atau simbol Rp.
                    </div>

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
                            $_POST["harga_jual"]
                            ?? ""
                        ); ?>"
                        min="0"
                        step="1"
                        placeholder="Contoh: 52000"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="stok">

                        Stok Awal
                        <span class="required">*</span>

                    </label>


                    <input
                        type="number"
                        id="stok"
                        name="stok"
                        value="<?= htmlspecialchars(
                            $_POST["stok"]
                            ?? "0"
                        ); ?>"
                        min="0"
                        step="1"
                        required
                    >

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
                            $_POST["stok_minimum"]
                            ?? "5"
                        ); ?>"
                        min="0"
                        step="1"
                        required
                    >

                </div>


                <div class="form-group full">

                    <label for="foto_produk">
                        Foto Produk
                    </label>


                    <input
                        type="file"
                        id="foto_produk"
                        name="foto_produk"
                        accept=".jpg,.jpeg,.png,.webp"
                    >


                    <div class="help">
                        Maksimal 5 MB.
                        Format JPG, JPEG, PNG, atau WEBP.
                    </div>

                </div>


            </div>

        </section>


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
                    $_POST["deskripsi"]
                    ?? ""
                ); ?></textarea>

            </div>


        </section>


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
                SIMPAN PRODUK
            </button>


        </div>


    </form>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>