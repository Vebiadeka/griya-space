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

$id_supplier = (int) ($_POST["id_supplier"] ?? 0);

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
// VALIDASI DASAR
// ======================================================

if (
    $id_supplier <= 0 ||
    $kode_supplier === "" ||
    $nama_supplier === ""
) {
    $_SESSION["supplier_error"] =
        "Data supplier belum lengkap.";

    header(
        "Location: edit.php?id=" . $id_supplier
    );

    exit;
}


// ======================================================
// VALIDASI PANJANG
// ======================================================

if (strlen($kode_supplier) < 3) {

    $_SESSION["supplier_error"] =
        "Kode supplier minimal 3 karakter.";

    header(
        "Location: edit.php?id=" . $id_supplier
    );

    exit;
}


if (strlen($nama_supplier) < 2) {

    $_SESSION["supplier_error"] =
        "Nama supplier minimal 2 karakter.";

    header(
        "Location: edit.php?id=" . $id_supplier
    );

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
        "Format email tidak valid.";

    header(
        "Location: edit.php?id=" . $id_supplier
    );

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

    header(
        "Location: edit.php?id=" . $id_supplier
    );

    exit;
}


// ======================================================
// CEK SUPPLIER ADA
// ======================================================

$stmt = $conn->prepare("
    SELECT id_supplier
    FROM supplier
    WHERE id_supplier = ?
    LIMIT 1
");

if (!$stmt) {

    $_SESSION["supplier_error"] =
        "Terjadi kesalahan pada sistem.";

    header(
        "Location: edit.php?id=" . $id_supplier
    );

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

    $_SESSION["supplier_error"] =
        "Supplier tidak ditemukan.";

    header("Location: index.php");

    exit;
}


$stmt->close();


// ======================================================
// CEK KODE SUPPLIER DUPLIKAT
// ======================================================

$stmt = $conn->prepare("
    SELECT id_supplier
    FROM supplier
    WHERE kode_supplier = ?
    AND id_supplier != ?
    LIMIT 1
");

if (!$stmt) {

    $_SESSION["supplier_error"] =
        "Gagal memeriksa kode supplier.";

    header(
        "Location: edit.php?id=" . $id_supplier
    );

    exit;
}


$stmt->bind_param(
    "si",
    $kode_supplier,
    $id_supplier
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows > 0) {

    $stmt->close();

    $_SESSION["supplier_error"] =
        "Kode supplier sudah digunakan oleh supplier lain.";

    header(
        "Location: edit.php?id=" . $id_supplier
    );

    exit;
}


$stmt->close();


// ======================================================
// UPDATE SUPPLIER
// ======================================================

$stmt = $conn->prepare("
    UPDATE supplier
    SET
        kode_supplier = ?,
        nama_supplier = ?,
        nama_kontak = ?,
        no_telepon = ?,
        email = ?,
        alamat = ?,
        status = ?
    WHERE id_supplier = ?
");


if (!$stmt) {

    $_SESSION["supplier_error"] =
        "Gagal menyiapkan perubahan supplier.";

    header(
        "Location: edit.php?id=" . $id_supplier
    );

    exit;
}


$stmt->bind_param(
    "sssssssi",
    $kode_supplier,
    $nama_supplier,
    $nama_kontak,
    $no_telepon,
    $email,
    $alamat,
    $status,
    $id_supplier
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

$_SESSION["supplier_error"] =
    "Supplier gagal diperbarui: " . $error;

header(
    "Location: edit.php?id=" . $id_supplier
);

exit;