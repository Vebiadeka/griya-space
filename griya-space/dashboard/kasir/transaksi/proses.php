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
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL ID PESANAN
// ======================================================

$id_pesanan = (int) (
    $_GET["id"]
    ?? $_POST["id_pesanan"]
    ?? 0
);


if ($id_pesanan <= 0) {
    header("Location: ../pesanan/");
    exit;
}


// ======================================================
// ERROR
// ======================================================

$error = "";


// ======================================================
// AMBIL DATA PESANAN
// ======================================================

$sql = "
    SELECT
        p.id_pesanan,
        p.nomor_pesanan,
        p.id_pelanggan,
        p.tanggal_pesanan,
        p.subtotal,
        p.diskon,
        p.total_harga,
        p.status,
        p.catatan,

        pl.kode_pelanggan,
        pl.nama_pelanggan,
        pl.no_telepon,
        pl.email,
        pl.alamat

    FROM pesanan p

    INNER JOIN pelanggan pl
        ON p.id_pelanggan = pl.id_pelanggan

    WHERE p.id_pesanan = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);


if (!$stmt) {
    die(
        "Gagal mengambil data pesanan: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_pesanan
);


$stmt->execute();


$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    header("Location: ../pesanan/");
    exit;
}


$pesanan = $result->fetch_assoc();


$stmt->close();


// ======================================================
// CEK STATUS PESANAN
// ======================================================

if (
    $pesanan["status"] === "Selesai"
) {

    $conn->close();

    header(
        "Location: ../transaksi/index.php"
    );

    exit;
}


// ======================================================
// AMBIL DETAIL PESANAN
// ======================================================

$sql_detail = "
    SELECT
        dp.id_detail_pesanan,
        dp.id_produk,
        dp.jumlah,
        dp.harga_satuan,
        dp.subtotal,

        pr.kode_produk,
        pr.nama_produk,
        pr.satuan,
        pr.stok

    FROM detail_pesanan dp

    INNER JOIN produk pr
        ON dp.id_produk = pr.id_produk

    WHERE dp.id_pesanan = ?

    ORDER BY
        dp.id_detail_pesanan ASC
";


$stmt = $conn->prepare($sql_detail);


if (!$stmt) {
    die(
        "Gagal mengambil detail pesanan: " .
        $conn->error
    );
}


$stmt->bind_param(
    "i",
    $id_pesanan
);


$stmt->execute();


$result_detail =
    $stmt->get_result();


$detail_list = [];


while (
    $detail =
    $result_detail->fetch_assoc()
) {

    $detail_list[] =
        $detail;
}


$stmt->close();


// ======================================================
// CEK DETAIL
// ======================================================

if (
    empty($detail_list)
) {

    $conn->close();

    die(
        "Pesanan tidak memiliki detail produk."
    );
}


// ======================================================
// CEK STOK
// ======================================================

foreach (
    $detail_list as $detail
) {

    $stok =
        (int) $detail["stok"];

    $jumlah =
        (int) $detail["jumlah"];


    if (
        $stok < $jumlah
    ) {

        $error =
            "Stok produk \"" .
            $detail["nama_produk"] .
            "\" tidak mencukupi. " .
            "Stok tersedia: " .
            $stok .
            " " .
            $detail["satuan"] .
            ", kebutuhan: " .
            $jumlah .
            " " .
            $detail["satuan"] .
            ".";

        break;
    }
}


// ======================================================
// PROSES PEMBAYARAN
// ======================================================

$metode_pembayaran =
    $_POST["metode_pembayaran"]
    ?? "Cash";


$uang_diterima =
    (float) (
        $_POST["uang_diterima"]
        ?? 0
    );


if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    $error === ""
) {


    // ==================================================
    // VALIDASI METODE
    // ==================================================

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

        $error =
            "Metode pembayaran tidak valid.";
    }


    // ==================================================
    // VALIDASI UANG DITERIMA
    // ==================================================

    if (
        $error === "" &&
        $metode_pembayaran === "Cash"
    ) {

        if (
            $uang_diterima <
            (float) $pesanan["total_harga"]
        ) {

            $error =
                "Uang diterima tidak mencukupi.";
        }
    }


    // ==================================================
    // UNTUK NON-CASH
    // ==================================================

    if (
        $metode_pembayaran !== "Cash"
    ) {

        $uang_diterima =
            (float) $pesanan["total_harga"];
    }


    // ==================================================
    // HITUNG KEMBALIAN
    // ==================================================

    $kembalian = max(
        0,
        $uang_diterima -
        (float) $pesanan["total_harga"]
    );


    // ==================================================
    // MULAI TRANSAKSI DATABASE
    // ==================================================

    if (
        $error === ""
    ) {

        mysqli_begin_transaction(
            $conn
        );


        try {


            // ==========================================
            // CEK ULANG PESANAN
            // ==========================================

            $stmt =
                $conn->prepare("
                    SELECT
                        status
                    FROM pesanan
                    WHERE id_pesanan = ?
                    FOR UPDATE
                ");


            if (!$stmt) {
                throw new Exception(
                    "Gagal mengunci pesanan."
                );
            }


            $stmt->bind_param(
                "i",
                $id_pesanan
            );


            $stmt->execute();


            $result_status =
                $stmt->get_result();


            if (
                $result_status->num_rows !== 1
            ) {

                $stmt->close();

                throw new Exception(
                    "Pesanan tidak ditemukan."
                );
            }


            $status_pesanan =
                $result_status->fetch_assoc()[
                    "status"
                ];


            $stmt->close();


            if (
                $status_pesanan === "Selesai"
            ) {

                throw new Exception(
                    "Pesanan sudah selesai diproses."
                );
            }


            // ==========================================
            // GENERATE NOMOR TRANSAKSI
            // ==========================================

            $nomor_transaksi =
                "TRX-" .
                date("YmdHis");


            // ==========================================
            // CEK NOMOR TRANSAKSI
            // ==========================================

            $stmt =
                $conn->prepare("
                    SELECT
                        id_transaksi
                    FROM transaksi
                    WHERE nomor_transaksi = ?
                    LIMIT 1
                ");


            if (!$stmt) {
                throw new Exception(
                    "Gagal memeriksa nomor transaksi."
                );
            }


            $stmt->bind_param(
                "s",
                $nomor_transaksi
            );


            $stmt->execute();


            $cek_nomor =
                $stmt->get_result();


            $stmt->close();


            if (
                $cek_nomor->num_rows > 0
            ) {

                $nomor_transaksi =
                    "TRX-" .
                    date("YmdHis") .
                    rand(10, 99);
            }


            // ==========================================
            // INSERT TRANSAKSI
            // ==========================================

            $stmt =
                $conn->prepare("
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
                    (?, ?, ?, NULL, NOW(), ?, ?, ?, 'Selesai', ?)
                ");


            if (!$stmt) {
                throw new Exception(
                    "Gagal menyiapkan transaksi: " .
                    $conn->error
                );
            }


            $id_user =
                (int) $_SESSION["user_id"];


            $subtotal =
                (float) $pesanan["subtotal"];


            $diskon =
                (float) $pesanan["diskon"];


            $total_harga =
                (float) $pesanan["total_harga"];


            $keterangan =
                "Pembayaran pesanan " .
                $pesanan["nomor_pesanan"];


            /*
             * 7 parameter:
             *
             * s nomor_transaksi
             * i id_pelanggan
             * i id_user
             * d subtotal
             * d diskon
             * d total_harga
             * s keterangan
             */

            $stmt->bind_param(
                "siiddds",
                $nomor_transaksi,
                $pesanan["id_pelanggan"],
                $id_user,
                $subtotal,
                $diskon,
                $total_harga,
                $keterangan
            );


            if (
                !$stmt->execute()
            ) {

                throw new Exception(
                    "Gagal menyimpan transaksi: " .
                    $stmt->error
                );
            }


            $id_transaksi =
                $stmt->insert_id;


            $stmt->close();


            // ==========================================
            // INSERT DETAIL TRANSAKSI
            // ==========================================

            foreach (
                $detail_list as $detail
            ) {

                $id_produk =
                    (int) $detail["id_produk"];


                $jumlah =
                    (int) $detail["jumlah"];


                $harga_satuan =
                    (float) $detail["harga_satuan"];


                $diskon_detail = 0;
                    (float) (
                        $detail["diskon"]
                        ?? 0
                    );


                $subtotal_detail =
                    (float) $detail["subtotal"];


                $stmt =
                    $conn->prepare("
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


                $stmt->bind_param(
                    "iiiddd",
                    $id_transaksi,
                    $id_produk,
                    $jumlah,
                    $harga_satuan,
                    $diskon_detail,
                    $subtotal_detail
                );


                if (
                    !$stmt->execute()
                ) {

                    throw new Exception(
                        "Gagal menyimpan detail transaksi: " .
                        $stmt->error
                    );
                }


                $stmt->close();


                // ======================================
                // KURANGI STOK DENGAN AMAN
                // ======================================

                $stmt =
                    $conn->prepare("
                        SELECT
                            stok
                        FROM produk
                        WHERE id_produk = ?
                        FOR UPDATE
                    ");


                if (!$stmt) {

                    throw new Exception(
                        "Gagal mengunci stok produk."
                    );
                }


                $stmt->bind_param(
                    "i",
                    $id_produk
                );


                $stmt->execute();


                $result_stok =
                    $stmt->get_result();


                if (
                    $result_stok->num_rows !== 1
                ) {

                    $stmt->close();

                    throw new Exception(
                        "Produk tidak ditemukan."
                    );
                }


                $stok_data =
                    $result_stok->fetch_assoc();


                $stmt->close();


                $stok_sebelum =
                    (int) $stok_data["stok"];


                if (
                    $stok_sebelum <
                    $jumlah
                ) {

                    throw new Exception(
                        "Stok produk tidak mencukupi."
                    );
                }


                $stok_sesudah =
                    $stok_sebelum -
                    $jumlah;


                // ======================================
                // UPDATE STOK
                // ======================================

                $stmt =
                    $conn->prepare("
                        UPDATE produk
                        SET
                            stok = ?
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


                if (
                    !$stmt->execute()
                ) {

                    throw new Exception(
                        "Gagal memperbarui stok."
                    );
                }


                $stmt->close();


                // ======================================
                // MUTASI STOK
                // ======================================

                $referensi =
                    $nomor_transaksi;


                $keterangan_mutasi =
                    "Penjualan dari pesanan " .
                    $pesanan["nomor_pesanan"];


                $jenis_mutasi =
                    "Penjualan";


                $stmt =
                    $conn->prepare("
                        INSERT INTO mutasi_stok
                        (
                            id_produk,
                            id_user,
                            jenis_mutasi,
                            jumlah,
                            stok_sebelum,
                            stok_sesudah,
                            referensi,
                            keterangan,
                            tanggal_mutasi
                        )
                        VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?, NOW())
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


                if (
                    !$stmt->execute()
                ) {

                    throw new Exception(
                        "Gagal mencatat mutasi stok: " .
                        $stmt->error
                    );
                }


                $stmt->close();
            }


            // ==========================================
            // INSERT PEMBAYARAN
            // ==========================================

            $status_bayar =
                "Lunas";


            $stmt =
                $conn->prepare("
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
                $status_bayar
            );


            if (
                !$stmt->execute()
            ) {

                throw new Exception(
                    "Gagal menyimpan pembayaran: " .
                    $stmt->error
                );
            }


            $stmt->close();


            // ==========================================
            // UPDATE STATUS PESANAN
            // ==========================================

            $status_selesai =
                "Selesai";


            $stmt =
                $conn->prepare("
                    UPDATE pesanan
                    SET
                        status = ?
                    WHERE id_pesanan = ?
                ");


            if (!$stmt) {

                throw new Exception(
                    "Gagal menyiapkan update status pesanan."
                );
            }


            $stmt->bind_param(
                "si",
                $status_selesai,
                $id_pesanan
            );


            if (
                !$stmt->execute()
            ) {

                throw new Exception(
                    "Gagal mengubah status pesanan."
                );
            }


            $stmt->close();


            // ==========================================
            // COMMIT
            // ==========================================

            mysqli_commit(
                $conn
            );


            // ==========================================
            // SIMPAN INFO BERHASIL
            // ==========================================

            $_SESSION[
                "transaksi_berhasil"
            ] = [

                "id_transaksi" =>
                    $id_transaksi,

                "nomor_transaksi" =>
                    $nomor_transaksi,

                "nomor_pesanan" =>
                    $pesanan[
                        "nomor_pesanan"
                    ],

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
                "Location: berhasil.php"
            );

            exit;


        } catch (
            Throwable $e
        ) {


            // ==========================================
            // ROLLBACK
            // ==========================================

            mysqli_rollback(
                $conn
            );


            $error =
                $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Proses Transaksi | Griya Space
    </title>


    <style>

        * {
            box-sizing: border-box;

            margin: 0;

            padding: 0;
        }


        body {

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f5f5f3;

            color: #1d1d1d;
        }


        header {

            min-height: 76px;

            background: #1d1d1d;

            color: white;

            padding: 18px 45px;

            display: flex;

            align-items: center;

            justify-content: space-between;
        }


        .brand {

            font-size: 18px;

            letter-spacing: 6px;

            font-weight: 500;
        }


        .header-right {

            display: flex;

            align-items: center;

            gap: 20px;
        }


        .role {

            font-size: 11px;

            letter-spacing: 2px;

            color: #aaa;
        }


        .back-link {

            color: white;

            text-decoration: none;

            border: 1px solid #555;

            padding: 9px 16px;

            font-size: 12px;
        }


        .back-link:hover {

            background: white;

            color: #1d1d1d;
        }


        main {

            max-width: 1000px;

            margin: 0 auto;

            padding: 45px 30px;
        }


        .page-header {

            margin-bottom: 25px;
        }


        .page-header small {

            display: block;

            color: #777;

            font-size: 10px;

            letter-spacing: 3px;

            margin-bottom: 9px;
        }


        .page-header h1 {

            font-size: 32px;

            font-weight: 500;
        }


        .page-header p {

            margin-top: 9px;

            color: #777;

            font-size: 13px;
        }


        .error {

            margin-bottom: 20px;

            padding: 14px 16px;

            background: #faf0f0;

            border: 1px solid #ead0d0;

            border-left: 4px solid #8b3030;

            color: #8b3030;

            font-size: 12px;

            line-height: 1.5;
        }


        .grid {

            display: grid;

            grid-template-columns:
                1.2fr
                .8fr;

            gap: 20px;

            align-items: start;
        }


        .card {

            background: white;

            border: 1px solid #e4e4e4;
        }


        .card-title {

            padding: 18px 22px;

            border-bottom: 1px solid #eeeeee;

            font-size: 11px;

            color: #777;

            letter-spacing: 2px;
        }


        .card-body {

            padding: 22px;
        }


        .info-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 11px 0;

            border-bottom: 1px solid #eeeeee;

            font-size: 12px;
        }


        .info-row:last-child {

            border-bottom: none;
        }


        .info-label {

            color: #888;
        }


        .info-value {

            text-align: right;

            font-weight: 600;
        }


        .product {

            padding: 15px 0;

            border-bottom: 1px solid #eeeeee;
        }


        .product:last-child {

            border-bottom: none;
        }


        .product-name {

            font-weight: 600;

            font-size: 13px;
        }


        .product-code {

            margin-top: 4px;

            color: #999;

            font-size: 10px;
        }


        .product-meta {

            display: flex;

            justify-content: space-between;

            gap: 15px;

            margin-top: 9px;

            font-size: 11px;

            color: #666;
        }


        .payment-group {

            margin-bottom: 18px;
        }


        label {

            display: block;

            margin-bottom: 7px;

            font-size: 11px;

            font-weight: 600;
        }


        select,
        input {

            width: 100%;

            min-height: 43px;

            border: 1px solid #d8d8d5;

            background: #fafafa;

            padding: 10px 12px;

            border-radius: 4px;

            font-family: inherit;

            font-size: 12px;

            outline: none;
        }


        select:focus,
        input:focus {

            background: white;

            border-color: #777;
        }


        .total-box {

            padding: 16px;

            background: #f7f7f5;

            border: 1px solid #e4e4e1;

            margin-bottom: 18px;
        }


        .total-label {

            color: #777;

            font-size: 10px;

            letter-spacing: 1px;
        }


        .total-value {

            display: block;

            margin-top: 6px;

            font-size: 25px;

            font-weight: 700;
        }


        .change-box {

            padding: 14px;

            background: #f7f7f5;

            border: 1px solid #e4e4e1;

            margin-top: 15px;

            display: flex;

            justify-content: space-between;

            gap: 15px;

            font-size: 12px;
        }


        .submit-button {

            width: 100%;

            border: none;

            background: #1d1d1d;

            color: white;

            padding: 13px 18px;

            font-size: 11px;

            letter-spacing: 1px;

            cursor: pointer;
        }


        .submit-button:hover {

            background: #333;
        }


        .note {

            margin-top: 14px;

            color: #999;

            font-size: 10px;

            line-height: 1.5;
        }


        @media (max-width: 800px) {

            header {

                padding: 18px 20px;
            }


            .brand {

                font-size: 15px;

                letter-spacing: 4px;
            }


            .role {

                display: none;
            }


            main {

                padding: 30px 18px;
            }


            .grid {

                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<header>

    <div class="brand">
        GRIYA SPACE
    </div>


    <div class="header-right">

        <div class="role">
            KASIR
        </div>


        <a
            href="../pesanan/"
            class="back-link"
        >
            KEMBALI
        </a>

    </div>

</header>


<main>


    <section class="page-header">

        <small>
            MANAJEMEN PENJUALAN
        </small>


        <h1>
            Proses Transaksi
        </h1>


        <p>
            Periksa pesanan dan lakukan pembayaran pelanggan.
        </p>

    </section>


    <?php if (
        $error !== ""
    ): ?>

        <div class="error">

            <?= htmlspecialchars(
                $error
            ); ?>

        </div>

    <?php endif; ?>


    <div class="grid">


        <!-- ==================================================
             PESANAN
        ================================================== -->

        <section class="card">


            <div class="card-title">
                INFORMASI PESANAN
            </div>


            <div class="card-body">


                <div class="info-row">

                    <span class="info-label">
                        Nomor Pesanan
                    </span>


                    <span class="info-value">

                        <?= htmlspecialchars(
                            $pesanan[
                                "nomor_pesanan"
                            ]
                        ); ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        Pelanggan
                    </span>


                    <span class="info-value">

                        <?= htmlspecialchars(
                            $pesanan[
                                "nama_pelanggan"
                            ]
                        ); ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        Tanggal
                    </span>


                    <span class="info-value">

                        <?= htmlspecialchars(
                            $pesanan[
                                "tanggal_pesanan"
                            ]
                        ); ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        Status
                    </span>


                    <span class="info-value">

                        <?= htmlspecialchars(
                            $pesanan[
                                "status"
                            ]
                        ); ?>

                    </span>

                </div>


                <div style="
                    margin-top:20px;
                    border-top:1px solid #eeeeee;
                ">


                    <?php foreach (
                        $detail_list
                        as $detail
                    ): ?>


                        <div class="product">


                            <div class="product-name">

                                <?= htmlspecialchars(
                                    $detail[
                                        "nama_produk"
                                    ]
                                ); ?>

                            </div>


                            <div class="product-code">

                                <?= htmlspecialchars(
                                    $detail[
                                        "kode_produk"
                                    ]
                                ); ?>

                            </div>


                            <div class="product-meta">

                                <span>

                                    <?= (int) $detail[
                                        "jumlah"
                                    ]; ?>

                                    ×

                                    <?= htmlspecialchars(
                                        $detail[
                                            "satuan"
                                        ]
                                    ); ?>

                                    × Rp

                                    <?= number_format(
                                        $detail[
                                            "harga_satuan"
                                        ],
                                        0,
                                        ",",
                                        "."
                                    ); ?>

                                </span>


                                <strong>

                                    Rp <?= number_format(
                                        $detail[
                                            "subtotal"
                                        ],
                                        0,
                                        ",",
                                        "."
                                    ); ?>

                                </strong>

                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>


                <div style="
                    margin-top:15px;
                    padding-top:15px;
                    border-top:1px solid #eeeeee;
                ">


                    <div class="info-row">

                        <span class="info-label">
                            Subtotal
                        </span>


                        <span class="info-value">

                            Rp <?= number_format(
                                $pesanan[
                                    "subtotal"
                                ],
                                0,
                                ",",
                                "."
                            ); ?>

                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">
                            Diskon
                        </span>


                        <span class="info-value">

                            Rp <?= number_format(
                                $pesanan[
                                    "diskon"
                                ],
                                0,
                                ",",
                                "."
                            ); ?>

                        </span>

                    </div>


                    <div
                        class="info-row"
                        style="
                            font-size:15px;
                            border-bottom:none;
                        "
                    >

                        <span
                            style="font-weight:700;"
                        >
                            TOTAL
                        </span>


                        <span
                            class="info-value"
                            style="font-size:17px;"
                        >

                            Rp <?= number_format(
                                $pesanan[
                                    "total_harga"
                                ],
                                0,
                                ",",
                                "."
                            ); ?>

                        </span>

                    </div>


                </div>


            </div>


        </section>


        <!-- ==================================================
             PEMBAYARAN
        ================================================== -->

        <section class="card">


            <div class="card-title">
                PEMBAYARAN
            </div>


            <div class="card-body">


                <form
                    action="proses.php?id=<?= $id_pesanan; ?>"
                    method="POST"
                >


                    <input
                        type="hidden"
                        name="id_pesanan"
                        value="<?= $id_pesanan; ?>"
                    >


                    <div class="total-box">

                        <span class="total-label">
                            TOTAL YANG HARUS DIBAYAR
                        </span>


                        <span class="total-value">

                            Rp <?= number_format(
                                $pesanan[
                                    "total_harga"
                                ],
                                0,
                                ",",
                                "."
                            ); ?>

                        </span>

                    </div>


                    <div class="payment-group">


                        <label
                            for="metode_pembayaran"
                        >
                            Metode Pembayaran
                        </label>


                        <select
                            id="metode_pembayaran"
                            name="metode_pembayaran"
                            required
                        >

                            <option
                                value="Cash"
                                <?= (
                                    $metode_pembayaran
                                    ===
                                    "Cash"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Cash
                            </option>


                            <option
                                value="Transfer"
                                <?= (
                                    $metode_pembayaran
                                    ===
                                    "Transfer"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Transfer
                            </option>


                            <option
                                value="QRIS"
                                <?= (
                                    $metode_pembayaran
                                    ===
                                    "QRIS"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                QRIS
                            </option>


                            <option
                                value="Debit"
                                <?= (
                                    $metode_pembayaran
                                    ===
                                    "Debit"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Debit
                            </option>


                            <option
                                value="Kredit"
                                <?= (
                                    $metode_pembayaran
                                    ===
                                    "Kredit"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Kredit
                            </option>

                        </select>


                    </div>


                    <div
                        class="payment-group"
                        id="cash-group"
                    >


                        <label
                            for="uang_diterima"
                        >
                            Uang Diterima
                        </label>


                        <input
                            type="number"
                            id="uang_diterima"
                            name="uang_diterima"
                            min="0"
                            step="1"
                            value="<?= (
                                $uang_diterima > 0
                            )
                                ? $uang_diterima
                                : ""
                            ?>"
                            placeholder="Masukkan jumlah uang"
                        >

                    </div>


                    <div class="change-box">

                        <span>
                            Kembalian
                        </span>


                        <strong
                            id="kembalian-display"
                        >
                            Rp 0
                        </strong>

                    </div>


                    <button
                        type="submit"
                        class="submit-button"
                        onclick="
                            return confirm(
                                'Yakin pembayaran sudah benar dan ingin menyelesaikan transaksi?'
                            );
                        "
                    >
                        KONFIRMASI PEMBAYARAN
                    </button>


                    <div class="note">

                        Setelah pembayaran dikonfirmasi,
                        transaksi akan disimpan, stok akan
                        dikurangi, mutasi stok dicatat,
                        dan pesanan menjadi Selesai.

                    </div>


                </form>


            </div>


        </section>


    </div>


</main>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const metode =
            document.getElementById(
                "metode_pembayaran"
            );

        const uang =
            document.getElementById(
                "uang_diterima"
            );

        const cashGroup =
            document.getElementById(
                "cash-group"
            );

        const kembalian =
            document.getElementById(
                "kembalian-display"
            );


        const total =
            <?= json_encode(
                (float) $pesanan["total_harga"]
            ); ?>;


        function formatRupiah(
            value
        ) {

            return "Rp " +
                Number(
                    value
                ).toLocaleString(
                    "id-ID"
                );
        }


        function updatePayment() {

            if (
                metode.value ===
                "Cash"
            ) {

                cashGroup.style.display =
                    "block";


                uang.disabled =
                    false;


                const diterima =
                    Number(
                        uang.value
                    ) || 0;


                const kembali =
                    Math.max(
                        0,
                        diterima - total
                    );


                kembalian.textContent =
                    formatRupiah(
                        kembali
                    );

            } else {

                cashGroup.style.display =
                    "none";


                uang.disabled =
                    true;


                kembalian.textContent =
                    formatRupiah(
                        0
                    );
            }
        }


        metode.addEventListener(
            "change",
            updatePayment
        );


        uang.addEventListener(
            "input",
            updatePayment
        );


        updatePayment();

    }
);

</script>


</body>

</html>


<?php

mysqli_close($conn);

?>