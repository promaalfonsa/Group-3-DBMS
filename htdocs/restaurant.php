<?php
require 'db.php';
require 'functions.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { echo "Restaurant not found"; exit; }

/* Restaurant */
$stmt = $mysqli->prepare("SELECT * FROM restaurants WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$restaurant = $stmt->get_result()->fetch_assoc();
if (!$restaurant) { echo "Restaurant not found"; exit; }

/* Menu */
$stmt = $mysqli->prepare("
  SELECT m.*, c.name AS category_name
  FROM menu_items m
  LEFT JOIN categories c ON m.category_id = c.id
  WHERE m.restaurant_id = ? AND m.available = 1
  ORDER BY m.id DESC
");
$stmt->bind_param('i', $id);
$stmt->execute();
$menu = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* Restaurant rating */
$avgRes = $mysqli->prepare("
  SELECT AVG(rating) a, COUNT(*) c
  FROM ratings
  WHERE restaurant_id = ? AND (menu_item_id IS NULL OR menu_item_id = 0)
");
$avgRes->bind_param('i', $id);
$avgRes->execute();
$avgRow = $avgRes->get_result()->fetch_assoc();
$avg_rating = $avgRow['a'] ? number_format($avgRow['a'],1) : 'N/A';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($restaurant['name']); ?> | Khadok</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">

<style>
/* HEADER */
.khadok-header {
  background:#fff;
  padding:14px 0;
  box-shadow:0 6px 18px rgba(0,0,0,.08);
}
.khadok-logo {
  font-size:1.6rem;
  font-weight:900;
  color:#e21b70;
  text-decoration:none;
}
.khadok-nav a {
  margin-left:16px;
  font-weight:600;
  color:#333;
  text-decoration:none;
}

/* MENU */
.menu-grid {
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(360px,1fr));
  gap:18px;
}
.menu-card {
  background:#fff;
  border-radius:14px;
  padding:14px;
  box-shadow:0 6px 18px rgba(0,0,0,.08);
  transition:.2s;
}
.menu-card:hover {
  transform:translateY(-4px);
  box-shadow:0 12px 28px rgba(0,0,0,.12);
}
.menu-img {
  width:120px;
  height:90px;
  object-fit:cover;
  border-radius:10px;
}
.menu-title { font-size:1.05rem; font-weight:600; }
.menu-category { font-size:.85rem; color:#777; }
.menu-desc { font-size:.9rem; color:#555; margin:6px 0; }
.menu-footer {
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-top:8px;
}
.menu-price { font-weight:700; color:#e21b70; }
.menu-add-btn {
  background:#e21b70;
  border:none;
  color:#fff;
  padding:6px 14px;
  border-radius:20px;
  font-size:.85rem;
}
.menu-add-btn:hover { background:#c41860; }
.menu-qty {
  width:60px;
  height:34px;
  text-align:center;
  border-radius:8px;
  border:1px solid #ddd;
}
.menu-qty-wrap { display:flex; gap:6px; }

/* RATING */
.rating-text { font-size:.85rem; color:#666; }
.rate-form select, .rate-form input {
  font-size:.8rem;
}
@media(max-width:576px){
  .menu-grid{grid-template-columns:1fr;}
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="khadok-header">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php" class="khadok-logo">Khadok</a>
    <div class="khadok-nav">
      <?php if(is_logged_in()): ?>
        <span>Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="cart.php">Cart (<?php echo count(cart_get()); ?>)</a>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="container my-4">

<!-- RESTAURANT INFO -->
<div class="d-flex justify-content-between align-items-start mb-4">
  <div>
    <h2><?php echo htmlspecialchars($restaurant['name']); ?></h2>
    <p><?php echo htmlspecialchars($restaurant['address']); ?> • <?php echo htmlspecialchars($restaurant['city']); ?></p>
    <strong><?php echo $avg_rating; ?></strong> ⭐ (<?php echo $avgRow['c']; ?> reviews)
  </div>
  <a class="btn btn-secondary" href="index.php">Back</a>
</div>

<!-- MENU -->
<h4 class="mb-3">Menu</h4>
<div class="menu-grid">

<?php foreach($menu as $m): ?>
<?php
/* Item rating */
$avgItem = $mysqli->prepare("SELECT AVG(rating)a, COUNT(*)c FROM ratings WHERE menu_item_id=?");
$avgItem->bind_param('i',$m['id']);
$avgItem->execute();
$ai=$avgItem->get_result()->fetch_assoc();

/* Can rate? */
$canRate=false; $existing=null;
if(is_logged_in()){
  $chk=$mysqli->prepare("
    SELECT oi.id FROM order_items oi
    JOIN orders o ON oi.order_id=o.id
    WHERE oi.menu_item_id=? AND o.user_id=?
    LIMIT 1
  ");
  $chk->bind_param('ii',$m['id'],$_SESSION['user_id']);
  $chk->execute();
  if($chk->get_result()->fetch_assoc()) $canRate=true;

  $er=$mysqli->prepare("SELECT * FROM ratings WHERE user_id=? AND menu_item_id=? LIMIT 1");
  $er->bind_param('ii',$_SESSION['user_id'],$m['id']);
  $er->execute();
  $existing=$er->get_result()->fetch_assoc();
}
?>

<div class="menu-card">
  <div class="d-flex gap-3">

    <?php if($m['image_path']): ?>
      <img src="<?php echo htmlspecialchars($m['image_path']); ?>" class="menu-img">
    <?php endif; ?>

    <div style="flex:1;">
      <div class="menu-title">
        <?php echo htmlspecialchars($m['name']); ?>
        <small class="menu-category">(<?php echo htmlspecialchars($m['category_name']); ?>)</small>
      </div>

      <div class="menu-desc"><?php echo htmlspecialchars($m['description']); ?></div>

      <div class="rating-text">
        ⭐ <?php echo $ai['a']?number_format($ai['a'],1):'N/A'; ?> (<?php echo $ai['c']; ?>)
      </div>

      <?php if($canRate): ?>
      <form method="post" action="submit_rating.php" class="rate-form d-flex gap-2 mt-1">
        <input type="hidden" name="menu_item_id" value="<?php echo $m['id']; ?>">
        <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">
        <select name="rating" class="form-select form-select-sm" style="width:80px">
          <?php for($i=5;$i>=1;$i--): ?>
            <option value="<?php echo $i; ?>" <?php if(($existing['rating']??0)==$i) echo 'selected'; ?>>
              <?php echo $i; ?>★
            </option>
          <?php endfor; ?>
        </select>
        <input type="text" name="comment" class="form-control form-control-sm"
          placeholder="Review"
          value="<?php echo htmlspecialchars($existing['comment']??''); ?>">
        <button class="btn btn-sm btn-outline-primary">
          <?php echo $existing?'Update':'Rate'; ?>
        </button>
      </form>
      <?php endif; ?>

      <form method="post" action="add_to_cart.php" class="mt-2">
        <input type="hidden" name="menu_id" value="<?php echo $m['id']; ?>">
        <input type="hidden" name="name" value="<?php echo htmlspecialchars($m['name']); ?>">
        <input type="hidden" name="price" value="<?php echo $m['price']; ?>">
        <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['id']; ?>">

        <div class="menu-footer">
          <div class="menu-price"><?php echo number_format($m['price'],2); ?></div>
          <div class="menu-qty-wrap">
            <input type="number" name="qty" class="menu-qty" min="1" value="1">
            <button class="menu-add-btn">Add</button>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>
<?php endforeach; ?>

</div>
</div>
</body>
</html>
