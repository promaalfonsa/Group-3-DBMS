<?php
require_once 'db.php';
require_once 'functions.php';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo $pageTitle ?? 'Khadok'; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- IMPORTANT: inline style kept so it looks EXACTLY same -->
<style>
*{box-sizing:border-box;font-family:'Segoe UI',sans-serif}
body{margin:0;background:#f6f6f6}

/* NAV */
.navbar{background:#e21b70;padding:14px 0}
.nav-inner{
  max-width:1200px;margin:auto;padding:0 20px;
  display:flex;justify-content:space-between;align-items:center;color:#fff
}
.nav-logo{font-size:1.6rem;font-weight:900;color:#fff;text-decoration:none}
.nav-right{display:flex;gap:18px;align-items:center}
.nav-right a{color:#fff;text-decoration:none;font-weight:600}

/* MOBILE MENU */
.menu-btn {
  background: rgba(255,255,255,0.15);
  border: none;
  color: #fff;
  font-size: 22px;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  cursor: pointer;
}

.menu-dropdown {
  display: none;
  position: absolute;
  right: 10px;
  top: 48px;
  background: #fff;
  border-radius: 12px;
  min-width: 170px;
  box-shadow: 0 12px 30px rgba(0,0,0,.2);
  z-index: 9999;
}

.menu-dropdown a {
  display:block;
  padding:12px 16px;
  color:#333;
  text-decoration:none;
  font-weight:600;
}
.menu-dropdown a:hover {
  background:#e21b70;
  color:#fff;
}

.menu-dropdown.show { display:block; }

.desktop-menu { display:flex; gap:18px; }
.mobile-menu { display:none; }

@media (max-width:768px){
  .desktop-menu{display:none;}
  .mobile-menu{display:block;}
}
/* FOOTER */
.khadok-footer{
  background:#1f1f1f;
  color:#ccc;
  margin-top:60px;
  padding-top:40px;
}

.footer-inner{
  max-width:1200px;
  margin:auto;
  padding:0 20px 30px;
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:30px;
}

.footer-logo{
  font-size:1.6rem;
  font-weight:900;
  color:#e21b70;
}

.footer-col h6{
  color:#fff;
  margin-bottom:12px;
}

.footer-col a{
  display:block;
  color:#ccc;
  text-decoration:none;
  margin-bottom:6px;
}

.footer-col a:hover{
  color:#e21b70;
}

.footer-bottom{
  border-top:1px solid #333;
  text-align:center;
  padding:12px;
  color:#888;
}

/* HEADER FIX */
.nav-inner{
  display:flex;
  align-items:center;
  justify-content:space-between;
}

/* DESKTOP */
.desktop-menu{
  display:flex;
  gap:18px;
  align-items:center;
}

.menu-dropdown {
  display: none;
  position: absolute;
  right: 10px;
  top: 48px;
  background: #fff;
  border-radius: 12px;
  min-width: 170px;
  box-shadow: 0 12px 30px rgba(0,0,0,.2);
  z-index: 9999;
  overflow: hidden;
}

.menu-dropdown a {
  display: block;
  padding: 12px 16px;
  color: #333;
  font-weight: 600;
  text-decoration: none;
}

.menu-dropdown a:hover {
  background: #e21b70;
  color: #fff;
}

.menu-dropdown.show {
  display: block;
}

/* RESPONSIVE */
@media (max-width: 768px){
  .desktop-menu{display:none;}
  .mobile-menu{display:block;}
}

.navbar {
  display: flex;
  justify-content: center; /* keep bar centered */
}

.nav-inner {
  width: 100%;
  max-width: 1200px;
  display: flex;
  align-items: center;
  justify-content: space-between; /* THIS fixes logo left */
}

/* HIDE MOBILE MENU ON DESKTOP */
.mobile-menu {
  display: none;
}

/* SHOW ONLY ON MOBILE */
@media (max-width: 768px) {
  .desktop-menu {
    display: none !important;
  }

  .mobile-menu {
    display: block;
  }
}

.menu-btn {
  background: rgba(255,255,255,0.15);
  border: none;
  color: #fff;
  font-size: 22px;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  cursor: pointer;
}


</style>
</head>

<body>

<!-- NAV -->
<div class="navbar">
  <div class="nav-inner">

    <a href="index.php" class="nav-logo">Khadok</a>

    <!-- DESKTOP MENU -->
    <div class="nav-right desktop-menu">
      <?php if(is_logged_in()): ?>

        <?php if($_SESSION["role"]==="driver"): ?>
          <a href="driver_orders.php">Driver Panel</a>
        <?php endif; ?>

        <?php if($_SESSION["role"]==="restaurant"): ?>
          <a href="restaurant_dashboard.php">My Restaurant</a>
        <?php endif; ?>

        <?php if($_SESSION["role"]==="admin"): ?>
          <a href="admin_dashboard.php">Admin Dashboard</a>
        <?php endif; ?>

        <span>Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>

        <?php if($_SESSION["role"]==="user"): ?>
          <a href="cart.php">Cart (<?php echo count(cart_get()); ?>)</a>
          <a href="orders.php">My Orders</a>
        <?php endif; ?>

        <a href="logout.php">Logout</a>

      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Sign up</a>
      <?php endif; ?>
    </div>

    <!-- MOBILE MENU -->
    <div class="mobile-menu">
      <button class="menu-btn" onclick="toggleMenu()">⋮</button>
      <div class="menu-dropdown" id="mobileMenu">

        <?php if(is_logged_in()): ?>
          <?php if($_SESSION["role"]==="driver"): ?>
            <a href="driver_orders.php">Driver Panel</a>
          <?php endif; ?>
          <?php if($_SESSION["role"]==="restaurant"): ?>
            <a href="restaurant_dashboard.php">My Restaurant</a>
          <?php endif; ?>
          <?php if($_SESSION["role"]==="admin"): ?>
            <a href="admin_dashboard.php">Admin Dashboard</a>
          <?php endif; ?>
          <?php if($_SESSION["role"]==="user"): ?>
            <a href="cart.php">Cart</a>
            <a href="orders.php">My Orders</a>
          <?php endif; ?>
          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="login.php">Login</a>
          <a href="register.php">Sign up</a>
        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

<script>
function toggleMenu(){
  document.getElementById("mobileMenu").classList.toggle("show");
}
</script>
