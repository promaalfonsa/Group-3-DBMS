<?php
require 'functions.php';

$menu_id = intval($_POST['menu_id']);
$action = $_POST['action'] ?? '';

$cart = cart_get();

if (!isset($cart[$menu_id])) {
    header('Location: cart.php');
    exit;
}

if ($action === 'inc') {
    $cart[$menu_id]['qty']++;
} elseif ($action === 'dec') {
    $cart[$menu_id]['qty']--;
    if ($cart[$menu_id]['qty'] <= 0) {
        unset($cart[$menu_id]);
    }
}

cart_set($cart);

header('Location: cart.php');
exit;
