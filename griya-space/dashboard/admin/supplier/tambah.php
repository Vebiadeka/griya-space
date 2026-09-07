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


    // ==================================================
    // AMBIL DATA
    // ==================================================

    $kode_supplier =
        trim(
            $_POST["kode_supplier"] ?? ""
        );

    $nama_supplier =
        trim(
            $_POST["nama_supplier"] ?? ""
        );

    $nama_kontak =
        trim(
            $_POST["nama_kontak"] ?? ""
        );

    $no_telepon =
        trim(
            $_POST["no_telepon"] ?? ""
        );

    $email =
        trim(
            $_POST["email"] ?? ""
        );

    $alamat =
        trim(
            $_POST["alamat"] ?? ""
        );

    $status =
        $_POST["status"] ?? "Aktif";


    // ==================================================
    // VALIDASI DASAR
    // ==================================================

    if (
        $kode_supplier === "" ||
        $nama_supplier === ""
    ) {

        $error =
            "Kode supplier dan nama supplier wajib diisi.";

    } elseif (
        strlen($kode_supplier) > 50
    ) {

        $error =
            "Kode supplier maksimal 50 karakter.";

    } elseif (
        strlen($nama_supplier) > 150
    ) {

        $error =
            "Nama supplier maksimal 150 karakter.";

    } elseif (
        strlen($nama_kontak) > 100
    ) {

        $error =
            "Nama kontak maksimal 100 karakter.";

    } elseif (
        strlen($no_telepon) > 30
    ) {

        $error =
            "Nomor telepon maksimal 30 karakter.";

    } elseif (
        strlen($email) > 100
    ) {

        $error =
            "Email maksimal 100 karakter.";

    } elseif (
        strlen($alamat) > 255
    ) {

        $error =
            "Alamat maksimal 255 karakter.";

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
            "Status supplier tidak valid.";
    }


    // ==================================================
    // VALIDASI EMAIL
    // ==================================================

    if (
        $error === "" &&
        $email !== "" &&
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "Format email tidak valid.";
    }


    // ==================================================
    // CEK DUPLIKAT KODE SUPPLIER
    // ==================================================

    if (
        $error === ""
    ) {

        $stmt =
            $conn->prepare("
                SELECT
                    id_supplier
                FROM supplier
                WHERE kode_supplier = ?
                LIMIT 1
            ");


        if (!$stmt) {

            $error =
                "Gagal memeriksa kode supplier: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "s",
                $kode_supplier
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            if (
                $result->num_rows > 0
            ) {

                $error =
                    "Kode supplier sudah digunakan.";
            }


            $stmt->close();
        }
    }


    // ==================================================
    // SIMPAN SUPPLIER
    // ==================================================

    if (
        $error === ""
    ) {

        $stmt =
            $conn->prepare("
                INSERT INTO supplier
                (
                    kode_supplier,
                    nama_supplier,
                    nama_kontak,
                    no_telepon,
                    email,
                    alamat,
                    status
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?)
            ");


        if (!$stmt) {

            $error =
                "Gagal menyiapkan penyimpanan supplier: " .
                $conn->error;

        } else {

            /*
             * 7 variabel:
             *
             * kode_supplier = s
             * nama_supplier = s
             * nama_kontak   = s
             * no_telepon    = s
             * email         = s
             * alamat        = s
             * status        = s
             */

            $stmt->bind_param(
                "sssssss",
                $kode_supplier,
                $nama_supplier,
                $nama_kontak,
                $no_telepon,
                $email,
                $alamat,
                $status
            );


            if (
                !$stmt->execute()
            ) {

                $error =
                    "Gagal menyimpan supplier: " .
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
        Tambah Supplier | Griya Space
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


        /* ==================================================
           FORM
        ================================================== */

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

            min-height: 120px;

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


        /* ==================================================
           ACTION
        ================================================== */

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
            DATA MASTER
        </small>


        <h1>
            Tambah Supplier
        </h1>


        <p>
            Tambahkan supplier baru ke dalam sistem Griya Space.
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


        <!-- ==================================================
             DATA SUPPLIER
        ================================================== -->

        <section class="section">


            <div class="section-title">
                DATA SUPPLIER
            </div>


            <div class="form-grid">


                <div class="form-group">

                    <label for="kode_supplier">

                        Kode Supplier
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="kode_supplier"
                        name="kode_supplier"
                        value="<?= htmlspecialchars(
                            $_POST["kode_supplier"]
                            ?? ""
                        ); ?>"
                        maxlength="50"
                        placeholder="Contoh: SPL003"
                        required
                    >


                    <div class="help">

                        Kode supplier harus unik.

                    </div>

                </div>


                <div class="form-group">

                    <label for="nama_supplier">

                        Nama Supplier
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="nama_supplier"
                        name="nama_supplier"
                        value="<?= htmlspecialchars(
                            $_POST["nama_supplier"]
                            ?? ""
                        ); ?>"
                        maxlength="150"
                        placeholder="Nama perusahaan supplier"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="nama_kontak">

                        Nama Kontak

                    </label>


                    <input
                        type="text"
                        id="nama_kontak"
                        name="nama_kontak"
                        value="<?= htmlspecialchars(
                            $_POST["nama_kontak"]
                            ?? ""
                        ); ?>"
                        maxlength="100"
                        placeholder="Nama orang yang dapat dihubungi"
                    >

                </div>


                <div class="form-group">

                    <label for="no_telepon">

                        Nomor Telepon

                    </label>


                    <input
                        type="text"
                        id="no_telepon"
                        name="no_telepon"
                        value="<?= htmlspecialchars(
                            $_POST["no_telepon"]
                            ?? ""
                        ); ?>"
                        maxlength="30"
                        placeholder="Contoh: 081234567890"
                    >

                </div>


                <div class="form-group">

                    <label for="email">

                        Email

                    </label>


                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars(
                            $_POST["email"]
                            ?? ""
                        ); ?>"
                        maxlength="100"
                        placeholder="supplier@example.com"
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
                                ) === "Aktif"
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
                                ) === "Nonaktif"
                            )
                                ? "selected"
                                : ""
                            ?>
                        >
                            Nonaktif
                        </option>

                    </select>

                </div>


                <div class="form-group full">

                    <label for="alamat">

                        Alamat

                    </label>


                    <textarea
                        id="alamat"
                        name="alamat"
                        maxlength="255"
                        placeholder="Alamat lengkap supplier..."
                    ><?= htmlspecialchars(
                        $_POST["alamat"]
                        ?? ""
                    ); ?></textarea>

                </div>


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
                SIMPAN SUPPLIER
            </button>


        </div>


    </form>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>