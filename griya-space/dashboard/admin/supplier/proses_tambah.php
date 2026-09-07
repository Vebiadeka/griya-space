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
// AMBIL DATA FORM
// ======================================================

$kode_supplier = trim(
    $_POST["kode_supplier"] ?? ""
);

$nama_supplier = trim(
    $_POST["nama_supplier"] ?? ""
);

$nama_kontak = trim(
    $_POST["nama_kontak"] ?? ""
);

$no_telepon = trim(
    $_POST["no_telepon"] ?? ""
);

$email = trim(
    $_POST["email"] ?? ""
);

$alamat = trim(
    $_POST["alamat"] ?? ""
);

$status = $_POST["status"] ?? "Aktif";


// ======================================================
// VALIDASI WAJIB
// ======================================================

if (
    $kode_supplier === "" ||
    $nama_supplier === ""
) {
    $_SESSION["supplier_error"] =
        "Kode supplier dan nama supplier wajib diisi.";

    header("Location: tambah.php");
    exit;
}


// ======================================================
// VALIDASI PANJANG
// ======================================================

if (strlen($kode_supplier) < 3) {

    $_SESSION["supplier_error"] =
        "Kode supplier minimal 3 karakter.";

    header("Location: tambah.php");
    exit;
}


if (strlen($nama_supplier) < 2) {

    $_SESSION["supplier_error"] =
        "Nama supplier minimal 2 karakter.";

    header("Location: tambah.php");
    exit;
}


// ======================================================
// VALIDASI EMAIL
// ======================================================

if (
    $email !== "" &&
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    $_SESSION["supplier_error"] =
        "Format email supplier tidak valid.";

    header("Location: tambah.php");
    exit;
}


// ======================================================
// VALIDASI STATUS
// ======================================================

if (
    !in_array(
        $status,
        ["Aktif", "Nonaktif"],
        true
    )
) {
    $_SESSION["supplier_error"] =
        "Status supplier tidak valid.";

    header("Location: tambah.php");
    exit;
}


// ======================================================
// CEK KODE SUPPLIER DUPLIKAT
// ======================================================

$stmt = $conn->prepare("
    SELECT id_supplier
    FROM supplier
    WHERE kode_supplier = ?
    LIMIT 1
");

if (!$stmt) {

    $_SESSION["supplier_error"] =
        "Terjadi kesalahan pada sistem.";

    header("Location: tambah.php");
    exit;
}


$stmt->bind_param(
    "s",
    $kode_supplier
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows > 0) {

    $stmt->close();

    $_SESSION["supplier_error"] =
        "Kode supplier sudah digunakan.";

    header("Location: tambah.php");
    exit;
}


$stmt->close();


// ======================================================
// SIMPAN SUPPLIER
// ======================================================

$stmt = $conn->prepare("
    INSERT INTO supplier
    (
        kode_supplier,
        nama_supplier,
        nama_kontak,
        no_telepon,
        email,
        alamat,
        status
    )
    VALUES
    (?, ?, ?, ?, ?, ?, ?)
");


if (!$stmt) {

    $_SESSION["supplier_error"] =
        "Gagal menyiapkan penyimpanan supplier.";

    header("Location: tambah.php");
    exit;
}


$stmt->bind_param(
    "sssssss",
    $kode_supplier,
    $nama_supplier,
    $nama_kontak,
    $no_telepon,
    $email,
    $alamat,
    $status
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


$_SESSION["supplier_error"] =
    "Supplier gagal disimpan: " . $error;

header("Location: tambah.php");

exit;