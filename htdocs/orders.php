<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = current_user_id();

/* Fetch user orders */
$stmt = $mysqli->prepare("
    SELECT o.*, r.name AS restaurant_name
    FROM orders o
    JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* Status badge helper */
function orderBadge($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'accepted': return 'info';
        case 'out_for_delivery': return 'primary';
        case 'delivered': return 'success';
        case 'cancelled':
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>My Orders | Khadok</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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

.order-top{

    padding-bottom:14px;

    border-bottom:1px solid #eee;
}

.restaurant-name{

    font-size:1.1rem;

    font-weight:700;
}

.order-meta{

    color:#777;

    font-size:.9rem;
}

/* =========================
   ITEMS
========================= */

.items-box{

    margin-top:18px;

    background:#fafafa;

    border-radius:16px;

    padding:14px 16px;
}

.items-box li{

    display:flex;

    justify-content:space-between;

    padding:8px 0;

    border-bottom:1px solid #ececec;
}

.items-box li:last-child{
    border:none;
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

    color:#fff;

    border:none;

    border-radius:999px;

    padding:10px 18px;

    text-decoration:none;

    font-weight:600;
}

.btn-outline-khadok{

    border:2px solid #e21b70;

    color:#e21b70;

    border-radius:999px;

    padding:10px 18px;

    text-decoration:none;

    font-weight:600;

    background:#fff;
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
}


body{
  background:#f6f6f6;
  font-family:'Segoe UI',sans-serif;
}

.order-card {
  border-radius: 18px;
  box-shadow: 0 14px 32px rgba(0,0,0,.08);
  border: none;
  margin-bottom: 20px;
}

.order-meta {
  font-size: .9rem;
  color: #666;
}

.delivery-note {
  background: #fff5f8;
  border-left: 4px solid #e21b70;
  padding: 10px 12px;
  border-radius: 10px;
  margin-top: 10px;
}

/* KHADOK BUTTON */
.btn-khadok {
  background: #e21b70;
  color: #fff;
  border: none;
  border-radius: 999px;
  padding: 6px 18px;
  font-size: .85rem;
  font-weight: 600;
}
.btn-khadok:hover {
  background: #c41860;
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

      <a href="cart.php">
        Cart (<?php echo count(cart_get()); ?>)
      </a>

      <a href="orders.php">
        My Orders
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

        <a href="cart.php">
          Cart
        </a>

        <a href="orders.php">
          My Orders
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

    <h2 class="page-title">
      My <span>Orders</span>
    </h2>

    <?php if (empty($orders)): ?>

      <div class="empty-box">

        <h4>No Orders Yet</h4>

        <p>
          Looks like you haven’t ordered anything yet.
        </p>

      </div>

    <?php endif; ?>


    <?php foreach ($orders as $o): ?>

    <?php

    $itemStmt = $mysqli->prepare("
        SELECT oi.qty, oi.price, m.name
        FROM order_items oi
        JOIN menu_items m ON oi.menu_item_id = m.id
        WHERE oi.order_id = ?
    ");

    $itemStmt->bind_param('i', $o['id']);

    $itemStmt->execute();

    $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    ?>

    <div class="card order-card mb-4">

      <div class="card-body p-4">

        <div class="d-flex justify-content-between align-items-start order-top">

          <div>

            <div class="restaurant-name">
              <?php echo htmlspecialchars($o['restaurant_name']); ?>
            </div>

            <div class="order-meta mt-1">

              Order #<?php echo $o['id']; ?>

              •

              <?php echo date('d M Y, h:i A', strtotime($o['created_at'])); ?>

            </div>

          </div>

          <span class="badge bg-<?php echo orderBadge($o['status']); ?>">

            <?php echo ucfirst(str_replace('_',' ',$o['status'])); ?>

          </span>

        </div>


        <!-- ITEMS -->
        <div class="items-box">

          <ul class="list-unstyled mb-0">

            <?php foreach ($items as $it): ?>

            <li>

              <div class="food-name">

                <?php echo htmlspecialchars($it['name']); ?>

                × <?php echo $it['qty']; ?>

              </div>

              <div class="food-price">

                ৳<?php echo number_format($it['price'] * $it['qty'],2); ?>

              </div>

            </li>

            <?php endforeach; ?>

          </ul>

        </div>


        <!-- TOTAL -->
        <div class="total-box">

          <strong>Total Amount</strong>

          <div class="total-price">

            ৳<?php echo number_format($o['total'],2); ?>

          </div>

        </div>


        <!-- DELIVERY NOTE -->
        <?php if (!empty($o['delivery_note'])): ?>

        <div class="delivery-note">

          <strong>Delivery Note</strong><br>

          <?php echo nl2br(htmlspecialchars($o['delivery_note'])); ?>

        </div>

        <?php endif; ?>


        <!-- ACTIONS -->
        <div class="mt-4 d-flex gap-2 flex-wrap">

          <?php if (in_array($o['status'], ['accepted','out_for_delivery'])): ?>

            <a
              href="track_order.php?order_id=<?php echo $o['id']; ?>"
              class="btn-khadok text-decoration-none"
            >
              Track Delivery
            </a>

          <?php endif; ?>


          <?php if ($o['status'] === 'delivered'): ?>

            <a
              href="restaurant.php?id=<?php echo $o['restaurant_id']; ?>"
              class="btn-outline-khadok text-decoration-none"
            >
              Rate Food
            </a>

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

      <h6>Legal</h6>

      <a href="privacy_policy.php">
        Privacy Policy
      </a>

      <a href="terms_conditions.php">
        Terms & Conditions
      </a>

    </div>

    <div class="footer-col">

      <h6>Company</h6>

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

function toggleMenu() {

  const menu = document.getElementById("mobileMenu");

  menu.classList.toggle("show");
}

</script>

</body>
</html>
