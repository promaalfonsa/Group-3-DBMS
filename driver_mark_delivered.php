<?php
require 'db.php';
require 'functions.php';
if (!is_driver()) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_id = intval($_POST['delivery_id']);
    $driver_id = current_user_id();
    // verify owner
    $check = $mysqli->prepare('SELECT order_id FROM deliveries WHERE id = ? AND driver_id = ? LIMIT 1');
    $check->bind_param('ii',$delivery_id,$driver_id); $check->execute(); $row = $check->get_result()->fetch_assoc();
    if ($row) {
        $order_id = $row['order_id'];
        $u = $mysqli->prepare("UPDATE deliveries SET status = 'delivered' WHERE id = ?");
        $u->bind_param('i',$delivery_id); $u->execute();
        $o = $mysqli->prepare("UPDATE orders SET status = 'delivered' WHERE id = ?");
        $o->bind_param('i',$order_id); $o->execute();
    }
}
header('Location: driver_orders.php'); exit;
