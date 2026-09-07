<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: tambah.php");
    exit;
}

require_once "../../../config/database.php";

$kode_produk = trim($_POST["kode_produk"] ?? "");
$id_kategori = (int) ($_POST["id_kategori"] ?? 0);
$id_supplier = trim($_POST["id_supplier"] ?? "");

$nama_produk = trim($_POST["nama_produk"] ?? "");
$satuan = trim($_POST["satuan"] ?? "");

$harga_beli = (float) ($_POST["harga_beli"] ?? 0);
$harga_jual = (float) ($_POST["harga_jual"] ?? 0);

$stok = (int) ($_POST["stok"] ?? 0);
$stok_minimum = (int) ($_POST["stok_minimum"] ?? 0);

$deskripsi = trim($_POST["deskripsi"] ?? "");
$status = $_POST["status"] ?? "Aktif";


// ======================================================
// VALIDASI DASAR
// ======================================================

if (
    $kode_produk === "" ||
    $id_kategori <= 0 ||
    $nama_produk === "" ||
    $satuan === ""
) {
    $_SESSION["produk_error"] =
        "Data wajib belum lengkap.";

    header("Location: tambah.php");
    exit;
}

if (
    $harga_beli < 0 ||
    $harga_jual < 0 ||
    $stok < 0 ||
    $stok_minimum < 0
) {
    $_SESSION["produk_error"] =
        "Nilai harga atau stok tidak valid.";

    header("Location: tambah.php");
    exit;
}


if (!in_array($status, ["Aktif", "Nonaktif"], true)) {
    $_SESSION["produk_error"] =
        "Status produk tidak valid.";

    header("Location: tambah.php");
    exit;
}


// ======================================================
// CEK KODE PRODUK
// ======================================================

$stmt = $conn->prepare("
    SELECT id_produk
    FROM produk
    WHERE kode_produk = ?
    LIMIT 1
");

$stmt->bind_param(
    "s",
    $kode_produk
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    $_SESSION["produk_error"] =
        "Kode produk sudah digunakan.";

    header("Location: tambah.php");
    exit;
}

$stmt->close();


// ======================================================
// CEK KATEGORI
// ======================================================

$stmt = $conn->prepare("
    SELECT id_kategori
    FROM kategori
    WHERE id_kategori = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id_kategori
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    $_SESSION["produk_error"] =
        "Kategori tidak ditemukan.";

    header("Location: tambah.php");
    exit;
}

$stmt->close();


// ======================================================
// CEK SUPPLIER
// ======================================================

$supplierValue = null;

if ($id_supplier !== "") {

    $supplierValue = (int) $id_supplier;

    $stmt = $conn->prepare("
        SELECT id_supplier
        FROM supplier
        WHERE id_supplier = ?
        AND status = 'Aktif'
        LIMIT 1
    ");

    $stmt->bind_param(
        "i",
        $supplierValue
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {

        $stmt->close();

        $_SESSION["produk_error"] =
            "Supplier tidak ditemukan atau tidak aktif.";

        header("Location: tambah.php");
        exit;
    }

    $stmt->close();
}


// ======================================================
// INSERT PRODUK
// ======================================================

$sql = "
    INSERT INTO produk
    (
        kode_produk,
        id_kategori,
        id_supplier,
        nama_produk,
        satuan,
        harga_beli,
        harga_jual,
        stok,
        stok_minimum,
        deskripsi,
        status
    )
    VALUES
    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    $_SESSION["produk_error"] =
        "Gagal menyiapkan penyimpanan produk.";

    header("Location: tambah.php");
    exit;
}


// ======================================================
// BIND PARAMETER
// ======================================================

$stmt->bind_param(
    "siissddiiss",
    $kode_produk,
    $id_kategori,
    $supplierValue,
    $nama_produk,
    $satuan,
    $harga_beli,
    $harga_jual,
    $stok,
    $stok_minimum,
    $deskripsi,
    $status
);


// ======================================================
// EKSEKUSI
// ======================================================

if ($stmt->execute()) {

    $stmt->close();
    $conn->close();

    header("Location: index.php?success=1");
    exit;
}


// ======================================================
// GAGAL
// ======================================================

$errorMessage = $stmt->error;

$stmt->close();
$conn->close();

$_SESSION["produk_error"] =
    "Produk gagal disimpan: " . $errorMessage;

header("Location: tambah.php");
exit;