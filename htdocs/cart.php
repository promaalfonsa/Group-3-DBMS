<?php
require 'functions.php';
$cart = cart_get();
$total = cart_total();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Your Cart</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">

<style>
.cart-card {
  border-radius: 18px;
  box-shadow: 0 14px 32px rgba(0,0,0,0.08);
  border: none;
}
.cart-title {
  font-size: 1.8rem;
  font-weight: 800;
}
.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #eee;
}
.cart-item:last-child { border-bottom: none; }
.cart-item-name { font-weight: 600; }
.cart-item-meta { font-size: 0.9rem; color: #777; }
.cart-price { font-weight: 700; }
.cart-summary {
  background: #fff5f8;
  border-radius: 14px;
  padding: 16px;
  margin-top: 20px;
}
.checkout-btn {
  background: #e21b70;
  border: none;
  border-radius: 999px;
  padding: 10px 20px;
}
.checkout-btn:hover { background: #c4175f; }
.continue-link {
  color: #e21b70;
  font-weight: 600;
  text-decoration: none;
}
.continue-link:hover { text-decoration: underline; }
.remove-btn {
  background: none;
  border: none;
  color: #dc3545;
  font-size: 1.2rem;
  cursor: pointer;
}
.remove-btn:hover { color: #a71d2a; }
</style>
</head>

<body class="p-4">
<div class="container main-wrapper">

  <div class="card cart-card p-4">
    <div class="mb-3">
      <div class="cart-title">Your Cart</div>
      <a class="continue-link" href="index.php">← Continue shopping</a>
    </div>

    <?php if (empty($cart)): ?>

      <div class="alert alert-info mt-3">
        Your cart is empty
      </div>

    <?php else: ?>

      <!-- CART ITEMS -->
      <?php foreach ($cart as $it): ?>
        <div class="cart-item">
          <div>
            <div class="cart-item-name"><?php echo htmlspecialchars($it['name']); ?></div>
           <div class="cart-item-meta d-flex align-items-center gap-2">

  <form method="post" action="cart_update.php" class="m-0">
    <input type="hidden" name="menu_id" value="<?php echo $it['menu_id']; ?>">
    <input type="hidden" name="action" value="dec">
    <button class="btn btn-sm btn-outline-secondary">−</button>
  </form>

  <strong><?php echo $it['qty']; ?></strong>

  <form method="post" action="cart_update.php" class="m-0">
    <input type="hidden" name="menu_id" value="<?php echo $it['menu_id']; ?>">
    <input type="hidden" name="action" value="inc">
    <button class="btn btn-sm btn-outline-secondary">+</button>
  </form>

  <span>× ৳<?php echo number_format($it['price'],2); ?></span>
</div>

          </div>

          <div class="d-flex align-items-center gap-3">
            <div class="cart-price">
              ৳<?php echo number_format($it['price'] * $it['qty'], 2); ?>
            </div>

            <!-- REMOVE BUTTON -->
            <form method="post" action="cart_remove.php" onsubmit="return confirm('Remove this item from cart?')">
              <input type="hidden" name="menu_id" value="<?php echo $it['menu_id']; ?>">
              <button class="remove-btn" title="Remove item">✕</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- SUMMARY -->
      <div class="cart-summary">
        <div class="d-flex justify-content-between align-items-center">
          <strong>Total</strong>
          <strong>৳<?php echo number_format($total, 2); ?></strong>
        </div>
      </div>

      <!-- ACTION -->
      <div class="mt-4 d-grid gap-2">
        <?php if (!is_logged_in()): ?>
          <a class="btn btn-primary checkout-btn" href="login.php">
            Login to Checkout
          </a>
        <?php else: ?>
          <a class="btn btn-success checkout-btn" href="checkout.php">
            Proceed to Checkout
          </a>
        <?php endif; ?>
      </div>

    <?php endif; ?>

  </div>

</div>
</body>
</html>
