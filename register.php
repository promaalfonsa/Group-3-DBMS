<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $phone = $_POST['phone'] ?? '';

    if (!$email || !$pass) $err = "Email and password required";
    else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (name,email,password_hash,role,phone) VALUES (?,?,?,?,?)");
        $role = 'user';
        $stmt->bind_param('sssss', $name, $email, $hash, $role, $phone);
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = 'user';
            header('Location: index.php'); exit;
        } else $err = "Registration failed: " . $stmt->error;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register</title>

<style>
*{
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
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

/* TERMS */
.auth-terms{
    font-size:13px;
    margin-bottom:18px;
}

.auth-terms a{
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
        <a href="register.php" class="active">Register</a>
        <a href="login.php">Log in</a>
    </div>

    <?php if(!empty($err)) echo "<div class='alert'>$err</div>"; ?>

    <form method="post">
        <div class="auth-group">
            <label>Full Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="auth-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>

        <div class="auth-group">
            <label>Phone</label>
            <input type="text" name="phone">
        </div>

        <div class="auth-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="auth-terms">
            <label>
                <input type="checkbox" required>
                I agree to <a href="#">Terms</a> & <a href="#">Privacy Policy</a>
            </label>
        </div>

        <button class="auth-btn">Registration</button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="login.php">Log in</a>
    </div>

</div>

</body>
</html>

