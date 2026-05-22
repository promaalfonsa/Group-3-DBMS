<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$rid = intval($_GET['rid'] ?? 0);

/* =========================
   PERMISSION CHECK
========================= */

if ($_SESSION['role'] !== 'admin') {

    $uid = current_user_id();

    $check = $mysqli->prepare("
        SELECT id
        FROM restaurants
        WHERE id = ?
        AND user_id = ?
        LIMIT 1
    ");

    $check->bind_param('ii', $rid, $uid);

    $check->execute();

    if (!$check->get_result()->fetch_assoc()) {

        echo 'Access denied';

        exit;
    }
}


/* =========================
   FETCH ORDERS
========================= */

$stmt = $mysqli->prepare("
    SELECT o.*, u.name AS user_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.restaurant_id = ?
    ORDER BY o.created_at DESC
");

$stmt->bind_param('i', $rid);

$stmt->execute();

$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


/* =========================
   STATUS BADGE
========================= */

function statusBadge($status){

    switch ($status){

        case 'pending':
            return 'warning';

        case 'accepted':
            return 'info';

        case 'out_for_delivery':
            return 'primary';

        case 'delivered':
            return 'success';

        case 'rejected':
        case 'cancelled':
            return 'danger';

        default:
            return 'secondary';
    }
}

?>

<!doctype html>
<html>

<head>

<meta charset="utf-8">

<title>Restaurant Orders | Khadok</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

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

/* =========================
   PAGE
========================= */

.orders-page{
    padding:50px 20px;
}

.main-wrapper{
    max-width:1050px;
}

/* =========================
   TITLE
========================= */

.page-title{

    font-size:3rem;

    font-weight:800;

    margin-bottom:30px;
}

.page-title span{
    color:#e21b70;
}

/* =========================
   ORDER CARD
========================= */

.order-card{

    border:none;

    border-radius:24px;

    background:#fff;

    box-shadow:
        0 12px 30px rgba(0,0,0,.06);

    overflow:hidden;
}

.order-header{

    display:flex;

    justify-content:space-between;

    align-items:start;

    padding-bottom:14px;

    border-bottom:1px solid #eee;
}

.order-meta{

    color:#777;

    font-size:.9rem;
}

/* =========================
   TOTAL
========================= */

.total-box{

    margin-top:18px;

    background:#fff0f6;

    border-radius:16px;

    padding:14px 18px;

    display:flex;

    justify-content:space-between;

    align-items:center;
}

.total-price{

    color:#e21b70;

    font-size:1.2rem;

    font-weight:800;
}

/* =========================
   DELIVERY NOTE
========================= */

.delivery-note{

    background:#fff5f8;

    border-left:4px solid #e21b70;

    border-radius:14px;

    padding:14px 16px;

    margin-top:18px;
}

/* =========================
   BUTTONS
========================= */

.btn-khadok{

    background:
        linear-gradient(
            135deg,
            #e21b70,
            #ff4f9d
        );

    border:none;

    color:#fff;

    border-radius:999px;

    padding:10px 18px;

    font-weight:600;

    transition:.2s ease;
}

.btn-khadok:hover{
    background:#c2185f;
}

.btn-reject{

    background:#ef4444;

    border:none;

    color:#fff;

    border-radius:999px;

    padding:10px 18px;

    font-weight:600;
}

/* =========================
   EMPTY BOX
========================= */

.empty-box{

    background:#fff;

    border-radius:24px;

    padding:60px 30px;

    text-align:center;

    box-shadow:
        0 12px 30px rgba(0,0,0,.05);
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

    .page-title{
        font-size:2rem;
    }

    .order-header{
        flex-direction:column;
        gap:10px;
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

        <div class="nav-right desktop-menu">

            <span>
                Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </span>

            <a href="restaurant_dashboard.php">
                Dashboard
            </a>

            <a href="logout.php">
                Logout
            </a>

        </div>

        <!-- MOBILE MENU -->
        <div class="mobile-menu">

            <button class="menu-btn" onclick="toggleMenu()">
                ⋮
            </button>

            <div class="menu-dropdown" id="mobileMenu">

                <a href="restaurant_dashboard.php">
                    Dashboard
                </a>

                <a href="logout.php">
                    Logout
                </a>

            </div>

        </div>

    </div>

</div>


<!-- =========================
     PAGE BODY
========================= -->

<div class="orders-page">

    <div class="container main-wrapper">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2 class="page-title">
                Restaurant <span>Orders</span>
            </h2>

            <a
                href="restaurant_dashboard.php"
                class="btn btn-dark rounded-pill px-4"
            >
                Back
            </a>

        </div>


        <?php if (empty($orders)): ?>

            <div class="empty-box">

                <h4>No Orders Yet</h4>

                <p class="text-muted">
                    Your restaurant hasn’t received any orders yet.
                </p>

            </div>

        <?php endif; ?>


        <?php foreach ($orders as $o): ?>

        <div class="card order-card mb-4">

            <div class="card-body p-4">

                <div class="order-header">

                    <div>

                        <h5 class="mb-1">
                            Order #<?php echo $o['id']; ?>
                        </h5>

                        <div class="order-meta">

                            <?php echo htmlspecialchars($o['user_name']); ?>

                            •

                            <?php echo date('d M Y, h:i A', strtotime($o['created_at'])); ?>

                        </div>

                    </div>

                    <span class="badge bg-<?php echo statusBadge($o['status']); ?>">

                        <?php echo ucfirst(str_replace('_',' ', $o['status'])); ?>

                    </span>

                </div>


                <!-- TOTAL -->
                <div class="total-box">

                    <strong>Total Amount</strong>

                    <div class="total-price">

                        ৳<?php echo number_format($o['total'],2); ?>

                    </div>

                </div>


                <!-- NOTE -->
                <?php if (!empty($o['delivery_note'])): ?>

                <div class="delivery-note">

                    <strong>Delivery Note</strong><br>

                    <?php echo nl2br(htmlspecialchars($o['delivery_note'])); ?>

                </div>

                <?php endif; ?>


                <!-- ACTION -->
                <div class="mt-4">

                    <?php if ($o['status'] === 'pending'): ?>

                        <form method="post"
                              action="restaurant_update_order.php"
                              class="d-flex gap-2 flex-wrap">

                            <input
                                type="hidden"
                                name="order_id"
                                value="<?php echo $o['id']; ?>"
                            >

                            <button
                                class="btn-khadok"
                                name="action"
                                value="accept"
                            >
                                Accept Order
                            </button>

                            <button
                                class="btn-reject"
                                name="action"
                                value="reject"
                            >
                                Reject
                            </button>

                        </form>

                    <?php else: ?>

                        <span class="text-muted">
                            No action available
                        </span>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

</div>


<!-- =========================
     FOOTER
========================= -->

<footer class="khadok-footer">

    <div class="footer-inner">

        <div class="footer-col">

            <div class="footer-logo">
                Khadok
            </div>

            <p>
                Fast, fresh food delivered to your doorstep.
            </p>

        </div>

        <div class="footer-col">

            <a href="privacy_policy.php">
                Privacy Policy
            </a>

            <a href="terms_conditions.php">
                Terms & Conditions
            </a>

        </div>

        <div class="footer-col">

            <a href="about_us.php">
                About Us
            </a>

            <a href="contact_us.php">
                Contact Us
            </a>

        </div>

    </div>

    <div class="footer-bottom">

        © <?php echo date('Y'); ?> Khadok — All Rights Reserved

    </div>

</footer>


<script>

function toggleMenu(){

    const menu = document.getElementById("mobileMenu");

    menu.classList.toggle("show");
}

</script>

</body>
</html>