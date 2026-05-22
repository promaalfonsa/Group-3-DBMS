<?php
$pageTitle = "Restaurant Menu";
require 'restaurant_layout.php';

if (!is_logged_in()) { header('Location: login.php'); exit; }

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

// Fetch menu items
$stmt = $mysqli->prepare(
    'SELECT * FROM menu_items WHERE restaurant_id = ? ORDER BY id DESC'
);
$stmt->bind_param('i', $rid);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<style>
.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 20px;
}
.menu-card {
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0,0,0,0.08);
  border: none;
  overflow: hidden;
}
.menu-img {
  width: 100%;
  height: 160px;
  object-fit: cover;
  background: #f2f2f2;
}
.menu-body {
  padding: 14px;
}
.menu-title {
  font-weight: 700;
  font-size: 1.05rem;
}
.menu-price {
  font-weight: 700;
  color: #e21b70;
}
.badge-available {
  background: #28a745;
}
.badge-unavailable {
  background: #dc3545;
}
.action-btn {
  border-radius: 999px;
  padding: 5px 12px;
  font-size: 0.8rem;
}
</style>



<div class="container main-wrapper">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-title">My Menu Items</h2>
    <div>
      <a class="btn btn-secondary action-btn" href="restaurant_dashboard.php">Back</a>
      <a class="btn btn-primary action-btn"
         href="restaurant_add_menu_item.php?rid=<?php echo $rid; ?>">
         ➕ Add Item
      </a>
    </div>
  </div>

  <?php if (empty($items)): ?>
    <div class="alert alert-info">No menu items added yet.</div>
  <?php else: ?>

    <div class="menu-grid">
      <?php foreach ($items as $it): ?>
        <div class="card menu-card">

          <?php if (!empty($it['image_path'])): ?>
            <img src="<?php echo htmlspecialchars($it['image_path']); ?>" class="menu-img">
          <?php else: ?>
            <div class="menu-img d-flex align-items-center justify-content-center text-muted">
              No image
            </div>
          <?php endif; ?>

          <div class="menu-body">
            <div class="menu-title">
              <?php echo htmlspecialchars($it['name']); ?>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2">
              <span class="menu-price">
                ৳<?php echo number_format($it['price'], 2); ?>
              </span>

              <?php if ($it['available']): ?>
                <span class="badge badge-available">Available</span>
              <?php else: ?>
                <span class="badge badge-unavailable">Unavailable</span>
              <?php endif; ?>
            </div>

            <div class="d-flex gap-2 mt-3">
              <a class="btn btn-outline-secondary action-btn"
                 href="edit_menu_item.php?id=<?php echo $it['id']; ?>">
                 ✏️ Edit
              </a>

              <a class="btn btn-outline-danger action-btn"
                 href="delete_menu_item.php?id=<?php echo $it['id']; ?>&rid=<?php echo $rid; ?>"
                 onclick="return confirm('Delete this item?')">
                 🗑 Delete
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
