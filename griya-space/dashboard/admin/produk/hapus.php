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
// AMBIL ID PRODUK
// ======================================================

$id_produk = (int) (
    $_POST["id_produk"] ?? 0
);


if ($id_produk <= 0) {
    header("Location: index.php");
    exit;
}


// ======================================================
// CEK PRODUK
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_produk,
        nama_produk,
        status
    FROM produk
    WHERE id_produk = ?
    LIMIT 1
");


if (!$stmt) {

    $conn->close();

    die(
        "Gagal memeriksa produk: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_produk
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


$produk =
    $result->fetch_assoc();


$stmt->close();


// ======================================================
// CEK STATUS
// ======================================================

if (
    $produk["status"] === "Nonaktif"
) {

    $conn->close();

    header("Location: index.php");
    exit;
}


// ======================================================
// CEK APAKAH SUDAH PERNAH DIGUNAKAN
// DALAM TRANSAKSI ATAU PESANAN
// ======================================================

$pernah_digunakan = false;


// ------------------------------------------------------
// CEK DETAIL TRANSAKSI
// ------------------------------------------------------

$stmt = $conn->prepare("
    SELECT
        id_detail_transaksi
    FROM detail_transaksi
    WHERE id_produk = ?
    LIMIT 1
");


if (!$stmt) {

    $conn->close();

    die(
        "Gagal memeriksa transaksi: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_produk
);


$stmt->execute();


$result =
    $stmt->get_result();


if (
    $result->num_rows > 0
) {

    $pernah_digunakan = true;
}


$stmt->close();


// ------------------------------------------------------
// CEK DETAIL PESANAN
// ------------------------------------------------------

if (
    !$pernah_digunakan
) {

    $stmt = $conn->prepare("
        SELECT
            id_detail_pesanan
        FROM detail_pesanan
        WHERE id_produk = ?
        LIMIT 1
    ");


    if (!$stmt) {

        $conn->close();

        die(
            "Gagal memeriksa pesanan: " .
            $conn->error
        );
    }


    $stmt->bind_param(
        "i",
        $id_produk
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if (
        $result->num_rows > 0
    ) {

        $pernah_digunakan = true;
    }


    $stmt->close();
}


// ======================================================
// NONAKTIFKAN PRODUK
// ======================================================
//
// Produk tidak dihapus secara fisik.
// Ini menjaga riwayat transaksi tetap aman.
//

$stmt = $conn->prepare("
    UPDATE produk
    SET status = 'Nonaktif'
    WHERE id_produk = ?
");


if (!$stmt) {

    $conn->close();

    die(
        "Gagal menyiapkan perubahan status: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_produk
);


if (
    !$stmt->execute()
) {

    $error =
        $stmt->error;

    $stmt->close();

    $conn->close();

    die(
        "Gagal menonaktifkan produk: " .
        $error
    );
}


$stmt->close();


// ======================================================
// SELESAI
// ======================================================

$conn->close();


header("Location: index.php");

exit;