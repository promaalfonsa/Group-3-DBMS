<?php
/*
|--------------------------------------------------------------------------
| setup.php
|--------------------------------------------------------------------------
| First-time installer for Khadok
| - Creates database
| - Imports tables
| - Creates admin account
| - Generates db.php automatically
|--------------------------------------------------------------------------
*/

session_start();

$step = $_POST['step'] ?? 1;
$msg = '';

/*
|--------------------------------------------------------------------------
| STEP 1 → DATABASE CONNECTION
|--------------------------------------------------------------------------
*/
if ($step == 2) {

    $db_host = trim($_POST['db_host']);
    $db_user = trim($_POST['db_user']);
    $db_pass = trim($_POST['db_pass']);
    $db_name = trim($_POST['db_name']);

    $admin_name = trim($_POST['admin_name']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = trim($_POST['admin_password']);

    try {

        /*
        |--------------------------------------------------------------------------
        | CONNECT MYSQL
        |--------------------------------------------------------------------------
        */
        $mysqli = new mysqli($db_host, $db_user, $db_pass);

        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE DATABASE
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
            CREATE DATABASE IF NOT EXISTS `$db_name`
            CHARACTER SET utf8mb4
            COLLATE utf8mb4_general_ci
        ");

        $mysqli->select_db($db_name);

        /*
        |--------------------------------------------------------------------------
        | USERS TABLE
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
        CREATE TABLE IF NOT EXISTS users (
          id INT AUTO_INCREMENT PRIMARY KEY,
          name VARCHAR(150),
          email VARCHAR(150) UNIQUE,
          password_hash VARCHAR(255),
          role VARCHAR(20) DEFAULT 'user',
          phone VARCHAR(50),
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
        ");

        /*
        |--------------------------------------------------------------------------
        | RESTAURANTS
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
        CREATE TABLE IF NOT EXISTS restaurants (
          id INT AUTO_INCREMENT PRIMARY KEY,
          user_id INT NULL,
          name VARCHAR(200),
          address TEXT,
          city VARCHAR(100),
          phone VARCHAR(50),
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (user_id)
          REFERENCES users(id)
          ON DELETE SET NULL
        )
        ");

        /*
        |--------------------------------------------------------------------------
        | CATEGORIES
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
        CREATE TABLE IF NOT EXISTS categories (
          id INT AUTO_INCREMENT PRIMARY KEY,
          name VARCHAR(100) UNIQUE
        )
        ");

        /*
        |--------------------------------------------------------------------------
        | MENU ITEMS
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
        CREATE TABLE IF NOT EXISTS menu_items (
          id INT AUTO_INCREMENT PRIMARY KEY,
          restaurant_id INT NULL,
          name VARCHAR(200),
          description TEXT,
          price DECIMAL(10,2),
          image_path VARCHAR(255),
          available TINYINT(1) DEFAULT 1,
          category_id INT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

          FOREIGN KEY (restaurant_id)
          REFERENCES restaurants(id)
          ON DELETE CASCADE,

          FOREIGN KEY (category_id)
          REFERENCES categories(id)
          ON DELETE SET NULL
        )
        ");

        /*
        |--------------------------------------------------------------------------
        | ORDERS
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
        CREATE TABLE IF NOT EXISTS orders (
          id INT AUTO_INCREMENT PRIMARY KEY,
          user_id INT NULL,
          restaurant_id INT NULL,
          total DECIMAL(10,2),
          address TEXT,
          phone VARCHAR(50),
          payment_method VARCHAR(50),
          delivery_note TEXT,
          status VARCHAR(50) DEFAULT 'pending',
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

          FOREIGN KEY (user_id)
          REFERENCES users(id)
          ON DELETE CASCADE,

          FOREIGN KEY (restaurant_id)
          REFERENCES restaurants(id)
          ON DELETE SET NULL
        )
        ");

        /*
        |--------------------------------------------------------------------------
        | ORDER ITEMS
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
        CREATE TABLE IF NOT EXISTS order_items (
          id INT AUTO_INCREMENT PRIMARY KEY,
          order_id INT NULL,
          menu_item_id INT NULL,
          qty INT,
          price DECIMAL(10,2),

          FOREIGN KEY (order_id)
          REFERENCES orders(id)
          ON DELETE CASCADE,

          FOREIGN KEY (menu_item_id)
          REFERENCES menu_items(id)
          ON DELETE SET NULL
        )
        ");

        /*
        |--------------------------------------------------------------------------
        | DELIVERIES
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
        CREATE TABLE IF NOT EXISTS deliveries (
          id INT AUTO_INCREMENT PRIMARY KEY,
          order_id INT NULL,
          driver_id INT NULL,
          assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          status VARCHAR(50) DEFAULT 'assigned',
          latitude DECIMAL(10,7),
          longitude DECIMAL(10,7),

          FOREIGN KEY (order_id)
          REFERENCES orders(id)
          ON DELETE CASCADE,

          FOREIGN KEY (driver_id)
          REFERENCES users(id)
          ON DELETE SET NULL
        )
        ");

        /*
        |--------------------------------------------------------------------------
        | DRIVER LOCATIONS
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
        CREATE TABLE IF NOT EXISTS driver_locations (
          driver_id INT PRIMARY KEY,
          latitude DECIMAL(10,7),
          longitude DECIMAL(10,7),
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
          ON UPDATE CURRENT_TIMESTAMP,

          FOREIGN KEY (driver_id)
          REFERENCES users(id)
          ON DELETE CASCADE
        )
        ");

        /*
        |--------------------------------------------------------------------------
        | RATINGS
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
        CREATE TABLE IF NOT EXISTS ratings (
          id INT AUTO_INCREMENT PRIMARY KEY,
          user_id INT NULL,
          restaurant_id INT NULL,
          order_id INT NULL,
          menu_item_id INT NULL,
          rating TINYINT,
          comment TEXT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

          FOREIGN KEY (user_id)
          REFERENCES users(id)
          ON DELETE CASCADE,

          FOREIGN KEY (restaurant_id)
          REFERENCES restaurants(id)
          ON DELETE CASCADE,

          FOREIGN KEY (menu_item_id)
          REFERENCES menu_items(id)
          ON DELETE CASCADE
        )
        ");

        /*
        |--------------------------------------------------------------------------
        | PROMOTIONS
        |--------------------------------------------------------------------------
        */
        $mysqli->query("
        CREATE TABLE IF NOT EXISTS promotions (
          id INT AUTO_INCREMENT PRIMARY KEY,
          code VARCHAR(50) UNIQUE,
          discount_percent INT,
          active TINYINT DEFAULT 1,
          valid_from DATE,
          valid_to DATE
        )
        ");

        /*
        |--------------------------------------------------------------------------
        | CREATE ADMIN
        |--------------------------------------------------------------------------
        */
        $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("
            INSERT INTO users
            (name,email,password_hash,role)
            VALUES (?,?,?,'admin')
        ");

        $stmt->bind_param(
            'sss',
            $admin_name,
            $admin_email,
            $password_hash
        );

        $stmt->execute();

        /*
        |--------------------------------------------------------------------------
        | GENERATE DB.PHP
        |--------------------------------------------------------------------------
        */
        $db_content = "<?php

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

\$mysqli = new mysqli(
    '$db_host',
    '$db_user',
    '$db_pass',
    '$db_name'
);

if(\$mysqli->connect_error){
    die('Database connection failed');
}
";

        file_put_contents('db.php', $db_content);

        /*
        |--------------------------------------------------------------------------
        | LOCK INSTALLER
        |--------------------------------------------------------------------------
        */
        file_put_contents('installed.lock', 'installed');

        $success = true;

    } catch (Exception $e) {
        $msg = $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| BLOCK IF INSTALLED
|--------------------------------------------------------------------------
*/
if (file_exists('installed.lock')) {
    die("
    <h2 style='font-family:sans-serif;color:#e21b70'>
      Khadok already installed.
    </h2>
    ");
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Khadok Setup</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
  background:#fff5f8;
  font-family:'Segoe UI',sans-serif;
}

.setup-card{
  max-width:700px;
  margin:60px auto;
  background:#fff;
  border-radius:20px;
  padding:40px;
  box-shadow:0 20px 40px rgba(0,0,0,.08);
}

.logo{
  color:#e21b70;
  font-size:2rem;
  font-weight:900;
}

.btn-pink{
  background:#e21b70;
  border:none;
  color:#fff;
  padding:12px 24px;
  border-radius:12px;
}

.btn-pink:hover{
  background:#c41860;
}
</style>
</head>
<body>

<div class="setup-card">

<?php if(!empty($success)): ?>

  <div class="text-center">

    <div class="logo mb-3">Khadok</div>

    <h2>Installation Complete 🎉</h2>

    <p class="text-muted">
      Database configured successfully.
    </p>

    <a href="login.php" class="btn btn-pink">
      Go to Login
    </a>

  </div>

<?php else: ?>

<div class="logo mb-3">Khadok Setup</div>

<p class="text-muted">
Configure database and create admin account.
</p>

<?php if($msg): ?>
<div class="alert alert-danger">
  <?php echo htmlspecialchars($msg); ?>
</div>
<?php endif; ?>

<form method="post">

<input type="hidden" name="step" value="2">

<h5 class="mb-3">Database Configuration</h5>

<div class="mb-3">
  <label>Database Host</label>
  <input
    type="text"
    name="db_host"
    class="form-control"
    value="localhost"
    required
  >
</div>

<div class="mb-3">
  <label>Database User</label>
  <input
    type="text"
    name="db_user"
    class="form-control"
    value="root"
    required
  >
</div>

<div class="mb-3">
  <label>Database Password</label>
  <input
    type="password"
    name="db_pass"
    class="form-control"
  >
</div>

<div class="mb-3">
  <label>Database Name</label>
  <input
    type="text"
    name="db_name"
    class="form-control"
    value="khadok"
    required
  >
</div>

<hr class="my-4">

<h5 class="mb-3">Admin Account</h5>

<div class="mb-3">
  <label>Admin Name</label>
  <input
    type="text"
    name="admin_name"
    class="form-control"
    required
  >
</div>

<div class="mb-3">
  <label>Admin Email</label>
  <input
    type="email"
    name="admin_email"
    class="form-control"
    required
  >
</div>

<div class="mb-4">
  <label>Admin Password</label>
  <input
    type="password"
    name="admin_password"
    class="form-control"
    required
  >
</div>

<button class="btn btn-pink">
  Install Khadok
</button>

</form>

<?php endif; ?>

</div>

</body>
</html>