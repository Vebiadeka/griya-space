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
    header("Location: index.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// DATA SESSION
// ======================================================

$id_user = (int) $_SESSION["user_id"];

$catatan = trim(
    $_POST["catatan"] ?? ""
);


// ======================================================
// CARI DATA PELANGGAN
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id_pelanggan
    FROM pelanggan
    WHERE id_user = ?
    LIMIT 1
");

if (!$stmt) {
    die(
        "Gagal memeriksa data pelanggan: " .
        $conn->error
    );
}

$stmt->bind_param(
    "i",
    $id_user
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    die("Data pelanggan tidak ditemukan.");
}

$pelanggan = $result->fetch_assoc();

$id_pelanggan =
    (int) $pelanggan["id_pelanggan"];

$stmt->close();


// ======================================================
// MULAI TRANSAKSI
// ======================================================

mysqli_begin_transaction($conn);

try {


    // ==================================================
    // 1. AMBIL ISI KERANJANG
    // ==================================================

    $stmt = $conn->prepare("
        SELECT
            k.id_keranjang,
            k.id_produk,
            k.jumlah,
            p.kode_produk,
            p.nama_produk,
            p.satuan,
            p.harga_jual,
            p.stok,
            p.status
        FROM keranjang k

        INNER JOIN produk p
            ON k.id_produk = p.id_produk

        WHERE k.id_pelanggan = ?

        ORDER BY k.id_keranjang ASC

        FOR UPDATE
    ");

    if (!$stmt) {
        throw new Exception(
            "Gagal mengambil data keranjang."
        );
    }

    $stmt->bind_param(
        "i",
        $id_pelanggan
    );

    if (!$stmt->execute()) {
        throw new Exception(
            "Gagal membaca isi keranjang."
        );
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {

        throw new Exception(
            "Keranjang masih kosong."
        );
    }


    // ==================================================
    // 2. BENTUK DETAIL PESANAN
    // ==================================================

    $items = [];

    $subtotal = 0;

    $jumlah_item = 0;


    while ($item = $result->fetch_assoc()) {

        $jumlah =
            (int) $item["jumlah"];

        $harga_satuan =
            (float) $item["harga_jual"];

        $stok =
            (int) $item["stok"];


        // ----------------------------------------------
        // CEK STATUS PRODUK
        // ----------------------------------------------

        if ($item["status"] !== "Aktif") {

            throw new Exception(
                "Produk \"" .
                $item["nama_produk"] .
                "\" sedang tidak aktif."
            );
        }


        // ----------------------------------------------
        // CEK STOK TERKINI
        // ----------------------------------------------

        if ($jumlah > $stok) {

            throw new Exception(
                "Stok \"" .
                $item["nama_produk"] .
                "\" tidak mencukupi. " .
                "Tersedia: " .
                $stok .
                " " .
                $item["satuan"] .
                "."
            );
        }


        $item_subtotal =
            $jumlah * $harga_satuan;


        $items[] = [
            "id_produk" =>
                (int) $item["id_produk"],

            "jumlah" =>
                $jumlah,

            "harga_satuan" =>
                $harga_satuan,

            "subtotal" =>
                $item_subtotal,

            "nama_produk" =>
                $item["nama_produk"]
        ];


        $subtotal +=
            $item_subtotal;

        $jumlah_item +=
            $jumlah;
    }


    $stmt->close();


    // ==================================================
    // 3. DISKON
    // ==================================================

    $diskon = 0;

    $total_harga =
        $subtotal - $diskon;


    if ($total_harga < 0) {

        throw new Exception(
            "Total pesanan tidak valid."
        );
    }


    // ==================================================
    // 4. BUAT NOMOR PESANAN
    // ==================================================

    $nomor_pesanan =
        "PS-" .
        date("YmdHis") .
        "-" .
        $id_pelanggan;


    $tanggal_pesanan =
        date("Y-m-d H:i:s");


    $status =
        "Menunggu";


    // ==================================================
    // 5. INSERT PESANAN
    // ==================================================

    $stmt = $conn->prepare("
        INSERT INTO pesanan
        (
            nomor_pesanan,
            id_pelanggan,
            tanggal_pesanan,
            subtotal,
            diskon,
            total_harga,
            status,
            catatan
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan pesanan."
        );
    }


    $stmt->bind_param(
        "sisdddss",
        $nomor_pesanan,
        $id_pelanggan,
        $tanggal_pesanan,
        $subtotal,
        $diskon,
        $total_harga,
        $status,
        $catatan
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal menyimpan pesanan: " .
            $stmt->error
        );
    }


    $id_pesanan =
        $conn->insert_id;


    $stmt->close();


    // ==================================================
    // 6. INSERT DETAIL PESANAN
    // ==================================================

    $stmt = $conn->prepare("
        INSERT INTO detail_pesanan
        (
            id_pesanan,
            id_produk,
            jumlah,
            harga_satuan,
            subtotal
        )
        VALUES
        (?, ?, ?, ?, ?)
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan detail pesanan."
        );
    }


    foreach ($items as $item) {

        $stmt->bind_param(
            "iiidd",
            $id_pesanan,
            $item["id_produk"],
            $item["jumlah"],
            $item["harga_satuan"],
            $item["subtotal"]
        );


        if (!$stmt->execute()) {

            throw new Exception(
                "Gagal menyimpan detail pesanan: " .
                $stmt->error
            );
        }
    }


    $stmt->close();


    // ==================================================
    // 7. KOSONGKAN KERANJANG
    // ==================================================

    $stmt = $conn->prepare("
        DELETE FROM keranjang
        WHERE id_pelanggan = ?
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan pengosongan keranjang."
        );
    }


    $stmt->bind_param(
        "i",
        $id_pelanggan
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal mengosongkan keranjang."
        );
    }


    $stmt->close();


    // ==================================================
    // COMMIT
    // ==================================================

    mysqli_commit($conn);

    $conn->close();


    // Simpan nomor pesanan untuk halaman berikutnya.
    $_SESSION["pesanan_berhasil"] = [
        "id_pesanan" =>
            $id_pesanan,

        "nomor_pesanan" =>
            $nomor_pesanan,

        "total_harga" =>
            $total_harga,

        "jumlah_item" =>
            $jumlah_item
    ];


    header(
        "Location: berhasil.php"
    );

    exit;


} catch (Throwable $e) {


    // ==================================================
    // ROLLBACK
    // ==================================================

    mysqli_rollback($conn);

    $conn->close();


    $_SESSION["checkout_error"] =
        $e->getMessage();


    header("Location: index.php");

    exit;
}