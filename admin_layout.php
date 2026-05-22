
<?php
require 'db.php';
require 'functions.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

/* DEFAULT ADMIN PAGE */
if (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') {
    header('Location: admin_analytics.php');
    exit;
}

$current = basename($_SERVER['PHP_SELF']);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Panel | Khadok</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

*{
  margin:0;
  padding:0;
  box-sizing:border-box;
  font-family:'Segoe UI',sans-serif;
}

body{
  background:#f6f6f6;
}

/* LAYOUT */
.admin-wrapper{
  display:flex;
  min-height:100vh;
}

/* SIDEBAR */
.sidebar{
  width:270px;
  background:#1f1f1f;
  color:#fff;
  padding:24px 18px;
  position:fixed;
  top:0;
  left:0;
  bottom:0;
  overflow-y:auto;
}

.sidebar-logo{
  font-size:2rem;
  font-weight:900;
  color:#e21b70;
  margin-bottom:30px;
}

.sidebar-menu{
  display:flex;
  flex-direction:column;
  gap:8px;
}

.sidebar-link{
  color:#ccc;
  text-decoration:none;
  padding:14px 16px;
  border-radius:14px;
  transition:.2s;
  font-weight:600;
}

.sidebar-link:hover{
  background:#2d2d2d;
  color:#fff;
}

.sidebar-link.active{
  background:#e21b70;
  color:#fff;
}

/* CONTENT */
.main-content{
  margin-left:270px;
  width:100%;
  padding:30px;
}

/* TOPBAR */
.topbar{
  background:#fff;
  border-radius:18px;
  padding:18px 24px;
  margin-bottom:24px;
  box-shadow:0 10px 24px rgba(0,0,0,.06);
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.topbar-title{
  font-size:1.7rem;
  font-weight:800;
}

.admin-user{
  color:#666;
  font-weight:600;
}

/* COMMON CARD */
.admin-card{
  background:#fff;
  border-radius:18px;
  padding:24px;
  box-shadow:0 12px 28px rgba(0,0,0,.08);
  border:none;
}

/* TABLE */
.table{
  vertical-align:middle;
}

/* BUTTON */
.btn-primary{
  background:#e21b70;
  border:none;
}

.btn-primary:hover{
  background:#c41860;
}

/* MOBILE */
@media(max-width:992px){

  .sidebar{
    width:100%;
    height:auto;
    position:relative;
  }

  .main-content{
    margin-left:0;
  }

  .admin-wrapper{
    flex-direction:column;
  }

}

</style>
</head>

<body>

<div class="admin-wrapper">

  <!-- SIDEBAR -->
  <div class="sidebar">

    <div class="sidebar-logo">Khadok</div>

   <div class="sidebar-menu">

  <a class="sidebar-link <?php if($current=='admin_analytics.php') echo 'active'; ?>" href="admin_analytics.php">
    📈 Analytics
  </a>

  <a class="sidebar-link <?php if($current=='admin_orders.php') echo 'active'; ?>" href="admin_orders.php">
    📦 Orders
  </a>

  <a class="sidebar-link <?php if($current=='admin_users.php') echo 'active'; ?>" href="admin_users.php">
    👥 Users
  </a>

  <a class="sidebar-link <?php if($current=='admin_categories.php') echo 'active'; ?>" href="admin_categories.php">
    🗂 Categories
  </a>

  <a class="sidebar-link <?php if($current=='admin_promotions.php') echo 'active'; ?>" href="admin_promotions.php">
    🎉 Promotions
  </a>

  <a class="sidebar-link <?php if($current=='admin_add_menu_item.php') echo 'active'; ?>" href="admin_add_menu_item.php">
    🍔 Add Menu Item
  </a>

 

  <a class="sidebar-link" href="index.php">
    🏠 Back To Site
  </a>

  <a class="sidebar-link" href="logout.php">
    🚪 Logout
  </a>

</div>
  </div>

  <!-- CONTENT -->
  <div class="main-content">

    <div class="topbar">
      <div class="topbar-title">
        <?php echo $pageTitle ?? 'Admin Panel'; ?>
      </div>

      <div class="admin-user">
        Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?>
      </div>
    </div>