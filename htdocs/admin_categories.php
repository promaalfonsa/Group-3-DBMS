<?php
$pageTitle = "Food Categories"; // change per page
require 'admin_layout.php';

if (!is_admin()) { header('Location: login.php'); exit; }

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if ($name) {
        $stmt = $mysqli->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt->bind_param('s', $name);
        $stmt->execute();
    }
    header('Location: admin_categories.php');
    exit;
}

// Delete category
if (isset($_GET['delete'])) {
    $d = intval($_GET['delete']);
    $stmt = $mysqli->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->bind_param('i', $d);
    $stmt->execute();
    header('Location: admin_categories.php');
    exit;
}

// Fetch categories
$cats = $mysqli->query('SELECT * FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin – Categories</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.admin-card {
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0,0,0,0.08);
  border: none;
}
.admin-title {
  font-size: 1.8rem;
  font-weight: 800;
}
.action-btn {
  border-radius: 999px;
  padding: 6px 14px;
  font-size: 0.85rem;
}
</style>
</head>

<body class="p-4">
<div class="container main-wrapper">

  <!-- PAGE TITLE -->
  <h2 class="admin-title mb-4">Manage Categories</h2>

  

  <!-- ADD CATEGORY -->
  <div class="card admin-card p-4 mb-4">
    <h5 class="mb-3">Add New Category</h5>
    <form method="post" class="row g-2">
      <div class="col-md-8">
        <input
          class="form-control"
          name="name"
          placeholder="Category name (e.g. Pizza, Burgers)"
          required
        >
      </div>
      <div class="col-md-4">
        <button class="btn btn-primary action-btn w-100">Add Category</button>
      </div>
    </form>
  </div>

  <!-- CATEGORY LIST -->
  <div class="card admin-card p-4">
    <h5 class="mb-3">Existing Categories</h5>

    <?php if (empty($cats)): ?>
      <div class="alert alert-info">No categories created yet.</div>
    <?php else: ?>
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Name</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($cats as $c): ?>
          <tr>
            <td><?php echo htmlspecialchars($c['name']); ?></td>
            <td class="text-end">
              <a
                class="btn btn-danger btn-sm action-btn"
                href="admin_categories.php?delete=<?php echo $c['id']; ?>"
                onclick="return confirm('Delete this category?')"
              >
                Delete
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

  </div>

</div>
</body>
</html>
