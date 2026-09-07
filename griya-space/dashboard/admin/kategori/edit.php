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
// AMBIL ID KATEGORI
// ======================================================

$id_kategori = (int) (
    $_GET["id"] ??
    $_POST["id_kategori"] ??
    0
);


if ($id_kategori <= 0) {
    header("Location: index.php");
    exit;
}


// ======================================================
// PESAN ERROR
// ======================================================

$error = "";


// ======================================================
// AMBIL DATA KATEGORI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_kategori,
        nama_kategori,
        deskripsi
    FROM kategori
    WHERE id_kategori = ?
    LIMIT 1
");


if (!$stmt) {
    die(
        "Gagal mengambil data kategori: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_kategori
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


$kategori =
    $result->fetch_assoc();


$stmt->close();


// ======================================================
// PROSES UPDATE
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // ==================================================
    // AMBIL DATA FORM
    // ==================================================

    $nama_kategori =
        trim(
            $_POST["nama_kategori"] ?? ""
        );


    $deskripsi =
        trim(
            $_POST["deskripsi"] ?? ""
        );


    // ==================================================
    // VALIDASI
    // ==================================================

    if (
        $nama_kategori === ""
    ) {

        $error =
            "Nama kategori wajib diisi.";

    } elseif (
        strlen($nama_kategori) > 100
    ) {

        $error =
            "Nama kategori maksimal 100 karakter.";
    }


    // ==================================================
    // CEK DUPLIKAT
    // ==================================================

    if (
        $error === ""
    ) {

        $stmt = $conn->prepare("
            SELECT
                id_kategori
            FROM kategori
            WHERE nama_kategori = ?
              AND id_kategori <> ?
            LIMIT 1
        ");


        if (!$stmt) {

            $error =
                "Gagal memeriksa nama kategori: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "si",
                $nama_kategori,
                $id_kategori
            );


            $stmt->execute();


            $resultCek =
                $stmt->get_result();


            if (
                $resultCek->num_rows > 0
            ) {

                $error =
                    "Nama kategori sudah digunakan oleh kategori lain.";
            }


            $stmt->close();
        }
    }


    // ==================================================
    // UPDATE KATEGORI
    // ==================================================

    if (
        $error === ""
    ) {

        $stmt = $conn->prepare("
            UPDATE kategori
            SET
                nama_kategori = ?,
                deskripsi = ?
            WHERE id_kategori = ?
        ");


        if (!$stmt) {

            $error =
                "Gagal menyiapkan update kategori: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "ssi",
                $nama_kategori,
                $deskripsi,
                $id_kategori
            );


            if (
                !$stmt->execute()
            ) {

                $error =
                    "Gagal memperbarui kategori: " .
                    $stmt->error;

            } else {

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
// DATA FORM
// ======================================================

$form_nama =
    $_POST["nama_kategori"]
    ?? $kategori["nama_kategori"];


$form_deskripsi =
    $_POST["deskripsi"]
    ?? (
        $kategori["deskripsi"]
        ?? ""
    );

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
        Edit Kategori | Griya Space
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

            transition: .2s;
        }


        .back-link:hover {

            background: white;

            color: #1d1d1d;
        }


        main {

            max-width: 800px;

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


        .form-group {

            display: flex;

            flex-direction: column;
        }


        label {

            margin-bottom: 7px;

            font-size: 11px;

            font-weight: 600;
        }


        input,
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


        input {

            min-height: 43px;
        }


        textarea {

            min-height: 130px;

            resize: vertical;
        }


        input:focus,
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
            DATA MASTER
        </small>


        <h1>
            Edit Kategori
        </h1>


        <p>
            Perbarui informasi kategori produk Griya Space.
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
        action="edit.php?id=<?= $id_kategori; ?>"
        method="POST"
        class="form-card"
    >


        <input
            type="hidden"
            name="id_kategori"
            value="<?= $id_kategori; ?>"
        >


        <section class="section">


            <div class="section-title">
                DATA KATEGORI
            </div>


            <div class="form-group">


                <label for="nama_kategori">

                    Nama Kategori
                    <span class="required">*</span>

                </label>


                <input
                    type="text"
                    id="nama_kategori"
                    name="nama_kategori"
                    value="<?= htmlspecialchars(
                        $form_nama
                    ); ?>"
                    maxlength="100"
                    required
                >


                <div class="help">

                    Nama kategori harus unik.

                </div>


            </div>


        </section>


        <section class="section">


            <div class="section-title">
                DESKRIPSI
            </div>


            <div class="form-group">


                <label for="deskripsi">

                    Deskripsi Kategori

                </label>


                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    placeholder="Masukkan deskripsi kategori..."
                ><?= htmlspecialchars(
                    $form_deskripsi
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