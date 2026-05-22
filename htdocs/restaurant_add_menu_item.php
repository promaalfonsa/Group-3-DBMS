<?php
$pageTitle = "Add Menu";
require 'restaurant_layout.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'restaurant' && $_SESSION['role'] !== 'admin') { echo 'Access denied'; exit; }

$rid = intval($_GET['rid'] ?? 0);
// check ownership for non-admin
if ($_SESSION['role'] !== 'admin') {
    $stmt = $mysqli->prepare("SELECT id FROM restaurants WHERE id = ? AND user_id = ? LIMIT 1");
    $uid = current_user_id();
    $stmt->bind_param('ii',$rid,$uid); $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) { echo 'Invalid restaurant or access denied'; exit; }
}

$uploadDir = 'uploads/';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']); $desc = trim($_POST['description']);
    $price = floatval($_POST['price']); $available = isset($_POST['available'])?1:0;
    $category_id = intval($_POST['category_id']?:0);
    $img_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $orig = basename($_FILES['image']['name']);
        $safe = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/','_',$orig);
        if (!is_dir($uploadDir)) mkdir($uploadDir,0777,true);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$safe);
        $img_path = $uploadDir.$safe;
    }
    $stmt = $mysqli->prepare("INSERT INTO menu_items (restaurant_id,name,description,price,image_path,available,category_id) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('issdsii', $rid, $name, $desc, $price, $img_path, $available, $category_id);
    if ($stmt->execute()) header('Location: restaurant_menu_list.php?rid='.$rid);
    else $err = $stmt->error;
}
$cats = $mysqli->query('SELECT * FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
?>

<div class="container"><h2>Add Menu Item for Restaurant</h2>
<?php if($err) echo '<div class="alert alert-danger">'.htmlspecialchars($err).'</div>'; ?>
<form method="post" enctype="multipart/form-data">
 <div class="mb-2">Name: <input class="form-control" name="name" required></div>
 <div class="mb-2">Description: <textarea class="form-control" name="description"></textarea></div>
 <div class="mb-2">Price: <input class="form-control" name="price" required></div>
 <div class="mb-2">Category: <select class="form-select" name="category_id"><option value="0">-- None --</option><?php foreach($cats as $c) echo '<option value="'.$c['id'].'">'.htmlspecialchars($c['name']).'</option>'; ?></select></div>
 <div class="mb-2">Image: <input class="form-control" type="file" name="image"></div>
 <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="available" checked> Available</div>
 <button class="btn btn-primary">Add</button>
</form>
</div></body></html>
