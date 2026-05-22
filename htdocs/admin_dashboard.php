<?php
require 'db.php';
require 'functions.php';

if (!is_admin()) { 
    header('Location: login.php'); 
    exit; 
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">

<style>
.admin-title {
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: 24px;
}

.admin-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 20px;
}

.admin-card {
  background: #fff;
  border-radius: 18px;
  padding: 24px;
  box-shadow: 0 14px 32px rgba(0,0,0,0.08);
  transition: transform .25s ease, box-shadow .25s ease;
  text-decoration: none;
  color: #111;
}

.admin-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 22px 44px rgba(0,0,0,0.12);
  color: #111;
}

.admin-icon {
  font-size: 2.2rem;
  margin-bottom: 10px;
}

.admin-label {
  font-size: 1.1rem;
  font-weight: 700;
}

.admin-desc {
  font-size: 0.9rem;
  color: #666;
  margin-top: 6px;
}

/* Accent colors */
.bg-orders { color: #e21b70; }
.bg-users { color: #3b82f6; }
.bg-menu { color: #22c55e; }
.bg-promo { color: #f59e0b; }
.bg-category { color: #8b5cf6; }
.bg-analytics { color: #14b8a6; }

</style>
</head>

<body class="p-4">
<div class="container main-wrapper">

  <h2 class="admin-title">Admin Dashboard</h2>

  <div class="admin-grid">

    <a href="admin_orders.php" class="admin-card">
      <div class="admin-icon bg-orders">📦</div>
      <div class="admin-label">Orders</div>
      <div class="admin-desc">View and manage all orders</div>
    </a>

    <a href="admin_users.php" class="admin-card">
      <div class="admin-icon bg-users">👥</div>
      <div class="admin-label">Users</div>
      <div class="admin-desc">Manage customers, restaurants & delivery staff</div>
    </a>

    <a href="admin_add_menu_item.php" class="admin-card">
      <div class="admin-icon bg-menu">🍔</div>
      <div class="admin-label">Menu Items</div>
      <div class="admin-desc">Add or update food items</div>
    </a>

    <a href="admin_categories.php" class="admin-card">
      <div class="admin-icon bg-category">🗂</div>
      <div class="admin-label">Categories</div>
      <div class="admin-desc">Manage food categories</div>
    </a>

    <a href="admin_promotions.php" class="admin-card">
      <div class="admin-icon bg-promo">🎉</div>
      <div class="admin-label">Promotions</div>
      <div class="admin-desc">Coupons & discounts</div>
    </a>

    <a href="admin_analytics.php" class="admin-card">
      <div class="admin-icon bg-analytics">📊</div>
      <div class="admin-label">Analytics</div>
      <div class="admin-desc">Sales & performance reports</div>
    </a>

  </div>

</div>
</body>
</html>
