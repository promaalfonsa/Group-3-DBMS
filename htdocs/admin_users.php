<?php
$pageTitle = "Users";
require 'admin_layout.php';

if (!is_admin()) {
    header('Location: login.php');
    exit;
}

$err = '';


/* =========================
   CREATE USER
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role  = $_POST['role'];
    $pass  = $_POST['password'];
    $phone = $_POST['phone'] ?? '';

    if (!$email || !$pass) {

        $err = 'Email and password required';

    } else {

        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("
            INSERT INTO users (name,email,password_hash,role,phone)
            VALUES (?,?,?,?,?)
        ");

        $stmt->bind_param(
            'sssss',
            $name,
            $email,
            $hash,
            $role,
            $phone
        );

        if (!$stmt->execute()) {
            $err = $stmt->error;
        }
    }
}


/* =========================
   DELETE USER
========================= */
if (isset($_GET['delete'])) {

    $did = intval($_GET['delete']);

    $d = $mysqli->prepare("DELETE FROM users WHERE id=?");
    $d->bind_param('i', $did);
    $d->execute();

    header('Location: admin_users.php');
    exit;
}


/* =========================
   SEARCH
========================= */
$search = trim($_GET['search'] ?? '');

$searchSql = '';

if ($search) {

    $safe = '%' . $mysqli->real_escape_string($search) . '%';

    $searchSql = "
        AND (
            name LIKE '$safe'
            OR email LIKE '$safe'
        )
    ";
}


/* =========================
   FETCH USERS
========================= */


/* DELIVERY STAFF FIRST */
$drivers = $mysqli->query("
    SELECT
        id,
        name,
        email,
        created_at
    FROM users
    WHERE role='driver'
    $searchSql
    ORDER BY created_at DESC
")->fetch_all(MYSQLI_ASSOC);


/* RESTAURANT OWNERS SECOND */
$restaurants = $mysqli->query("
    SELECT
        u.id,
        u.name,
        u.email,
        r.name AS restaurant_name
    FROM users u
    LEFT JOIN restaurants r
        ON r.user_id = u.id
    WHERE u.role='restaurant'
    $searchSql
    ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);


/* CONSUMERS LAST */
$normal_users = $mysqli->query("
    SELECT
        id,
        name,
        email,
        created_at
    FROM users
    WHERE role='user'
    $searchSql
    ORDER BY created_at DESC
")->fetch_all(MYSQLI_ASSOC);

?>

<!doctype html>
<html>

<head>

<meta charset="utf-8">

<title>Admin - Users</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="/assets/css/style.css">

<style>

.admin-card{
    border-radius:16px;
    box-shadow:0 12px 28px rgba(0,0,0,0.08);
    border:none;
}

.role-badge{
    background:#e21b70;
}

.table th{
    background:#f8f9fa;
}

.search-btn{
    background:#111827;
    border:none;
}

.search-btn:hover{
    background:#000;
}

</style>

</head>

<body class="p-4">

<div class="container main-wrapper">

    <h2 class="section-title mb-4">
        User Management
    </h2>


    <!-- ERROR -->
    <?php if ($err): ?>

        <div class="alert alert-danger">
            <?php echo htmlspecialchars($err); ?>
        </div>

    <?php endif; ?>


    <!-- CREATE USER -->
    <div class="card admin-card p-4 mb-4">

        <h5 class="mb-3">
            Create New User
        </h5>

        <form method="post" class="row g-3">

            <input type="hidden" name="create_user" value="1">

            <div class="col-md-6">
                <input
                    class="form-control"
                    name="name"
                    placeholder="Full Name"
                >
            </div>

            <div class="col-md-6">
                <input
                    class="form-control"
                    name="email"
                    placeholder="Email"
                    required
                >
            </div>

            <div class="col-md-6">
                <input
                    class="form-control"
                    name="password"
                    placeholder="Password"
                    required
                >
            </div>

            <div class="col-md-6">
                <input
                    class="form-control"
                    name="phone"
                    placeholder="Phone"
                >
            </div>

            <div class="col-md-6">

                <select class="form-select" name="role">

                    <option value="user">
                        Consumer
                    </option>

                    <option value="restaurant">
                        Restaurant Owner
                    </option>

                    <option value="driver">
                        Delivery Staff
                    </option>

                    <option value="admin">
                        Admin
                    </option>

                </select>

            </div>

            <div class="col-md-6">

                <button class="btn btn-primary w-100">
                    Create User
                </button>

            </div>

        </form>

    </div>


    <!-- SEARCH -->
    <div class="card admin-card p-3 mb-4">

        <form method="GET" class="row g-2">

            <div class="col-md-10">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by name or email..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >

            </div>

            <div class="col-md-2">

                <button class="btn search-btn text-white w-100">
                    Search
                </button>

            </div>

        </form>

    </div>



    <!-- =========================
         DELIVERY STAFF
    ========================== -->
    <h4 class="mt-4 mb-3">
        Delivery Staff
    </h4>

    <div class="card admin-card mb-4">

        <table class="table table-hover mb-0">

            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>

            <?php foreach ($drivers as $u): ?>

            <tr>

                <td>
                    <?php echo $u['id']; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($u['name']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($u['email']); ?>
                </td>

                <td>

                    <a
                        class="btn btn-sm btn-danger"
                        href="admin_users.php?delete=<?php echo $u['id']; ?>"
                        onclick="return confirm('Delete user?')"
                    >
                        Delete
                    </a>

                </td>

            </tr>

            <?php endforeach; ?>

        </table>

    </div>



    <!-- =========================
         RESTAURANT OWNERS
    ========================== -->
    <h4 class="mb-3">
        Restaurant Owners
    </h4>

    <div class="card admin-card mb-4">

        <table class="table table-hover mb-0">

            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Restaurant</th>
                <th>Action</th>
            </tr>

            <?php foreach ($restaurants as $u): ?>

            <tr>

                <td>
                    <?php echo $u['id']; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($u['name']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($u['email']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($u['restaurant_name'] ?? '-'); ?>
                </td>

                <td>

                    <a
                        class="btn btn-sm btn-danger"
                        href="admin_users.php?delete=<?php echo $u['id']; ?>"
                        onclick="return confirm('Delete user?')"
                    >
                        Delete
                    </a>

                </td>

            </tr>

            <?php endforeach; ?>

        </table>

    </div>



    <!-- =========================
         CONSUMERS
    ========================== -->
    <h4 class="mb-3">
        Consumers
    </h4>

    <div class="card admin-card">

        <table class="table table-hover mb-0">

            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>

            <?php foreach ($normal_users as $u): ?>

            <tr>

                <td>
                    <?php echo $u['id']; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($u['name']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($u['email']); ?>
                </td>

                <td>

                    <a
                        class="btn btn-sm btn-danger"
                        href="admin_users.php?delete=<?php echo $u['id']; ?>"
                        onclick="return confirm('Delete user?')"
                    >
                        Delete
                    </a>

                </td>

            </tr>

            <?php endforeach; ?>

        </table>

    </div>

</div>

</body>
</html>