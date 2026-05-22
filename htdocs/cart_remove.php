<?php
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$menu_id = intval($_POST['menu_id'] ?? 0);

$cart = cart_get();

if ($menu_id && isset($cart[$menu_id])) {
    unset($cart[$menu_id]);   // 🔥 remove item
    cart_set($cart);          // 🔥 save cart
}

header('Location: cart.php');
exit;
