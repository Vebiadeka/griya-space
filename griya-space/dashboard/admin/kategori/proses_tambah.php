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
// CEK METHOD
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: tambah.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL DATA
// ======================================================

$nama_kategori = trim(
    $_POST["nama_kategori"] ?? ""
);

$deskripsi = trim(
    $_POST["deskripsi"] ?? ""
);


// ======================================================
// VALIDASI
// ======================================================

if ($nama_kategori === "") {

    $_SESSION["kategori_error"] =
        "Nama kategori wajib diisi.";

    header("Location: tambah.php");
    exit;
}


// ======================================================
// CEK PANJANG NAMA
// ======================================================

if (strlen($nama_kategori) < 2) {

    $_SESSION["kategori_error"] =
        "Nama kategori minimal 2 karakter.";

    header("Location: tambah.php");
    exit;
}


// ======================================================
// CEK DUPLIKAT KATEGORI
// ======================================================

$stmt = $conn->prepare("
    SELECT id_kategori
    FROM kategori
    WHERE nama_kategori = ?
    LIMIT 1
");

if (!$stmt) {

    $_SESSION["kategori_error"] =
        "Terjadi kesalahan pada sistem.";

    header("Location: tambah.php");
    exit;
}


$stmt->bind_param(
    "s",
    $nama_kategori
);

$stmt->execute();

$result = $stmt->get_result();


// ======================================================
// KATEGORI SUDAH ADA
// ======================================================

if ($result->num_rows > 0) {

    $stmt->close();

    $_SESSION["kategori_error"] =
        "Kategori tersebut sudah ada.";

    header("Location: tambah.php");
    exit;
}

$stmt->close();


// ======================================================
// INSERT KATEGORI
// ======================================================

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

    $_SESSION["kategori_error"] =
        "Gagal menyiapkan penyimpanan kategori.";

    header("Location: tambah.php");
    exit;
}


$stmt->bind_param(
    "ss",
    $nama_kategori,
    $deskripsi
);


// ======================================================
// EKSEKUSI
// ======================================================

if ($stmt->execute()) {

    $stmt->close();
    $conn->close();

    header(
        "Location: index.php?success=1"
    );

    exit;
}


// ======================================================
// GAGAL
// ======================================================

$error = $stmt->error;

$stmt->close();

$conn->close();


$_SESSION["kategori_error"] =
    "Kategori gagal disimpan: " . $error;

header("Location: tambah.php");

exit;