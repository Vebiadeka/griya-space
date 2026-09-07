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

$id_produk = (int) ($_POST["id_produk"] ?? 0);

$jumlah = (int) ($_POST["jumlah"] ?? 0);

$tanggal_keluar = $_POST["tanggal_keluar"] ?? "";

$keterangan = trim(
    $_POST["keterangan"] ?? ""
);


// ======================================================
// VALIDASI DASAR
// ======================================================

if (
    $id_produk <= 0 ||
    $jumlah <= 0 ||
    $tanggal_keluar === ""
) {
    $_SESSION["barang_keluar_error"] =
        "Data barang keluar belum lengkap atau tidak valid.";

    header("Location: tambah.php");
    exit;
}


// ======================================================
// VALIDASI TANGGAL
// ======================================================

$timestamp = strtotime($tanggal_keluar);

if ($timestamp === false) {

    $_SESSION["barang_keluar_error"] =
        "Tanggal barang keluar tidak valid.";

    header("Location: tambah.php");
    exit;
}


$tanggal_keluar_mysql =
    date("Y-m-d H:i:s", $timestamp);


// ======================================================
// MULAI TRANSAKSI
// ======================================================

mysqli_begin_transaction($conn);

try {


    // ==================================================
    // 1. AMBIL PRODUK + KUNCI BARIS
    // ==================================================

    $stmt = $conn->prepare("
        SELECT
            id_produk,
            kode_produk,
            nama_produk,
            stok,
            status
        FROM produk
        WHERE id_produk = ?
        LIMIT 1
        FOR UPDATE
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal memeriksa produk."
        );
    }


    $stmt->bind_param(
        "i",
        $id_produk
    );


    if (!$stmt->execute()) {
        throw new Exception(
            "Gagal mengambil data produk."
        );
    }


    $result = $stmt->get_result();


    if ($result->num_rows !== 1) {

        throw new Exception(
            "Produk tidak ditemukan."
        );
    }


    $produk = $result->fetch_assoc();


    $stmt->close();


    // ==================================================
    // CEK STATUS PRODUK
    // ==================================================

    if ($produk["status"] !== "Aktif") {

        throw new Exception(
            "Produk sedang tidak aktif."
        );
    }


    // ==================================================
    // CEK STOK
    // ==================================================

    $stok_sebelum =
        (int) $produk["stok"];


    if ($jumlah > $stok_sebelum) {

        throw new Exception(
            "Stok tidak mencukupi. " .
            "Stok tersedia: " .
            $stok_sebelum .
            "."
        );
    }


    $stok_sesudah =
        $stok_sebelum - $jumlah;


    // ==================================================
    // NOMOR BARANG KELUAR
    // ==================================================

    $nomor_keluar =
        "BK-" . date("YmdHis");


    // ==================================================
    // ID USER
    // ==================================================

    $id_user =
        (int) $_SESSION["user_id"];


    // ==================================================
    // 2. INSERT HEADER BARANG KELUAR
    // ==================================================

    $stmt = $conn->prepare("
        INSERT INTO barang_keluar
        (
            nomor_keluar,
            id_user,
            tanggal_keluar,
            keterangan
        )
        VALUES
        (?, ?, ?, ?)
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan transaksi barang keluar."
        );
    }


    $stmt->bind_param(
        "siss",
        $nomor_keluar,
        $id_user,
        $tanggal_keluar_mysql,
        $keterangan
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal menyimpan transaksi barang keluar: " .
            $stmt->error
        );
    }


    $id_barang_keluar =
        $conn->insert_id;


    $stmt->close();


    // ==================================================
    // 3. INSERT DETAIL BARANG KELUAR
    // ==================================================

    $stmt = $conn->prepare("
        INSERT INTO detail_barang_keluar
        (
            id_barang_keluar,
            id_produk,
            jumlah,
            keterangan
        )
        VALUES
        (?, ?, ?, ?)
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan detail barang keluar."
        );
    }


    $stmt->bind_param(
        "iiis",
        $id_barang_keluar,
        $id_produk,
        $jumlah,
        $keterangan
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal menyimpan detail barang keluar: " .
            $stmt->error
        );
    }


    $stmt->close();


    // ==================================================
    // 4. UPDATE STOK PRODUK
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
    // 5. CATAT MUTASI STOK
    // ==================================================

    $jenis_mutasi =
        "Barang Keluar";


    $referensi =
        $nomor_keluar;


    $keterangan_mutasi =
        "Barang keluar dari persediaan";


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


    $_SESSION["barang_keluar_error"] =
        $e->getMessage();


    header("Location: tambah.php");

    exit;
}