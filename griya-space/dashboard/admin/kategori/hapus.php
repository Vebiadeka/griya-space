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
// AMBIL ID KATEGORI
// ======================================================

$id_kategori = (int) (
    $_POST["id_kategori"] ?? 0
);


if ($id_kategori <= 0) {
    header("Location: index.php");
    exit;
}


// ======================================================
// CEK KATEGORI
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_kategori,
        nama_kategori
    FROM kategori
    WHERE id_kategori = ?
    LIMIT 1
");


if (!$stmt) {

    $error =
        "Gagal memeriksa kategori: " .
        $conn->error;

    $conn->close();

    die($error);
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
// CEK APAKAH KATEGORI MASIH DIGUNAKAN PRODUK
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_produk,
        nama_produk
    FROM produk
    WHERE id_kategori = ?
    LIMIT 1
");


if (!$stmt) {

    $error =
        "Gagal memeriksa penggunaan kategori: " .
        $conn->error;

    $conn->close();

    die($error);
}


$stmt->bind_param(
    "i",
    $id_kategori
);


$stmt->execute();


$resultProduk =
    $stmt->get_result();


if ($resultProduk->num_rows > 0) {

    $produk =
        $resultProduk->fetch_assoc();


    $stmt->close();
    $conn->close();

    die(
        "Kategori \"" .
        htmlspecialchars(
            $kategori["nama_kategori"]
        ) .
        "\" tidak dapat dihapus karena masih digunakan oleh produk \"" .
        htmlspecialchars(
            $produk["nama_produk"]
        ) .
        "\". Silakan ubah kategori produk tersebut terlebih dahulu."
    );
}


$stmt->close();


// ======================================================
// HAPUS KATEGORI
// ======================================================

$stmt = $conn->prepare("
    DELETE FROM kategori
    WHERE id_kategori = ?
");


if (!$stmt) {

    $error =
        "Gagal menyiapkan penghapusan kategori: " .
        $conn->error;

    $conn->close();

    die($error);
}


$stmt->bind_param(
    "i",
    $id_kategori
);


if (!$stmt->execute()) {

    $error =
        $stmt->error;

    $stmt->close();
    $conn->close();

    die(
        "Kategori gagal dihapus: " .
        $error
    );
}


$stmt->close();

$conn->close();


// ======================================================
// KEMBALI
// ======================================================

header("Location: index.php");

exit;