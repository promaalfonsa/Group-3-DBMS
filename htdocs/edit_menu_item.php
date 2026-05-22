<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in() || $_SESSION['role'] !== 'restaurant') {
    exit('Access denied');
}

$item_id = intval($_GET['id'] ?? 0);
$uid = current_user_id();

/* ======================
   FETCH MENU ITEM (SECURE)
====================== */
$stmt = $mysqli->prepare("
SELECT m.*, r.user_id
FROM menu_items m
JOIN restaurants r ON r.id = m.restaurant_id
WHERE m.id = ? AND r.user_id = ?
LIMIT 1
");
$stmt->bind_param('ii', $item_id, $uid);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    exit('Item not found or access denied');
}

/* ======================
   UPDATE MENU ITEM
====================== */
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $available = isset($_POST['available']) ? 1 : 0;

    $image_path = $item['image_path'];

    // IMAGE UPLOAD (OPTIONAL)
    if (!empty($_FILES['image']['name'])) {
        $dir = 'uploads/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $filename = time() . '_' . basename($_FILES['image']['name']);
        $target = $dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = $target;
        }
    }

    $upd = $mysqli->prepare("
        UPDATE menu_items
        SET name=?, price=?, description=?, image_path=?, available=?
        WHERE id=?
    ");
    $upd->bind_param(
        'sdssii',
        $name,
        $price,
        $description,
        $image_path,
        $available,
        $item_id
    );

    if ($upd->execute()) {
        $msg = "Menu item updated successfully";
        // refresh item
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
    } else {
        $msg = "Update failed";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Menu Item</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ================================
   KHADOK THEME – EDIT MENU ITEM
================================ */
body{
  background:#f6f6f6;
  font-family:'Segoe UI',sans-serif;
}

/* Card */
.edit-card{
  background:#fff;
  border-radius:18px;
  padding:26px;
  box-shadow:0 12px 30px rgba(0,0,0,.08);
  max-width:650px;
  margin:auto;
}

/* Title */
.edit-title{
  font-size:1.8rem;
  font-weight:800;
  color:#e21b70;
  margin-bottom:16px;
}

/* Labels */
.form-label{
  font-weight:600;
  color:#444;
}

/* Inputs */
.form-control, textarea{
  border-radius:12px;
  padding:12px 14px;
  border:1px solid #ddd;
}

.form-control:focus, textarea:focus{
  border-color:#e21b70;
  box-shadow:0 0 0 .15rem rgba(226,27,112,.25);
}

/* Checkbox */
.form-check-input{
  border-radius:6px;
}
.form-check-input:checked{
  background:#e21b70;
  border-color:#e21b70;
}

/* Buttons */
.btn-khadok{
  background:#e21b70;
  color:#fff;
  border:none;
  padding:10px 24px;
  border-radius:999px;
  font-weight:600;
}
.btn-khadok:hover{
  background:#c41860;
}

.btn-outline-khadok{
  border:2px solid #e21b70;
  color:#e21b70;
  padding:8px 18px;
  border-radius:999px;
  font-weight:600;
  text-decoration:none;
}
.btn-outline-khadok:hover{
  background:#e21b70;
  color:#fff;
}

/* Image preview */
.image-preview{
  margin-top:10px;
  max-width:140px;
  border-radius:12px;
  box-shadow:0 6px 18px rgba(0,0,0,.15);
}

/* Alert */
.alert-info{
  background:#fff5f8;
  border-color:#f3c1d8;
  color:#7a1c4b;
  border-radius:12px;
}

/* Mobile */
@media(max-width:576px){
  .edit-card{padding:18px;}
}
</style>
</head>

<body class="p-4">

<div class="container mt-4">
  <div class="edit-card">

    <a href="restaurant_dashboard.php" class="btn-outline-khadok mb-3 d-inline-block">
      ← Back to Dashboard
    </a>

    <div class="edit-title">Edit Menu Item</div>

    <?php if($msg): ?>
      <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

      <div class="mb-3">
        <label class="form-label">Food Name</label>
        <input class="form-control"
               name="name"
               value="<?php echo htmlspecialchars($item['name']); ?>"
               required>
      </div>

      <div class="mb-3">
        <label class="form-label">Price</label>
        <input class="form-control"
               type="number"
               step="0.01"
               name="price"
               value="<?php echo $item['price']; ?>"
               required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control"
                  name="description"
                  rows="3"><?php echo htmlspecialchars($item['description']); ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Image (optional)</label>
        <input type="file" name="image" class="form-control">
        <?php if($item['image_path']): ?>
          <img src="<?php echo $item['image_path']; ?>" class="image-preview">
        <?php endif; ?>
      </div>

      <div class="form-check mb-4">
        <input class="form-check-input"
               type="checkbox"
               name="available"
               id="available"
               <?php if($item['available']) echo 'checked'; ?>>
        <label class="form-check-label" for="available">
          Available for order
        </label>
      </div>

      <button class="btn btn-khadok">Save Changes</button>

    </form>

  </div>
</div>

</body>
</html>
