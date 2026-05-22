<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in()) { header('Location: login.php'); exit; }
if (!in_array($_SESSION['role'], ['restaurant','admin'])) { echo 'Access denied'; exit; }

// Get restaurant(s) owned by this user
$uid = current_user_id();
$res = $mysqli->prepare("SELECT * FROM restaurants WHERE user_id = ?");
$res->bind_param('i', $uid);
$res->execute();
$rests = $res->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Restaurant Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.dashboard-card {
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0,0,0,0.08);
  border: none;
}
.dashboard-title {
  font-size: 1.8rem;
  font-weight: 800;
}
.restaurant-name {
  font-size: 1.3rem;
  font-weight: 700;
}
.restaurant-meta {
  color: #666;
  font-size: 0.9rem;
}
.action-btn {
  border-radius: 999px;
  padding: 6px 14px;
  font-size: 0.85rem;
}
    
</style>
</head>

<body class="p-4">
<div class="container main-wrapper">

  <h2 class="dashboard-title mb-4">Restaurant Dashboard</h2>

  <?php if (empty($rests)): ?>

    <!-- NO RESTAURANT -->
    <div class="card dashboard-card p-4">
      <h5>You don't have a restaurant profile yet</h5>
      <p class="text-muted">Create your restaurant profile to start accepting orders.</p>

      <form method="post" action="restaurant_create.php">
        <div class="mb-2">
          <input class="form-control" name="name" placeholder="Restaurant name" required>
        </div>
        <div class="mb-2">
          <input class="form-control" name="address" placeholder="Address">
        </div>
        <div class="mb-2">
          <input class="form-control" name="city" placeholder="City">
        </div>
        <div class="mb-2">
          <input class="form-control" name="phone" placeholder="Phone">
        </div>
        <button class="btn btn-primary action-btn">Create Restaurant</button>
      </form>
    </div>

  <?php else: ?>

    <!-- RESTAURANT LIST -->
    <div class="row">
      <?php foreach ($rests as $r): ?>
        <div class="col-md-6 mb-4">
          <div class="card dashboard-card p-4 h-100">

            <div class="restaurant-name mb-1">
              <?php echo htmlspecialchars($r['name']); ?>
            </div>

            <div class="restaurant-meta mb-3">
              <?php echo htmlspecialchars($r['address']); ?>
              <?php if ($r['city']): ?> • <?php echo htmlspecialchars($r['city']); ?><?php endif; ?>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-primary action-btn"
                 href="restaurant_add_menu_item.php?rid=<?php echo $r['id']; ?>">
                 ➕ Add Menu Item
              </a>

              <a class="btn btn-outline-secondary action-btn"
                 href="restaurant_menu_list.php?rid=<?php echo $r['id']; ?>">
                 🍽 My Menu Items
              </a>

              <a class="btn btn-success action-btn"
                 href="restaurant_orders.php?rid=<?php echo $r['id']; ?>">
                 📦 View Orders
              </a>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

</div>
</body>
</html>
