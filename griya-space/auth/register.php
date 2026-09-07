<?php

session_start();

// Jika user sudah login, arahkan ke dashboard sesuai role
if (isset($_SESSION["user_id"]) && isset($_SESSION["role"])) {

    switch ($_SESSION["role"]) {

        case "pemilik":
            header("Location: ../dashboard/pemilik/index.php");
            exit;

        case "admin":
            header("Location: ../dashboard/admin/index.php");
            exit;

        case "kasir":
            header("Location: ../dashboard/kasir/index.php");
            exit;

        case "gudang":
            header("Location: ../dashboard/gudang/index.php");
            exit;

        case "pelanggan":
            header("Location: ../dashboard/pelanggan/index.php");
            exit;
    }
}

$error = $_SESSION["register_error"] ?? "";
$success = $_SESSION["register_success"] ?? "";

unset($_SESSION["register_error"]);
unset($_SESSION["register_success"]);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Daftar Akun | Griya Space</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {

            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #f8f8f6 0%,
                    #ffffff 50%,
                    #eeeeeb 100%
                );

            color: #181818;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 20px;

        }


        .register-wrapper {

            width: 100%;

            max-width: 1050px;

            min-height: 650px;

            background: #ffffff;

            border: 1px solid #e7e7e7;

            border-radius: 24px;

            overflow: hidden;

            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.10);

            display: grid;

            grid-template-columns: 0.9fr 1.1fr;

        }


        /* =================================================
           LEFT SIDE
           ================================================= */

        .register-left {

            background: #171717;

            color: #ffffff;

            padding: 55px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            position: relative;

            overflow: hidden;

        }


        .register-left::before {

            content: "";

            position: absolute;

            width: 320px;

            height: 320px;

            border: 1px solid rgba(255,255,255,.08);

            border-radius: 50%;

            top: -130px;

            left: -130px;

        }


        .register-left::after {

            content: "";

            position: absolute;

            width: 420px;

            height: 420px;

            border: 1px solid rgba(255,255,255,.06);

            border-radius: 50%;

            right: -230px;

            bottom: -230px;

        }


        .brand-area {

            position: relative;

            z-index: 2;

            margin-bottom: 45px;

        }


        .logo-box {

            width: 100%;

            max-width: 360px;

            height: 100px;

            background: #ffffff;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            overflow: hidden;

            margin-bottom: 24px;

        }


        .logo-box img {

            width: 100%;

            height: 100%;

            object-fit: contain;

            display: block;

        }


        .brand-subtitle {

            font-size: 11px;

            letter-spacing: 4px;

            color: rgba(255,255,255,.65);

            margin-left: 2px;

        }


        .register-left h1 {

            position: relative;

            z-index: 2;

            font-size: 38px;

            line-height: 1.15;

            font-weight: 500;

            margin-bottom: 20px;

        }


        .register-description {

            position: relative;

            z-index: 2;

            max-width: 390px;

            color: rgba(255,255,255,.65);

            line-height: 1.8;

            font-size: 14px;

        }


        .benefits {

            position: relative;

            z-index: 2;

            margin-top: 35px;

            display: grid;

            gap: 14px;

        }


        .benefit {

            display: flex;

            align-items: center;

            gap: 12px;

            font-size: 13px;

            color: rgba(255,255,255,.82);

        }


        .benefit-icon {

            width: 28px;

            height: 28px;

            border: 1px solid rgba(255,255,255,.2);

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 11px;

        }


        /* =================================================
           RIGHT SIDE
           ================================================= */

        .register-right {

            padding: 50px 55px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            overflow-y: auto;

        }


        .form-header {

            margin-bottom: 30px;

        }


        .form-header small {

            display: block;

            color: #777;

            font-size: 10px;

            letter-spacing: 3px;

            margin-bottom: 10px;

        }


        .form-header h2 {

            font-size: 30px;

            font-weight: 500;

            margin-bottom: 8px;

        }


        .form-header p {

            color: #777;

            font-size: 13px;

            line-height: 1.6;

        }


        .alert {

            padding: 12px 14px;

            border-radius: 8px;

            font-size: 12px;

            margin-bottom: 18px;

            line-height: 1.5;

        }


        .alert-error {

            background: #faf0f0;

            border: 1px solid #ead0d0;

            color: #8b3030;

        }


        .alert-success {

            background: #f0f7f1;

            border: 1px solid #cfe2d1;

            color: #35633b;

        }


        .register-form {

            width: 100%;

        }


        .form-row {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 16px;

        }


        .form-group {

            margin-bottom: 18px;

        }


        .form-group label {

            display: block;

            font-size: 12px;

            font-weight: 600;

            margin-bottom: 8px;

        }


        .input-box {

            position: relative;

        }


        .input-box input,

        .input-box textarea {

            width: 100%;

            border: 1px solid #dedede;

            background: #fafafa;

            border-radius: 9px;

            padding: 13px 14px;

            font-size: 13px;

            color: #1d1d1d;

            outline: none;

            transition: .2s ease;

            font-family: inherit;

        }


        .input-box input {

            height: 46px;

        }


        .input-box textarea {

            min-height: 78px;

            resize: vertical;

        }


        .input-box input:focus,

        .input-box textarea:focus {

            background: #ffffff;

            border-color: #333;

            box-shadow:
                0 0 0 3px rgba(0,0,0,.04);

        }


        .input-box input::placeholder,

        .input-box textarea::placeholder {

            color: #aaa;

        }


        .password-box input {

            padding-right: 48px;

        }


        .password-toggle {

            position: absolute;

            right: 12px;

            top: 50%;

            transform: translateY(-50%);

            width: 28px;

            height: 28px;

            border: none;

            background: transparent;

            cursor: pointer;

            color: #777;

            display: flex;

            align-items: center;

            justify-content: center;

        }


        .password-toggle svg {

            width: 18px;

            height: 18px;

        }


        .submit-button {

            width: 100%;

            height: 50px;

            margin-top: 5px;

            border: none;

            border-radius: 9px;

            background: #181818;

            color: #ffffff;

            font-size: 12px;

            font-weight: 600;

            letter-spacing: 1.5px;

            cursor: pointer;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 12px;

            transition: .2s ease;

        }


        .submit-button:hover {

            background: #303030;

            transform: translateY(-1px);

        }


        .submit-button svg {

            width: 17px;

            height: 17px;

            stroke-width: 1.7;

        }


        .login-back {

            margin-top: 22px;

            text-align: center;

            font-size: 12px;

            color: #777;

        }


        .login-back a {

            color: #181818;

            text-decoration: none;

            font-weight: 600;

        }


        .login-back a:hover {

            text-decoration: underline;

        }


        .security-note {

            margin-top: 18px;

            text-align: center;

            color: #aaa;

            font-size: 10px;

            letter-spacing: .3px;

        }


        /* =================================================
           RESPONSIVE
           ================================================= */

        @media (max-width: 850px) {

            .register-wrapper {

                grid-template-columns: 1fr;

                max-width: 600px;

            }


            .register-left {

                padding: 40px;

                min-height: 330px;

            }


            .register-left h1 {

                font-size: 30px;

            }


            .benefits {

                grid-template-columns: repeat(3, 1fr);

            }


            .register-description {

                display: none;

            }


            .brand-area {

                margin-bottom: 25px;

            }

        }


        @media (max-width: 600px) {

            body {

                padding: 0;

                align-items: stretch;

            }


            .register-wrapper {

                min-height: 100vh;

                border-radius: 0;

                border: none;

                box-shadow: none;

            }


            .register-left {

                min-height: auto;

                padding: 32px 24px;

            }


            .logo-box {

                height: 75px;

                margin-bottom: 18px;

            }


            .register-left h1 {

                font-size: 26px;

                margin-bottom: 0;

            }


            .benefits {

                display: none;

            }


            .register-right {

                padding: 35px 24px 40px;

            }


            .form-row {

                grid-template-columns: 1fr;

                gap: 0;

            }


            .form-header h2 {

                font-size: 26px;

            }

        }

    </style>

</head>


<body>


<div class="register-wrapper">


    <!-- =================================================
         LEFT
         ================================================= -->

    <section class="register-left">


        <div class="brand-area">


            <div class="logo-box">

                <img
                    src="../assets/images/logo.png"
                    alt="Griya Space"
                >

            </div>


            <div class="brand-subtitle">
                TOKO BANGUNAN
            </div>


        </div>


        <h1>
            Mulai berbelanja
            bersama Griya Space.
        </h1>


        <p class="register-description">

            Buat akun pelanggan untuk mendapatkan
            pengalaman belanja yang lebih mudah,
            cepat, dan terorganisir.

        </p>


        <div class="benefits">


            <div class="benefit">

                <span class="benefit-icon">✓</span>

                <span>
                    Akses katalog produk
                </span>

            </div>


            <div class="benefit">

                <span class="benefit-icon">✓</span>

                <span>
                    Pesanan lebih mudah
                </span>

            </div>


            <div class="benefit">

                <span class="benefit-icon">✓</span>

                <span>
                    Riwayat pembelian tersimpan
                </span>

            </div>


        </div>


    </section>


    <!-- =================================================
         RIGHT
         ================================================= -->

    <section class="register-right">


        <div class="form-header">

            <small>
                CUSTOMER REGISTRATION
            </small>

            <h2>
                Buat akun pelanggan
            </h2>

            <p>
                Lengkapi data berikut untuk membuat
                akun Griya Space.
            </p>

        </div>


        <?php if ($error !== ""): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <?php if ($success !== ""): ?>

            <div class="alert alert-success">
                <?= htmlspecialchars($success); ?>
            </div>

        <?php endif; ?>


        <form
            action="proses_register.php"
            method="POST"
            class="register-form"
            autocomplete="on"
        >


            <!-- NAMA + USERNAME -->

            <div class="form-row">


                <div class="form-group">

                    <label for="nama_lengkap">
                        Nama Lengkap
                    </label>

                    <div class="input-box">

                        <input
                            type="text"
                            id="nama_lengkap"
                            name="nama_lengkap"
                            placeholder="Nama lengkap"
                            autocomplete="name"
                            maxlength="100"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <div class="input-box">

                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Buat username"
                            autocomplete="username"
                            maxlength="50"
                            required
                        >

                    </div>

                </div>


            </div>


            <!-- EMAIL + TELEPON -->

            <div class="form-row">


                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <div class="input-box">

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            maxlength="100"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label for="no_telepon">
                        Nomor Telepon
                    </label>

                    <div class="input-box">

                        <input
                            type="tel"
                            id="no_telepon"
                            name="no_telepon"
                            placeholder="08xxxxxxxxxx"
                            autocomplete="tel"
                            maxlength="20"
                            required
                        >

                    </div>

                </div>


            </div>


            <!-- ALAMAT -->

            <div class="form-group">

                <label for="alamat">
                    Alamat
                </label>

                <div class="input-box">

                    <textarea
                        id="alamat"
                        name="alamat"
                        placeholder="Masukkan alamat lengkap"
                        autocomplete="street-address"
                        maxlength="255"
                        required
                    ></textarea>

                </div>

            </div>


            <!-- PASSWORD -->

            <div class="form-row">


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-box password-box">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Buat password"
                            autocomplete="new-password"
                            minlength="6"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            data-target="password"
                            aria-label="Tampilkan password"
                        >

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            >

                                <path
                                    d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                                />

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="2.5"
                                />

                            </svg>

                        </button>

                    </div>

                </div>


                <div class="form-group">

                    <label for="konfirmasi_password">
                        Konfirmasi Password
                    </label>

                    <div class="input-box password-box">

                        <input
                            type="password"
                            id="konfirmasi_password"
                            name="konfirmasi_password"
                            placeholder="Ulangi password"
                            autocomplete="new-password"
                            minlength="6"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            data-target="konfirmasi_password"
                            aria-label="Tampilkan password"
                        >

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            >

                                <path
                                    d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                                />

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="2.5"
                                />

                            </svg>

                        </button>

                    </div>

                </div>


            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="submit-button"
            >

                <span>
                    BUAT AKUN
                </span>

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                >

                    <path d="M5 12h13"/>

                    <path d="m13 6 6 6-6 6"/>

                </svg>

            </button>


        </form>


        <div class="login-back">

            Sudah memiliki akun?

            <a href="login.php">
                Kembali ke login
            </a>

        </div>


        <div class="security-note">

            Data Anda akan disimpan secara aman
            dan digunakan untuk kebutuhan layanan Griya Space.

        </div>


    </section>


</div>


<script>

    document
        .querySelectorAll(".password-toggle")
        .forEach(function(button) {

            button.addEventListener("click", function() {

                const targetId =
                    this.getAttribute("data-target");

                const input =
                    document.getElementById(targetId);

                if (input.type === "password") {

                    input.type = "text";

                } else {

                    input.type = "password";

                }

            });

        });

</script>


</body>

</html>