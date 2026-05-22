<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

if (!in_array($_SESSION['role'], ['restaurant','admin'])) {
    echo "Access denied";
    exit;
}

$current = basename($_SERVER['PHP_SELF']);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $pageTitle ?? 'Restaurant Panel'; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

:root{
    --khadok:#e21b70;
    --khadok-dark:#c2185f;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#f6f6f6;
}

.wrapper{
    display:flex;
    min-height:100vh;
}

/* SIDEBAR */

.sidebar{
    width:270px;
    background:#1f1f1f;
    color:white;
    position:fixed;
    top:0;
    bottom:0;
    left:0;
    padding:25px;
}

.logo{
    font-size:2rem;
    font-weight:900;
    color:var(--khadok);
    margin-bottom:35px;
}

.menu{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.menu a{
    color:#ccc;
    text-decoration:none;
    padding:14px 16px;
    border-radius:14px;
    transition:.2s;
    font-weight:600;
}

.menu a:hover{
    background:#333;
    color:white;
}

.menu a.active{
    background:var(--khadok);
    color:white;
}

/* CONTENT */

.content{
    flex:1;
    margin-left:270px;
    padding:30px;
}

.topbar{
    background:white;
    border-radius:18px;
    padding:18px 25px;
    margin-bottom:25px;
    box-shadow:0 10px 25px rgba(0,0,0,.06);

    display:flex;
    justify-content:space-between;
    align-items:center;
}

.page-title{
    font-size:1.8rem;
    font-weight:800;
}

.user{
    font-weight:600;
    color:#666;
}

/* CARDS */

.panel-card{
    background:white;
    border-radius:18px;
    padding:25px;
    box-shadow:0 12px 28px rgba(0,0,0,.08);
}

/* BUTTON */

.btn-khadok{
    background:var(--khadok);
    border:none;
    color:white;
}

.btn-khadok:hover{
    background:var(--khadok-dark);
    color:white;
}

/* MOBILE */

@media(max-width:992px){

    .wrapper{
        flex-direction:column;
    }

    .sidebar{
        width:100%;
        position:relative;
    }

    .content{
        margin-left:0;
    }

}
.sidebar{
    width:260px;
    min-height:100vh;
    background:linear-gradient(180deg,#1f1f1f,#111);
    position:fixed;
    left:0;
    top:0;
    padding:25px 15px;
    box-shadow:4px 0 20px rgba(0,0,0,.25);
    z-index:1000;
}

.sidebar-logo{
    color:#e21b70;
    font-size:28px;
    font-weight:800;
    margin-bottom:35px;
    text-align:center;
}

.sidebar a{
    display:flex;
    align-items:center;
    gap:10px;
    padding:14px 16px;
    margin-bottom:8px;
    color:#d1d5db;
    text-decoration:none;
    border-radius:12px;
    font-weight:600;
    transition:.25s;
}

.sidebar a:hover{
    background:#e21b70;
    color:#fff;
    transform:translateX(5px);
}

.sidebar a.active{
    background:#e21b70;
    color:#fff;
    box-shadow:0 8px 20px rgba(226,27,112,.35);
}

.sidebar hr{
    border-color:rgba(255,255,255,.1);
    margin:20px 0;
}

.main-content{
    margin-left:260px;
    padding:30px;
    min-height:100vh;
    background:#f5f6fa;
}

.topbar{
    background:#fff;
    padding:20px 30px;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,.06);
    margin-bottom:25px;
}

.page-title{
    margin:0;
    font-size:32px;
    font-weight:800;
    color:#222;
}

@media(max-width:768px){

    .sidebar{
        width:100%;
        min-height:auto;
        position:relative;
    }

    .main-content{
        margin-left:0;
        padding:15px;
    }
}
</style>
</head>

<body>

<div class="wrapper">

<div class="sidebar">

<div class="logo">
Khadok
</div>

<div class="menu">

<?php
$currentRid = intval($_GET['rid'] ?? 0);
?>

<div class="sidebar">

    <div class="sidebar-logo">
        🍽 Restaurant Panel
    </div>

  

    <a href="restaurant_orders.php?rid=<?php echo $currentRid; ?>"
       class="<?= basename($_SERVER['PHP_SELF'])=='restaurant_orders.php' ? 'active':'' ?>">
       📦 Orders
    </a>

    <a href="restaurant_menu_list.php?rid=<?php echo $currentRid; ?>"
       class="<?= basename($_SERVER['PHP_SELF'])=='restaurant_menu_list.php' ? 'active':'' ?>">
       🍽 Menu Items
    </a>

    <a href="restaurant_add_menu_item.php?rid=<?php echo $currentRid; ?>"
       class="<?= basename($_SERVER['PHP_SELF'])=='restaurant_add_menu_item.php' ? 'active':'' ?>">
       ➕ Add Menu Item
    </a>

    <hr>

    <a href="index.php">
       🌐 Back TO Home
    </a>

    <a href="logout.php">
       🚪 Logout
    </a>

</div>

</div>

</div>

<div class="content">

<div class="topbar">

<div class="page-title">
<?php echo $pageTitle ?? 'Restaurant Panel'; ?>
</div>

<div class="user">
Hello <?php echo htmlspecialchars($_SESSION['user_name']); ?>
</div>

</div>