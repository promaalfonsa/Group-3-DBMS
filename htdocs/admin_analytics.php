<?php
$pageTitle = "Analytics";
require 'admin_layout.php';

/* ======================
   SUMMARY TOTALS
====================== */
$totOrders = $mysqli->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];

$totUsers = $mysqli->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];

$totRestaurants = $mysqli->query("SELECT COUNT(*) AS c FROM restaurants")->fetch_assoc()['c'];

/* ======================
   LAST 7 DAYS
====================== */
$last7 = $mysqli->query("
  SELECT DATE(created_at) AS d,
         COUNT(*) AS cnt,
         SUM(total) AS revenue
  FROM orders
  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
  GROUP BY DATE(created_at)
  ORDER BY d ASC
")->fetch_all(MYSQLI_ASSOC);

/* ======================
   LAST 30 DAYS
====================== */
$last30 = $mysqli->query("
  SELECT DATE(created_at) AS d,
         COUNT(*) AS cnt,
         SUM(total) AS revenue
  FROM orders
  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
  GROUP BY DATE(created_at)
  ORDER BY d ASC
")->fetch_all(MYSQLI_ASSOC);

/* ======================
   MONTHLY FILTER
====================== */
$year  = intval($_GET['year'] ?? date('Y'));
$month = intval($_GET['month'] ?? date('m'));

$monthly = $mysqli->prepare("
  SELECT DATE(created_at) AS d,
         COUNT(*) AS cnt,
         SUM(total) AS revenue
  FROM orders
  WHERE YEAR(created_at) = ?
  AND MONTH(created_at) = ?
  GROUP BY DATE(created_at)
  ORDER BY d ASC
");

$monthly->bind_param('ii', $year, $month);
$monthly->execute();

$monthlyData = $monthly->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<style>
.stat-card{
  background:#fff;
  border-radius:20px;
  padding:24px;
  box-shadow:0 12px 28px rgba(0,0,0,.08);
  height:100%;
}

.stat-value{
  font-size:2rem;
  font-weight:800;
  color:#e21b70;
}

.stat-label{
  color:#666;
  margin-top:4px;
}

.section-card{
  background:#fff;
  border-radius:20px;
  padding:24px;
  box-shadow:0 12px 28px rgba(0,0,0,.08);
  margin-top:24px;
}

.section-title{
  font-size:1.3rem;
  font-weight:700;
  margin-bottom:20px;
}

.table{
  margin-bottom:0;
}
</style>

<h2 class="mb-4 fw-bold">Analytics Dashboard</h2>

<!-- STATS -->
<div class="row g-4">

  <div class="col-md-4">
    <div class="stat-card text-center">
      <div class="stat-value"><?php echo $totOrders; ?></div>
      <div class="stat-label">Total Orders</div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="stat-card text-center">
      <div class="stat-value"><?php echo $totUsers; ?></div>
      <div class="stat-label">Total Users</div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="stat-card text-center">
      <div class="stat-value"><?php echo $totRestaurants; ?></div>
      <div class="stat-label">Total Restaurants</div>
    </div>
  </div>

</div>

<!-- LAST 7 DAYS -->
<div class="section-card">
  <div class="section-title">Last 7 Days Performance</div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Date</th>
          <th>Orders</th>
          <th>Revenue</th>
        </tr>
      </thead>

      <tbody>
      <?php foreach($last7 as $r): ?>
        <tr>
          <td><?php echo $r['d']; ?></td>
          <td><?php echo $r['cnt']; ?></td>
          <td>৳<?php echo number_format($r['revenue'],2); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- LAST 30 DAYS -->
<div class="section-card">
  <div class="section-title">Last 30 Days Performance</div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Date</th>
          <th>Orders</th>
          <th>Revenue</th>
        </tr>
      </thead>

      <tbody>
      <?php foreach($last30 as $r): ?>
        <tr>
          <td><?php echo $r['d']; ?></td>
          <td><?php echo $r['cnt']; ?></td>
          <td>৳<?php echo number_format($r['revenue'],2); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MONTHLY -->
<div class="section-card">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="section-title mb-0">Monthly Analytics</div>
  </div>

  <form method="get" class="row g-3 mb-4">

    <div class="col-md-3">
      <select name="month" class="form-select">
        <?php for($m=1;$m<=12;$m++): ?>
          <option value="<?php echo $m; ?>"
            <?php if($month==$m) echo 'selected'; ?>>
            <?php echo date('F', mktime(0,0,0,$m,1)); ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="col-md-3">
      <select name="year" class="form-select">
        <?php for($y=date('Y');$y>=date('Y')-5;$y--): ?>
          <option value="<?php echo $y; ?>"
            <?php if($year==$y) echo 'selected'; ?>>
            <?php echo $y; ?>
          </option>
        <?php endfor; ?>
      </select>
    </div>

    <div class="col-md-3">
      <button class="btn btn-primary">
        View Analytics
      </button>
    </div>

  </form>

  <div class="table-responsive">
    <table class="table table-hover align-middle">

      <thead>
        <tr>
          <th>Date</th>
          <th>Orders</th>
          <th>Revenue</th>
        </tr>
      </thead>

      <tbody>

      <?php if(empty($monthlyData)): ?>
        <tr>
          <td colspan="3" class="text-center text-muted">
            No analytics found
          </td>
        </tr>
      <?php endif; ?>

      <?php foreach($monthlyData as $r): ?>
        <tr>
          <td><?php echo $r['d']; ?></td>
          <td><?php echo $r['cnt']; ?></td>
          <td>৳<?php echo number_format($r['revenue'],2); ?></td>
        </tr>
      <?php endforeach; ?>

      </tbody>

    </table>
  </div>

</div>

</div> <!-- CONTENT -->
</body>
</html>