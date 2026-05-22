<?php
require 'db.php';
require 'functions.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
$id = intval($_GET['id'] ?? 0);
$uid = current_user_id();
$stmt = $mysqli->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param('ii',$id,$uid); $stmt->execute(); $addr = $stmt->get_result()->fetch_assoc();
if (!$addr) { echo 'Not found'; exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $label = trim($_POST['label']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);
    $u = $mysqli->prepare("UPDATE addresses SET label=?,address=?,city=?,phone=? WHERE id=? AND user_id=?");
    $u->bind_param('sssiii', $label, $address, $city, $phone, $id, $uid);
    if ($u->execute()) header('Location: addresses.php'); else $err = $u->error;
}
?>
<!doctype html><html><head><meta charset="utf-8"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    <link rel="stylesheet" href="/assets/css/style.css">
</head><body class="p-4">
<div class="container"><h2>Edit Address</h2>
<?php if($err) echo '<div class="alert alert-danger">'.htmlspecialchars($err).'</div>'; ?>
<form method="post">
 <div class="mb-2">Label: <input class="form-control" name="label" value="<?php echo htmlspecialchars($addr['label']); ?>" required></div>
 <div class="mb-2">Address: <textarea class="form-control" name="address" required><?php echo htmlspecialchars($addr['address']); ?></textarea></div>
 <div class="mb-2">City: <input class="form-control" name="city" value="<?php echo htmlspecialchars($addr['city']); ?>"></div>
 <div class="mb-2">Phone: <input class="form-control" name="phone" value="<?php echo htmlspecialchars($addr['phone']); ?>"></div>
 <button class="btn btn-primary">Save</button>
</form>
</div></body></html>
