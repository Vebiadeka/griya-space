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
// CEK ROLE
// ======================================================

if (
    $_SESSION["role"] !== "admin" &&
    $_SESSION["role"] !== "gudang"
) {
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
// AMBIL DATA FORM
// ======================================================

$id_supplier = (int) ($_POST["id_supplier"] ?? 0);
$id_produk = (int) ($_POST["id_produk"] ?? 0);

$jumlah = (int) ($_POST["jumlah"] ?? 0);

$harga_beli = (float) ($_POST["harga_beli"] ?? 0);

$tanggal_masuk = $_POST["tanggal_masuk"] ?? "";

$keterangan = trim(
    $_POST["keterangan"] ?? ""
);


// ======================================================
// VALIDASI DASAR
// ======================================================

if (
    $id_supplier <= 0 ||
    $id_produk <= 0 ||
    $jumlah <= 0 ||
    $harga_beli < 0 ||
    $tanggal_masuk === ""
) {
    $_SESSION["barang_masuk_error"] =
        "Data barang masuk belum lengkap atau tidak valid.";

    header("Location: tambah.php");
    exit;
}


// ======================================================
// VALIDASI TANGGAL
// ======================================================

$timestamp = strtotime($tanggal_masuk);

if ($timestamp === false) {

    $_SESSION["barang_masuk_error"] =
        "Tanggal barang masuk tidak valid.";

    header("Location: tambah.php");
    exit;
}


// Format untuk MySQL DATETIME
$tanggal_masuk_mysql =
    date("Y-m-d H:i:s", $timestamp);


// ======================================================
// CEK SUPPLIER
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_supplier,
        nama_supplier
    FROM supplier
    WHERE id_supplier = ?
    AND status = 'Aktif'
    LIMIT 1
");

if (!$stmt) {

    $_SESSION["barang_masuk_error"] =
        "Gagal memeriksa supplier.";

    header("Location: tambah.php");
    exit;
}

$stmt->bind_param(
    "i",
    $id_supplier
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    $_SESSION["barang_masuk_error"] =
        "Supplier tidak ditemukan atau tidak aktif.";

    header("Location: tambah.php");
    exit;
}

$stmt->close();


// ======================================================
// CEK PRODUK
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_produk,
        kode_produk,
        nama_produk,
        stok
    FROM produk
    WHERE id_produk = ?
    AND status = 'Aktif'
    LIMIT 1
");

if (!$stmt) {

    $_SESSION["barang_masuk_error"] =
        "Gagal memeriksa produk.";

    header("Location: tambah.php");
    exit;
}

$stmt->bind_param(
    "i",
    $id_produk
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    $_SESSION["barang_masuk_error"] =
        "Produk tidak ditemukan atau tidak aktif.";

    header("Location: tambah.php");
    exit;
}

$produk = $result->fetch_assoc();

$stmt->close();


// ======================================================
// DATA STOK
// ======================================================

$stok_sebelum = (int) $produk["stok"];

$stok_sesudah =
    $stok_sebelum + $jumlah;


// ======================================================
// HITUNG TOTAL
// ======================================================

$subtotal =
    $jumlah * $harga_beli;

$total_harga =
    $subtotal;


// ======================================================
// GENERATE NOMOR BARANG MASUK
// ======================================================

$nomor_masuk =
    "BM-" . date("YmdHis");


// ======================================================
// MULAI TRANSAKSI DATABASE
// ======================================================

mysqli_begin_transaction($conn);

try {


    // ==================================================
    // 1. INSERT BARANG MASUK
    // ==================================================

    $stmt = $conn->prepare("
        INSERT INTO barang_masuk
        (
            nomor_masuk,
            id_supplier,
            id_user,
            tanggal_masuk,
            total_harga,
            keterangan
        )
        VALUES
        (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan transaksi barang masuk."
        );
    }


    $id_user =
        (int) $_SESSION["user_id"];


    $stmt->bind_param(
        "siisds",
        $nomor_masuk,
        $id_supplier,
        $id_user,
        $tanggal_masuk_mysql,
        $total_harga,
        $keterangan
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal menyimpan transaksi barang masuk: " .
            $stmt->error
        );
    }


    $id_barang_masuk =
        $conn->insert_id;


    $stmt->close();


    // ==================================================
    // 2. INSERT DETAIL BARANG MASUK
    // ==================================================

    $stmt = $conn->prepare("
        INSERT INTO detail_barang_masuk
        (
            id_barang_masuk,
            id_produk,
            jumlah,
            harga_beli,
            subtotal
        )
        VALUES
        (?, ?, ?, ?, ?)
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan detail barang masuk."
        );
    }


    $stmt->bind_param(
        "iiidd",
        $id_barang_masuk,
        $id_produk,
        $jumlah,
        $harga_beli,
        $subtotal
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal menyimpan detail barang masuk: " .
            $stmt->error
        );
    }


    $stmt->close();


    // ==================================================
    // 3. UPDATE STOK PRODUK
    // ==================================================

    $stmt = $conn->prepare("
        UPDATE produk
        SET stok = ?
        WHERE id_produk = ?
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan pembaruan stok."
        );
    }


    $stmt->bind_param(
        "ii",
        $stok_sesudah,
        $id_produk
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal memperbarui stok produk: " .
            $stmt->error
        );
    }


    $stmt->close();


    // ==================================================
    // 4. INSERT MUTASI STOK
    // ==================================================

    $jenis_mutasi =
        "Barang Masuk";


    $referensi =
        $nomor_masuk;


    $keterangan_mutasi =
        "Barang masuk dari supplier";


    $stmt = $conn->prepare("
        INSERT INTO mutasi_stok
        (
            id_produk,
            id_user,
            jenis_mutasi,
            jumlah,
            stok_sebelum,
            stok_sesudah,
            referensi,
            keterangan
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan mutasi stok."
        );
    }


    $stmt->bind_param(
        "iisiiiss",
        $id_produk,
        $id_user,
        $jenis_mutasi,
        $jumlah,
        $stok_sebelum,
        $stok_sesudah,
        $referensi,
        $keterangan_mutasi
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal mencatat mutasi stok: " .
            $stmt->error
        );
    }


    $stmt->close();


    // ==================================================
    // COMMIT
    // ==================================================

    mysqli_commit($conn);

    $conn->close();


    header(
        "Location: index.php?success=1"
    );

    exit;


} catch (Throwable $e) {


    // ==================================================
    // ROLLBACK
    // ==================================================

    mysqli_rollback($conn);

    $conn->close();


    $_SESSION["barang_masuk_error"] =
        $e->getMessage();


    header("Location: tambah.php");

    exit;
}