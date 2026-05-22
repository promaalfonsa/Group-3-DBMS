<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $stmt = $mysqli->prepare("SELECT id,name,password_hash,role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($u = $res->fetch_assoc()) {
        if (password_verify($pass, $u['password_hash'])) {
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_name'] = $u['name'];
            $_SESSION['role'] = $u['role'];
            if ($u['role'] === 'admin') header('Location: admin_analytics.php');
            elseif ($u['role'] === 'driver') header('Location: driver_orders.php');
            else header('Location: index.php');
            exit;
        } else $err = "Invalid creds";
    } else $err = "Invalid creds";
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Login</title>

<style>
/* RESET */
*{
    box-sizing:border-box;
    font-family: 'Segoe UI', sans-serif;
}

/* PAGE */
body{
    margin:0;
    min-height:100vh;
    background:#e2b6a9;
    display:flex;
    align-items:center;
    justify-content:center;
}

/* CARD */
.auth-card{
    width:360px;
    background:#f7f7f9;
    border-radius:28px;
    padding:28px 22px;
    box-shadow:0 20px 45px rgba(0,0,0,.18);
}

/* TOGGLE */
.auth-toggle{
    display:flex;
    background:#eee;
    border-radius:50px;
    padding:6px;
    margin-bottom:24px;
}

.auth-toggle a{
    flex:1;
    text-align:center;
    padding:10px 0;
    text-decoration:none;
    font-weight:600;
    color:#555;
    border-radius:50px;
}

.auth-toggle .active{
    background:#ff4d30;
    color:white;
}

/* FORM */
.auth-group{
    margin-bottom:16px;
}

.auth-group label{
    font-size:14px;
    margin-bottom:6px;
    display:block;
    color:#444;
}

.auth-group input{
    width:100%;
    padding:14px 16px;
    border:none;
    border-radius:50px;
    outline:none;
    background:white;
    box-shadow: inset 0 0 0 1px #ddd;
}

/* ROW */
.auth-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:13px;
    margin-bottom:18px;
}

.auth-row a{
    color:#ff4d30;
    text-decoration:none;
}

/* BUTTON */
.auth-btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:50px;
    background:#ff4d30;
    color:white;
    font-size:16px;
    cursor:pointer;
}

.auth-btn:hover{
    background:#e8432a;
}

/* ERROR */
.alert{
    background:#ffd6d6;
    color:#b40000;
    padding:10px;
    border-radius:12px;
    margin-bottom:14px;
    font-size:14px;
}

/* FOOTER */
.auth-footer{
    text-align:center;
    font-size:14px;
    margin-top:18px;
}

.auth-footer a{
    color:#ff4d30;
    font-weight:600;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="auth-card">

    <div class="auth-toggle">
        <a href="register.php">Register</a>
        <a href="login.php" class="active">Log in</a>
    </div>

    <?php if(!empty($err)) echo "<div class='alert'>$err</div>"; ?>

    <form method="post">
        <div class="auth-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="auth-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="auth-row">
            <label>
                <input type="checkbox"> Remember me
            </label>
            <a href="#">Forgot Password?</a>
        </div>

        <button class="auth-btn">Log In</button>
    </form>

    <div class="auth-footer">
        Don’t have an account? <a href="register.php">Sign up</a>
    </div>

</div>

</body>
</html>

