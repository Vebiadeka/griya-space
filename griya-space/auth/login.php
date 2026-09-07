<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#171717">

    <title>Login | Griya Space</title>

    <link rel="stylesheet"
          href="../assets/css/login.css">

</head>

<body>

<div class="login-page">

    <!-- =====================================================
         PANEL KIRI
    ====================================================== -->

    <section class="brand-panel">

        <div class="decor-line line-one"></div>
        <div class="decor-line line-two"></div>
        <div class="decor-circle"></div>

        <div class="brand-top">
            GRIYA SPACE
        </div>


        <div class="brand-content">

            <!-- =================================================
                 LOGO BANNER
            ================================================== -->

            <div class="logo-banner">

                <img
                    src="../assets/images/logo.png"
                    alt="Griya Space"
                    class="brand-logo"
                >

            </div>


            <!-- =================================================
                 BRAND INFORMATION
            ================================================== -->

            <div class="brand-information">

                <span class="brand-kicker">
                    TOKO BANGUNAN
                </span>

                <h1>
                    GRIYA SPACE
                </h1>

                <div class="brand-line"></div>

                <p class="brand-description">
                    Solusi material bangunan untuk kebutuhan
                    hunian, renovasi, dan konstruksi Anda.
                </p>

            </div>


            <!-- =================================================
                 FEATURES
            ================================================== -->

            <div class="features">

                <div class="feature">

                    <span class="feature-number">
                        01
                    </span>

                    <div>

                        <h3>
                            MATERIAL BERKUALITAS
                        </h3>

                        <p>
                            Produk pilihan untuk berbagai kebutuhan
                            bangunan.
                        </p>

                    </div>

                </div>


                <div class="feature">

                    <span class="feature-number">
                        02
                    </span>

                    <div>

                        <h3>
                            TERPERCAYA
                        </h3>

                        <p>
                            Pelayanan profesional untuk setiap
                            kebutuhan pelanggan.
                        </p>

                    </div>

                </div>


                <div class="feature">

                    <span class="feature-number">
                        03
                    </span>

                    <div>

                        <h3>
                            LENGKAP
                        </h3>

                        <p>
                            Beragam material untuk hunian dan
                            konstruksi Anda.
                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 FOOTER KIRI
            ================================================== -->

            <div class="brand-footer">

                <span>EST. 2026</span>

                <span class="footer-separator">•</span>

                <span>BUILD WITH CONFIDENCE</span>

            </div>

        </div>

    </section>



    <!-- =====================================================
         PANEL KANAN
    ====================================================== -->

    <section class="login-panel">

        <div class="login-container">


            <!-- =================================================
                 HEADER KECIL
            ================================================== -->

            <div class="login-mini-header">

                <span>
                    GRIYA SPACE
                </span>

                <div></div>

                <small>
                    SECURE ACCESS
                </small>

            </div>


            <!-- =================================================
                 HEADER LOGIN
            ================================================== -->

            <div class="login-heading">

                <span class="login-eyebrow">
                    WELCOME BACK
                </span>

                <h2>
                    Selamat Datang
                </h2>

                <p>
                    Silakan masuk untuk melanjutkan ke
                    sistem Griya Space.
                </p>

            </div>


            <!-- =================================================
                 FORM LOGIN
            ================================================== -->
<?php if (!empty($_SESSION["login_error"])): ?>

    <div style="
        margin-bottom:20px;
        padding:12px 15px;
        background:#faf0f0;
        border:1px solid #ead0d0;
        border-left:4px solid #8b3030;
        color:#8b3030;
        font-size:12px;
        line-height:1.5;
    ">
        <?= htmlspecialchars($_SESSION["login_error"]); ?>
    </div>

<?php
    unset($_SESSION["login_error"]);
endif;
?>
            <form
                action="proses_login.php"
                method="POST"
                class="login-form"
                autocomplete="on"
            >


                <!-- USERNAME -->

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <div class="input-box">

                        <span class="input-icon">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                            >

                                <circle
                                    cx="12"
                                    cy="8"
                                    r="3"
                                />

                                <path
                                    d="M5.5 20c.7-3.4 2.9-5.2 6.5-5.2s5.8 1.8 6.5 5.2"
                                />

                            </svg>

                        </span>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            required
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <div class="password-label">

                        <label for="password">
                            Password
                        </label>

                        <a href="#">
                            Lupa password?
                        </a>

                    </div>


                    <div class="input-box">

                        <span class="input-icon">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                            >

                                <rect
                                    x="5"
                                    y="10"
                                    width="14"
                                    height="10"
                                    rx="2"
                                />

                                <path
                                    d="M8 10V7a4 4 0 0 1 8 0v3"
                                />

                            </svg>

                        </span>


                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >


                        <button
                            type="button"
                            id="passwordToggle"
                            class="password-toggle"
                            aria-label="Tampilkan password"
                        >

                            <svg
                                id="eyeOpen"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
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


                            <svg
                                id="eyeClosed"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                style="display:none"
                            >

                                <path d="M3 3l18 18"/>

                                <path
                                    d="M10.6 10.7a2.5 2.5 0 0 0 3.5 3.5"
                                />

                                <path
                                    d="M9.8 5.2A10.8 10.8 0 0 1 12 5c6 0 9.5 7 9.5 7a17 17 0 0 1-3.1 3.8"
                                />

                                <path
                                    d="M6.3 6.3C3.7 8.1 2.5 12 2.5 12s3.5 7 9.5 7c1.5 0 2.8-.4 4-.9"
                                />

                            </svg>

                        </button>

                    </div>

                </div>


                <!-- REMEMBER -->

                <label class="remember">

                    <input
                        type="checkbox"
                        name="remember"
                    >

                    <span class="checkbox"></span>

                    <span>
                        Ingat saya
                    </span>

                </label>


                <!-- BUTTON -->

                <button
                    type="submit"
                    class="login-button"
                >

                    <span>
                        MASUK KE SISTEM
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


            <!-- =================================================
                 REGISTER
            ================================================== -->

            <div class="register-area">

                <div class="register-divider">

                    <span></span>

                    <small>
                        ATAU
                    </small>

                    <span></span>

                </div>


                <p>
                    Belum memiliki akun?
                </p>


                <a
                    href="register.php"
                    class="register-link"
                >

                    <span>
                        Buat akun pelanggan
                    </span>

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                    >

                        <path d="M5 12h13"/>

                        <path d="m13 6 6 6-6 6"/>

                    </svg>

                </a>

            </div>


            <!-- =================================================
                 SECURITY
            ================================================== -->

            <div class="security">

                <span class="security-icon">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                    >

                        <path
                            d="M12 3 20 6v5c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6l8-3Z"
                        />

                        <path d="m9 12 2 2 4-4"/>

                    </svg>

                </span>

                <span>
                    Keamanan data Anda adalah prioritas kami
                </span>

            </div>


            <!-- =================================================
                 COPYRIGHT
            ================================================== -->

            <div class="copyright">

                © 2026 Griya Space

                <span>•</span>

                Sistem Manajemen Toko

            </div>


        </div>

    </section>

</div>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const password =
            document.getElementById("password");

        const toggle =
            document.getElementById("passwordToggle");

        const eyeOpen =
            document.getElementById("eyeOpen");

        const eyeClosed =
            document.getElementById("eyeClosed");


        toggle.addEventListener(
            "click",
            function () {

                if (
                    password.type === "password"
                ) {

                    password.type = "text";

                    eyeOpen.style.display =
                        "none";

                    eyeClosed.style.display =
                        "block";

                } else {

                    password.type =
                        "password";

                    eyeOpen.style.display =
                        "block";

                    eyeClosed.style.display =
                        "none";

                }

            }
        );

    }
);

</script>


</body>
</html>