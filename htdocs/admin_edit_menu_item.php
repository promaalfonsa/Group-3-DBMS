<?php
require 'db.php';
require 'functions.php';

if (!is_restaurant()) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { echo 'Invalid menu item'; exit; }

// Fetch item
$stmt = $mysqli->prepare("SELECT * FROM menu_items WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
if (!$item) { echo 'Menu item not found'; exit; }

// Categories
$cats = $mysqli->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $available = isset($_POST['available']) ? 1 : 0;
    $category_id = intval($_POST['category_id'] ?? 0);

    // Keep existing image unless replaced
    $img_path = $item['image_path'];

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $orig = basename($_FILES['image']['name']);
        $safe = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);
        $dest = 'uploads/' . $safe;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $img_path = $dest;
        }
    }

    $u = $mysqli->prepare("
        UPDATE menu_items 
        SET name = ?, description = ?, price = ?, image_path = ?, available = ?, category_id = ?
        WHERE id = ?
    ");
    $u->bind_param('ssdsiii', $name, $desc, $price, $img_path, $available, $category_id, $id);

    if ($u->execute()) {
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $err = $u->error;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Menu Item</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.edit-card {
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0,0,0,0.08);
  border: none;
}
.form-title {
  font-size: 1.8rem;
  font-weight: 800;
}
.save-btn {
  background: #e21b70;
  border: none;
  border-radius: 999px;
  padding: 8px 22px;
}
.save-btn:hover {
  background: #c4175f;
}
.img-preview {
  max-width: 200px;
  border-radius: 12px;
  margin-bottom: 10px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}
</style>
</head>

<body class="p-4">
<div class="container main-wrapper">

  <div class="mb-3">
    <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm">← Back to Dashboard</a>
  </div>

  <div class="card edit-card p-4">
    <h2 class="form-title mb-4">Edit Menu Item</h2>

    <?php if ($err): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

      <div class="mb-3">
        <label class="form-label">Item Name</label>
        <input class="form-control" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($item['description']); ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Price</label>
        <input class="form-control" type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($item['price']); ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Category</label>
        <select class="form-select" name="category_id">
          <option value="0">-- None --</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php if ($item['category_id'] == $c['id']) echo 'selected'; ?>>
              <?php echo htmlspecialchars($c['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Current Image</label><br>
        <?php if ($item['image_path']): ?>
          <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="img-preview">
        <?php else: ?>
          <div class="text-muted">No image uploaded</div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Change Image (optional)</label>
        <input class="form-control" type="file" name="image">
      </div>

      <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="available" <?php if ($item['available']) echo 'checked'; ?>>
        <label class="form-check-label">Available for order</label>
      </div>

      <button class="btn btn-primary save-btn">Save Changes</button>

    </form>
  </div>

</div>
</body>
</html>
