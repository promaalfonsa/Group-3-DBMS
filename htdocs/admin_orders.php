<?php
$pageTitle = "Orders";
require 'admin_layout.php';

if (!is_admin()) { header('Location: login.php'); exit; }

// Fetch orders
$res = $mysqli->query("
  SELECT o.*, 
         u.name AS user_name, 
         r.name AS restaurant_name
  FROM orders o
  LEFT JOIN users u ON o.user_id = u.id
  LEFT JOIN restaurants r ON o.restaurant_id = r.id
  ORDER BY o.created_at DESC
");
$orders = $res->fetch_all(MYSQLI_ASSOC);

// Fetch delivery staff
$drv = $mysqli->query("
  SELECT id, name FROM users WHERE role='driver'
")->fetch_all(MYSQLI_ASSOC);

// Status badge helper
function statusBadge($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'accepted': return 'info';
        case 'out_for_delivery': return 'primary';
        case 'delivered': return 'success';
        case 'rejected':
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>All Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.order-card {
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0,0,0,.08);
  border: none;
}
.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.order-meta {
  font-size: .9rem;
  color: #666;
}
.delivery-note {
  background: #fff5f8;
  border-left: 4px solid #e21b70;
  padding: 10px 12px;
  border-radius: 8px;
  margin-top: 10px;
  font-size: .9rem;
}
.assign-box select {
  border-radius: 999px;
}
.assign-box button {
  border-radius: 999px;
}
</style>
</head>

<body class="p-4">
<div class="container main-wrapper">

  <h2 class="section-title mb-4">All Orders</h2>

  <?php if (empty($orders)): ?>
    <div class="alert alert-info">No orders found.</div>
  <?php endif; ?>

  <?php foreach ($orders as $o): ?>
    <div class="card order-card mb-4">
      <div class="card-body">

        <div class="order-header mb-2">
          <div>
            <strong>Order #<?php echo $o['id']; ?></strong><br>
            <span class="order-meta">
              <?php echo htmlspecialchars($o['user_name']); ?> • 
              <?php echo htmlspecialchars($o['restaurant_name']); ?> • 
              <?php echo htmlspecialchars($o['created_at']); ?>
            </span>
          </div>

          <span class="badge bg-<?php echo statusBadge($o['status']); ?>">
            <?php echo ucfirst(str_replace('_',' ', $o['status'])); ?>
          </span>
        </div>

        <div class="mb-2">
          <strong>Total:</strong> ৳<?php echo number_format($o['total'],2); ?>
        </div>

        <?php if (!empty($o['delivery_note'])): ?>
          <div class="delivery-note">
            <strong>Delivery Note:</strong><br>
            <?php echo nl2br(htmlspecialchars($o['delivery_note'])); ?>
          </div>
        <?php endif; ?>

        <div class="mt-3">
          <?php if ($o['status'] === 'pending'): ?>
            <form method="post" action="assign_delivery.php" class="assign-box d-flex gap-2 align-items-center">
              <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
              <select name="driver_id" class="form-select form-select-sm" required>
                <option value="">Assign delivery staff</option>
                <?php foreach ($drv as $d): ?>
                  <option value="<?php echo $d['id']; ?>">
                    <?php echo htmlspecialchars($d['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-primary btn-sm">Assign</button>
            </form>
          <?php else: ?>
            <span class="text-muted">No action available</span>
          <?php endif; ?>
        </div>

      </div>
    </div>
  <?php endforeach; ?>

</div>
</body>
</html>
