<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "pemilik") {
    header("Location: ../../auth/login.php");
    exit;
}

$nama = $_SESSION["nama"];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Pemilik | Griya Space</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f3;
            color: #1d1d1d;
        }

        header {
            background: #1d1d1d;
            color: white;
            padding: 22px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            letter-spacing: 5px;
            font-size: 18px;
        }

        .role {
            font-size: 12px;
            letter-spacing: 2px;
            opacity: .7;
        }

        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 25px;
        }

        .welcome {
            margin-bottom: 35px;
        }

        .welcome small {
            color: #777;
            letter-spacing: 2px;
        }

        .welcome h1 {
            font-size: 34px;
            margin-top: 10px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .card {
            background: white;
            padding: 28px;
            border: 1px solid #e4e4e4;
            border-radius: 12px;
        }

        .card span {
            color: #777;
            font-size: 13px;
        }

        .card h2 {
            margin-top: 12px;
            font-size: 25px;
        }

        @media (max-width: 800px) {
            .cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 500px) {
            header {
                padding: 20px;
            }

            main {
                padding: 30px 18px;
            }

            .cards {
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

    <div style="display:flex; align-items:center; gap:20px;">

        <div class="role">
            PEMILIK TOKO
        </div>

        <a
            href="../../auth/logout.php"
            style="
                color:white;
                text-decoration:none;
                border:1px solid #555;
                padding:8px 14px;
                border-radius:6px;
                font-size:12px;
            "
        >
            Logout
        </a>

    </div>

</header>

<main>

    <section class="welcome">
        <small>DASHBOARD PEMILIK</small>

        <h1>
            Selamat datang, <?= htmlspecialchars($nama); ?>
        </h1>
    </section>

    <section class="cards">

        <div class="card">
            <span>Penjualan Hari Ini</span>
            <h2>Rp 0</h2>
        </div>

        <div class="card">
            <span>Omzet Bulan Ini</span>
            <h2>Rp 0</h2>
        </div>

        <div class="card">
            <span>Total Produk</span>
            <h2>0</h2>
        </div>

        <div class="card">
            <span>Stok Menipis</span>
            <h2>0</h2>
        </div>

    </section>

</main>

</body>
</html>