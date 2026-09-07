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

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../../auth/login.php");
    exit;
}


// ======================================================
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL DATA PELANGGAN
// ======================================================

$sql = "
    SELECT
        p.id_pelanggan,
        p.id_user,
        p.kode_pelanggan,
        p.nama_pelanggan,
        p.no_telepon,
        p.email,
        p.alamat,
        u.username,
        u.status AS status_user
    FROM pelanggan p

    LEFT JOIN users u
        ON p.id_user = u.id_user

    ORDER BY
        p.id_pelanggan DESC
";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {
    die(
        "Gagal mengambil data pelanggan: " .
        mysqli_error($conn)
    );
}


$pelanggan_list = [];


while (
    $pelanggan =
    mysqli_fetch_assoc($result)
) {

    $pelanggan_list[] =
        $pelanggan;
}


$total_pelanggan =
    count($pelanggan_list);

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
        Pelanggan | Admin Griya Space
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

            max-width: 1250px;

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


        .summary-card {

            min-width: 170px;

            background: white;

            border: 1px solid #e4e4e4;

            padding: 16px 20px;
        }


        .summary-label {

            display: block;

            color: #888;

            font-size: 9px;

            letter-spacing: 1.5px;
        }


        .summary-value {

            display: block;

            margin-top: 7px;

            font-size: 25px;

            font-weight: 600;
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

            min-width: 1100px;

            border-collapse: collapse;
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


        .code {

            font-weight: 600;

            white-space: nowrap;
        }


        .name {

            font-weight: 600;
        }


        .username {

            color: #666;

            font-size: 11px;
        }


        .contact {

            color: #666;

            line-height: 1.6;
        }


        .address {

            max-width: 250px;

            color: #666;

            line-height: 1.5;
        }


        /* ==================================================
           STATUS
        ================================================== */

        .status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 9px;

            font-weight: 600;

            letter-spacing: .7px;

            white-space: nowrap;
        }


        .status-aktif {

            background: #eef5ef;

            color: #3d6c43;
        }


        .status-nonaktif {

            background: #f1f1ef;

            color: #777;
        }


        .status-default {

            background: #f1f1ef;

            color: #555;
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

            color: #1d1d1d;

            font-size: 24px;

            font-weight: 400;
        }


        .empty p {

            margin-top: 10px;

            line-height: 1.5;
        }


        /* ==================================================
           MOBILE
        ================================================== */

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


            .page-header {

                flex-direction: column;

                align-items: flex-start;
            }


            .summary-card {

                width: 100%;
            }


            .page-title h1 {

                font-size: 28px;
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
            ADMIN
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
                DATA MASTER
            </small>


            <h1>
                Pelanggan
            </h1>


            <p>
                Lihat data pelanggan yang terdaftar pada Griya Space.
            </p>

        </div>


        <div class="summary-card">

            <span class="summary-label">
                TOTAL PELANGGAN
            </span>


            <span class="summary-value">

                <?= $total_pelanggan; ?>

            </span>

        </div>


    </section>


    <!-- ==================================================
         TABLE
    ================================================== -->

    <?php if (
        !empty($pelanggan_list)
    ): ?>


        <section class="table-container">


            <table>


                <thead>

                    <tr>

                        <th>
                            KODE
                        </th>

                        <th>
                            NAMA PELANGGAN
                        </th>

                        <th>
                            AKUN
                        </th>

                        <th>
                            TELEPON
                        </th>

                        <th>
                            EMAIL
                        </th>

                        <th>
                            ALAMAT
                        </th>

                        <th>
                            STATUS
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach (
                        $pelanggan_list
                        as $pelanggan
                    ): ?>


                        <?php

                        $status =
                            $pelanggan[
                                "status_user"
                            ] ?? "";


                        if (
                            strtolower(
                                $status
                            ) === "aktif"
                        ) {

                            $statusClass =
                                "status-aktif";

                        } elseif (
                            strtolower(
                                $status
                            ) === "nonaktif"
                        ) {

                            $statusClass =
                                "status-nonaktif";

                        } else {

                            $statusClass =
                                "status-default";
                        }

                        ?>


                        <tr>


                            <td class="code">

                                <?= htmlspecialchars(
                                    $pelanggan[
                                        "kode_pelanggan"
                                    ]
                                ); ?>

                            </td>


                            <td>

                                <div class="name">

                                    <?= htmlspecialchars(
                                        $pelanggan[
                                            "nama_pelanggan"
                                        ]
                                    ); ?>

                                </div>

                            </td>


                            <td>

                                <div class="name">

                                    <?= htmlspecialchars(
                                        $pelanggan[
                                            "username"
                                        ] ?? "-"
                                    ); ?>

                                </div>


                                <div class="username">

                                    User ID:
                                    <?= (int) $pelanggan[
                                        "id_user"
                                    ]; ?>

                                </div>

                            </td>


                            <td class="contact">

                                <?= htmlspecialchars(
                                    $pelanggan[
                                        "no_telepon"
                                    ] ?? "-"
                                ); ?>

                            </td>


                            <td class="contact">

                                <?= htmlspecialchars(
                                    $pelanggan[
                                        "email"
                                    ] ?? "-"
                                ); ?>

                            </td>


                            <td class="address">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $pelanggan[
                                            "alamat"
                                        ] ?? "-"
                                    )
                                ); ?>

                            </td>


                            <td>

                                <span
                                    class="
                                        status
                                        <?= $statusClass; ?>
                                    "
                                >

                                    <?= htmlspecialchars(
                                        $status ?: "Tidak Diketahui"
                                    ); ?>

                                </span>

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
                    Belum Ada Pelanggan
                </h2>


                <p>
                    Belum ada data pelanggan yang terdaftar.
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