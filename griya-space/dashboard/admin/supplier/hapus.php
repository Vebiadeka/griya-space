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
// AMBIL ID SUPPLIER
// ======================================================

$id_supplier = (int) (
    $_POST["id_supplier"] ?? 0
);


if ($id_supplier <= 0) {
    header("Location: index.php");
    exit;
}


// ======================================================
// CEK SUPPLIER
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_supplier,
        kode_supplier,
        nama_supplier
    FROM supplier
    WHERE id_supplier = ?
    LIMIT 1
");


if (!$stmt) {

    $error =
        "Gagal memeriksa supplier: " .
        $conn->error;

    $conn->close();

    die($error);
}


$stmt->bind_param(
    "i",
    $id_supplier
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


$supplier =
    $result->fetch_assoc();


$stmt->close();


// ======================================================
// CEK APAKAH SUPPLIER MASIH DIGUNAKAN PRODUK
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_produk,
        nama_produk
    FROM produk
    WHERE id_supplier = ?
    LIMIT 1
");


if (!$stmt) {

    $error =
        "Gagal memeriksa penggunaan supplier: " .
        $conn->error;

    $conn->close();

    die($error);
}


$stmt->bind_param(
    "i",
    $id_supplier
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
        "Supplier \"" .
        htmlspecialchars(
            $supplier["nama_supplier"]
        ) .
        "\" tidak dapat dihapus karena masih digunakan oleh produk \"" .
        htmlspecialchars(
            $produk["nama_produk"]
        ) .
        "\". Silakan ubah supplier produk tersebut terlebih dahulu."
    );
}


$stmt->close();


// ======================================================
// HAPUS SUPPLIER
// ======================================================

$stmt = $conn->prepare("
    DELETE FROM supplier
    WHERE id_supplier = ?
");


if (!$stmt) {

    $error =
        "Gagal menyiapkan penghapusan supplier: " .
        $conn->error;

    $conn->close();

    die($error);
}


$stmt->bind_param(
    "i",
    $id_supplier
);


if (!$stmt->execute()) {

    $error =
        $stmt->error;

    $stmt->close();

    $conn->close();

    die(
        "Supplier gagal dihapus: " .
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