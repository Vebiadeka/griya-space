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
// KONEKSI DATABASE
// ======================================================

require_once "../../../config/database.php";


// ======================================================
// AMBIL DATA USER + ROLE
// ======================================================

$sql = "
    SELECT
        u.id_user,
        u.nama_lengkap,
        u.username,
        u.email,
        u.no_telepon,
        u.alamat,
        u.status,
        u.created_at,

        r.id_role,
        r.nama_role

    FROM users u

    LEFT JOIN roles r
        ON u.id_role = r.id_role

    ORDER BY
        u.id_user ASC
";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {
    die(
        "Gagal mengambil data pengguna: " .
        mysqli_error($conn)
    );
}


$user_list = [];


while (
    $user =
    mysqli_fetch_assoc($result)
) {

    $user_list[] =
        $user;
}


// ======================================================
// HITUNG RINGKASAN
// ======================================================

$total_user =
    count($user_list);


$user_aktif = 0;

$user_nonaktif = 0;


$role_count = [];


foreach (
    $user_list as $user
) {

    if (
        strtolower(
            $user["status"] ?? ""
        ) === "aktif"
    ) {

        $user_aktif++;

    } else {

        $user_nonaktif++;
    }


    $role_name =
        $user["nama_role"] ?? "Tanpa Role";


    if (
        !isset(
            $role_count[
                $role_name
            ]
        )
    ) {

        $role_count[
            $role_name
        ] = 0;
    }


    $role_count[
        $role_name
    ]++;
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
        Pengguna | Admin Griya Space
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

            max-width: 1300px;

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

            min-width: 420px;
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

            margin-top: 7px;

            font-size: 23px;

            font-weight: 600;
        }


        .summary-aktif {

            color: #3d6c43;
        }


        .summary-nonaktif {

            color: #8a4444;
        }


        /* ==================================================
           ROLE SUMMARY
        ================================================== */

        .role-summary {

            display: flex;

            flex-wrap: wrap;

            gap: 8px;

            margin-bottom: 20px;
        }


        .role-badge {

            background: white;

            border: 1px solid #ddd;

            padding: 8px 12px;

            font-size: 10px;

            color: #666;

            border-radius: 4px;
        }


        .role-badge strong {

            color: #1d1d1d;
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

            min-width: 1200px;

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


        .id {

            color: #777;

            font-weight: 600;

            white-space: nowrap;
        }


        .name {

            font-weight: 600;
        }


        .username {

            color: #555;

            margin-top: 4px;

            font-size: 11px;
        }


        .contact {

            color: #666;

            line-height: 1.6;
        }


        .date {

            color: #777;

            white-space: nowrap;

            font-size: 11px;
        }


        /* ==================================================
           ROLE
        ================================================== */

        .role-badge-table {

            display: inline-block;

            padding: 6px 10px;

            background: #f1f1ef;

            border: 1px solid #ddd;

            border-radius: 4px;

            color: #555;

            font-size: 9px;

            font-weight: 600;

            letter-spacing: .6px;

            white-space: nowrap;
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

            background: #f5eeee;

            color: #8a4444;
        }


        .status-default {

            background: #f1f1ef;

            color: #777;
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

                width: 100%;

                min-width: 0;
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
         HEADER
    ================================================== -->

    <section class="page-header">


        <div class="page-title">

            <small>
                MANAJEMEN AKUN
            </small>


            <h1>
                Pengguna
            </h1>


            <p>
                Kelola dan pantau seluruh pengguna sistem Griya Space.
            </p>

        </div>


        <div class="summary-grid">


            <div class="summary-card">

                <span class="summary-label">
                    TOTAL USER
                </span>


                <span class="summary-value">
                    <?= $total_user; ?>
                </span>

            </div>


            <div class="summary-card">

                <span class="summary-label">
                    USER AKTIF
                </span>


                <span class="summary-value summary-aktif">
                    <?= $user_aktif; ?>
                </span>

            </div>


            <div class="summary-card">

                <span class="summary-label">
                    NONAKTIF
                </span>


                <span class="summary-value summary-nonaktif">
                    <?= $user_nonaktif; ?>
                </span>

            </div>


        </div>


    </section>


    <!-- ==================================================
         ROLE SUMMARY
    ================================================== -->

    <div class="role-summary">


        <?php foreach (
            $role_count
            as $role_name =>
            $jumlah
        ): ?>


            <div class="role-badge">

                <?= htmlspecialchars(
                    $role_name
                ); ?>

                :

                <strong>
                    <?= $jumlah; ?>
                </strong>

            </div>


        <?php endforeach; ?>


    </div>


    <!-- ==================================================
         TABLE
    ================================================== -->

    <?php if (
        !empty($user_list)
    ): ?>


        <section class="table-container">


            <table>


                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            PENGGUNA
                        </th>

                        <th>
                            ROLE
                        </th>

                        <th>
                            EMAIL
                        </th>

                        <th>
                            TELEPON
                        </th>

                        <th>
                            ALAMAT
                        </th>

                        <th>
                            STATUS
                        </th>

                        <th>
                            DIBUAT
                        </th>

                        <th>
                        AKSI

                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach (
                        $user_list
                        as $user
                    ): ?>


                        <?php

                        $status =
                            strtolower(
                                $user["status"]
                                ?? ""
                            );


                        if (
                            $status === "aktif"
                        ) {

                            $statusClass =
                                "status-aktif";

                        } elseif (
                            $status === "nonaktif"
                        ) {

                            $statusClass =
                                "status-nonaktif";

                        } else {

                            $statusClass =
                                "status-default";
                        }

                        ?>


                        <tr>


                            <td class="id">

                                #<?= (int) $user[
                                    "id_user"
                                ]; ?>

                            </td>


                            <td>


                                <div class="name">

                                    <?= htmlspecialchars(
                                        $user[
                                            "nama_lengkap"
                                        ]
                                    ); ?>

                                </div>


                                <div class="username">

                                    @<?= htmlspecialchars(
                                        $user[
                                            "username"
                                        ]
                                    ); ?>

                                </div>


                            </td>


                            <td>

                                <span
                                    class="
                                        role-badge-table
                                    "
                                >

                                    <?= htmlspecialchars(
                                        $user[
                                            "nama_role"
                                        ]
                                        ?? "Tanpa Role"
                                    ); ?>

                                </span>

                            </td>


                            <td class="contact">

                                <?= htmlspecialchars(
                                    $user[
                                        "email"
                                    ] ?? "-"
                                ); ?>

                            </td>


                            <td class="contact">

                                <?= htmlspecialchars(
                                    $user[
                                        "no_telepon"
                                    ] ?? "-"
                                ); ?>

                            </td>


                            <td class="contact">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $user[
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
                                        $user[
                                            "status"
                                        ]
                                        ?? "Tidak Diketahui"
                                    ); ?>

                                </span>

                            </td>


                            <td class="date">

                                <?= htmlspecialchars(
                                    $user[
                                        "created_at"
                                    ]
                                ); ?>

                            </td>

<td>

    <a
        href="edit.php?id=<?= (int) $user["id_user"]; ?>"
        style="
            display:inline-block;
            padding:7px 10px;
            background:#f0f0ee;
            border:1px solid #d9d9d6;
            color:#333;
            text-decoration:none;
            font-size:10px;
            border-radius:4px;
        "
    >
        EDIT
    </a>

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
                    Belum Ada Pengguna
                </h2>


                <p>
                    Belum ada akun pengguna yang terdaftar.
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