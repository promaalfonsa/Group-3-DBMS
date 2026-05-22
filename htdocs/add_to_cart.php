<?php
require 'functions.php';

$menu_id = intval($_POST['menu_id']);
$name = $_POST['name'];
$price = floatval($_POST['price']);
$qty = max(1, intval($_POST['qty'] ?? 1));
$restaurant_id = intval($_POST['restaurant_id']);

$cart = cart_get();

/* Prevent mixing restaurants */
if (!empty($cart)) {
    $first = reset($cart);
    if ($first['restaurant_id'] != $restaurant_id) {
        cart_clear();
    }
}

/* If item already exists → increase qty */
if (isset($cart[$menu_id])) {
    $cart[$menu_id]['qty'] += $qty;
} else {
    $cart[$menu_id] = [
        'menu_id' => $menu_id,
        'name' => $name,
        'price' => $price,
        'qty' => $qty,
        'restaurant_id' => $restaurant_id
    ];
}

$_SESSION['cart'] = $cart;


header('Location: cart.php');
exit;
