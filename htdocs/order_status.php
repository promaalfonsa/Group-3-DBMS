<?php
// order_status.php
require 'db.php';
require 'functions.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }

$order_id = intval($_GET['order_id'] ?? 0);
if ($order_id <= 0) { echo "Invalid order id"; exit; }

// Ensure the user owns this order (or admin)
$stmt = $mysqli->prepare("SELECT o.*, r.name AS restaurant_name FROM orders o LEFT JOIN restaurants r ON o.restaurant_id = r.id WHERE o.id = ? LIMIT 1");
$stmt->bind_param('i', $order_id); $stmt->execute(); $order = $stmt->get_result()->fetch_assoc();
if (!$order) { echo "Order not found"; exit; }
if ($order['user_id'] != current_user_id() && !is_admin()) { echo "Access denied"; exit; }

// get items
$qi = $mysqli->prepare("SELECT oi.*, mi.name AS item_name FROM order_items oi LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id WHERE oi.order_id = ?");
$qi->bind_param('i', $order_id); $qi->execute(); $items = $qi->get_result()->fetch_all(MYSQLI_ASSOC);

// get delivery (if any)
$del = $mysqli->prepare("SELECT d.*, u.name AS driver_name, u.phone AS driver_phone FROM deliveries d LEFT JOIN users u ON d.driver_id = u.id WHERE d.order_id = ? LIMIT 1");
$del->bind_param('i', $order_id); $del->execute(); $delivery = $del->get_result()->fetch_assoc();

// function for badge (reuse)
function status_badge_cls($s) {
    $s = strtolower($s);
    if ($s === 'pending') return 'warning';
    if ($s === 'accepted') return 'info';
    if ($s === 'out_for_delivery' || $s === 'out for delivery') return 'primary';
    if ($s === 'delivered') return 'success';
    if ($s === 'cancelled' || $s === 'rejected') return 'danger';
    return 'secondary';
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Order #<?php echo $order_id; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    <link rel="stylesheet" href="/assets/css/style.css">
</head><body class="p-4">
<div class="container">
  <a href="user_dashboard.php" class="btn btn-sm btn-secondary mb-3">Back to My Orders</a>
  <h2>Order #<?php echo $order_id; ?> <small class="text-muted">from <?php echo htmlspecialchars($order['restaurant_name']); ?></small></h2>
  <div class="mb-2">
    <span class="badge bg-<?php echo status_badge_cls($order['status']); ?>"><?php echo ucfirst(str_replace('_',' ',$order['status'])); ?></span>
    <?php if($delivery): ?>
      <span class="badge bg-<?php echo status_badge_cls($delivery['status']); ?>"><?php echo ucfirst(str_replace('_',' ',$delivery['status'])); ?></span>
    <?php endif; ?>
  </div>

  <h4>Items</h4>
  <ul>
    <?php foreach($items as $it): ?>
      <li><?php echo htmlspecialchars($it['item_name'] ?? 'Item #'.$it['menu_item_id']); ?> — x<?php echo intval($it['qty']); ?> — <?php echo number_format($it['price'],2); ?></li>
    <?php endforeach; ?>
  </ul>

  <h4>Details</h4>
  <p><strong>Total:</strong> <?php echo number_format($order['total'],2); ?></p>
  <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
  <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
  <p><strong>Placed:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>

  <?php if($delivery): ?>
    <hr>
    <h4>Delivery</h4>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($delivery['status']); ?></p>
    <p><strong>Assigned to:</strong> <?php echo htmlspecialchars($delivery['driver_name'] ?: 'Not assigned'); ?> <?php if($delivery['driver_phone']) echo ' — '.$delivery['driver_phone']; ?></p>
    <p>
      <a class="btn btn-sm btn-success" href="get_driver_location.php?order_id=<?php echo $order_id; ?>">Live Track</a>
    </p>
  <?php else: ?>
    <p class="text-muted">Delivery has not been assigned yet.</p>
  <?php endif; ?>

  <!-- ETA estimate (simple) -->
  <?php
    $eta = null;
    if ($order['status'] === 'accepted') $eta = 'Preparing — ETA 15-30 mins';
    if ($delivery && in_array(strtolower($delivery['status']), ['out_for_delivery','out for delivery'])) $eta = 'Driver is on the way — ETA 10-30 mins';
    if ($order['status'] === 'delivered' || ($delivery && $delivery['status'] === 'delivered')) $eta = 'Delivered';
  ?>
  <?php if($eta): ?><div class="alert alert-info"><strong>Status info:</strong> <?php echo $eta; ?></div><?php endif; ?>

</div>
</body></html>
