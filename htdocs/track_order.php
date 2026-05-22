<?php
require 'db.php';
require 'functions.php';

$order_id = intval($_GET['order_id'] ?? 0);
if ($order_id <= 0) {
    exit('Invalid order');
}

/* ================= ORDER INFO ================= */
$stmt = $mysqli->prepare("
SELECT o.*, r.name AS restaurant_name
FROM orders o
JOIN restaurants r ON o.restaurant_id = r.id
WHERE o.id = ?
LIMIT 1
");
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    exit('Order not found');
}

/* ================= ORDER ITEMS ================= */
$stmt = $mysqli->prepare("
SELECT oi.qty, oi.price, m.name
FROM order_items oi
JOIN menu_items m ON oi.menu_item_id = m.id
WHERE oi.order_id = ?
");
$stmt->bind_param('i', $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Track Order #<?php echo $order_id; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
body{
  background:#f6f6f6;
  font-family:'Segoe UI',sans-serif;
}

/* PAGE CARD */
.track-card{
  max-width:900px;
  margin:auto;
  background:#fff;
  border-radius:18px;
  padding:22px;
  box-shadow:0 14px 32px rgba(0,0,0,.08);
}

/* HEADER */
.track-title{
  font-size:1.7rem;
  font-weight:800;
  color:#e21b70;
}

/* MAP */
#map{
  height:380px;
  width:100%;
  border-radius:16px;
  margin-top:16px;
}

/* ORDER INFO */
.order-box{
  background:#fff5f8;
  border-radius:14px;
  padding:16px;
  margin-top:18px;
}

/* ITEMS */
.item-row{
  display:flex;
  justify-content:space-between;
  padding:10px 0;
  border-bottom:1px solid #eee;
}
.item-row:last-child{border-bottom:none}

.item-name{font-weight:600}
.item-meta{font-size:.9rem;color:#777}

.order-total{
  font-weight:800;
  font-size:1.1rem;
}

/* STATUS BADGE */
.badge-status{
  background:#e21b70;
  padding:6px 14px;
  border-radius:999px;
  color:#fff;
  font-size:.85rem;
}

/* BUTTON */
.btn-khadok{
  background:#e21b70;
  border:none;
  color:#fff;
  padding:10px 22px;
  border-radius:999px;
  font-weight:600;
}
.btn-khadok:hover{background:#c41860}

/* MOBILE */
@media(max-width:768px){
  #map{height:300px}
}
</style>
</head>

<body class="p-4">

<div class="track-card">

  <div class="d-flex justify-content-between align-items-center">
    <div class="track-title">Track Your Order</div>
    <span class="badge-status">
      <?php echo ucfirst($order['status']); ?>
    </span>
  </div>

  <div class="text-muted mt-1">
    Order #<?php echo $order_id; ?> • <?php echo htmlspecialchars($order['restaurant_name']); ?>
  </div>

  <!-- MAP -->
  <div id="map"></div>

  <!-- ORDER DETAILS -->
  <div class="order-box">
    <h5 class="mb-3">Order Details</h5>

    <?php foreach($items as $it): ?>
      <div class="item-row">
        <div>
          <div class="item-name"><?php echo htmlspecialchars($it['name']); ?></div>
          <div class="item-meta">
            Qty: <?php echo $it['qty']; ?> × ৳<?php echo number_format($it['price'],2); ?>
          </div>
        </div>
        <div>
          ৳<?php echo number_format($it['qty'] * $it['price'],2); ?>
        </div>
      </div>
    <?php endforeach; ?>

    <hr>

    <div class="d-flex justify-content-between align-items-center">
      <strong>Total</strong>
      <span class="order-total">
        ৳<?php echo number_format($order['total'],2); ?>
      </span>
    </div>
  </div>

  <div class="mt-4 text-end">
    <a href="index.php" class="btn-khadok">Back to Home</a>
  </div>

</div>

<script>
// Default center (Dhaka)
const map = L.map('map').setView([23.8103, 90.4125], 14);

// Tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap'
}).addTo(map);

// Marker
let marker = L.marker([23.8103, 90.4125]).addTo(map);

// Poll driver location
setInterval(() => {
  fetch('get_driver_location.php?order_id=<?php echo $order_id; ?>')
    .then(res => res.json())
    .then(data => {
      if (data.lat && data.lng) {
        marker.setLatLng([data.lat, data.lng]);
        map.panTo([data.lat, data.lng], { animate:true });
      }
    });
}, 5000);
</script>

</body>
</html>
