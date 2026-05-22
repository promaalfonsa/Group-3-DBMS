<?php
require 'db.php';
require 'functions.php';

$q = trim($_GET['q'] ?? '');
$foods = [];
$restaurants = [];
$popularFoods = [];

/* SEARCH FOOD */
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $mysqli->prepare("
        SELECT 
          m.id AS menu_id,
          m.name AS food_name,
          m.description,
          m.price,
          m.image_path,
          r.id AS restaurant_id,
          r.name AS restaurant_name
        FROM menu_items m
        JOIN restaurants r ON m.restaurant_id = r.id
        WHERE m.name LIKE ? OR m.description LIKE ?
        ORDER BY m.name
    ");
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $foods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
/* HOME PAGE */
else {
    // Popular / random foods
    $stmt = $mysqli->prepare("
        SELECT 
          m.id AS menu_id,
          m.name AS food_name,
          m.description,
          m.price,
          m.image_path,
          r.id AS restaurant_id,
          r.name AS restaurant_name
        FROM menu_items m
        JOIN restaurants r ON m.restaurant_id = r.id
        WHERE m.available = 1
        ORDER BY RAND()
        LIMIT 8
    ");
    $stmt->execute();
    $popularFoods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Restaurants
    $restaurants = $mysqli->query("
        SELECT * FROM restaurants ORDER BY id DESC
    ")->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Khadok — Food Delivery</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/assets/css/style.css" rel="stylesheet">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',sans-serif}
body{margin:0;background:#f6f6f6}

/* NAV */
.navbar{background:#e21b70;padding:14px 0}
.nav-inner{
  max-width:1200px;margin:auto;padding:0 20px;
  display:flex;justify-content:space-between;align-items:center;color:#fff
}
.nav-logo{font-size:1.6rem;font-weight:900;color:#fff;text-decoration:none}
.nav-right{display:flex;gap:18px;align-items:center}
.nav-right a{color:#fff;text-decoration:none;font-weight:600}

/* HERO */
.hero{background:#fff6f6;padding:50px 0}
.hero-inner{max-width:1200px;margin:auto;padding:0 20px}
.hero h1{font-size:36px}
.hero span{color:#e21b70}

/* SEARCH */
.search-box{
  max-width:600px;display:flex;background:#fff;border-radius:14px;
  overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,.1)
}
.search-box input{
  flex:1;border:none;padding:16px;font-size:16px;outline:none
}

/* FOOD GRID */
.food-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
  gap:20px;
  margin-top:20px;
}
.food-card{
  background:#fff;border-radius:16px;padding:14px;
  box-shadow:0 6px 18px rgba(0,0,0,.08);
  transition:.2s;
}
.food-card:hover{
  transform:translateY(-4px);
  box-shadow:0 12px 28px rgba(0,0,0,.12);
}
.food-img{
  width:120px;height:90px;object-fit:cover;border-radius:10px
}
.food-title{font-size:1.05rem;font-weight:600}
.food-restaurant{font-size:.85rem;color:#777}
.food-desc{font-size:.9rem;color:#555;margin:6px 0}
.food-footer{
  display:flex;justify-content:space-between;align-items:center;margin-top:8px
}
.food-price{font-weight:700;color:#e21b70}
.food-qty{width:60px;height:34px;border-radius:8px;border:1px solid #ddd;text-align:center}
.food-qty-wrap{display:flex;gap:6px}
.food-btn{
  background:#e21b70;border:none;color:#fff;
  padding:6px 14px;border-radius:20px;font-size:.85rem
}
.food-btn:hover{background:#c41860}
.rating-text{font-size:.85rem;color:#666}

/* RESTAURANTS */
.section{max-width:1200px;margin:40px auto;padding:0 20px}
.restaurant-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
  gap:20px
}
.restaurant-card{
  background:#fff;border-radius:16px;padding:18px;
  box-shadow:0 6px 18px rgba(0,0,0,.08)
}

/* FOOTER */
.khadok-footer{
  background:#1f1f1f;color:#ccc;margin-top:60px;padding-top:40px
}
.footer-inner{
  max-width:1200px;margin:auto;padding:0 20px 30px;
  display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:30px
}
.footer-logo{font-size:1.6rem;font-weight:900;color:#e21b70}
.footer-col h6{color:#fff;margin-bottom:12px}
.footer-col a{display:block;color:#ccc;text-decoration:none;margin-bottom:6px}
.footer-col a:hover{color:#e21b70}
.footer-bottom{
  border-top:1px solid #333;text-align:center;padding:12px;color:#888
}

/* HEADER FIX */
.nav-inner{
  display:flex;
  align-items:center;
  justify-content:space-between;
}

/* DESKTOP */
.desktop-menu{
  display:flex;
  gap:18px;
  align-items:center;
}

.menu-dropdown {
  display: none;
  position: absolute;
  right: 10px;
  top: 48px;
  background: #fff;
  border-radius: 12px;
  min-width: 170px;
  box-shadow: 0 12px 30px rgba(0,0,0,.2);
  z-index: 9999;
  overflow: hidden;
}

.menu-dropdown a {
  display: block;
  padding: 12px 16px;
  color: #333;
  font-weight: 600;
  text-decoration: none;
}

.menu-dropdown a:hover {
  background: #e21b70;
  color: #fff;
}

.menu-dropdown.show {
  display: block;
}

/* RESPONSIVE */
@media (max-width: 768px){
  .desktop-menu{display:none;}
  .mobile-menu{display:block;}
}

.navbar {
  display: flex;
  justify-content: center; /* keep bar centered */
}

.nav-inner {
  width: 100%;
  max-width: 1200px;
  display: flex;
  align-items: center;
  justify-content: space-between; /* THIS fixes logo left */
}

/* HIDE MOBILE MENU ON DESKTOP */
.mobile-menu {
  display: none;
}

/* SHOW ONLY ON MOBILE */
@media (max-width: 768px) {
  .desktop-menu {
    display: none !important;
  }

  .mobile-menu {
    display: block;
  }
}

.menu-btn {
  background: rgba(255,255,255,0.15);
  border: none;
  color: #fff;
  font-size: 22px;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  cursor: pointer;
}


</style>
</head>

<body>

<!-- NAV -->
<!-- NAV -->
<div class="navbar">
  <div class="nav-inner">
    <!-- LEFT: LOGO -->
    <a href="index.php" class="nav-logo">Khadok</a>

    <!-- RIGHT: DESKTOP MENU -->
    <div class="nav-right desktop-menu">
      <?php if(is_logged_in()): ?>
        <?php if($_SESSION["role"]==="driver"): ?>
          <a href="driver_orders.php">Driver Panel</a>
        <?php endif; ?>
        <?php if($_SESSION["role"]==="restaurant"): ?>
          <a href="restaurant_dashboard.php">My Restaurant</a>
        <?php endif; ?>
        <?php if($_SESSION["role"]==="admin"): ?>
          <a href="admin_analytics.php">Admin Dashboard</a>
        <?php endif; ?>

       <a href="profile.php">
    Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?>
</a>

        <?php if($_SESSION["role"]==="user"): ?>
          <a href="cart.php">Cart (<?php echo count(cart_get()); ?>)</a>
          <a href="orders.php">My Orders</a>
        <?php endif; ?>

        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Sign up</a>
      <?php endif; ?>
    </div>

    <!-- MOBILE 3 DOT MENU -->
    <div class="mobile-menu">
      <button class="menu-btn" onclick="toggleMenu()">⋮</button>
      <div class="menu-dropdown" id="mobileMenu">
        <?php if(is_logged_in()): ?>
          <?php if($_SESSION["role"]==="driver"): ?>
            <a href="driver_orders.php">Driver Panel</a>
          <?php endif; ?>
          <?php if($_SESSION["role"]==="restaurant"): ?>
            <a href="restaurant_dashboard.php">My Restaurant</a>
          <?php endif; ?>
          <?php if($_SESSION["role"]==="admin"): ?>
            <a href="admin_dashboard.php">Admin Dashboard</a>
          <?php endif; ?>

          <?php if($_SESSION["role"]==="user"): ?>
            <a href="cart.php">Cart</a>
            <a href="orders.php">My Orders</a>
          <?php endif; ?>

          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="login.php">Login</a>
          <a href="register.php">Sign up</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- HERO -->
<div class="hero">
<div class="hero-inner">
<h1>Fast, Fresh <span>& Delivered</span></h1>

<form method="get" class="search-box mt-3">
  <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search food (Burger, Pizza)">
</form>

<!-- POPULAR FOODS -->
<?php if($q===''): ?>
<h4 class="mt-4">Popular Foods</h4>
<div class="food-grid">
<?php foreach($popularFoods as $f): ?>
<?php
$rt = $mysqli->prepare("SELECT AVG(rating)a,COUNT(*)c FROM ratings WHERE menu_item_id=?");
$rt->bind_param('i',$f['menu_id']); $rt->execute();
$r=$rt->get_result()->fetch_assoc();
?>
<div class="food-card">
  <div class="d-flex gap-3">
    <?php if($f['image_path']): ?>
      <img src="<?php echo htmlspecialchars($f['image_path']); ?>" class="food-img">
    <?php endif; ?>
    <div style="flex:1">
      <div class="food-title"><?php echo htmlspecialchars($f['food_name']); ?></div>
      <div class="food-restaurant"><?php echo htmlspecialchars($f['restaurant_name']); ?></div>
      <div class="food-desc"><?php echo htmlspecialchars(substr($f['description'],0,60)); ?>…</div>
      <div class="rating-text">⭐ <?php echo $r['a']?number_format($r['a'],1):'N/A'; ?> (<?php echo $r['c']; ?>)</div>
      <form method="post" action="add_to_cart.php">
        <input type="hidden" name="menu_id" value="<?php echo $f['menu_id']; ?>">
        <input type="hidden" name="name" value="<?php echo htmlspecialchars($f['food_name']); ?>">
        <input type="hidden" name="price" value="<?php echo $f['price']; ?>">
        <input type="hidden" name="restaurant_id" value="<?php echo $f['restaurant_id']; ?>">
        <div class="food-footer">
          <div class="food-price"><?php echo number_format($f['price'],2); ?></div>
          <div class="food-qty-wrap">
            <input type="number" name="qty" class="food-qty" min="1" value="1">
            <button class="food-btn">Add</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- SEARCH RESULTS -->
<?php if($q!==''): ?>
<h4 class="mt-4">Food results for "<?php echo htmlspecialchars($q); ?>"</h4>

<?php if(empty($foods)): ?>
  <div class="alert alert-warning mt-3">No food found</div>
<?php else: ?>

<div class="food-grid">
<?php foreach($foods as $f): ?>
<?php
$rt = $mysqli->prepare("SELECT AVG(rating)a,COUNT(*)c FROM ratings WHERE menu_item_id=?");
$rt->bind_param('i',$f['menu_id']);
$rt->execute();
$r = $rt->get_result()->fetch_assoc();
?>
<div class="food-card">
  <div class="d-flex gap-3">
    <?php if($f['image_path']): ?>
      <img src="<?php echo htmlspecialchars($f['image_path']); ?>" class="food-img">
    <?php endif; ?>

    <div style="flex:1">
      <div class="food-title"><?php echo htmlspecialchars($f['food_name']); ?></div>
      <div class="food-restaurant"><?php echo htmlspecialchars($f['restaurant_name']); ?></div>
      <div class="food-desc"><?php echo htmlspecialchars(substr($f['description'],0,60)); ?>…</div>

      <div class="rating-text">
        ⭐ <?php echo $r['a']?number_format($r['a'],1):'N/A'; ?> (<?php echo $r['c']; ?>)
      </div>

      <form method="post" action="add_to_cart.php">
        <input type="hidden" name="menu_id" value="<?php echo $f['menu_id']; ?>">
        <input type="hidden" name="name" value="<?php echo htmlspecialchars($f['food_name']); ?>">
        <input type="hidden" name="price" value="<?php echo $f['price']; ?>">
        <input type="hidden" name="restaurant_id" value="<?php echo $f['restaurant_id']; ?>">

        <div class="food-footer">
          <div class="food-price"><?php echo number_format($f['price'],2); ?></div>
          <div class="food-qty-wrap">
            <input type="number" name="qty" class="food-qty" min="1" value="1">
            <button class="food-btn">Add</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>
<?php endif; ?>

</div>
</div>

<!-- RESTAURANTS -->
<?php if($q===''): ?>
<div class="section">
<h3>Restaurants Near You</h3>
<div class="restaurant-grid">
<?php foreach($restaurants as $r): ?>
  <div class="restaurant-card">
    <h5><?php echo htmlspecialchars($r['name']); ?></h5>
    <small><?php echo htmlspecialchars($r['city']); ?></small><br>
    <a href="restaurant.php?id=<?php echo $r['id']; ?>" class="food-btn mt-2">View Menu</a>
  </div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<!-- FOOTER -->
<footer class="khadok-footer">
  <div class="footer-inner">
    <div class="footer-col">
      <div class="footer-logo">Khadok</div>
      <p>Fast, fresh food delivered to your doorstep.</p>
    </div>
    <div class="footer-col">
      
      <a href="privacy_policy.php">Privacy Policy</a>
      <a href="terms_conditions.php">Terms & Conditions</a>
    </div>
    <div class="footer-col">
      
      <a href="about_us.php">About Us</a>
      <a href="contact_us.php">Contact Us</a>
    </div>
  </div>
  <div class="footer-bottom">
    © <?php echo date('Y'); ?> Khadok
  </div>
</footer>
<script>
function toggleMenu() {
  const menu = document.getElementById("mobileMenu");
  menu.classList.toggle("show");
}
</script>

</body>
</html>
