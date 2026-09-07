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
// AMBIL DATA
// ======================================================

$id_user = (int) (
    $_POST["id_user"] ?? 0
);


$status_baru =
    $_POST["status"] ?? "";


if ($id_user <= 0) {
    header("Location: index.php");
    exit;
}


// ======================================================
// VALIDASI STATUS
// ======================================================

if (
    !in_array(
        $status_baru,
        [
            "Aktif",
            "Nonaktif"
        ],
        true
    )
) {

    header("Location: index.php");
    exit;
}


// ======================================================
// CEGAH ADMIN MENONAKTIFKAN DIRI SENDIRI
// ======================================================

if (
    $id_user ===
    (int) $_SESSION["user_id"]
    &&
    $status_baru === "Nonaktif"
) {

    die(
        "Anda tidak dapat menonaktifkan akun Admin yang sedang digunakan."
    );
}


// ======================================================
// CEK USER
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_user,
        nama_lengkap,
        status
    FROM users
    WHERE id_user = ?
    LIMIT 1
");


if (!$stmt) {

    $error =
        "Gagal memeriksa pengguna: " .
        $conn->error;

    $conn->close();

    die($error);
}


$stmt->bind_param(
    "i",
    $id_user
);


$stmt->execute();


$result =
    $stmt->get_result();


if (
    $result->num_rows !== 1
) {

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit;
}


$user =
    $result->fetch_assoc();


$stmt->close();


// ======================================================
// UPDATE STATUS
// ======================================================

$stmt = $conn->prepare("
    UPDATE users
    SET
        status = ?
    WHERE id_user = ?
");


if (!$stmt) {

    $error =
        "Gagal menyiapkan perubahan status: " .
        $conn->error;

    $conn->close();

    die($error);
}


$stmt->bind_param(
    "si",
    $status_baru,
    $id_user
);


if (
    !$stmt->execute()
) {

    $error =
        $stmt->error;

    $stmt->close();

    $conn->close();

    die(
        "Gagal mengubah status pengguna: " .
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