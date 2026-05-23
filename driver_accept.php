<?php
require 'db.php';
require 'functions.php';
if (!is_driver()) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $driver_id = current_user_id();
    // create delivery assigned to this driver
    $stmt = $mysqli->prepare("INSERT INTO deliveries (order_id, driver_id, assigned_at, status) VALUES (?,?,NOW(),'accepted')");
    $stmt->bind_param('ii',$order_id,$driver_id); $stmt->execute();
    // update order status to out_for_delivery
    $u = $mysqli->prepare("UPDATE orders SET status = 'out_for_delivery' WHERE id = ?");
    $u->bind_param('i',$order_id); $u->execute();
}
header('Location: driver_orders.php'); exit;
