<?php
// submit_rating.php
require 'db.php';
require 'functions.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }

$user_id = current_user_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$restaurant_id = intval($_POST['restaurant_id'] ?? 0);
$menu_item_id  = intval($_POST['menu_item_id'] ?? 0); // optional
$order_id      = intval($_POST['order_id'] ?? 0);     // optional
$rating        = intval($_POST['rating'] ?? 0);
$comment       = trim($_POST['comment'] ?? '');

if ($rating < 1 || $rating > 5) {
    echo 'Invalid rating value.'; exit;
}

// Verify purchase: either user bought that menu item OR bought from that restaurant
$allowed = false;
if ($menu_item_id > 0) {
    $q = $mysqli->prepare("SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.menu_item_id = ? AND o.user_id = ? LIMIT 1");
    $q->bind_param('ii', $menu_item_id, $user_id);
    $q->execute();
    if ($q->get_result()->fetch_assoc()) $allowed = true;
} elseif ($restaurant_id > 0) {
    $q = $mysqli->prepare("SELECT id FROM orders WHERE restaurant_id = ? AND user_id = ? LIMIT 1");
    $q->bind_param('ii', $restaurant_id, $user_id);
    $q->execute();
    if ($q->get_result()->fetch_assoc()) $allowed = true;
}

if (!$allowed) {
    echo 'You can only leave a review after purchasing from this restaurant or item.'; exit;
}

// Check existing rating for this user + target (menu_item => item rating; otherwise restaurant rating)
if ($menu_item_id > 0) {
    $chk = $mysqli->prepare("SELECT id FROM ratings WHERE user_id = ? AND menu_item_id = ? LIMIT 1");
    $chk->bind_param('ii', $user_id, $menu_item_id);
} else {
    $chk = $mysqli->prepare("SELECT id FROM ratings WHERE user_id = ? AND restaurant_id = ? AND (menu_item_id IS NULL OR menu_item_id = 0) LIMIT 1");
    $chk->bind_param('ii', $user_id, $restaurant_id);
}
$chk->execute();
$exist = $chk->get_result()->fetch_assoc();

if ($exist) {
    // update
    $rid = intval($exist['id']);
    $u = $mysqli->prepare("UPDATE ratings SET rating = ?, comment = ?, created_at = NOW() WHERE id = ?");
    $u->bind_param('isi', $rating, $comment, $rid);
    if ($u->execute()) {
        header('Location: restaurant.php?id=' . ($restaurant_id ?: ($_GET['id'] ?? 0)));
        exit;
    } else {
        echo 'Failed to update review: ' . $u->error; exit;
    }
} else {
    // insert
    if ($menu_item_id > 0) {
        $ins = $mysqli->prepare("INSERT INTO ratings (user_id, restaurant_id, menu_item_id, order_id, rating, comment, created_at) VALUES (?,?,?,?,?,?,NOW())");
        $ins->bind_param('iiiiss', $user_id, $restaurant_id, $menu_item_id, $order_id, $rating, $comment);
    } else {
        $ins = $mysqli->prepare("INSERT INTO ratings (user_id, restaurant_id, order_id, rating, comment, created_at) VALUES (?,?,?,?,?,NOW())");
        $ins->bind_param('iiiis', $user_id, $restaurant_id, $order_id, $rating, $comment);
    }
    if ($ins->execute()) {
        header('Location: restaurant.php?id=' . ($restaurant_id ?: ($_GET['id'] ?? 0)));
        exit;
    } else {
        echo 'Failed to save review: ' . $ins->error; exit;
    }
}
