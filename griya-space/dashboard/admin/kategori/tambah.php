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
// ERROR
// ======================================================

$error = "";


// ======================================================
// PROSES FORM
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama_kategori = trim(
        $_POST["nama_kategori"] ?? ""
    );

    $deskripsi = trim(
        $_POST["deskripsi"] ?? ""
    );


    // ==================================================
    // VALIDASI
    // ==================================================

    if ($nama_kategori === "") {

        $error =
            "Nama kategori wajib diisi.";

    } elseif (strlen($nama_kategori) > 100) {

        $error =
            "Nama kategori maksimal 100 karakter.";
    }


    // ==================================================
    // CEK DUPLIKAT
    // ==================================================

    if ($error === "") {

        $stmt = $conn->prepare("
            SELECT
                id_kategori
            FROM kategori
            WHERE nama_kategori = ?
            LIMIT 1
        ");


        if (!$stmt) {

            $error =
                "Gagal memeriksa kategori: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "s",
                $nama_kategori
            );

            $stmt->execute();

            $result =
                $stmt->get_result();

            if (
                $result->num_rows > 0
            ) {

                $error =
                    "Nama kategori sudah digunakan.";
            }

            $stmt->close();
        }
    }


    // ==================================================
    // SIMPAN KATEGORI
    // ==================================================

    if ($error === "") {

        $stmt = $conn->prepare("
            INSERT INTO kategori
            (
                nama_kategori,
                deskripsi
            )
            VALUES
            (?, ?)
        ");


        if (!$stmt) {

            $error =
                "Gagal menyiapkan penyimpanan kategori: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "ss",
                $nama_kategori,
                $deskripsi
            );


            if (
                !$stmt->execute()
            ) {

                $error =
                    "Gagal menyimpan kategori: " .
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
        Tambah Kategori | Griya Space
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


        .required {

            color: #8b3030;
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
            Tambah Kategori
        </h1>


        <p>
            Tambahkan kategori material baru ke dalam sistem.
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
        class="form-card"
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
                        $_POST["nama_kategori"]
                        ?? ""
                    ); ?>"
                    maxlength="100"
                    placeholder="Contoh: Keramik"
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
                SIMPAN KATEGORI
            </button>


        </div>


    </form>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>