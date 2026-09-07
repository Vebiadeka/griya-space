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

if ($_SESSION["role"] !== "gudang") {
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL DATA MUTASI STOK
// ======================================================

$sql = "
    SELECT
        m.id_mutasi,
        m.id_produk,
        m.id_user,
        m.jenis_mutasi,
        m.jumlah,
        m.stok_sebelum,
        m.stok_sesudah,
        m.referensi,
        m.keterangan,
        m.tanggal_mutasi,

        p.kode_produk,
        p.nama_produk,
        p.satuan,

        u.nama_lengkap

    FROM mutasi_stok m

    INNER JOIN produk p
        ON m.id_produk = p.id_produk

    LEFT JOIN users u
        ON m.id_user = u.id_user

    ORDER BY
        m.id_mutasi DESC
";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {
    die(
        "Gagal mengambil data mutasi stok: " .
        mysqli_error($conn)
    );
}


$mutasi_list = [];

$total_mutasi = 0;

$total_masuk = 0;

$total_keluar = 0;


while (
    $mutasi =
    mysqli_fetch_assoc($result)
) {

    $mutasi_list[] =
        $mutasi;


    $total_mutasi++;


    $jenis =
        strtolower(
            trim(
                $mutasi["jenis_mutasi"]
            )
        );


    if (
        $jenis === "barang masuk"
    ) {

        $total_masuk +=
            (int) $mutasi["jumlah"];

    } else {

        $total_keluar +=
            (int) $mutasi["jumlah"];
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
        Mutasi Stok | Griya Space
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


        /* ==================================================
           HEADER
        ================================================== */

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

            transition: .2s;
        }


        .back-link:hover {

            background: white;

            color: #1d1d1d;
        }


        /* ==================================================
           MAIN
        ================================================== */

        main {

            max-width: 1350px;

            margin: 0 auto;

            padding: 45px 30px;
        }


        /* ==================================================
           PAGE HEADER
        ================================================== */

        .page-header {

            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 30px;
        }


        .page-title small {

            display: block;

            color: #777;

            font-size: 10px;

            letter-spacing: 3px;

            margin-bottom: 9px;
        }


        .page-title h1 {

            font-size: 32px;

            font-weight: 500;
        }


        .page-title p {

            margin-top: 9px;

            color: #777;

            font-size: 13px;

            line-height: 1.5;
        }


        /* ==================================================
           SUMMARY
        ================================================== */

        .summary-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 10px;

            min-width: 430px;
        }


        .summary-card {

            background: white;

            border: 1px solid #e4e4e4;

            padding: 15px 18px;
        }


        .summary-label {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1.5px;
        }


        .summary-value {

            display: block;

            margin-top: 6px;

            font-size: 22px;

            font-weight: 600;
        }


        .summary-in {

            color: #3d6c43;
        }


        .summary-out {

            color: #8a4444;
        }


        /* ==================================================
           TABLE
        ================================================== */

        .table-container {

            background: white;

            border: 1px solid #e4e4e4;

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 1250px;
        }


        thead {

            background: #1d1d1d;

            color: white;
        }


        th {

            padding: 15px 14px;

            text-align: left;

            font-size: 10px;

            font-weight: 600;

            letter-spacing: 1px;

            white-space: nowrap;
        }


        td {

            padding: 15px 14px;

            border-bottom: 1px solid #eeeeee;

            font-size: 12px;

            vertical-align: middle;
        }


        tbody tr:hover {

            background: #fafafa;
        }


        .product-code {

            color: #777;

            font-size: 10px;

            margin-top: 4px;
        }


        .product-name {

            font-weight: 600;
        }


        .type {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 9px;

            font-weight: 600;

            letter-spacing: .7px;

            white-space: nowrap;
        }


        .type-in {

            background: #edf5ef;

            color: #3d6c43;
        }


        .type-out {

            background: #f5eeee;

            color: #8a4444;
        }


        .type-default {

            background: #f1f1ef;

            color: #555;
        }


        .quantity {

            text-align: right;

            white-space: nowrap;

            font-weight: 600;
        }


        .stock-flow {

            white-space: nowrap;

            font-size: 12px;
        }


        .stock-before {

            color: #777;
        }


        .arrow {

            margin: 0 5px;

            color: #999;
        }


        .stock-after {

            font-weight: 700;
        }


        .reference {

            color: #555;

            font-size: 11px;

            white-space: nowrap;
        }


        .user {

            color: #666;

            white-space: nowrap;
        }


        .date {

            color: #666;

            white-space: nowrap;

            font-size: 11px;
        }


        .description {

            max-width: 230px;

            color: #777;

            line-height: 1.5;
        }


        /* ==================================================
           EMPTY
        ================================================== */

        .empty {

            padding: 70px 25px;

            text-align: center;

            color: #777;

            font-size: 12px;
        }


        .empty h2 {

            font-size: 24px;

            font-weight: 400;

            color: #1d1d1d;
        }


        .empty p {

            margin-top: 10px;

            line-height: 1.5;
        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 850px) {

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


            .page-header {

                flex-direction: column;

                align-items: flex-start;
            }


            .summary-grid {

                min-width: 0;

                width: 100%;
            }


            .page-title h1 {

                font-size: 28px;
            }

        }


        @media (max-width: 550px) {

            .summary-grid {

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
            STAFF GUDANG
        </div>


        <a
            href="../index.php"
            class="back-link"
        >
            KEMBALI
        </a>

    </div>

</header>


<main>


    <!-- ==================================================
         PAGE HEADER
    ================================================== -->

    <section class="page-header">


        <div class="page-title">

            <small>
                PERSEDIAAN
            </small>


            <h1>
                Mutasi Stok
            </h1>


            <p>
                Riwayat seluruh perubahan stok produk Griya Space.
            </p>

        </div>


        <div class="summary-grid">


            <div class="summary-card">

                <span class="summary-label">
                    TOTAL MUTASI
                </span>


                <span class="summary-value">
                    <?= $total_mutasi; ?>
                </span>

            </div>


            <div class="summary-card">

                <span class="summary-label">
                    TOTAL MASUK
                </span>


                <span class="summary-value summary-in">
                    +<?= $total_masuk; ?>
                </span>

            </div>


            <div class="summary-card">

                <span class="summary-label">
                    TOTAL KELUAR
                </span>


                <span class="summary-value summary-out">
                    -<?= $total_keluar; ?>
                </span>

            </div>


        </div>


    </section>


    <!-- ==================================================
         TABLE
    ================================================== -->

    <?php if (
        !empty($mutasi_list)
    ): ?>


        <section class="table-container">


            <table>


                <thead>

                    <tr>

                        <th>
                            JENIS MUTASI
                        </th>

                        <th>
                            PRODUK
                        </th>

                        <th>
                            JUMLAH
                        </th>

                        <th>
                            PERUBAHAN STOK
                        </th>

                        <th>
                            REFERENSI
                        </th>

                        <th>
                            USER
                        </th>

                        <th>
                            KETERANGAN
                        </th>

                        <th>
                            TANGGAL
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach (
                        $mutasi_list
                        as $mutasi
                    ): ?>


                        <?php

                        $jenis =
                            strtolower(
                                trim(
                                    $mutasi[
                                        "jenis_mutasi"
                                    ]
                                )
                            );


                        if (
                            $jenis ===
                            "barang masuk"
                        ) {

                            $typeClass =
                                "type-in";

                        } elseif (
                            $jenis ===
                            "barang keluar"
                            ||
                            $jenis ===
                            "penjualan"
                        ) {

                            $typeClass =
                                "type-out";

                        } else {

                            $typeClass =
                                "type-default";
                        }


                        ?>


                        <tr>


                            <!-- JENIS -->

                            <td>

                                <span
                                    class="type <?= $typeClass; ?>"
                                >

                                    <?= htmlspecialchars(
                                        $mutasi[
                                            "jenis_mutasi"
                                        ]
                                    ); ?>

                                </span>

                            </td>


                            <!-- PRODUK -->

                            <td>


                                <div class="product-name">

                                    <?= htmlspecialchars(
                                        $mutasi[
                                            "nama_produk"
                                        ]
                                    ); ?>

                                </div>


                                <div class="product-code">

                                    <?= htmlspecialchars(
                                        $mutasi[
                                            "kode_produk"
                                        ]
                                    ); ?>

                                </div>


                            </td>


                            <!-- JUMLAH -->

                            <td class="quantity">

                                <?php if (
                                    $jenis ===
                                    "barang masuk"
                                ): ?>

                                    +

                                <?php else: ?>

                                    -

                                <?php endif; ?>


                                <?= (int) $mutasi["jumlah"]; ?>

                                <?= htmlspecialchars(
                                    $mutasi["satuan"]
                                ); ?>

                            </td>


                            <!-- STOK -->

                            <td class="stock-flow">

                                <span class="stock-before">

                                    <?= (int) $mutasi[
                                        "stok_sebelum"
                                    ]; ?>

                                </span>


                                <span class="arrow">
                                    →
                                </span>


                                <span class="stock-after">

                                    <?= (int) $mutasi[
                                        "stok_sesudah"
                                    ]; ?>

                                </span>

                            </td>


                            <!-- REFERENSI -->

                            <td class="reference">

                                <?= htmlspecialchars(
                                    $mutasi[
                                        "referensi"
                                    ] ?? "-"
                                ); ?>

                            </td>


                            <!-- USER -->

                            <td class="user">

                                <?= htmlspecialchars(
                                    $mutasi[
                                        "nama_lengkap"
                                    ] ?? "-"
                                ); ?>

                            </td>


                            <!-- KETERANGAN -->

                            <td class="description">

                                <?= htmlspecialchars(
                                    $mutasi[
                                        "keterangan"
                                    ] ?? "-"
                                ); ?>

                            </td>


                            <!-- TANGGAL -->

                            <td class="date">

                                <?= htmlspecialchars(
                                    $mutasi[
                                        "tanggal_mutasi"
                                    ]
                                ); ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>


            </table>


        </section>


    <?php else: ?>


        <section class="table-container">


            <div class="empty">

                <h2>
                    Belum Ada Mutasi
                </h2>


                <p>
                    Belum ada perubahan stok yang tercatat.
                </p>

            </div>


        </section>


    <?php endif; ?>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>