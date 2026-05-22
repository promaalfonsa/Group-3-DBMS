<?php
    error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = "Restaurant Orders";
require 'restaurant_layout.php';
if (!is_logged_in()) { 
    header('Location: login.php'); 
    exit; 
}


$rid = intval($_GET['rid'] ?? 0);

// Permission check
if ($_SESSION['role'] !== 'admin') {
    $uid = current_user_id();
    $check = $mysqli->prepare(
        'SELECT id FROM restaurants WHERE id = ? AND user_id = ? LIMIT 1'
    );
    $check->bind_param('ii', $rid, $uid);
    $check->execute();
    if (!$check->get_result()->fetch_assoc()) {
        echo 'Access denied';
        exit;
    }
}

// Fetch orders
$stmt = $mysqli->prepare("
    SELECT o.*, u.name AS user_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.restaurant_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param('i', $rid);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

  <style>
    .order-card {
      border-radius: 14px;
      box-shadow: 0 10px 25px rgba(0,0,0,.08);
      border: none;
      margin-bottom: 20px;
    }
    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
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
  </style>

<div class="container main-wrapper">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title">Orders for Your Restaurant</h2>
    <a class="btn btn-secondary" href="restaurant_dashboard.php">Back</a>
  </div>

  <?php if (empty($orders)): ?>
    <div class="alert alert-info">No orders yet.</div>
  <?php endif; ?>

  <?php foreach ($orders as $o): ?>
    <div class="card order-card">
      <div class="card-body">

        <div class="order-header mb-2">
          <div>
            <strong>Order #<?php echo $o['id']; ?></strong><br>
            <span class="order-meta">
              <?php echo htmlspecialchars($o['user_name']); ?> • 
              <?php echo htmlspecialchars($o['created_at']); ?>
            </span>
          </div>

          <span class="badge bg-<?php echo statusBadge($o['status']); ?>">
            <?php echo ucfirst(str_replace('_',' ', $o['status'])); ?>
          </span>
        </div>

        <div class="mb-2">
          <strong>Total:</strong> ৳<?php echo number_format($o['total'], 2); ?>
        </div>

        <?php if (!empty($o['delivery_note'])): ?>
          <div class="delivery-note">
            <strong>Delivery Note:</strong><br>
            <?php echo nl2br(htmlspecialchars($o['delivery_note'])); ?>
          </div>
        <?php endif; ?>

        <div class="mt-3">
          <?php if ($o['status'] === 'pending'): ?>
            <form method="post" action="restaurant_update_order.php" class="d-inline">
              <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
              <button class="btn btn-success btn-sm" name="action" value="accept">
                Accept
              </button>
              <button class="btn btn-danger btn-sm" name="action" value="reject">
                Reject
              </button>
            </form>
          <?php else: ?>
            <span class="text-muted">No action available</span>
          <?php endif; ?>
        </div>

      </div>
    </div>
  <?php endforeach; ?>
    </div>
</div>
</body>
</html>


