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

if (
    $_SESSION["role"] !== "admin" &&
    $_SESSION["role"] !== "gudang"
) {
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL DATA BARANG KELUAR
// ======================================================

$sql = "
    SELECT
        bk.id_barang_keluar,
        bk.nomor_keluar,
        bk.tanggal_keluar,
        bk.keterangan,
        u.nama_lengkap
    FROM barang_keluar bk

    LEFT JOIN users u
        ON bk.id_user = u.id_user

    ORDER BY
        bk.id_barang_keluar DESC
";


$result = mysqli_query($conn, $sql);


if (!$result) {
    die(
        "Gagal mengambil data barang keluar: " .
        mysqli_error($conn)
    );
}


$jumlah_barang_keluar = mysqli_num_rows($result);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Barang Keluar | Griya Space</title>


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

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 18px 45px;
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

            font-size: 12px;

            border: 1px solid #555;

            padding: 9px 16px;

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
            max-width: 1200px;

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
        }


        .summary {
            background: white;

            border: 1px solid #e4e4e4;

            padding: 15px 20px;

            min-width: 170px;
        }


        .summary-label {
            display: block;

            color: #888;

            font-size: 10px;

            letter-spacing: 1.5px;
        }


        .summary-value {
            display: block;

            margin-top: 6px;

            font-size: 24px;

            font-weight: 600;
        }


        /* ==================================================
           TOOLBAR
        ================================================== */

        .toolbar {
            background: white;

            border: 1px solid #e4e4e4;

            padding: 16px 20px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 18px;
        }


        .toolbar-info {
            color: #777;

            font-size: 12px;
        }


        .add-button {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            background: #1d1d1d;

            color: white;

            padding: 11px 18px;

            font-size: 11px;

            letter-spacing: 1px;
        }


        .add-button:hover {
            background: #333;
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

            min-width: 850px;
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


        .number {
            font-weight: 600;

            white-space: nowrap;
        }


        .date {
            white-space: nowrap;

            color: #666;
        }


        .user {
            color: #666;
        }


        .description {
            color: #777;

            max-width: 300px;

            line-height: 1.5;
        }


        .empty {
            padding: 60px 20px;

            text-align: center;

            color: #777;
        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 700px) {

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
                align-items: flex-start;

                flex-direction: column;
            }


            .page-title h1 {
                font-size: 28px;
            }


            .summary {
                width: 100%;
            }


            .toolbar {
                flex-direction: column;

                align-items: stretch;
            }


            .add-button {
                width: 100%;
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

            <?= strtoupper(
                htmlspecialchars(
                    $_SESSION["role"]
                )
            ); ?>

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
                MANAJEMEN PERSEDIAAN
            </small>

            <h1>
                Barang Keluar
            </h1>

            <p>
                Catat pengeluaran barang dan perubahan stok Griya Space.
            </p>

        </div>


        <div class="summary">

            <span class="summary-label">
                TOTAL TRANSAKSI
            </span>

            <span class="summary-value">
                <?= $jumlah_barang_keluar; ?>
            </span>

        </div>


    </section>



    <!-- ==================================================
         TOOLBAR
    ================================================== -->

    <section class="toolbar">

        <div class="toolbar-info">

            Menampilkan
            <?= $jumlah_barang_keluar; ?>
            transaksi barang keluar.

        </div>


        <a
            href="tambah.php"
            class="add-button"
        >
            + TAMBAH BARANG KELUAR
        </a>

    </section>



    <!-- ==================================================
         TABLE
    ================================================== -->

    <section class="table-container">


        <?php if ($jumlah_barang_keluar > 0): ?>


            <table>


                <thead>

                    <tr>

                        <th>
                            NOMOR KELUAR
                        </th>

                        <th>
                            TANGGAL
                        </th>

                        <th>
                            INPUT OLEH
                        </th>

                        <th>
                            KETERANGAN
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php while (
                        $barang_keluar =
                        mysqli_fetch_assoc($result)
                    ): ?>


                        <tr>


                            <td class="number">

                                <?= htmlspecialchars(
                                    $barang_keluar["nomor_keluar"]
                                ); ?>

                            </td>


                            <td class="date">

                                <?= htmlspecialchars(
                                    $barang_keluar["tanggal_keluar"]
                                ); ?>

                            </td>


                            <td class="user">

                                <?= htmlspecialchars(
                                    $barang_keluar["nama_lengkap"]
                                    ?? "-"
                                ); ?>

                            </td>


                            <td class="description">

                                <?= htmlspecialchars(
                                    $barang_keluar["keterangan"]
                                    ?? "-"
                                ); ?>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="empty">

                Belum ada transaksi barang keluar.

            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>


<?php

mysqli_close($conn);

?>