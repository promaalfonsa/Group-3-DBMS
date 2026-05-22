<?php
/* =========================================================
   KHADOK GLOBAL LAYOUT
   FILE: layout.php
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'functions.php';
?>

<!doctype html>
<html lang="en">

<head>

<meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>
    <?php echo $pageTitle ?? 'Khadok'; ?>
</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>

:root{
    --khadok:#e21b70;
    --khadok-dark:#c2185f;
}

/* =========================
   GLOBAL
========================= */

*{
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    margin:0;
    background:#f6f6f6;
}

/* =========================
   NAVBAR
========================= */

.navbar{

    background:
        linear-gradient(
            135deg,
            #e21b70,
            #ff4f9d
        );

    padding:16px 0;

    position:sticky;

    top:0;

    z-index:999;

    box-shadow:
        0 10px 30px rgba(226,27,112,.18);
}

.nav-inner{

    width:100%;

    max-width:1200px;

    margin:auto;

    padding:0 20px;

    display:flex;

    align-items:center;

    justify-content:space-between;
}

.nav-logo{

    font-size:2rem;

    font-weight:900;

    color:#fff;

    text-decoration:none;
}

.nav-right{

    display:flex;

    gap:18px;

    align-items:center;
}

.nav-right a,
.nav-right span{

    color:#fff;

    text-decoration:none;

    font-weight:600;
}

/* =========================
   MOBILE MENU
========================= */

.mobile-menu{
    display:none;
}

.menu-btn{

    background:rgba(255,255,255,.16);

    border:none;

    color:#fff;

    width:42px;

    height:42px;

    border-radius:12px;

    font-size:22px;

    cursor:pointer;
}

.menu-dropdown{

    display:none;

    position:absolute;

    right:10px;

    top:58px;

    background:#fff;

    border-radius:18px;

    min-width:220px;

    overflow:hidden;

    box-shadow:
        0 20px 45px rgba(0,0,0,.18);

    z-index:9999;
}

.menu-dropdown a{

    display:block;

    padding:14px 18px;

    color:#333;

    text-decoration:none;

    font-weight:600;
}

.menu-dropdown a:hover{

    background:#e21b70;

    color:#fff;
}

.menu-dropdown.show{
    display:block;
}
html, body{
    height:100%;
}

body{
    min-height:100vh;
    display:flex;
    flex-direction:column;
    background:#f6f6f6;
}

.page-body{
    flex:1;
    padding:50px 20px;
}

/* =========================
   FOOTER
========================= */

.khadok-footer{

    background:
        linear-gradient(
            180deg,
            #1a1a1a,
            #111111
        );

    color:#d1d5db;

    margin-top:70px;

    padding-top:60px;

    border-top:4px solid #e21b70;
}

.footer-inner{

    max-width:1200px;

    margin:auto;

    padding:0 20px 40px;

    display:grid;

    grid-template-columns:
        repeat(auto-fit,minmax(220px,1fr));

    gap:40px;
}

.footer-logo{

    font-size:2rem;

    font-weight:900;

    color:#e21b70;
}

.footer-col a{

    display:block;

    color:#d1d5db;

    text-decoration:none;

    margin-bottom:10px;
}

.footer-bottom{

    border-top:1px solid rgba(255,255,255,.08);

    text-align:center;

    padding:18px;

    color:#9ca3af;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width:768px){

    .desktop-menu{
        display:none !important;
    }

    .mobile-menu{
        display:block;
    }

    .nav-logo{
        font-size:1.7rem;
    }
}

</style>

</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<div class="navbar">

    <div class="nav-inner">

        <a href="index.php" class="nav-logo">
            Khadok
        </a>

        <!-- DESKTOP -->
        <div class="nav-right desktop-menu">

            <?php if(is_logged_in()): ?>

             <a href="profile.php">
    Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?>
</a>

                <?php if($_SESSION['role'] === 'user'): ?>

                    <a href="cart.php">
                        Cart (<?php echo count(cart_get()); ?>)
                    </a>

                    <a href="orders.php">
                        My Orders
                    </a>

                <?php endif; ?>


                <?php if($_SESSION['role'] === 'restaurant'): ?>

                    <a href="restaurant_dashboard.php">
                        Dashboard
                    </a>

                <?php endif; ?>


                <?php if($_SESSION['role'] === 'driver'): ?>

                    <a href="driver_orders.php">
                        Driver Panel
                    </a>

                <?php endif; ?>


                <?php if($_SESSION['role'] === 'admin'): ?>

                    <a href="admin_analytics.php">
                        Admin Panel
                    </a>

                <?php endif; ?>

                <a href="logout.php">
                    Logout
                </a>

            <?php else: ?>

                <a href="login.php">Login</a>

                <a href="register.php">Register</a>

            <?php endif; ?>

        </div>

        <!-- MOBILE -->
        <div class="mobile-menu">

            <button class="menu-btn" onclick="toggleMenu()">
                ⋮
            </button>

            <div class="menu-dropdown" id="mobileMenu">

                <?php if(is_logged_in()): ?>

                    <?php if($_SESSION['role'] === 'user'): ?>

                        <a href="cart.php">
                            Cart
                        </a>

                        <a href="orders.php">
                            My Orders
                        </a>

                    <?php endif; ?>


                    <?php if($_SESSION['role'] === 'restaurant'): ?>

                        <a href="restaurant_dashboard.php">
                            Dashboard
                        </a>

                    <?php endif; ?>


                    <?php if($_SESSION['role'] === 'driver'): ?>

                        <a href="driver_orders.php">
                            Driver Panel
                        </a>

                    <?php endif; ?>


                    <?php if($_SESSION['role'] === 'admin'): ?>

                        <a href="admin_dashboard.php">
                            Admin Panel
                        </a>

                    <?php endif; ?>

                    <a href="logout.php">
                        Logout
                    </a>

                <?php else: ?>

                    <a href="login.php">Login</a>

                    <a href="register.php">Register</a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<!-- =========================
     PAGE CONTENT START
========================= -->

<div class="page-body">