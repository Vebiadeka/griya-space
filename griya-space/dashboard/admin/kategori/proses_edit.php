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
    header("Location: index.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL DATA FORM
// ======================================================

$id_kategori = (int) ($_POST["id_kategori"] ?? 0);

$nama_kategori = trim(
    $_POST["nama_kategori"] ?? ""
);

$deskripsi = trim(
    $_POST["deskripsi"] ?? ""
);


// ======================================================
// VALIDASI
// ======================================================

if (
    $id_kategori <= 0 ||
    $nama_kategori === ""
) {
    $_SESSION["kategori_error"] =
        "Data kategori belum lengkap.";

    header(
        "Location: edit.php?id=" . $id_kategori
    );

    exit;
}


if (strlen($nama_kategori) < 2) {

    $_SESSION["kategori_error"] =
        "Nama kategori minimal 2 karakter.";

    header(
        "Location: edit.php?id=" . $id_kategori
    );

    exit;
}


// ======================================================
// CEK KATEGORI ADA
// ======================================================

$stmt = $conn->prepare("
    SELECT id_kategori
    FROM kategori
    WHERE id_kategori = ?
    LIMIT 1
");

if (!$stmt) {

    $_SESSION["kategori_error"] =
        "Terjadi kesalahan pada sistem.";

    header(
        "Location: edit.php?id=" . $id_kategori
    );

    exit;
}

$stmt->bind_param(
    "i",
    $id_kategori
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    $_SESSION["kategori_error"] =
        "Kategori tidak ditemukan.";

    header("Location: index.php");

    exit;
}

$stmt->close();


// ======================================================
// CEK NAMA KATEGORI DUPLIKAT
// ======================================================

$stmt = $conn->prepare("
    SELECT id_kategori
    FROM kategori
    WHERE nama_kategori = ?
    AND id_kategori != ?
    LIMIT 1
");

if (!$stmt) {

    $_SESSION["kategori_error"] =
        "Terjadi kesalahan saat memeriksa kategori.";

    header(
        "Location: edit.php?id=" . $id_kategori
    );

    exit;
}

$stmt->bind_param(
    "si",
    $nama_kategori,
    $id_kategori
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    $_SESSION["kategori_error"] =
        "Nama kategori tersebut sudah digunakan.";

    header(
        "Location: edit.php?id=" . $id_kategori
    );

    exit;
}

$stmt->close();


// ======================================================
// UPDATE KATEGORI
// ======================================================

$stmt = $conn->prepare("
    UPDATE kategori
    SET
        nama_kategori = ?,
        deskripsi = ?
    WHERE id_kategori = ?
");

if (!$stmt) {

    $_SESSION["kategori_error"] =
        "Gagal menyiapkan perubahan kategori.";

    header(
        "Location: edit.php?id=" . $id_kategori
    );

    exit;
}


$stmt->bind_param(
    "ssi",
    $nama_kategori,
    $deskripsi,
    $id_kategori
);


// ======================================================
// EKSEKUSI UPDATE
// ======================================================

if ($stmt->execute()) {

    $stmt->close();
    $conn->close();

    header(
        "Location: index.php?updated=1"
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
    "Kategori gagal diperbarui: " . $error;

header(
    "Location: edit.php?id=" . $id_kategori
);

exit;