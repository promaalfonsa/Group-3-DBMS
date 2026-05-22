<?php
$pageTitle = "Manage Coupons"; // change per page
require 'admin_layout.php';
if (!is_admin()) { header('Location: login.php'); exit; }

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $percent = intval($_POST['discount_percent']);
    $from = $_POST['valid_from'] ?: NULL;
    $to = $_POST['valid_to'] ?: NULL;

    $stmt = $mysqli->prepare("
        INSERT INTO promotions (code, discount_percent, active, valid_from, valid_to)
        VALUES (?, ?, 1, ?, ?)
    ");
    $stmt->bind_param('siss', $code, $percent, $from, $to);

    if (!$stmt->execute()) {
        $err = $stmt->error;
    }
}

$prom = $mysqli->query("
    SELECT * FROM promotions 
    ORDER BY id DESC
")->fetch_all(MYSQLI_ASSOC);

function badge($active) {
    return $active ? 'success' : 'secondary';
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin - Promotions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.admin-card {
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0,0,0,0.08);
  border: none;
}
.section-title {
  font-size: 1.6rem;
  font-weight: 800;
}
.form-control {
  border-radius: 12px;
}
</style>
</head>

<body class="p-4">
<div class="container main-wrapper">

  <h2 class="section-title mb-4">Promotions & Coupons</h2>

  <?php if ($err): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
  <?php endif; ?>

  <!-- CREATE PROMOTION -->
  <div class="card admin-card p-4 mb-4">
    <h5 class="mb-3">Create New Promotion</h5>

    <form method="post" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Promo Code</label>
        <input class="form-control" name="code" placeholder="FOOD50" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Discount (%)</label>
        <input class="form-control" type="number" min="1" max="100"
               name="discount_percent" placeholder="50" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Valid From</label>
        <input class="form-control" type="date" name="valid_from">
      </div>

      <div class="col-md-3">
        <label class="form-label">Valid To</label>
        <input class="form-control" type="date" name="valid_to">
      </div>

      <div class="col-12">
        <button class="btn btn-primary">Create Promotion</button>
      </div>
    </form>
  </div>

  <!-- EXISTING PROMOTIONS -->
  <div class="card admin-card p-4">
    <h5 class="mb-3">Existing Promotions</h5>

    <?php if (empty($prom)): ?>
      <div class="alert alert-info">No promotions created yet.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Code</th>
              <th>Discount</th>
              <th>Valid Period</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($prom as $p): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($p['code']); ?></strong></td>
              <td><?php echo intval($p['discount_percent']); ?>%</td>
              <td>
                <?php
                  echo $p['valid_from'] ? htmlspecialchars($p['valid_from']) : '—';
                  echo ' → ';
                  echo $p['valid_to'] ? htmlspecialchars($p['valid_to']) : '—';
                ?>
              </td>
              <td>
                <span class="badge bg-<?php echo badge($p['active']); ?>">
                  <?php echo $p['active'] ? 'Active' : 'Inactive'; ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
