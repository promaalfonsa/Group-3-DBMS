<?php
require 'db.php';
require 'functions.php';
$pageTitle = "Orders";
require 'layout.php';
if (!is_driver()) { 
    header('Location: login.php'); 
    exit; 
}

$driver_id = current_user_id();

/* Available orders:
   accepted by restaurant but not yet assigned */
$avail = $mysqli->query("
    SELECT o.*, r.name AS restaurant_name
    FROM orders o
    LEFT JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.status = 'accepted'
      AND o.id NOT IN (SELECT order_id FROM deliveries)
")->fetch_all(MYSQLI_ASSOC);

/* My deliveries */
$stmt = $mysqli->prepare("
    SELECT d.*, o.total, o.address, o.phone, o.delivery_note, r.name AS restaurant_name
    FROM deliveries d
    JOIN orders o ON d.order_id = o.id
    JOIN restaurants r ON o.restaurant_id = r.id
    WHERE d.driver_id = ?
    ORDER BY d.assigned_at DESC
");
$stmt->bind_param('i', $driver_id);
$stmt->execute();
$mine = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* Status badge helper */
function badge($status) {
    switch ($status) {
        case 'assigned': return 'secondary';
        case 'out_for_delivery': return 'primary';
        case 'delivered': return 'success';
        default: return 'warning';
    }
}
?>


<style>
.dashboard-title {
  font-size: 1.8rem;
  font-weight: 800;
}
.order-card {
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0,0,0,0.08);
  border: none;
  margin-bottom: 20px;
}
.order-meta {
  font-size: 0.9rem;
  color: #666;
}
.delivery-note {
  background: #fff5f8;
  border-left: 4px solid #e21b70;
  padding: 10px 12px;
  border-radius: 8px;
  margin-top: 10px;
  font-size: 0.9rem;
}
.action-btn {
  border-radius: 999px;
  padding: 6px 16px;
  font-size: 0.85rem;
}
</style>
</head>


<div class="container main-wrapper">

  <h2 class="dashboard-title mb-4">Delivery Staff Dashboard</h2>

  <!-- AVAILABLE ORDERS -->
  <h4 class="section-title">Available Orders</h4>

  <?php if (empty($avail)): ?>
    <div class="alert alert-info">No available orders right now.</div>
  <?php endif; ?>

  <?php foreach ($avail as $a): ?>
    <div class="card order-card">
      <div class="card-body">

        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <strong>Order #<?php echo $a['id']; ?></strong><br>
            <span class="order-meta">
              <?php echo htmlspecialchars($a['restaurant_name']); ?>
            </span>
          </div>

          <span class="badge bg-info">Available</span>
        </div>

        <div class="mb-2">
          <strong>Total:</strong> ৳<?php echo number_format($a['total'],2); ?>
        </div>

        <?php if (!empty($a['delivery_note'])): ?>
          <div class="delivery-note">
            <strong>Delivery Note:</strong><br>
            <?php echo nl2br(htmlspecialchars($a['delivery_note'])); ?>
          </div>
        <?php endif; ?>

        <form method="post" action="driver_accept.php" class="mt-3">
          <input type="hidden" name="order_id" value="<?php echo $a['id']; ?>">
          <button class="btn btn-primary action-btn">Accept Delivery</button>
        </form>

      </div>
    </div>
  <?php endforeach; ?>

  <!-- MY DELIVERIES -->
  <h4 class="section-title mt-5">My Deliveries</h4>

  <?php if (empty($mine)): ?>
    <div class="alert alert-secondary">You have no assigned deliveries.</div>
  <?php endif; ?>

  <?php foreach ($mine as $m): ?>
    <div class="card order-card">
      <div class="card-body">

        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <strong>Delivery #<?php echo $m['id']; ?></strong><br>
            <span class="order-meta">
              Order #<?php echo $m['order_id']; ?> • 
              <?php echo htmlspecialchars($m['restaurant_name']); ?>
            </span>
          </div>

          <span class="badge bg-<?php echo badge($m['status']); ?>">
            <?php echo ucfirst(str_replace('_',' ', $m['status'])); ?>
          </span>
        </div>

        <div class="mb-2">
          <strong>Total:</strong> ৳<?php echo number_format($m['total'],2); ?>
        </div>

        <div class="mb-2">
          <strong>Delivery Address:</strong><br>
          <?php echo nl2br(htmlspecialchars($m['address'])); ?><br>
          <strong>Phone:</strong> <?php echo htmlspecialchars($m['phone']); ?>
        </div>

        <?php if (!empty($m['delivery_note'])): ?>
          <div class="delivery-note">
            <strong>Customer Note:</strong><br>
            <?php echo nl2br(htmlspecialchars($m['delivery_note'])); ?>
          </div>
        <?php endif; ?>

        <?php if ($m['status'] !== 'delivered'): ?>
          <form method="post" action="driver_mark_delivered.php" class="mt-3">
            <input type="hidden" name="delivery_id" value="<?php echo $m['id']; ?>">
            <button class="btn btn-success action-btn">Mark as Delivered</button>
          </form>
        <?php else: ?>
          <span class="text-muted">Delivery completed</span>
        <?php endif; ?>

      </div>
    </div>
  <?php endforeach; ?>

</div>
<script>
if ("geolocation" in navigator) {

  navigator.geolocation.watchPosition(
    pos => {
      fetch("driver_location_update.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: new URLSearchParams({
          lat: pos.coords.latitude,
          lng: pos.coords.longitude
        })
      });
    },
    err => console.error("GPS error:", err.message),
    {
      enableHighAccuracy: true,
      maximumAge: 0,
      timeout: 5000
    }
  );

}
</script>





