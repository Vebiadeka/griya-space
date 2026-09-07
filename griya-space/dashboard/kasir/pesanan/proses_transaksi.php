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
// CEK ROLE KASIR
// ======================================================

if ($_SESSION["role"] !== "kasir") {
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

$id_pesanan = (int) ($_POST["id_pesanan"] ?? 0);

$metode_pembayaran =
    trim($_POST["metode_pembayaran"] ?? "");

$uang_diterima =
    (float) ($_POST["uang_diterima"] ?? 0);


// ======================================================
// VALIDASI DASAR
// ======================================================

if (
    $id_pesanan <= 0 ||
    $metode_pembayaran === ""
) {
    $_SESSION["transaksi_error"] =
        "Data transaksi belum lengkap.";

    header(
        "Location: transaksi.php?id=" .
        $id_pesanan
    );

    exit;
}


// ======================================================
// VALIDASI METODE PEMBAYARAN
// ======================================================

$metode_valid = [
    "Cash",
    "Transfer",
    "QRIS",
    "Debit",
    "Kredit"
];


if (
    !in_array(
        $metode_pembayaran,
        $metode_valid,
        true
    )
) {
    $_SESSION["transaksi_error"] =
        "Metode pembayaran tidak valid.";

    header(
        "Location: transaksi.php?id=" .
        $id_pesanan
    );

    exit;
}


// ======================================================
// MULAI TRANSAKSI DATABASE
// ======================================================

mysqli_begin_transaction($conn);

try {


    // ==================================================
    // 1. AMBIL PESANAN + KUNCI BARIS
    // ==================================================

    $stmt = $conn->prepare("
        SELECT
            id_pesanan,
            nomor_pesanan,
            id_pelanggan,
            subtotal,
            diskon,
            total_harga,
            status
        FROM pesanan
        WHERE id_pesanan = ?
        LIMIT 1
        FOR UPDATE
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal mengambil data pesanan."
        );
    }


    $stmt->bind_param(
        "i",
        $id_pesanan
    );


    if (!$stmt->execute()) {
        throw new Exception(
            "Gagal membaca pesanan."
        );
    }


    $result =
        $stmt->get_result();


    if ($result->num_rows !== 1) {
        throw new Exception(
            "Pesanan tidak ditemukan."
        );
    }


    $pesanan =
        $result->fetch_assoc();


    $stmt->close();


    // ==================================================
    // CEK STATUS PESANAN
    // ==================================================

    if (
        $pesanan["status"] === "Dibatalkan"
    ) {
        throw new Exception(
            "Pesanan sudah dibatalkan."
        );
    }


    if (
        $pesanan["status"] === "Selesai"
    ) {
        throw new Exception(
            "Pesanan sudah selesai diproses."
        );
    }


    // ==================================================
    // DATA PESANAN
    // ==================================================

    $nomor_pesanan =
        $pesanan["nomor_pesanan"];

    $subtotal =
        (float) $pesanan["subtotal"];

    $diskon =
        (float) $pesanan["diskon"];

    $total_harga =
        (float) $pesanan["total_harga"];


    // ==================================================
    // 2. AMBIL DETAIL PESANAN
    // ==================================================

    $stmt = $conn->prepare("
        SELECT
            dp.id_produk,
            dp.jumlah,
            dp.harga_satuan,
            dp.subtotal,

            pr.kode_produk,
            pr.nama_produk,
            pr.satuan,
            pr.stok,
            pr.status

        FROM detail_pesanan dp

        INNER JOIN produk pr
            ON dp.id_produk = pr.id_produk

        WHERE dp.id_pesanan = ?

        ORDER BY dp.id_detail_pesanan ASC

        FOR UPDATE
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal mengambil detail pesanan."
        );
    }


    $stmt->bind_param(
        "i",
        $id_pesanan
    );


    if (!$stmt->execute()) {
        throw new Exception(
            "Gagal membaca detail pesanan."
        );
    }


    $resultDetail =
        $stmt->get_result();


    if (
        $resultDetail->num_rows === 0
    ) {
        throw new Exception(
            "Detail pesanan tidak ditemukan."
        );
    }


    $detail_items = [];


    while (
        $detail =
        $resultDetail->fetch_assoc()
    ) {
        $detail_items[] =
            $detail;
    }


    $stmt->close();


    // ==================================================
    // 3. VALIDASI SEMUA STOK
    // ==================================================

    foreach (
        $detail_items as $detail
    ) {

        $jumlah =
            (int) $detail["jumlah"];

        $stok =
            (int) $detail["stok"];


        if (
            $detail["status"] !== "Aktif"
        ) {

            throw new Exception(
                "Produk \"" .
                $detail["nama_produk"] .
                "\" sedang tidak aktif."
            );
        }


        if (
            $jumlah > $stok
        ) {

            throw new Exception(
                "Stok produk \"" .
                $detail["nama_produk"] .
                "\" tidak mencukupi. " .
                "Tersedia: " .
                $stok .
                " " .
                $detail["satuan"] .
                "."
            );
        }
    }


    // ==================================================
    // 4. VALIDASI PEMBAYARAN
    // ==================================================

    if (
        $metode_pembayaran === "Cash"
    ) {

        if (
            $uang_diterima < $total_harga
        ) {

            throw new Exception(
                "Uang yang diterima kurang dari total pembayaran."
            );
        }

    } else {

        $uang_diterima =
            $total_harga;
    }


    $kembalian =
        max(
            0,
            $uang_diterima -
            $total_harga
        );


    // ==================================================
    // 5. BUAT NOMOR TRANSAKSI
    // ==================================================

    $nomor_transaksi =
        "TRX-" .
        date("YmdHis");


    $id_user =
        (int) $_SESSION["user_id"];


    $tanggal_transaksi =
        date("Y-m-d H:i:s");


    // ==================================================
    // 6. INSERT TRANSAKSI
    // ==================================================

    $stmt = $conn->prepare("
        INSERT INTO transaksi
        (
            nomor_transaksi,
            id_pelanggan,
            id_user,
            id_promo,
            tanggal_transaksi,
            subtotal,
            diskon,
            total_harga,
            status,
            keterangan
        )
        VALUES
        (?, ?, ?, NULL, ?, ?, ?, ?, ?, ?)
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan transaksi."
        );
    }


    $status_transaksi =
        "Selesai";


    $keterangan_transaksi =
        "Pembayaran pesanan " .
        $nomor_pesanan;


    $stmt->bind_param(
        "siisdddss",
        $nomor_transaksi,
        $pesanan["id_pelanggan"],
        $id_user,
        $tanggal_transaksi,
        $subtotal,
        $diskon,
        $total_harga,
        $status_transaksi,
        $keterangan_transaksi
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal menyimpan transaksi: " .
            $stmt->error
        );
    }


    $id_transaksi =
        $conn->insert_id;


    $stmt->close();


    // ==================================================
    // 7. INSERT DETAIL TRANSAKSI
    // ==================================================

    $stmt = $conn->prepare("
        INSERT INTO detail_transaksi
        (
            id_transaksi,
            id_produk,
            jumlah,
            harga_satuan,
            diskon,
            subtotal
        )
        VALUES
        (?, ?, ?, ?, ?, ?)
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan detail transaksi."
        );
    }


    foreach (
        $detail_items as $detail
    ) {

        $id_produk =
            (int) $detail["id_produk"];

        $jumlah =
            (int) $detail["jumlah"];

        $harga_satuan =
            (float) $detail["harga_satuan"];

        $diskon_detail =
            0;

        $subtotal_detail =
            (float) $detail["subtotal"];


        $stmt->bind_param(
            "iiiddd",
            $id_transaksi,
            $id_produk,
            $jumlah,
            $harga_satuan,
            $diskon_detail,
            $subtotal_detail
        );


        if (!$stmt->execute()) {

            throw new Exception(
                "Gagal menyimpan detail transaksi: " .
                $stmt->error
            );
        }
    }


    $stmt->close();


    // ==================================================
    // 8. INSERT PEMBAYARAN
    // ==================================================

    $status_pembayaran =
        "Lunas";


    $stmt = $conn->prepare("
        INSERT INTO pembayaran
        (
            id_transaksi,
            metode_pembayaran,
            jumlah_bayar,
            uang_diterima,
            kembalian,
            status
        )
        VALUES
        (?, ?, ?, ?, ?, ?)
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan pembayaran."
        );
    }


    $stmt->bind_param(
        "isddds",
        $id_transaksi,
        $metode_pembayaran,
        $total_harga,
        $uang_diterima,
        $kembalian,
        $status_pembayaran
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal menyimpan pembayaran: " .
            $stmt->error
        );
    }


    $stmt->close();


    // ==================================================
    // 9. UPDATE STOK + CATAT MUTASI
    // ==================================================

    $jenis_mutasi =
        "Penjualan";


    $referensi =
        $nomor_transaksi;


    foreach (
        $detail_items as $detail
    ) {

        $id_produk =
            (int) $detail["id_produk"];

        $jumlah =
            (int) $detail["jumlah"];

        $stok_sebelum =
            (int) $detail["stok"];

        $stok_sesudah =
            $stok_sebelum -
            $jumlah;


        // ----------------------------------------------
        // UPDATE STOK
        // ----------------------------------------------

        $stmt = $conn->prepare("
            UPDATE produk
            SET stok = ?
            WHERE id_produk = ?
        ");


        if (!$stmt) {
            throw new Exception(
                "Gagal menyiapkan update stok."
            );
        }


        $stmt->bind_param(
            "ii",
            $stok_sesudah,
            $id_produk
        );


        if (!$stmt->execute()) {

            throw new Exception(
                "Gagal memperbarui stok: " .
                $stmt->error
            );
        }


        $stmt->close();


        // ----------------------------------------------
        // MUTASI STOK
        // ----------------------------------------------

        $keterangan_mutasi =
            "Penjualan dari pesanan " .
            $nomor_pesanan;


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
    }


    // ==================================================
    // 10. UPDATE PESANAN
    // ==================================================

    $status_pesanan =
        "Selesai";


    $stmt = $conn->prepare("
        UPDATE pesanan
        SET status = ?
        WHERE id_pesanan = ?
    ");


    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan perubahan status pesanan."
        );
    }


    $stmt->bind_param(
        "si",
        $status_pesanan,
        $id_pesanan
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal memperbarui status pesanan: " .
            $stmt->error
        );
    }


    $stmt->close();


    // ==================================================
    // COMMIT
    // ==================================================

    mysqli_commit($conn);


    $_SESSION["transaksi_berhasil"] = [

        "id_transaksi" =>
            $id_transaksi,

        "nomor_transaksi" =>
            $nomor_transaksi,

        "id_pesanan" =>
            $id_pesanan,

        "total_harga" =>
            $total_harga,

        "metode_pembayaran" =>
            $metode_pembayaran,

        "uang_diterima" =>
            $uang_diterima,

        "kembalian" =>
            $kembalian
    ];


    $conn->close();


    header(
        "Location: transaksi_berhasil.php"
    );

    exit;


} catch (Throwable $e) {

    // ==================================================
    // ROLLBACK
    // ==================================================

    mysqli_rollback($conn);

    $conn->close();


    $_SESSION["transaksi_error"] =
        $e->getMessage();


    header(
        "Location: transaksi.php?id=" .
        $id_pesanan
    );

    exit;
}