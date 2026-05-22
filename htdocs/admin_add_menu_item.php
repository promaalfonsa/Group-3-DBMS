<?php
$pageTitle = "Add restaurant menus"; // change per page
require 'admin_layout.php';
if (!is_admin()) { header('Location: login.php'); exit; }

$uploadDir = 'uploads/';
$err = '';

// fetch restaurants
$rests = $mysqli->query("SELECT id,name FROM restaurants ORDER BY name")->fetch_all(MYSQLI_ASSOC);
// fetch categories
$cats = $mysqli->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurant_id = intval($_POST['restaurant_id']);
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id'] ?? 0);
    $available = isset($_POST['available']) ? 1 : 0;
    $img_path = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $orig = basename($_FILES['image']['name']);
        $safe = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/','_',$orig);
        if (!is_dir($uploadDir)) mkdir($uploadDir,0777,true);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$safe)) {
            $img_path = $uploadDir.$safe;
        }
    }

    $stmt = $mysqli->prepare("
        INSERT INTO menu_items 
        (restaurant_id, name, description, price, image_path, available, category_id) 
        VALUES (?,?,?,?,?,?,?)
    ");
    $stmt->bind_param(
        'issdsii',
        $restaurant_id,
        $name,
        $desc,
        $price,
        $img_path,
        $available,
        $category_id
    );

    if ($stmt->execute()) {
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $err = $stmt->error;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin – Add Menu Item</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.form-card {
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0,0,0,.08);
  border: none;
}
.page-title {
  font-size: 1.8rem;
  font-weight: 800;
}
.action-btn {
  border-radius: 999px;
  padding: 6px 18px;
}
</style>
</head>

<body class="p-4">
<div class="container main-wrapper">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title">Add Menu Item (Admin)</h2>
    <a class="btn btn-secondary action-btn" href="admin_dashboard.php">Back</a>
  </div>

  <?php if ($err): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
  <?php endif; ?>

  <div class="card form-card p-4">

    <form method="post" enctype="multipart/form-data">

      <div class="mb-3">
        <label class="form-label">Restaurant</label>
        <select class="form-select" name="restaurant_id" required>
          <option value="">-- Select Restaurant --</option>
          <?php foreach($rests as $r): ?>
            <option value="<?php echo $r['id']; ?>">
              <?php echo htmlspecialchars($r['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Food Name</label>
        <input class="form-control" name="name" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Price</label>
        <input class="form-control" name="price" type="number" step="0.01" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Category</label>
        <select class="form-select" name="category_id">
          <option value="0">-- None --</option>
          <?php foreach($cats as $c): ?>
            <option value="<?php echo $c['id']; ?>">
              <?php echo htmlspecialchars($c['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Image (optional)</label>
        <input class="form-control" type="file" name="image" accept=".png,.jpg,.jpeg,.webp">
      </div>

      <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="available" checked>
        <label class="form-check-label">Available</label>
      </div>

      <button class="btn btn-primary action-btn">
        ➕ Add Menu Item
      </button>

    </form>

  </div>

</div>
</body>
</html>
