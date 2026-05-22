<?php

require_once 'db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}
function is_admin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}
function is_driver() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'driver');
}
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function cart_add($item) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $id = $item['menu_id'];
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty'] += $item['qty'];
    } else {
        $_SESSION['cart'][$id] = $item;
    }
}
function cart_get() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function cart_set($cart) {
    $_SESSION['cart'] = $cart;
}

function cart_clear() {
    $_SESSION['cart'] = [];
}

function cart_total() {
    $total = 0;
    foreach (cart_get() as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return $total;
}


?>