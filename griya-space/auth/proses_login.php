<?php

session_start();

require_once "../config/database.php";


// ======================================================
// CEK METHOD REQUEST
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.php");

    exit;
}


// ======================================================
// AMBIL DATA LOGIN
// ======================================================

$username =
    trim(
        $_POST["username"] ?? ""
    );

$password =
    $_POST["password"] ?? "";


// ======================================================
// VALIDASI INPUT
// ======================================================

if (
    $username === "" ||
    $password === ""
) {

    $_SESSION["login_error"] =
        "Username dan password wajib diisi.";

    header("Location: login.php");

    exit;
}


// ======================================================
// CARI USER
// ======================================================

$sql = "
    SELECT
        id_user,
        id_role,
        nama_lengkap,
        username,
        password,
        email,
        no_telepon,
        alamat,
        status
    FROM users
    WHERE username = ?
    LIMIT 1
";


$stmt =
    mysqli_prepare(
        $conn,
        $sql
    );


if (!$stmt) {

    $_SESSION["login_error"] =
        "Terjadi kesalahan pada sistem.";

    header("Location: login.php");

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "s",
    $username
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result(
        $stmt
    );


// ======================================================
// USER TIDAK DITEMUKAN
// ======================================================

if (
    mysqli_num_rows($result) !== 1
) {

    $_SESSION["login_error"] =
        "Username atau password salah.";

    mysqli_stmt_close($stmt);

    header("Location: login.php");

    exit;
}


$user =
    mysqli_fetch_assoc(
        $result
    );


// ======================================================
// CEK STATUS AKUN
// ======================================================

if (
    strtolower(
        $user["status"]
    ) !== "aktif"
) {

    $_SESSION["login_error"] =
        "Akun Anda tidak aktif.";

    mysqli_stmt_close($stmt);

    header("Location: login.php");

    exit;
}


// ======================================================
// CEK PASSWORD
// ======================================================
//
// Mendukung:
//
// 1. Password hash
// 2. Password lama plain text
//
// Password plain text akan otomatis
// diubah menjadi hash setelah login berhasil.
// ======================================================

$passwordValid = false;


// ------------------------------------------------------
// PASSWORD HASH
// ------------------------------------------------------

if (
    strlen(
        $user["password"]
    ) >= 60 &&
    password_get_info(
        $user["password"]
    )["algo"] !== 0
) {

    $passwordValid =
        password_verify(
            $password,
            $user["password"]
        );

}


// ------------------------------------------------------
// PASSWORD PLAIN TEXT
// ------------------------------------------------------

else {

    $passwordValid =
        hash_equals(
            $user["password"],
            $password
        );
}


// ======================================================
// PASSWORD SALAH
// ======================================================

if (!$passwordValid) {

    $_SESSION["login_error"] =
        "Username atau password salah.";

    mysqli_stmt_close($stmt);

    header("Location: login.php");

    exit;
}


// ======================================================
// UBAH PASSWORD PLAIN TEXT
// MENJADI HASH
// ======================================================

if (
    password_get_info(
        $user["password"]
    )["algo"] === 0
) {

    $newPasswordHash =
        password_hash(
            $password,
            PASSWORD_DEFAULT
        );


    $updatePassword = "
        UPDATE users
        SET
            password = ?
        WHERE id_user = ?
    ";


    $updateStmt =
        mysqli_prepare(
            $conn,
            $updatePassword
        );


    if ($updateStmt) {

        mysqli_stmt_bind_param(
            $updateStmt,
            "si",
            $newPasswordHash,
            $user["id_user"]
        );


        mysqli_stmt_execute(
            $updateStmt
        );


        mysqli_stmt_close(
            $updateStmt
        );
    }
}


// ======================================================
// LOGIN BERHASIL
// ======================================================

session_regenerate_id(true);


// ======================================================
// SIMPAN DATA USER KE SESSION
// ======================================================

$_SESSION["user_id"] =
    $user["id_user"];


$_SESSION["id_role"] =
    $user["id_role"];


$_SESSION["username"] =
    $user["username"];


$_SESSION["nama"] =
    $user["nama_lengkap"];


$_SESSION["email"] =
    $user["email"];


$_SESSION["no_telepon"] =
    $user["no_telepon"];


$_SESSION["alamat"] =
    $user["alamat"];


$_SESSION["status"] =
    $user["status"];


// ======================================================
// SIMPAN ROLE
// ======================================================
//
// 1 = Pemilik
// 2 = Admin
// 3 = Kasir
// 4 = Gudang
// 5 = Pelanggan
//
// ======================================================

switch (
    (int) $user["id_role"]
) {

    case 1:

        $_SESSION["role"] =
            "owner";

        break;


    case 2:

        $_SESSION["role"] =
            "admin";

        break;


    case 3:

        $_SESSION["role"] =
            "kasir";

        break;


    case 4:

        $_SESSION["role"] =
            "gudang";

        break;


    case 5:

        $_SESSION["role"] =
            "pelanggan";

        break;


    default:

        session_destroy();

        session_start();


        $_SESSION["login_error"] =
            "Role pengguna tidak dikenali.";


        mysqli_stmt_close(
            $stmt
        );


        header(
            "Location: login.php"
        );


        exit;
}


// ======================================================
// BERSIHKAN ERROR LOGIN
// ======================================================

unset(
    $_SESSION["login_error"]
);


// ======================================================
// TUTUP STATEMENT
// ======================================================

mysqli_stmt_close(
    $stmt
);


// ======================================================
// REDIRECT BERDASARKAN ROLE
// ======================================================

switch (
    $_SESSION["role"]
) {


    // --------------------------------------------------
    // OWNER
    // --------------------------------------------------

    case "owner":

        header(
            "Location: ../dashboard/owner/index.php"
        );

        break;


    // --------------------------------------------------
    // ADMIN
    // --------------------------------------------------

    case "admin":

        header(
            "Location: ../dashboard/admin/index.php"
        );

        break;


    // --------------------------------------------------
    // KASIR
    // --------------------------------------------------

    case "kasir":

        header(
            "Location: ../dashboard/kasir/index.php"
        );

        break;


    // --------------------------------------------------
    // GUDANG
    // --------------------------------------------------

    case "gudang":

        header(
            "Location: ../dashboard/gudang/index.php"
        );

        break;


    // --------------------------------------------------
    // PELANGGAN
    // --------------------------------------------------

    case "pelanggan":

        header(
            "Location: ../dashboard/pelanggan/index.php"
        );

        break;


    // --------------------------------------------------
    // DEFAULT
    // --------------------------------------------------

    default:

        session_destroy();

        session_start();


        $_SESSION["login_error"] =
            "Role pengguna tidak dikenali.";


        header(
            "Location: login.php"
        );

        break;
}


mysqli_close(
    $conn
);


exit;