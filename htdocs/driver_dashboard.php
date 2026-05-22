<?php
// driver_dashboard.php
require 'db.php';
require 'functions.php';
if (!is_driver()) { header('Location: login.php'); exit; }

$driver_id = current_user_id();

// Available orders: accepted by restaurant and not yet assigned
$avail_q = "SELECT o.*, r.name AS restaurant_name 
            FROM orders o 
            LEFT JOIN restaurants r ON o.restaurant_id = r.id
            WHERE o.status = 'accepted' 
              AND o.id NOT IN (SELECT order_id FROM deliveries)";
$avail = $mysqli->query($avail_q)->fetch_all(MYSQLI_ASSOC);

// My assigned deliveries
$stmt = $mysqli->prepare("SELECT d.*, o.total, o.address, o.phone, r.name AS restaurant_name 
                          FROM deliveries d 
                          JOIN orders o ON d.order_id = o.id
                          LEFT JOIN restaurants r ON o.restaurant_id = r.id
                          WHERE d.driver_id = ? 
                          ORDER BY d.assigned_at DESC");
$stmt->bind_param('i', $driver_id);
$stmt->execute();
$mine = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Driver Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="p-4">
<div class="container">
  <h2>Driver Dashboard</h2>
  <p><a class="btn btn-secondary" href="index.php">Home</a> <a class="btn btn-secondary" href="logout.php">Logout</a></p>

  <h4>Available Orders (Accept to pick)</h4>
  <?php if(empty($avail)): ?><div class="alert alert-info">No available orders right now.</div><?php else: ?>
    <table class="table">
      <tr><th>Order</th><th>Restaurant</th><th>Total</th><th>Action</th></tr>
    <?php foreach($avail as $a): ?>
      <tr>
        <td><?php echo $a['id']; ?></td>
        <td><?php echo htmlspecialchars($a['restaurant_name']); ?></td>
        <td><?php echo $a['total']; ?></td>
        <td>
          <form method="post" action="driver_accept.php" style="display:inline;">
            <input type="hidden" name="order_id" value="<?php echo $a['id']; ?>">
            <button class="btn btn-sm btn-primary">Accept</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <h4 class="mt-4">My Deliveries</h4>
  <?php if(empty($mine)): ?><div class="alert alert-info">No assigned deliveries.</div><?php else: ?>
    <table class="table">
      <tr><th>Delivery ID</th><th>Order</th><th>Restaurant</th><th>Total</th><th>Status</th><th>Action</th></tr>
    <?php foreach($mine as $m): ?>
      <tr>
        <td><?php echo $m['id']; ?></td>
        <td><?php echo $m['order_id']; ?></td>
        <td><?php echo htmlspecialchars($m['restaurant_name']); ?></td>
        <td><?php echo $m['total']; ?></td>
        <td><?php echo $m['status']; ?></td>
        <td>
          <?php if($m['status'] !== 'delivered'): ?>
            <form method="post" action="driver_mark_delivered.php" style="display:inline;">
              <input type="hidden" name="delivery_id" value="<?php echo $m['id']; ?>">
              <button class="btn btn-sm btn-success">Mark Delivered</button>
            </form>
          <?php else: echo '-'; endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </table>

    <script>
if (navigator.geolocation) {
  // Send the position to server when it changes
  navigator.geolocation.watchPosition(function(pos) {
    var lat = pos.coords.latitude;
    var lng = pos.coords.longitude;

    // send POST to update_driver_location.php
    var fd = new FormData();
    fd.append('lat', lat);
    fd.append('lng', lng);

    fetch('update_driver_location.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: fd
    }).catch(function(err){
      console.warn('Location update failed', err);
    });

  }, function(err){
    console.warn('Geolocation error', err);
  }, { enableHighAccuracy: true, maximumAge: 3000, timeout: 5000 });
} else {
  console.warn('Geolocation not supported');
}
</script>

  <?php endif; ?>
<script>
if (navigator.geolocation) {
  setInterval(() => {
    navigator.geolocation.getCurrentPosition(pos => {
      fetch('driver_location_update.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          order_id: '<?php echo $order_id; ?>',
          lat: pos.coords.latitude,
          lng: pos.coords.longitude
        })
      });
    });
  }, 5000);
}
</script>



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

</body>
</html>
