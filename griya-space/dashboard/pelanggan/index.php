<?php

session_start();


// ======================================================
// CEK LOGIN
// ======================================================

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}


// ======================================================
// CEK ROLE
// ======================================================

if ($_SESSION["role"] !== "pelanggan") {
    die("ROLE SESSION TIDAK SESUAI. Role saat ini: " . ($_SESSION["role"] ?? "TIDAK ADA"));
}


// ======================================================
// DATA SESSION
// ======================================================

$nama = $_SESSION["nama"] ?? "Pelanggan";

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard Pelanggan | Griya Space</title>


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

            height: 76px;

            background: #1d1d1d;

            color: white;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 45px;

        }


        .brand {

            font-size: 18px;

            letter-spacing: 6px;

            font-weight: 500;

        }


        .header-right {

            display: flex;

            align-items: center;

            gap: 25px;

        }


        .role {

            font-size: 11px;

            letter-spacing: 2px;

            color: #aaa;

        }


        .logout {

            color: white;

            text-decoration: none;

            font-size: 13px;

            border: 1px solid #555;

            padding: 9px 16px;

            transition: .2s;

        }


        .logout:hover {

            background: white;

            color: #1d1d1d;

        }


        /* ==================================================
           MAIN
        ================================================== */

        main {

            max-width: 1250px;

            margin: auto;

            padding: 55px 30px;

        }


        .welcome {

            margin-bottom: 40px;

        }


        .welcome small {

            color: #777;

            letter-spacing: 3px;

            font-size: 11px;

        }


        .welcome h1 {

            margin-top: 10px;

            font-size: 34px;

            font-weight: 500;

        }


        .welcome p {

            margin-top: 10px;

            color: #777;

            font-size: 14px;

        }


        /* ==================================================
           MENU
        ================================================== */

        .menu-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

        }


        .menu-card {

            background: white;

            border: 1px solid #e5e5e5;

            padding: 28px;

            min-height: 170px;

            text-decoration: none;

            color: #1d1d1d;

            transition: .25s;

            position: relative;

        }


        .menu-card:hover {

            transform: translateY(-4px);

            border-color: #aaa;

            box-shadow:
                0 10px 30px
                rgba(0,0,0,.06);

        }


        .menu-number {

            font-size: 11px;

            letter-spacing: 2px;

            color: #999;

        }


        .menu-card h2 {

            margin-top: 22px;

            font-size: 18px;

            font-weight: 500;

        }


        .menu-card p {

            margin-top: 10px;

            font-size: 13px;

            line-height: 1.6;

            color: #777;

        }


        .arrow {

            position: absolute;

            right: 25px;

            bottom: 25px;

            font-size: 20px;

            color: #777;

        }


        /* ==================================================
           INFORMATION
        ================================================== */

        .info {

            margin-top: 35px;

            background: #1d1d1d;

            color: white;

            padding: 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 30px;

        }


        .info h3 {

            font-size: 18px;

            font-weight: 400;

        }


        .info p {

            margin-top: 8px;

            color: #aaa;

            font-size: 13px;

        }


        .info-button {

            color: white;

            text-decoration: none;

            border: 1px solid #777;

            padding: 11px 20px;

            font-size: 12px;

            white-space: nowrap;

        }


        /* ==================================================
           FOOTER
        ================================================== */

        footer {

            margin-top: 60px;

            padding-top: 20px;

            border-top: 1px solid #ddd;

            color: #999;

            font-size: 11px;

            letter-spacing: 1px;

        }


        /* ==================================================
           TABLET
        ================================================== */

        @media (max-width: 900px) {

            .menu-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        /* ==================================================
           HP
        ================================================== */

        @media (max-width: 600px) {

            header {

                height: auto;

                padding: 20px;

                gap: 15px;

            }


            .brand {

                font-size: 15px;

                letter-spacing: 4px;

            }


            .header-right {

                gap: 10px;

            }


            .role {

                display: none;

            }


            main {

                padding: 35px 18px;

            }


            .welcome h1 {

                font-size: 27px;

            }


            .menu-grid {

                grid-template-columns: 1fr;

            }


            .info {

                flex-direction: column;

                align-items: flex-start;

            }


            .logout {

                padding: 8px 12px;

            }

        }

    </style>

</head>


<body>


<!-- ======================================================
     HEADER
====================================================== -->

<header>

    <div class="brand">
        GRIYA SPACE
    </div>


    <div class="header-right">

        <div class="role">
            PELANGGAN
        </div>

        <a
            href="../../auth/logout.php"
            class="logout"
        >
            Keluar
        </a>

    </div>

</header>



<!-- ======================================================
     MAIN
====================================================== -->

<main>


    <section class="welcome">

        <small>
            CUSTOMER AREA
        </small>

        <h1>
            Selamat datang,
            <?= htmlspecialchars($nama); ?>
        </h1>

        <p>
            Temukan berbagai material bangunan
            untuk kebutuhan hunian dan konstruksi Anda.
        </p>

    </section>



   <!-- ==================================================
     MENU
================================================== -->

<section class="menu-grid">


    <a
        href="katalog/index.php"
        class="menu-card"
    >

        <div class="menu-number">
            01
        </div>

        <h2>
            Katalog Produk
        </h2>

        <p>
            Lihat berbagai material dan
            produk bangunan yang tersedia.
        </p>

        <div class="arrow">
            →
        </div>

    </a>


    <a
        href="#"
        class="menu-card"
    >

        <div class="menu-number">
            02
        </div>

        <h2>
            Keranjang
            </h2>

            <p>
                Periksa produk yang ingin
                Anda beli sebelum melakukan transaksi.
            </p>

            <div class="arrow">
                →
            </div>

        </a>



        <a
            href="#"
            class="menu-card"
        >

            <div class="menu-number">
                03
            </div>

            <h2>
                Pesanan Saya
            </h2>

            <p>
                Lihat status dan riwayat
                pesanan Anda.
            </p>

            <div class="arrow">
                →
            </div>

        </a>



        <a
            href="#"
            class="menu-card"
        >

            <div class="menu-number">
                04
            </div>

            <h2>
                Profil Saya
            </h2>

            <p>
                Kelola informasi akun dan
                data pelanggan Anda.
            </p>

            <div class="arrow">
                →
            </div>

        </a>


    </section>



    <!-- ==================================================
         INFORMATION
    ================================================== -->

    <section class="info">

        <div>

            <h3>
                Butuh material bangunan?
            </h3>

            <p>
                Jelajahi katalog Griya Space
                dan temukan produk yang Anda butuhkan.
            </p>

        </div>


        <a
            href="#"
            class="info-button"
        >
            LIHAT PRODUK →
        </a>

    </section>



    <!-- ==================================================
         FOOTER
    ================================================== -->

    <footer>

        © 2026 GRIYA SPACE
        &nbsp; • &nbsp;
        SISTEM MANAJEMEN TOKO

    </footer>


</main>


</body>

</html>