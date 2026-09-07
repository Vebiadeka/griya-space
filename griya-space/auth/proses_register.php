<?php

session_start();

require_once "../config/database.php";


// ======================================================
// CEK METHOD REQUEST
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: register.php");
    exit;
}


// ======================================================
// AMBIL DATA FORM
// ======================================================

$nama_lengkap = trim($_POST["nama_lengkap"] ?? "");
$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$no_telepon = trim($_POST["no_telepon"] ?? "");
$alamat = trim($_POST["alamat"] ?? "");
$password = $_POST["password"] ?? "";
$konfirmasi_password = $_POST["konfirmasi_password"] ?? "";


// ======================================================
// VALIDASI WAJIB DIISI
// ======================================================

if (
    $nama_lengkap === "" ||
    $username === "" ||
    $email === "" ||
    $no_telepon === "" ||
    $alamat === "" ||
    $password === "" ||
    $konfirmasi_password === ""
) {

    $_SESSION["register_error"] =
        "Semua data wajib diisi.";

    header("Location: register.php");
    exit;
}


// ======================================================
// VALIDASI NAMA
// ======================================================

if (strlen($nama_lengkap) < 3) {

    $_SESSION["register_error"] =
        "Nama lengkap minimal 3 karakter.";

    header("Location: register.php");
    exit;
}


// ======================================================
// VALIDASI USERNAME
// ======================================================

if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {

    $_SESSION["register_error"] =
        "Username hanya boleh menggunakan huruf, angka, titik, garis bawah, dan tanda minus.";

    header("Location: register.php");
    exit;
}


if (strlen($username) < 4) {

    $_SESSION["register_error"] =
        "Username minimal 4 karakter.";

    header("Location: register.php");
    exit;
}


// ======================================================
// VALIDASI EMAIL
// ======================================================

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $_SESSION["register_error"] =
        "Format email tidak valid.";

    header("Location: register.php");
    exit;
}


// ======================================================
// VALIDASI PASSWORD
// ======================================================

if (strlen($password) < 6) {

    $_SESSION["register_error"] =
        "Password minimal 6 karakter.";

    header("Location: register.php");
    exit;
}


// ======================================================
// CEK PASSWORD DAN KONFIRMASI
// ======================================================

if ($password !== $konfirmasi_password) {

    $_SESSION["register_error"] =
        "Password dan konfirmasi password tidak sama.";

    header("Location: register.php");
    exit;
}


// ======================================================
// CEK USERNAME SUDAH DIGUNAKAN
// ======================================================

$sql = "
    SELECT id_user
    FROM users
    WHERE username = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    $_SESSION["register_error"] =
        "Terjadi kesalahan pada sistem.";

    header("Location: register.php");
    exit;
}

$stmt->bind_param("s", $username);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();
    $conn->close();

    $_SESSION["register_error"] =
        "Username sudah digunakan. Silakan pilih username lain.";

    header("Location: register.php");
    exit;
}

$stmt->close();


// ======================================================
// CEK EMAIL SUDAH DIGUNAKAN
// ======================================================

$sql = "
    SELECT id_user
    FROM users
    WHERE email = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    $_SESSION["register_error"] =
        "Terjadi kesalahan pada sistem.";

    header("Location: register.php");
    exit;
}

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();
    $conn->close();

    $_SESSION["register_error"] =
        "Email sudah terdaftar. Silakan gunakan email lain.";

    header("Location: register.php");
    exit;
}

$stmt->close();


// ======================================================
// HASH PASSWORD
// ======================================================

$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// ======================================================
// ROLE PELANGGAN
// ======================================================
//
// id_role 5 = Pelanggan Griya Space
//
// Pengguna yang melakukan registrasi dari halaman ini
// otomatis menjadi pelanggan.
//
// ======================================================

$id_role = 5;


// ======================================================
// STATUS AKUN
// ======================================================

$status = "Aktif";


// ======================================================
// SIMPAN DATA PELANGGAN
// ======================================================

$sql = "
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
";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    $_SESSION["register_error"] =
        "Terjadi kesalahan saat menyiapkan data.";

    header("Location: register.php");
    exit;
}


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


// ======================================================
// EKSEKUSI INSERT
// ======================================================

if ($stmt->execute()) {

    $stmt->close();
    $conn->close();

    $_SESSION["login_success"] =
        "Pendaftaran berhasil. Silakan login menggunakan akun Anda.";

    header("Location: login.php");
    exit;

}


// ======================================================
// JIKA GAGAL
// ======================================================

$stmt->close();
$conn->close();

$_SESSION["register_error"] =
    "Pendaftaran gagal. Silakan coba lagi.";

header("Location: register.php");
exit;