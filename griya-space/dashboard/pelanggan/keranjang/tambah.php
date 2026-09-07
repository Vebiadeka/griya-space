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

if ($_SESSION["role"] !== "pelanggan") {
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// CEK METHOD
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../katalog/index.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL DATA
// ======================================================

$id_user = (int) $_SESSION["user_id"];

$id_produk = (int) ($_POST["id_produk"] ?? 0);


// ======================================================
// VALIDASI
// ======================================================

if ($id_produk <= 0) {

    $_SESSION["cart_error"] =
        "Produk tidak valid.";

    header("Location: ../katalog/index.php");
    exit;
}


// ======================================================
// CARI PELANGGAN
// ======================================================

$stmt = $conn->prepare("
    SELECT id_pelanggan
    FROM pelanggan
    WHERE id_user = ?
    LIMIT 1
");

if (!$stmt) {
    die("Gagal menyiapkan data pelanggan: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $id_user
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    $_SESSION["cart_error"] =
        "Data pelanggan tidak ditemukan.";

    header("Location: ../katalog/index.php");
    exit;
}

$pelanggan =
    $result->fetch_assoc();

$id_pelanggan =
    (int) $pelanggan["id_pelanggan"];

$stmt->close();


// ======================================================
// CEK PRODUK
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_produk,
        nama_produk,
        stok,
        harga_jual,
        status
    FROM produk
    WHERE id_produk = ?
    LIMIT 1
");

if (!$stmt) {
    die("Gagal menyiapkan data produk: " . $conn->error);
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

    $_SESSION["cart_error"] =
        "Produk tidak ditemukan.";

    header("Location: ../katalog/index.php");
    exit;
}

$produk =
    $result->fetch_assoc();

$stmt->close();


// ======================================================
// CEK STATUS
// ======================================================

if ($produk["status"] !== "Aktif") {

    $_SESSION["cart_error"] =
        "Produk sedang tidak tersedia.";

    header("Location: ../katalog/index.php");
    exit;
}


// ======================================================
// CEK STOK
// ======================================================

$stok =
    (int) $produk["stok"];

if ($stok <= 0) {

    $_SESSION["cart_error"] =
        "Stok produk habis.";

    header("Location: ../katalog/index.php");
    exit;
}


// ======================================================
// CEK KERANJANG
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_keranjang,
        jumlah
    FROM keranjang
    WHERE id_pelanggan = ?
      AND id_produk = ?
    LIMIT 1
");

if (!$stmt) {
    die("Gagal memeriksa keranjang: " . $conn->error);
}

$stmt->bind_param(
    "ii",
    $id_pelanggan,
    $id_produk
);

$stmt->execute();

$result =
    $stmt->get_result();


// ======================================================
// UPDATE JIKA SUDAH ADA
// ======================================================

if ($result->num_rows === 1) {

    $data =
        $result->fetch_assoc();

    $id_keranjang =
        (int) $data["id_keranjang"];

    $jumlah_lama =
        (int) $data["jumlah"];

    $jumlah_baru =
        $jumlah_lama + 1;

    $stmt->close();


    if ($jumlah_baru > $stok) {

        $_SESSION["cart_error"] =
            "Jumlah di keranjang sudah mencapai stok.";

        header("Location: ../katalog/index.php");
        exit;
    }


    $stmt = $conn->prepare("
        UPDATE keranjang
        SET jumlah = ?
        WHERE id_keranjang = ?
    ");

    if (!$stmt) {
        die("Gagal memperbarui keranjang: " . $conn->error);
    }

    $stmt->bind_param(
        "ii",
        $jumlah_baru,
        $id_keranjang
    );

    if (!$stmt->execute()) {

        $stmt->close();

        $_SESSION["cart_error"] =
            "Gagal memperbarui keranjang.";

        header("Location: ../katalog/index.php");
        exit;
    }

    $stmt->close();


// ======================================================
// INSERT JIKA BELUM ADA
// ======================================================

} else {

    $stmt->close();

    $jumlah =
        1;

    $stmt = $conn->prepare("
        INSERT INTO keranjang
        (
            id_pelanggan,
            id_produk,
            jumlah
        )
        VALUES
        (?, ?, ?)
    ");

    if (!$stmt) {
        die("Gagal menyiapkan keranjang: " . $conn->error);
    }

    $stmt->bind_param(
        "iii",
        $id_pelanggan,
        $id_produk,
        $jumlah
    );

    if (!$stmt->execute()) {

        $stmt->close();

        $_SESSION["cart_error"] =
            "Gagal menambahkan produk ke keranjang.";

        header("Location: ../katalog/index.php");
        exit;
    }

    $stmt->close();
}


// ======================================================
// PESAN BERHASIL
// ======================================================

$_SESSION["cart_success"] =
    $produk["nama_produk"] .
    " berhasil ditambahkan ke keranjang.";


// ======================================================
// TUTUP KONEKSI
// ======================================================

$conn->close();


// ======================================================
// KEMBALI KE KATALOG
// ======================================================

header("Location: ../katalog/index.php");
exit;