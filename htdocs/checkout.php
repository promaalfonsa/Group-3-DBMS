<?php
require 'db.php';
require 'functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$cart = cart_get();
if (empty($cart)) {
    echo "Cart empty";
    exit;
}

$first = reset($cart);
$restaurant_id = $first['restaurant_id'];
$total = cart_total();

/*
|--------------------------------------------------------------------------
| SHOW CHECKOUT FORM (GET)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Checkout | Khadok</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">

<style>
.checkout-card {
  max-width: 640px;
  margin: 40px auto;
  border-radius: 18px;
  box-shadow: 0 14px 32px rgba(0,0,0,0.08);
  border: none;
}
.checkout-title {
  font-size: 1.8rem;
  font-weight: 800;
}
.checkout-label {
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 6px;
}
.checkout-input {
  height: 48px;
  border-radius: 12px;
}
.checkout-textarea {
  border-radius: 12px;
}
.checkout-btn {
  background: #e21b70;
  border: none;
  border-radius: 999px;
  padding: 10px 22px;
}
.checkout-btn:hover {
  background: #c4175f;
}
.link-muted {
  color: #e21b70;
  font-weight: 600;
  text-decoration: none;
}
.link-muted:hover {
  text-decoration: underline;
}
.total-box {
  background: #fff5f8;
  border-radius: 14px;
  padding: 14px;
  margin-bottom: 20px;
  font-size: 1.1rem;
  font-weight: 700;
}
</style>
</head>

<body class="p-4">
<div class="container main-wrapper">

  <div class="card checkout-card">
    <div class="card-body p-4">

      <div class="mb-4 text-center">
        <div class="checkout-title">Checkout</div>
        <div class="text-muted">Complete your order</div>
      </div>

      <div class="total-box d-flex justify-content-between">
        <span>Total</span>
        <span>৳<?php echo number_format($total, 2); ?></span>
      </div>

      <form method="post" action="checkout.php">

        <div class="mb-3">
          <div class="checkout-label">Delivery Address</div>
          <input class="form-control checkout-input" name="address" required>
        </div>

        <div class="mb-3">
          <div class="checkout-label">Phone Number</div>
          <input class="form-control checkout-input" name="phone" required>
        </div>

        <div class="mb-3">
          <div class="checkout-label">Payment Method</div>
          <select class="form-select checkout-input" name="payment_method">
            <option value="Cash on Delivery">Cash on Delivery</option>
          </select>
        </div>

        <div class="mb-3">
          <div class="checkout-label">Delivery Note (optional)</div>
          <textarea
            name="delivery_note"
            class="form-control checkout-textarea"
            rows="3"
            placeholder="Call before delivery, Leave at gate, No doorbell"
          ></textarea>
        </div>

        <div class="mb-4">
          <div class="checkout-label">Promo Code (optional)</div>
          <input class="form-control checkout-input" name="promo_code">
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <a href="cart.php" class="link-muted">← Back to Cart</a>
          <button class="btn btn-primary checkout-btn">Place Order</button>
        </div>

      </form>

    </div>
  </div>

</div>
</body>
</html>
<?php
exit;
}

/*
|--------------------------------------------------------------------------
| PLACE ORDER (POST)
|--------------------------------------------------------------------------
*/

$address = trim($_POST['address']);
$phone = trim($_POST['phone']);
$payment_method = $_POST['payment_method'];
$promo_code = trim($_POST['promo_code'] ?? '');
$delivery_note = trim($_POST['delivery_note'] ?? '');

$discount_percent = 0;

// Promo code logic
if ($promo_code !== '') {
    $p = $mysqli->prepare("
        SELECT discount_percent, active, valid_from, valid_to
        FROM promotions
        WHERE code = ?
        LIMIT 1
    ");
    $p->bind_param('s', $promo_code);
    $p->execute();
    $pr = $p->get_result()->fetch_assoc();

    if ($pr && $pr['active']) {
        $today = date('Y-m-d');
        if (
            (empty($pr['valid_from']) || $pr['valid_from'] <= $today) &&
            (empty($pr['valid_to']) || $pr['valid_to'] >= $today)
        ) {
            $discount_percent = intval($pr['discount_percent']);
        }
    }
}

// Apply discount
if ($discount_percent > 0) {
    $total = $total - ($total * $discount_percent / 100);
}

$uid = current_user_id();
$status = 'pending';

$stmt = $mysqli->prepare("
    INSERT INTO orders
    (user_id, restaurant_id, total, address, phone, payment_method, delivery_note, status)
    VALUES (?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
    'iissssss',
    $uid,
    $restaurant_id,
    $total,
    $address,
    $phone,
    $payment_method,
    $delivery_note,
    $status
);

if (!$stmt->execute()) {
    echo "Order failed: " . $stmt->error;
    exit;
}

$order_id = $stmt->insert_id;

$ins = $mysqli->prepare("
    INSERT INTO order_items (order_id, menu_item_id, qty, price)
    VALUES (?,?,?,?)
");

foreach ($cart as $it) {
    $ins->bind_param(
        'iiid',
        $order_id,
        $it['menu_id'],
        $it['qty'],
        $it['price']
    );
    $ins->execute();
}

cart_clear();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Order Placed | Khadok</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container main-wrapper">

  <div class="card checkout-card p-4 text-center">
    <div class="alert alert-success">
      ✅ Order placed successfully!
      <br>
      <strong>Order ID:</strong> <?php echo $order_id; ?>
    </div>

    <div class="d-flex justify-content-center gap-3">
      <a href="orders.php" class="btn btn-primary checkout-btn">
        My Orders
      </a>
      <a href="index.php" class="btn btn-secondary">
        Back Home
      </a>
    </div>
  </div>

</div>
</body>
</html>
