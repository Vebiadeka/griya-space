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
// AMBIL DATA ROLE
// ======================================================

$role_list = [];

$stmt = $conn->prepare("
    SELECT
        id_role,
        nama_role
    FROM roles
    ORDER BY id_role ASC
");


if (!$stmt) {
    die(
        "Gagal mengambil role: " .
        $conn->error
    );
}


$stmt->execute();


$result_role =
    $stmt->get_result();


while (
    $role =
    $result_role->fetch_assoc()
) {
    $role_list[] =
        $role;
}


$stmt->close();


// ======================================================
// PROSES FORM
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    // ==================================================
    // AMBIL DATA
    // ==================================================

    $id_role =
        (int) (
            $_POST["id_role"] ?? 0
        );


    $nama_lengkap =
        trim(
            $_POST["nama_lengkap"] ?? ""
        );


    $username =
        trim(
            $_POST["username"] ?? ""
        );


    $password =
        $_POST["password"] ?? "";


    $konfirmasi_password =
        $_POST["konfirmasi_password"] ?? "";


    $email =
        trim(
            $_POST["email"] ?? ""
        );


    $no_telepon =
        trim(
            $_POST["no_telepon"] ?? ""
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
        $id_role <= 0 ||
        $nama_lengkap === "" ||
        $username === "" ||
        $password === ""
    ) {

        $error =
            "Role, nama lengkap, username, dan password wajib diisi.";

    } elseif (
        strlen($nama_lengkap) > 150
    ) {

        $error =
            "Nama lengkap maksimal 150 karakter.";

    } elseif (
        strlen($username) > 100
    ) {

        $error =
            "Username maksimal 100 karakter.";

    } elseif (
        strlen($password) < 6
    ) {

        $error =
            "Password minimal 6 karakter.";

    } elseif (
        $password !==
        $konfirmasi_password
    ) {

        $error =
            "Konfirmasi password tidak sama.";

    } elseif (
        strlen($email) > 100
    ) {

        $error =
            "Email maksimal 100 karakter.";

    } elseif (
        strlen($no_telepon) > 30
    ) {

        $error =
            "Nomor telepon maksimal 30 karakter.";

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
            "Status pengguna tidak valid.";
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
    // CEK ROLE
    // ==================================================

    if (
        $error === ""
    ) {

        $stmt =
            $conn->prepare("
                SELECT
                    id_role
                FROM roles
                WHERE id_role = ?
                LIMIT 1
            ");


        if (!$stmt) {

            $error =
                "Gagal memeriksa role: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "i",
                $id_role
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            if (
                $result->num_rows !== 1
            ) {

                $error =
                    "Role yang dipilih tidak valid.";
            }


            $stmt->close();
        }
    }


    // ==================================================
    // CEK USERNAME DUPLIKAT
    // ==================================================

    if (
        $error === ""
    ) {

        $stmt =
            $conn->prepare("
                SELECT
                    id_user
                FROM users
                WHERE username = ?
                LIMIT 1
            ");


        if (!$stmt) {

            $error =
                "Gagal memeriksa username: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "s",
                $username
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            if (
                $result->num_rows > 0
            ) {

                $error =
                    "Username sudah digunakan.";
            }


            $stmt->close();
        }
    }


    // ==================================================
    // SIMPAN USER
    // ==================================================

    if (
        $error === ""
    ) {


        // ----------------------------------------------
        // HASH PASSWORD
        // ----------------------------------------------

        $password_hash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        if (
            $password_hash === false
        ) {

            $error =
                "Password gagal diproses.";

        } else {


            // ------------------------------------------
            // INSERT USER
            // ------------------------------------------

            $stmt =
                $conn->prepare("
                    INSERT INTO users
                    (
                        id_role,
                        nama_lengkap,
                        username,
                        password,
                        email,
                        no_telepon,
                        alamat,
                        status
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)
                ");


            if (!$stmt) {

                $error =
                    "Gagal menyiapkan penyimpanan pengguna: " .
                    $conn->error;

            } else {


                /*
                 * 8 variabel:
                 *
                 * id_role      = i
                 * nama_lengkap = s
                 * username     = s
                 * password     = s
                 * email        = s
                 * no_telepon   = s
                 * alamat       = s
                 * status       = s
                 */

                $stmt->bind_param(
                    "isssssss",
                    $id_role,
                    $nama_lengkap,
                    $username,
                    $password_hash,
                    $email,
                    $no_telepon,
                    $alamat,
                    $status
                );


                if (
                    !$stmt->execute()
                ) {

                    $error =
                        "Gagal menyimpan pengguna: " .
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
        Tambah Pengguna | Griya Space
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
           PASSWORD NOTE
        ================================================== */

        .password-note {

            margin-top: 15px;

            padding: 12px 14px;

            background: #f7f7f5;

            border: 1px solid #e4e4e1;

            color: #777;

            font-size: 10px;

            line-height: 1.6;
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
            MANAJEMEN AKUN
        </small>


        <h1>
            Tambah Pengguna
        </h1>


        <p>
            Tambahkan akun pengguna baru ke sistem Griya Space.
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
             DATA AKUN
        ================================================== -->

        <section class="section">


            <div class="section-title">
                DATA AKUN
            </div>


            <div class="form-grid">


                <div class="form-group">

                    <label for="id_role">

                        Role
                        <span class="required">*</span>

                    </label>


                    <select
                        id="id_role"
                        name="id_role"
                        required
                    >

                        <option value="">
                            Pilih role
                        </option>


                        <?php foreach (
                            $role_list
                            as $role
                        ): ?>

                            <option
                                value="<?= (int) $role["id_role"]; ?>"
                                <?= (
                                    (int) (
                                        $_POST["id_role"]
                                        ?? 0
                                    )
                                    ===
                                    (int) $role["id_role"]
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $role["nama_role"]
                                ); ?>

                            </option>

                        <?php endforeach; ?>


                    </select>

                </div>


                <div class="form-group">

                    <label for="nama_lengkap">

                        Nama Lengkap
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="nama_lengkap"
                        name="nama_lengkap"
                        value="<?= htmlspecialchars(
                            $_POST["nama_lengkap"]
                            ?? ""
                        ); ?>"
                        maxlength="150"
                        placeholder="Nama lengkap pengguna"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="username">

                        Username
                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars(
                            $_POST["username"]
                            ?? ""
                        ); ?>"
                        maxlength="100"
                        placeholder="Username untuk login"
                        autocomplete="off"
                        required
                    >


                    <div class="help">
                        Username harus unik.
                    </div>

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


            </div>


        </section>


        <!-- ==================================================
             PASSWORD
        ================================================== -->

        <section class="section">


            <div class="section-title">
                PASSWORD
            </div>


            <div class="form-grid">


                <div class="form-group">

                    <label for="password">

                        Password
                        <span class="required">*</span>

                    </label>


                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="6"
                        autocomplete="new-password"
                        required
                    >


                    <div class="help">
                        Minimal 6 karakter.
                    </div>

                </div>


                <div class="form-group">

                    <label for="konfirmasi_password">

                        Konfirmasi Password
                        <span class="required">*</span>

                    </label>


                    <input
                        type="password"
                        id="konfirmasi_password"
                        name="konfirmasi_password"
                        minlength="6"
                        autocomplete="new-password"
                        required
                    >

                </div>


            </div>


            <div class="password-note">

                Password akan disimpan dalam bentuk hash
                menggunakan fungsi keamanan PHP,
                bukan sebagai teks biasa.

            </div>


        </section>


        <!-- ==================================================
             KONTAK
        ================================================== -->

        <section class="section">


            <div class="section-title">
                INFORMASI KONTAK
            </div>


            <div class="form-grid">


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
                        placeholder="contoh@email.com"
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
                        placeholder="08xxxxxxxxxx"
                    >

                </div>


                <div class="form-group full">

                    <label for="alamat">
                        Alamat
                    </label>


                    <textarea
                        id="alamat"
                        name="alamat"
                        maxlength="255"
                        placeholder="Alamat lengkap pengguna..."
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
                SIMPAN PENGGUNA
            </button>


        </div>


    </form>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>