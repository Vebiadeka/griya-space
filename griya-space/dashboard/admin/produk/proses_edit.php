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

$id_produk = (int) ($_POST["id_produk"] ?? 0);

$kode_produk = trim(
    $_POST["kode_produk"] ?? ""
);

$id_kategori = (int) (
    $_POST["id_kategori"] ?? 0
);

$id_supplier = trim(
    $_POST["id_supplier"] ?? ""
);

$nama_produk = trim(
    $_POST["nama_produk"] ?? ""
);

$satuan = trim(
    $_POST["satuan"] ?? ""
);

$harga_beli = (float) (
    $_POST["harga_beli"] ?? 0
);

$harga_jual = (float) (
    $_POST["harga_jual"] ?? 0
);

$stok = (int) (
    $_POST["stok"] ?? 0
);

$stok_minimum = (int) (
    $_POST["stok_minimum"] ?? 0
);

$deskripsi = trim(
    $_POST["deskripsi"] ?? ""
);

$status = $_POST["status"] ?? "Aktif";


// ======================================================
// VALIDASI DASAR
// ======================================================

if (
    $id_produk <= 0 ||
    $kode_produk === "" ||
    $id_kategori <= 0 ||
    $nama_produk === "" ||
    $satuan === ""
) {
    $_SESSION["produk_error"] =
        "Data produk belum lengkap.";

    header(
        "Location: edit.php?id=" . $id_produk
    );

    exit;
}


if (
    $harga_beli < 0 ||
    $harga_jual < 0 ||
    $stok < 0 ||
    $stok_minimum < 0
) {
    $_SESSION["produk_error"] =
        "Harga atau stok tidak valid.";

    header(
        "Location: edit.php?id=" . $id_produk
    );

    exit;
}


if (
    !in_array(
        $status,
        ["Aktif", "Nonaktif"],
        true
    )
) {
    $_SESSION["produk_error"] =
        "Status produk tidak valid.";

    header(
        "Location: edit.php?id=" . $id_produk
    );

    exit;
}


// ======================================================
// CEK PRODUK ADA
// ======================================================

$stmt = $conn->prepare("
    SELECT id_produk
    FROM produk
    WHERE id_produk = ?
    LIMIT 1
");

if (!$stmt) {
    die("Gagal memeriksa produk.");
}

$stmt->bind_param(
    "i",
    $id_produk
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");

    exit;
}

$stmt->close();


// ======================================================
// CEK KODE PRODUK DUPLIKAT
// ======================================================

$stmt = $conn->prepare("
    SELECT id_produk
    FROM produk
    WHERE kode_produk = ?
    AND id_produk != ?
    LIMIT 1
");

if (!$stmt) {
    die("Gagal memeriksa kode produk.");
}

$stmt->bind_param(
    "si",
    $kode_produk,
    $id_produk
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    $_SESSION["produk_error"] =
        "Kode produk sudah digunakan oleh produk lain.";

    header(
        "Location: edit.php?id=" . $id_produk
    );

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

if (!$stmt) {
    die("Gagal memeriksa kategori.");
}

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

    header(
        "Location: edit.php?id=" . $id_produk
    );

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

    if (!$stmt) {
        die("Gagal memeriksa supplier.");
    }

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

        header(
            "Location: edit.php?id=" . $id_produk
        );

        exit;
    }

    $stmt->close();
}


// ======================================================
// UPDATE PRODUK
// ======================================================

$sql = "
    UPDATE produk
    SET
        kode_produk = ?,
        id_kategori = ?,
        id_supplier = ?,
        nama_produk = ?,
        satuan = ?,
        harga_beli = ?,
        harga_jual = ?,
        stok = ?,
        stok_minimum = ?,
        deskripsi = ?,
        status = ?
    WHERE id_produk = ?
";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    $_SESSION["produk_error"] =
        "Gagal menyiapkan perubahan produk.";

    header(
        "Location: edit.php?id=" . $id_produk
    );

    exit;
}


// ======================================================
// BIND
// ======================================================

$stmt->bind_param(
    "siissddiissi",
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
    $status,
    $id_produk
);


// ======================================================
// UPDATE
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

$_SESSION["produk_error"] =
    "Gagal memperbarui produk: " . $error;

header(
    "Location: edit.php?id=" . $id_produk
);

exit;